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
    public $chanCount = array(); // 모든 반찬카운트
    public $chanBanWeek = array(); // 같은 반찬이 같은 요일에 들어가지 않도록 처리
    public $year = '';
    public $month = '';
    public $try = 1;

    public function __construct() {
        $this->dailyFile = $_SERVER['DOCUMENT_ROOT'].'/data/dailychan.json';
        $this->dailyFileBakup = $_SERVER['DOCUMENT_ROOT'].'/data/dailychan.bak.json';
    }

    public function __destruct() {
    }

    /**
     * 식단 보기
     */
    function getMenuExist($data) {
      $this->year = $data['year'];
      $this->month = $data['month'];
      $this->month = sprintf("%02d", $this->month);
      $ymTarget = $this->year.$this->month;
      $result = array();

      // 기존 정보 가져오기
      if (file_exists($this->dailyFile)) {
        $menu = file_get_contents($this->dailyFile);

        if (!empty($menu)) {
          $menu = json_decode($menu, true);

          foreach ($menu as $key => $value) {
            if ($ymTarget == substr($key, 0, 6)) {
              $result[$key] = $value;
            }
          }
        }

        return $result;
      }
    }

    /**
     * 정기배송 주문가져오기
     */
    function getOrder($data) {
        $this->year = $data['year'];
        $this->month = $data['month'];
        $this->month = sprintf("%02d", $this->month);
        $ymd = $this->year.$this->month.'01';

        $accountDb = parse_ini_file("../config/db.ini");
        require_once dirname(__FILE__).'/../class/pdo.php';
        $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

        $list = $db->query("SELECT * FROM smartstore_order_hanki WHERE date >= ? OR date = '00000000' ORDER BY date ASC", array($ymd));

        if (!empty($list)) {
          foreach ($list as $key => $value) {
            $value['contents'] = unserialize($value['contents']);
            $list[$key] = $value;
          }
        }

        $db->CloseConnection();

        return $list;
    }

    /**
     * 주문자 검색
     */
    function getSearch($data) {
      $keyword = $data['keyword'];
      $offset = $data['offset'];

      if (empty($keyword)) return false;

      $accountDb = parse_ini_file("../config/db.ini");
      require_once dirname(__FILE__).'/../class/pdo.php';
      $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

      $list = $db->query("SELECT * FROM smartstore_order_jshk WHERE name like '%{$keyword}%' OR deposit like '%{$keyword}%' OR tel1 like '%{$keyword}%' ORDER BY date DESC LIMIT {$offset}, 1");

      $db->CloseConnection();

      if (empty($list)) return false;

      return $list[0];
  }

    /**
     * 정기배송 주문 등록
     */
    function addOrder($data) {
      if (empty($data)) return false;
      if (empty($data['date'])) return false;
      if (empty($data['menu'])) return false;

      $accountDb = parse_ini_file("../config/db.ini");
      require_once dirname(__FILE__).'/../class/pdo.php';
      $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

      // ,가 있을경우 처리
      $date = $data['date'];
      unset($data['date']);

      $dates = explode(',', $date);

      // date 수만큼 insert
      foreach ($dates as $value) {
        $data['date'] = trim($value);

        $dbName = array_keys($data, true);
        $dbValues = array_map(function ($val) {
            return ':'.$val;
        }, $dbName);
        $dbName = implode(',', $dbName);
        $dbValues = implode(',', $dbValues);

        $result = $db->query("INSERT INTO smartstore_order_jshk ({$dbName}) VALUES({$dbValues})", $data);
      }

      $db->CloseConnection();

      return $result;
    }

    /**
     * 정기배송 주문 수정
     */
    function editOrder($data) {
      if (empty($data)) return false;
      if (empty($data['id'])) return false;
      if (empty($data['date'])) return false;
      if (empty($data['menu'])) return false;
      if (empty($data['item_order_no'])) unset($data['item_order_no']);

      $id = $data['id'];
      unset($data['id']);

      $accountDb = parse_ini_file("../config/db.ini");
      require_once dirname(__FILE__).'/../class/pdo.php';
      $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

      $dbUpdate = array_map(function ($key) {
          return $key.' = :'.$key;
      }, array_keys($data, true));
      $dbUpdate = implode(', ', $dbUpdate);

      $result = $db->query("UPDATE smartstore_order_jshk SET {$dbUpdate} WHERE id = {$id}", $data);

      $db->CloseConnection();

      return $result;
    }

    /**
     * 정기배송 주문 삭제
     */
    function delOrder($data) {
      if (empty($data)) return false;
      if (empty($data['id'])) return false;

      $id = $data['id'];

      $accountDb = parse_ini_file("../config/db.ini");
      require_once dirname(__FILE__).'/../class/pdo.php';
      $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

      $result = $db->query("DELETE FROM smartstore_order_jshk WHERE id = {$id}");

      $db->CloseConnection();

      return $result;
    }

    /**
     * 식단 생성
     */
    function getMenu($data) {
        $this->year = $data['year'];
        $this->month = $data['month'];
        $this->month = sprintf("%02d", $this->month);
        // $this->year = date('Y', strtotime('+1 months'));
        // $this->month = date('m', strtotime('+1 months')); // 계산할 월
        // $this->month = '04';
        $this->jshkData = include $_SERVER['DOCUMENT_ROOT'].'/class/PHPExcelDataJshk.php';

        // 공휴일 가져오기
        $this->getHoliday();

        // 유효 배송일 계산
        $this->getValidDay();

        // 정기배송용 반찬 분류
        $this->filterChan();

        // 날짜에 맞게 반찬세트 생성
        foreach ($this->validDay as $value) {
            $dayOfWeek = date('w', strtotime($value));

            $chans = $this->getBanchanSet($dayOfWeek);

            // $sort = array();

            // foreach ($chans as $v) {
            //     $sort[] = $this->jshkDataSort[$v];
            // }

            // // 금, 토는 1인 메뉴에 나물도 들어갈 수 있도록 반대 sort
            // if ($dayOfWeek != 5 && $dayOfWeek != 6) {
            //   array_multisort($sort, SORT_DESC, $chans);
            // } else {
            //   array_multisort($sort, SORT_ASC, $chans);
            // }

            // 화, 금은 특별식
            if ($dayOfWeek == 2 || $dayOfWeek == 5) {
                // array_unshift($chans, $this->jshkDataSoup[0]);
                // array_unshift($chans, $this->jshkDataSpecial[0]);
                $chans[] = $this->jshkDataSpecial[0];
                $chans[] = $this->jshkDataSoup[0];

                // 특별식 카운트 추가
                // $this->chanCount[$this->jshkDataSpecial[0]]++;
                // $this->chanCount[$this->jshkDataSoup[0]]++;

                // if ($dayOfWeek == 5) {
                $c = array_shift($this->jshkDataSpecial);
                $this->jshkDataSpecial[] = $c;
                $c = array_shift($this->jshkDataSoup);
                $this->jshkDataSoup[] = $c;
                // }
            }

            $this->allDay[$value] = $chans;
        }

        // 모든 반찬카운트
        // echo '<pre>';
        // print_r($this->allDay);
        // print_r($this->chanCount);
        // print_r($this->chanBanWeek);
        // echo '</pre>';
        // exit();

        return $this->allDay;
    }

    /**
     * 반찬 최적화 (많은 수량의 반찬 대신 적은 수량의 반찬을 늘려준다)
     */
    function validChanCount() {

      // 전체 반찬카운트를 돌면서 수량이 많은 반찬은 수량이 적은 반찬으로 대체
      $adds = array();
      $minuss = array();
      foreach ($this->chanCount as $key => $value) {
        // 수량이 1개 이하라면
        if ($value < 2) {
          $adds[] = $key;
        } else // 수량이 4개 이상이라면
        if ($value > 3) {
          $minuss[] = $key;
        }
      }

      if (!empty($adds)) {
        foreach ($adds as $value) {
          $minus = array_pop($minuss);
          $this->chanChange($value, $minus);
        }

        echo '<pre>';
        print_r('----');
        print_r($adds);
        print_r($minuss);
        print_r('----');
        echo '</pre>';
      }
    }

    function chanChange($add, $minus, $dayOfWeekBan = 0) {
      $adds = array();
      $minuss = array();

      foreach ($this->allDay as $key => $value) {
        if (empty($value)) continue;

        if (empty($adds) && in_array($add, $value)) {
          $adds[$key] = array_search($add, $value);
        } else if (empty($minuss) && in_array($minus, $value)) {
          $minuss[$key] = array_search($minus, $value);
        }

        // minus를 빼고 add로 변경
        if ($dayOfWeekBan == 0 && !empty($adds) && !empty($minuss)) {
          $date = array_key_first($minuss);
          $dateNum = str_replace("-", " ", $key);
          $dayOfWeek = date('w', strtotime($dateNum));

          $this->allDay[$date][$minuss[$date]] = $add;

          // 수량 카운트 변경
          $this->chanCount[$add]++;
          $this->chanCount[$minus]--;
          $this->chanBanWeek[$dayOfWeek][$add]++;

          return True;

          echo '<pre>';
          print_r($adds);
          print_r($minuss);
          echo '</pre>';
          exit();
        }
        // 금지 주차가 있다면 금지
        // if ($dayOfWeekBan > 0) {
        //   $date = str_replace("-", " ", $key);
        //   $dayOfWeek = date('w', strtotime($value));
        //   if ($dayOfWeek == $dayOfWeekBan) continue;
        // }
      }
    }

    /**
     * 한달간 유효일 가져오기
     */
    function getValidDay() {
        $lastDay = date("t", strtotime($this->year.$this->month.'01'));

        for ($i=1; $i <= $lastDay; $i++) {
            $d = sprintf("%02d", $i);
            $ymd = $this->year.$this->month.$d;

            $this->allDay[$ymd] = 0;

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

    if (empty($newArr['body']['items']['item'][0])) {
        $newArr['body']['items']['item'] = array($newArr['body']['items']['item']);
    }

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

        $this->chanCount[$key] = 0; // 반찬선택 총 갯수
      } else if (!empty($value['_special'])) {
        $this->jshkDataSpecial[] = $key;
        // $this->chanCount[$key] = 0; // 반찬선택 총 갯수
      } else if (!empty($value['_soup'])) {
        $this->jshkDataSoup[] = $key;
        // $this->chanCount[$key] = 0; // 반찬선택 총 갯수
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
      '장조림' => array('메추리알조림', '계란장조림'),
      '부침' => array('계란말이', '분홍소세지'),
      '계란' => array('계란말이', '계란장조림'),
      '무침' => array('더덕무침', '고들빼기무침', '무말랭이무침', '오징어젓갈'),
    );

    $bans = array();

    if (empty($this->chanBanWeek[$dayOfWeek])) {
      $this->chanBanWeek[$dayOfWeek] = array();
    }

    $soyTotal = 0;
    $spicyTotal = 0;
    $fryTotal = 0;
    $boilTotal = 0;

    // 횟수 출력
    // echo $this->try;
    // $this->try++;

    if ($this->try > 200) {
      exit('Retry');
    }

    // echo array_sum(array_values($this->chanCount));
    // echo ',';

    $zeroChan = array();

    // 후반부에서는 선택받지 못한 반찬을 강제로 담아줌
    if (array_sum(array_values($this->chanCount)) > 40) {
      // 반찬이 0개인 친구들 먼저 3개 선택
      $shuffled = $this->shuffle_assoc($this->chanCount);

      foreach ($shuffled as $k => $v) {
        if ($v == 0) {
          $zeroChan[] = $k;
          if (sizeof($zeroChan) > 2) break;
        }
      }

      // shuffle($zeroChan);

      // 반찬이 0개인게 모자라면 1개인 친구들로 채워줍니다.
      if (sizeof($zeroChan) < 2) {
        foreach ($shuffled as $k => $v) {
          if ($v == 1) {
            $zeroChan[] = $k;
            if (sizeof($zeroChan) > 2) break;
          }
        }
      }

      // echo '<pre>';
      // print_r($zeroChan);
      // echo '</pre>';
      // exit();
    }

    // 2021-04-01 반찬 5종 + 쉐프반찬으로 변경
    while (sizeof($sets) < 5) {
      if (sizeof($zeroChan) > 0) {
        $chan = $zeroChan[0];
        array_shift($zeroChan);

        // echo '<pre>';
        // print_r($zeroChan);
        // echo '</pre>';
      } else {
        $chan = $this->jshkDataLots[array_rand($this->jshkDataLots)];
      }

      $price = 0;

      if (in_array($chan, $sets)) continue; // 이미 있는 반찬인 경우

      // 월요일 또는 화요일인 경우 1번분류 반찬만 (야채X)
      // if ($dayOfWeek == 2 || $dayOfWeek == 3) {
      //   if ($this->jshkDataType[$chan] == 2) continue;
      // }

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

      // 같은 요일에 같은 반찬이 1개 이상 들어가지 않도록
      if (empty($this->chanBanWeek[$dayOfWeek][$chan])) $this->chanBanWeek[$dayOfWeek][$chan] = 0;
      if ($this->chanBanWeek[$dayOfWeek][$chan] > 0) continue;

      // 같은부류반찬처리
      if ($soyTotal > 1 && strpos($chan, '간장') !== false) continue;
      if ($spicyTotal > 1 && strpos($chan, '매콤') !== false) continue;
      if ($fryTotal > 2 && strpos($chan, '볶음') !== false) continue;
      if ($boilTotal > 2 && strpos($chan, '조림') !== false) continue;

      // 너무 많이 선택된 반찬은 제외 (1반찬당 최대 3회)
      if ($this->chanCount[$chan] > 2) continue;

      // 특정반찬은 횟수제한 (최대 2회)
      if ($chan == '잡채' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '우엉조림' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '수제단호박샐러드' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '오징어젓갈' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '고들빼기무침' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '더덕무침' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '무말랭이무침' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '시금치무침' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '아삭이된장무침' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '오이부추무침' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '깻잎무침' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '땅콩조림' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '검은콩조림' && $this->chanCount[$chan] > 1) continue;

      // 같은부류반찬은 횟수제한 (최대 2~3회)
      if ($chan == '간장멸치견과류볶음' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '간장멸치볶음' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '매콤멸치견과류볶음' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '매콤멸치볶음' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '간장어묵볶음' && $this->chanCount[$chan] > 1) continue;
      if ($chan == '매콤어묵볶음' && $this->chanCount[$chan] > 2) continue;
      if ($chan == '어묵마늘쫑볶음' && $this->chanCount[$chan] > 1) continue;

      // 통과한 반찬은 담아주기
      $sets[] = $chan;

      // 담은 반찬 카운트
      if (strpos($chan, '간장') !== false) $soyTotal++;
      if (strpos($chan, '매콤') !== false) $spicyTotal++;
      if (strpos($chan, '볶음') !== false) $fryTotal++;
      if (strpos($chan, '조림') !== false) $boilTotal++;
    }

    // echo '<pre>';
    // print_r($soyTotal.'/'.$soyTotal.'/'.$soyTotal.'/'.$soyTotal);
    // echo '</pre>';

    // 가격 체크
    foreach ($sets as $value) {
      $price += $this->jshkDataPrice[$value];
    }

    // 21,200원까지만
    // if ($price > 21200) {
    //   return $this->getBanchanSet($dayOfWeek);
    // }

    // 골고루인지 확인 (조림, 볶음은 최소 1개씩 포함)
    if ($fryTotal < 1 || $boilTotal < 1) {
      return $this->getBanchanSet($dayOfWeek);
    }

    // 뽑기에서 선택된 반찬을 제거
    // foreach ($sets as $value) {
    //     if (($key = array_search($value, $this->jshkDataLots)) !== false) {
    //         unset($this->jshkDataLots[$key]);
    //     }
    // }

    foreach ($sets as $v) {
      $this->chanCount[$v]++;
      $this->chanBanWeek[$dayOfWeek][$v]++;
    }

    shuffle($sets);

    // 1인세트가 최대한 겹치지 않도록 순서변경 (2021-01-05)
    $lastChars = [];
    for ($i=0; $i<3 ; $i++) {
      $lastChar = mb_substr($sets[$i], -1);

      if (in_array($lastChar, $lastChars) == false) {
        $lastChars[] = $lastChar;
      // 중복되는 반찬종류인 경우
      } else {
        for ($j=3; $j<5 ; $j++) {
          $lastCharBench = mb_substr($sets[$j], -1);

          // 다른 반찬들 중 중복되지 않는 반찬이 있다면 순서를 바꿔주자
          if (in_array($lastCharBench, $lastChars) == false) {
            $tmpSet = $sets[$j];
            $sets[$j] = $sets[$i];
            $sets[$i] = $tmpSet;
            $lastChars[] = $lastCharBench;
            break;
          }
        }
      }
    }

    // 가장 마지막은 항상 오늘의반찬
    $sets[] = '오늘의반찬';

    // echo '<pre>';
    // print_r($sets);
    // echo '</pre>';
    // exit();

    return $sets;
  }

  /**
   * 파일 저장
   */
  function saveJson($data, $isOverWrite = false) {
      if (empty($data)) return false;

      $dailyChan = array();

      // 기존 정보 가져오기
      if (file_exists($this->dailyFile)) {
        $dailyChan = json_decode(file_get_contents($this->dailyFile), true);
      }

      // 기존 정보에 추가
      foreach ($data as $key => $value) {
          if ($isOverWrite == false && !empty($dailyChan[$key])) return false;

          $dailyChan[$key] = $value;
      }

      // 기존 파일 백업
      copy($this->dailyFile, $this->dailyFileBakup);

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

        $data = array(array('수령일', '세트', '옵션가', '재고수량', '관리코드', '사용여부'));
        $dayKor = array('일', '월', '화', '수', '목', '금', '토');

        foreach ($dailyChan as $key => $value) {
            if (!is_array($value)) continue;

            $dayOfWeek = date('w', strtotime($key));
            $m = ltrim(date('m', strtotime($key)), '0');
            $d = ltrim(date('d', strtotime($key)), '0');
            $ym = $m.'월'.$d.'일'.'('.$dayKor[$dayOfWeek].')';

            // $var = ltrim($var, '0');

            if (sizeOf($value) > 6) {
              $data[] = array($ym, '1인세트(3개)', 0, 20, '', 'Y');
              $data[] = array($ym, '1.5인세트(5개)', 8000, 20, '', 'Y');
              $data[] = array($ym, '2인세트(6개)', 8000, 20, '', 'Y');
              $data[] = array($ym, '패밀리세트(8개)', 16000, 20, '', 'Y');
            } else {
              $data[] = array($ym, '1인세트(3개)', 0, 20, '', 'Y');
              $data[] = array($ym, '2인세트(6개)', 8000, 20, '', 'Y');
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

    public function shuffle_assoc($list) {
      if (!is_array($list)) return $list;

      $keys = array_keys($list);
      shuffle($keys);
      $random = array();
      foreach ($keys as $key) {
        $random[$key] = $list[$key];
      }
      return $random;
    }
}