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
    // $code = ['41273', '안산시 단원구'];
    // $code = ['41430', '의왕시'];
    // $code = ['41465', '용인시 수지구'];
    // $code = ['41410', '군포시'];
    // $code = ['41190', '부천시'];

    // $code = ['30200', '대전광역시 유성구'];
    // $code = ['30170', '대전광역시 서구'];
    // $code = ['31200', '울산광역시 북구'];
    // $code = ['31110', '울산광역시 중구'];
    // $code = ['31140', '울산광역시 남구'];

    // $code = ['42110', '춘천시'];
    // $code = ['42130', '원주시'];
    // $code = ['42150', '강릉시'];
    // $code = ['43113', '청주시 흥덕구'];
    // $code = ['43130', '충주시'];
    // $code = ['44133', '천안시 서북구'];
    // $code = ['44200', '아산시'];
    // $code = ['45111', '전주시 완산구'];
    // $code = ['45130', '군산시'];
    // $code = ['45140', '익산시'];
    // $code = ['46150', '순천시'];
    // $code = ['46130', '여수시'];
    // $code = ['46110', '목포시'];
    // $code = ['26320', '포항시 북구'];
    // $code = ['47130', '경주시'];
    // $code = ['47190', '구미시'];
    // $code = ['47290', '경산시'];
    // $code = ['48121', '창원시 의창구'];
    // $code = ['48250', '김해시'];
    // $code = ['48170', '진주시'];
    // $code = ['48330', '양산시'];
    $code = ['48310', '거제시'];

    // 수집시작일 (이번달)
    // 5월 거래가 없으므로 4월로
    $date = date('Ym');
    // $date = '202104';

    // 계정
    $accountDb = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config/db.ini');

    require_once $_SERVER['DOCUMENT_ROOT'].'/class/pdo.php';

    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

    $data = array();
    $apt = array();

    include_once($_SERVER['DOCUMENT_ROOT'].'/class/yoona.php');
    $yoona = new Yoona();

    // 지역이 수집된 가장 최근 년월 가져오기
    $row = $db->row("SELECT yoona_apt_deal.date FROM yoona_apt_deal JOIN yoona_apt ON yoona_apt_deal.yoona_apt_id = yoona_apt.id AND yoona_apt.code_sigoongoo = ? ORDER BY yoona_apt_deal.date DESC LIMIT 1", array($code[0]));

    if (!empty($row)) {
        $dateEnd = date('Ym', strtotime('-5 months', strtotime($row['date'].'01')));
    } else {
        // 최대 10년
        $dateEnd = date('Y', strtotime('-10 years')).'01';
    }

    // $dateEnd = '201501';

    echo "$code[1] ({$dateEnd} ~ {$date}) 까지 수집시작.".PHP_EOL;
    // exit();

    // while roof
    while (1) {
        /**
         * 매매
        */
        $sale = $yoona->getAptSale($code[0], $date);

        // 데이터가 없다면 끝.
        if ($sale == false) break;

        foreach ($sale as $value) {
            $data = $yoona->setAptData($data, $value, 'sale', $date);

            // 아파트 id 획득 (없을 시 저장), 매매만 상세데이터가 조회 가능해서 아파트 정보를 저장할 수 있다.
            $codeName = "{$value['지역코드']}||{$value['아파트']}";

            if (empty($apt[$codeName])) {
                $apt[$codeName] = $yoona->getAptId($db, $value, $code[1]);
            }
        }

        /**
         * 전세
         */
        $jeonse = $yoona->getAptJeonse($code[0], $date);

        // 데이터가 없다면 끝.
        if ($jeonse == false) break;

        foreach ($jeonse as $value) {
            $data = $yoona->setAptData($data, $value, 'jeonse', $date);

            // 아파트 id 획득
            $codeName = "{$value['지역코드']}||{$value['아파트']}";

            if (empty($apt[$codeName])) {
                $apt[$codeName] = $yoona->getAptId($db, $value, $code[1]);
            }
        }

        /**
         * 수집한 데이터 저장
         */
        if (!empty($data)) {
            foreach ($data as $codeName => $value) {
                // 아파트 ID가 있다면 ID로 DB에 저장 후 배열에서 삭제
                if (!empty($apt[$codeName])) {
                    if ($yoona->setAptDeal($db, $apt[$codeName], $value, $code[0]) == true) {
                        unset($data[$codeName]);
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

    echo "[{$code[1]}({$code[0]})] {$date}까지 수집완료.".PHP_EOL;

    $db->CloseConnection();
} catch (Exception $e) {
    if (!empty($db)) $db->CloseConnection();
    echo $e->getCode().PHP_EOL;
    echo $e->getMessage().PHP_EOL;
}