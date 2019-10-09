<?php
/**
 * 아파트 정보 수집
 * https://www.data.go.kr
 */
try {
    ini_set("display_errors", 1);
    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');         

    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(__FILE__));    
    
    // 지역코드 5자리 (시군구), 인구수 차트로 확인 가능    
    $lawdCd = '31200';
    $sigoongoo = '울산광역시 북구';

    // 수집시작일 (이번달)
    $date = date('Ym');        
    $dateEnd = null;
    // $dateEnd = '201501';    

    // 계정    
    $accountDb = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config/db.ini');

    require_once $_SERVER['DOCUMENT_ROOT'].'/class/pdo.php';

    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);    

    $data = array();
    $apt = array();

    include_once($_SERVER['DOCUMENT_ROOT'].'/class/yoona.php');
    $yoona = new Yoona();        

    // 지역이 수집된 가장 최근 년월 가져오기
    $row = $db->row("SELECT yoona_apt_deal.date FROM yoona_apt_deal JOIN yoona_apt ON yoona_apt_deal.yoona_apt_id = yoona_apt.id AND yoona_apt.code_sigoongoo = ? ORDER BY yoona_apt_deal.date DESC", array($lawdCd));

    if (!empty($row)) {
        $dateEnd = $row['date'];
    }    
    
    echo "$sigoongoo ({$dateEnd} ~ {$date}) 까지 수집시작.".PHP_EOL;      

    // while roof
    while (1) {
        /**
         * 매매
        */
        $sale = $yoona->getAptSale($lawdCd, $date);

        // 데이터가 없다면 끝.
        if ($sale == false) break;

        foreach ($sale as $value) {
            $data = $yoona->setAptData($data, $value, 'sale', $date);        
            
            // 아파트 id 획득 (없을 시 저장), 매매만 상세데이터가 조회 가능해서 아파트 정보를 저장할 수 있다.        
            $code = "{$value['지역코드']}||{$value['아파트']}";

            if (empty($apt[$code])) {            
                $apt[$code] = $yoona->getAptId($db, $value, $sigoongoo);
            }
        }

        /**
         * 전세
         */
        $jeonse = $yoona->getAptJeonse($lawdCd, $date);        
        
        // 데이터가 없다면 끝.
        if ($jeonse == false) break;

        foreach ($jeonse as $value) {
            $data = $yoona->setAptData($data, $value, 'jeonse', $date);        

            // 아파트 id 획득
            $code = "{$value['지역코드']}||{$value['아파트']}";

            if (empty($apt[$code])) {            
                $apt[$code] = $yoona->getAptId($db, $value, $sigoongoo);
            }
        }

        /**
         * 수집한 데이터 저장
         */
        if (!empty($data)) {
            foreach ($data as $code => $value) {
                // 아파트 ID가 있다면 ID로 DB에 저장 후 배열에서 삭제
                if (!empty($apt[$code])) {
                    if ($yoona->setAptDeal($db, $apt[$code], $value, $lawdCd) == true) {
                        unset($data[$code]);
                    } 
                }
            }
        }

        sleep(1);

        $date = date('Ym', strtotime("-1 months", strtotime("{$date}01")));        

        if (!empty($dateEnd)) {
            if ($date < $dateEnd) {
                $date = date('Ym', strtotime("+1 months", strtotime("{$date}01")));        
                break;
            }
        }        
    }

    // echo '잔여데이터 ({'.count($data).'}개)'.PHP_EOL;    
    print_r($data);
    // print_r($apt);     
    
    echo "[{$sigoongoo}({$lawdCd})] {$date}까지 수집완료.".PHP_EOL;    

    $db->CloseConnection();    
} catch (Exception $e) {
    if (!empty($db)) $db->CloseConnection();
    echo $e->getCode().PHP_EOL;
    echo $e->getMessage().PHP_EOL;
}