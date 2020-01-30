<?php

class Hanki {
    public $jshkData = array();
    public $jshkDataPrice = array(); // 가격표
    public $jshkDataSort = array(); // 정렬용 가중치
    public $jshkDataType = array(); // 반찬 분류
    public $jshkDataLots = array(); // 뽑기
    public $jshkDataSpecial = array(); // 특식
    public $jshkDataSoup = array(); // 국
    public $holiday = array(); // 공휴일 
    public $validDay = array(); // 배송일자
    public $allDay = array(); // 배송X일자
    public $year = '';
    public $month = '';    

    public function __construct() {        
        $this->dailyFile = $_SERVER['DOCUMENT_ROOT'].'/data/dailychan.json';
    }

    public function __destruct() {
    }       

/**
 * 식단 생성
 */
function getMenu() {    
    $this->year = date('Y', strtotime('+1 months'));
    $this->month = date('m', strtotime('+1 months')); // 계산할 월    
    $this->jshkData = include $_SERVER['DOCUMENT_ROOT'].'/class/PHPExcelDataJshk.php';     

    // 공휴일 가져오기
    // $this->getHoliday();

    // 유효 배송일 계산
    $this->getValidDay();

    // 정기배송용 반찬 분류
    $this->filterChan();

    // 날짜에 맞게 반찬세트 생성
    foreach ($this->validDay as $value) {
        $dayOfWeek = date('w', strtotime($value));

        $chans = $this->getBanchanSet($dayOfWeek);

        $sort = array();

        foreach ($chans as $v) {
            $sort[] = $this->jshkDataSort[$v];
        }  
        
        array_multisort($sort, SORT_DESC, $chans);
        
        // 목요일은 특별식
        if ($dayOfWeek == 4) {
            array_unshift($chans, $this->jshkDataSoup[0]);
            array_unshift($chans, $this->jshkDataSpecial[0]);    

            $c = array_shift($this->jshkDataSpecial);
            $this->jshkDataSpecial[] = $c;
            $c = array_shift($this->jshkDataSoup);
            $this->jshkDataSoup[] = $c;
        }
        
        $this->allDay[$value] = $chans;
    }   

    return $this->allDay;
}

/**
 * 한달간 유효일 가져오기
 */
function getValidDay() {   
    $lastDay = date("t", strtotime($this->year.$this->month.'01'));
    
    for ($i=1; $i <= $lastDay; $i++) { 
      $d = sprintf("%02d", $i);
      $ymd = $this->year.$this->month.$d;

      $this->allDay[$ymd] = false;
  
      // 공휴일 체크
      if (in_array($ymd, $this->holiday)) {          
          continue;
      }
  
      // 주말체크
      $weekDay = date('w', strtotime($ymd));
      
      if ($weekDay == 0 || $weekDay == 1) {          
          continue;
      }
  
      $this->validDay[] = $ymd;
    }
  }
  
  /**
   * 공휴일 가져오기
   */
  function getHoliday() {
    $ch = curl_init();
    $url = 'http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo'; /*URL*/
    $queryParams = '?' . urlencode('ServiceKey') . '=N5FupqoyFxqwcuyheudquznCCBi6IjOliKOT5DpHhTmomTde1WgpW4EkXwCZQ777CmYfcBbtgf%2FBuUqFwbEg2Q%3D%3D'; /*Service Key*/
    $queryParams .= '&' . urlencode('solYear') . '=' . $this->year; /*연*/
    // $queryParams .= '&' . urlencode('solMonth') . '=' . urlencode(date('m', strtotime('+1 months'))); /*월*/
    $queryParams .= '&' . urlencode('solMonth') . '=' . $this->month; /*월*/
  
    curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $response = curl_exec($ch);
    curl_close($ch);
  
    // Convert xml string into an object 
    $new = simplexml_load_string($response); 
      
    // Convert into json 
    $con = json_encode($new); 
      
    // Convert into associative array 
    $newArr = json_decode($con, true); 
  
    if ($newArr['body']['totalCount'] == 0) return $this->holiday;
  
    foreach ($newArr['body']['items']['item'] as $value) {
      if ($value['isHoliday'] != 'Y') continue;
  
      // 휴일의 다음날 배송X
      $before = date('Ymd', strtotime('+1 days', strtotime($value['locdate'])));
  
      if (!in_array($before, $this->holiday)) {
        $this->holiday[] = $before;
      }    
  
      if (!in_array($value['locdate'], $this->holiday)) {
        $this->holiday[] = $value['locdate'];
      }    
    }  
  }
  
  /**
   * 반찬 필터링 (정기배송용)
   */
  function filterChan() {  
    foreach ($this->jshkData as $key => $value) {
      // 일반반찬
      if (!empty($value['_regular'])) {        
        $this->jshkDataSort[$key] = $value['_regular'][0];
        $this->jshkDataPrice[$key] = $value['_regular'][1];
        $this->jshkDataType[$key] = $value['_regular'][2];
  
        // 가중치만큼 뽑기에 넣어줌
        for ($i=0; $i < $value['_regular'][0]; $i++) { 
          $this->jshkDataLots[] = $key;
        }
      } else if (!empty($value['_special'])) {
        $this->jshkDataSpecial[] = $key;
      } else if (!empty($value['_soup'])) {
        $this->jshkDataSoup[] = $key;
      }
    }
  
    // 뽑기 섞기
    shuffle($this->jshkDataSpecial);
    shuffle($this->jshkDataSoup);
    shuffle($this->jshkDataLots);
  }
  
  /**
   * 세트반찬 6종 획득
   */
  function getBanchanSet($dayOfWeek) {
    $sets = array();
    $banWords = array(
      '멸치' => array('멸치'),
      '어묵' => array('어묵'),
      '두부' => array('두부'),
      '우엉연근' => array('우엉', '연근'),
      '콩' => array('검은콩', '땅콩'),
      '생무침' => array('오이', '깻잎'),
      '무' => array('무나물', '무생채'),
    );
    $bans = array();
  
    while (sizeof($sets) < 6) {        
      $chan = $this->jshkDataLots[array_rand($this->jshkDataLots)];
      $price = 0;
      
      if (in_array($chan, $sets)) continue; // 이미 있는 반찬인 경우
    
      // 월요일 또는 화요일인 경우 1번분류 반찬만 (야채X)
      if ($dayOfWeek == 2 || $dayOfWeek == 3) {
        if ($this->jshkDataType[$chan] == 2) continue;
      }
      
      // 중복반찬처리1
      if (!empty($bans)) {
        $isBan = false;
        foreach ($bans as $value) {
          if (strpos($chan, $value) !== false) $isBan = true;
        }
  
        if ($isBan) continue;
      }
  
      // 중복반찬처리2
      foreach ($banWords as $key => $value) {
        foreach ($value as $v) {
          if (strpos($chan, $v) !== false) $bans = array_merge($bans, $value);  
        }
      }
  
      $sets[] = $chan;
    }

    $soyTotal = 0;
    $spicyTotal = 0;
  
    // 가격 체크
    foreach ($sets as $value) {
      $price += $this->jshkDataPrice[$value];

      if (strpos($value, '간장') !== false) $soyTotal++;
      if (strpos($value, '매콤') !== false) $spicyTotal++;
    }

    if ($soyTotal > 2) {
        return $this->getBanchanSet($dayOfWeek);      
    }

    if ($spicyTotal == 0) {
        return $this->getBanchanSet($dayOfWeek);      
    }
  
    // 21,200원까지만
    if ($price > 21200) {    
      return $this->getBanchanSet($dayOfWeek);    
    }
    
    // echo '<pre>';
    // print_r($price);
    // print_r($bans);  
    // echo '</pre>';

    // 뽑기에서 선택된 반찬을 제거
    foreach ($sets as $value) {
        if (($key = array_search($value, $this->jshkDataLots)) !== false) {                        
            unset($this->jshkDataLots[$key]);
        }
    }
  
    return $sets;
  }

  /**
   * 파일 저장
   */
  function saveJson($data) {  
      if (empty($data)) return false;

      $dailyChan = array();

      // 기존 정보 가져오기
      if (file_exists($this->dailyFile)) {            
        $dailyChan = json_decode(file_get_contents($this->dailyFile), true);
      }
      
      // 기존 정보에 추가
      foreach ($data as $key => $value) {
          if (!empty($dailyChan[$key])) return false;          

          $dailyChan[$key] = $value;
      }

      $fp = fopen($this->dailyFile, 'w');
      fwrite($fp, json_encode($dailyChan));
      fclose($fp);

      return true;
  }

    /**
     * 엑셀 옵션 다운로드
     */   
    function excelOption () {
        // 기존 정보 가져오기
        if (file_exists($this->dailyFile)) {            
            $dailyChan = json_decode(file_get_contents($this->dailyFile), true);
        }

        if (empty($dailyChan)) {
            exit('데이터가 없습니다.');
        }

        $data = array(array('날짜', '세트', '옵션가', '재고수량', '관리코드', '사용여부'));
        $dayKor = array('일', '월', '화', '수', '목', '금', '토');

        foreach ($dailyChan as $key => $value) {            
            if (!is_array($value)) continue;

            $dayOfWeek = date('w', strtotime($key));
            $m = ltrim(date('m', strtotime($key)), '0');
            $d = ltrim(date('d', strtotime($key)), '0');
            $ym = $m.'월'.$d.'일'.'('.$dayKor[$dayOfWeek].')';
            
            // $var = ltrim($var, '0');
            $data[] = array($ym, '1인세트(3개)', 0, 9999, '', 'Y');
            $data[] = array($ym, '2인세트(6개)', 7000, 9999, '', 'Y');

            if ($dayOfWeek == 4) {
                $data[] = array($ym, '패밀리세트(8개)', 14000, 9999, '', 'Y');
            }             
        }

        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';

        include_once('./class/PHPExcel.php');

        $cntRow = sizeof($data);                
        $lastChar = PHPExcel_Cell::stringFromColumnIndex((count($data[0]) - 1));

        $excel = new PHPExcel();        
        
        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}{$cntRow}")->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        
        // 셀 폭 최적화
        $widths = array(30, 30, 10, 10, 10, 10);
        foreach ($widths as $i => $w) {
            $excel->setActiveSheetIndex(0)->getColumnDimension($this->columnChar($i))->setWidth($w);
        }

        // 상품명 줄내림
        // $excel->getActiveSheet()->getStyle("H1:H{$cntRow}")->getAlignment()->setWrapText(true);  
        
        // 시트명 변경
        $excel->setActiveSheetIndex(0)->setTitle('Sheet0');

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');

        // 양식 설정
        ob_end_clean();        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');        

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="정성한끼정기배송옵션'.date('Ymd').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        // 다운로드
        $writer->save('php://output');
        die();
    }

    public function columnChar($i) {
        return chr(65 + $i); 
    }
}