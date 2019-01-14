<?php
try {
    ini_set('memory_limit', '-1');
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');

    define('DEBUG', filter_input(INPUT_GET, 'debug'));

    if (DEBUG == true) define('KEYWORD', (filter_input(INPUT_GET, 'keyword', FILTER_SANITIZE_STRING)));
    else define('KEYWORD', (filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING)));

    if (!KEYWORD) {
        throw new Exception(null, 400);
    }

    $accountDb = parse_ini_file("../config/db.ini");

    require_once '../class/pdo.php';
    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

    // // DB 조회
    $row = $db->row("SELECT * FROM keywords WHERE keyword=?", array(KEYWORD));

    if (!empty($row)) {
        if (!empty($row['modDate']) && $row['modDate'] > date('Y-m-d H:i:s', strtotime('-1 day'))) {
            $db->CloseConnection();
            echo json_encode($row);
            exit();
        }
    }

    if (DEBUG == true) print_r($row);

    // 새로 정보가져오기
    $accountNaver = parse_ini_file("../config/naver.ini");

    require_once './naver/restapi.php';
    require_once './naver/NaverShoppingCrawling.php';

    $api = new RestApi($accountNaver['API_KEY'], $accountNaver['SECRET_KEY'], $accountNaver['CUSTOMER_ID'], $accountNaver['CLIENT_ID'], $accountNaver['CLIENT_SECRET']);

    $result = array();

    // 키워드광고 api
    $keywordstool = $api->GET("https://api.naver.com/keywordstool", array(
        'hintKeywords' => KEYWORD,
        'showDetail' => 1
    ));

    if (DEBUG == true) print_r($keywordstool);

    if (empty($keywordstool) || empty($keywordstool['keywordList']) || empty($keywordstool['keywordList'][0])) {
        $db->CloseConnection();

        if (!empty($keywordstool['code'])) {
            throw new Exception(null, $keywordstool['code']);
        }

        throw new Exception(null, 204);
    }

    $result = $keywordstool['keywordList'][0];
    $result['monthlyAveMobileClkCnt'] = @ceil($result['monthlyAveMobileClkCnt']);
    $result['monthlyAvePcClkCnt'] = @ceil($result['monthlyAvePcClkCnt']);
    $result['monthlyMobileQcCnt'] = @filter_var($result['monthlyMobileQcCnt'], FILTER_SANITIZE_NUMBER_INT);
    $result['monthlyPcQcCnt'] = @filter_var($result['monthlyPcQcCnt'], FILTER_SANITIZE_NUMBER_INT);
    $result['monthlyQcCnt'] = @ceil($result['monthlyMobileQcCnt'] + $result['monthlyPcQcCnt']);
    $result['monthlyPcQcCnt'] = @ceil($result['monthlyPcQcCnt']);
    $result['monthlyQcCnt'] = @ceil($result['monthlyQcCnt']);
    $result['modDate'] = date('Y-m-d H:i:s');

    unset($result['relKeyword']);

    $result['trends'] = '';
    $result['season'] = 0;
    $result['seasonMonth'] = 0;

    // 키워드 검색 트렌드 api (https://developers.naver.com/docs/datalab/search/) - 하루 1000건 제한 - 월검색 500건 이상인 경우에만 수집
    if ($result['monthlyQcCnt'] > 500) {
        $keywordtrend = $api->POST("https://openapi.naver.com/v1/datalab/search", array(
            'startDate' => date('Y', strtotime('-1 year')).'-01-01',
            'endDate' => date('Y', strtotime('-1 year')).'-12-31',
            'timeUnit'=> 'month',
            "keywordGroups" => array(
                array(
                    'groupName' => KEYWORD,
                    'keywords' => array(KEYWORD)
                )
            )
        ));

        if (DEBUG == true) print_r($keywordtrend);

        if (!empty($keywordtrend) && !empty($keywordtrend['results']) && !empty($keywordtrend['results'][0]['data'])) {
            $trends = array();

            foreach ($keywordtrend['results'][0]['data'] as $trend) {
                $trends[] = @ceil($trend['ratio']);
            }

            if ((max($trends) - min($trends)) > 70) {
                $result['seasonMonth'] = array_keys($trends, max($trends))[0] + 1;

                if ($result['seasonMonth'] < 3 || $result['seasonMonth'] > 10) $result['season'] = 4;
                else if ($result['seasonMonth'] < 6) $result['season'] = 1;
                else if ($result['seasonMonth'] < 9) $result['season'] = 2;
                else if ($result['seasonMonth'] < 11) $result['season'] = 3;
            }

            $result['trends'] = implode(',', $trends);
        }
    }

    if (empty($result['trends'])) $result['trends'] = '';

    // 네이버쇼핑 크롤링
    $oNaverShoppingCrawling = new NaverShoppingCrawling();

    $dataNaverShopping = $oNaverShoppingCrawling->collectByKeyword(KEYWORD);

    if (DEBUG == true) print_r($dataNaverShopping);

    if (empty($dataNaverShopping)) {
        $db->CloseConnection();
        throw new Exception(null, 204);
    }

    $result = array_merge($result, $dataNaverShopping);

    # 새로운 키워드라면 쇼핑연관 키워드 수집
    // if (empty($row)) {
        if (!empty($result['relKeywords'])) {
            $keywordsExist = $db->column("SELECT keyword FROM keywords WHERE keyword IN (:keywords)", array('keywords' => $result['relKeywords']));

            if (DEBUG == true) print_r($keywordsExist);

            foreach ($result['relKeywords'] as $relKeyword) {
                if (empty($relKeyword)) continue;
                if (!empty($keywordsExist) && in_array($relKeyword, $keywordsExist)) continue;

                $db->query("INSERT INTO keywords (keyword) VALUES(?)", array($relKeyword));
            }
        }
    // }

    // 추가 데이터 정리
    $result['raceIndex'] = @round($result['totalItems'] / $result['monthlyQcCnt'], 4);
    $result['saleIndex'] = $result['avgReview'] + $result['avgSell'];
    $result['relKeywords'] = @implode(',', $result['relKeywords']);

    // 업데이트
    if (!empty($row) && !empty($row['id'])) {
        $dbUpdate = array_map(function ($key) {
            return $key.' = :'.$key;
        }, array_keys(array_filter($result), true));
        $dbUpdate = implode(', ', $dbUpdate);

        if (DEBUG == true) print_r("UPDATE keywords SET {$dbUpdate} WHERE id = {$row['id']}");

        $dbResult = $db->query("UPDATE keywords SET {$dbUpdate} WHERE id = {$row['id']}", array_filter($result));
    // 새로 추가
    } else {
        $dbName = array_keys(array_filter($result), true);
        $dbValues = array_map(function ($val) {
            return ':'.$val;
        }, $dbName);
        $dbName = implode(',', $dbName);
        $dbValues = implode(',', $dbValues);

        if (DEBUG == true) print_r("INSERT INTO keywords ({$dbName}) VALUES({$dbValues})");

        $dbResult = $db->query("INSERT INTO keywords ({$dbName}) VALUES({$dbValues})", array_filter($result));
    }

    $db->CloseConnection();
    echo json_encode($result);
} catch (Exception $e) {
    if (DEBUG == true) print_r('exception: '.$e->getCode());

    if (!empty($row) && !empty($row['id']) && $e->getCode() == 204) {
        $result['modDate'] = date('Y-m-d H:i:s', strtotime('+1 year'));
        $result['category'] = '9999';

        $dbUpdate = array_map(function ($key) {
            return $key.' = :'.$key;
        }, array_keys(array_filter($result), true));
        $dbUpdate = implode(', ', $dbUpdate);

        if (DEBUG == true) print_r("UPDATE keywords SET {$dbUpdate} WHERE id = {$row['id']}");

        $db->query("UPDATE keywords SET {$dbUpdate} WHERE id = {$row['id']}", array_filter($result));
    }

    http_response_code($e->getCode());
}
?>
