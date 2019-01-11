<?php
try {
    ini_set("display_errors", 1);
    header("Access-Control-Allow-Origin: *");
    ini_set("default_socket_timeout", 30);
    header('Content-Type: application/json');

    //define('KEYWORD', (filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING)));
    define('KEYWORD', '또봇');

    if (!KEYWORD) throw new Exception(null, 400);

    $accountDb = parse_ini_file("../config/db.ini");

    require_once '../class/pdo.php';
    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

    // DB 조회
    $row = $db->row("SELECT * FROM KEYWORDS WHERE keyword=?", array(KEYWORD));

    if (!empty($row)) {
        if (!empty($row['modDate']) && $row['modDate'] > date('Y-m-d H:i:s', strtotime('-1 week'))) {
            $db->CloseConnection();
            echo json_encode($row);
            exit();
        }
    }

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

    if (empty($keywordstool) || empty($keywordstool['keywordList']) || $keywordstool['keywordList'][0]['monthlyMobileQcCnt'] < 1) {
        throw new Exception(null, 204);
    }

    $result = $keywordstool['keywordList'][0];
    unset($result['relKeyword']);

    // 키워드 검색 트렌드 api (https://developers.naver.com/docs/datalab/search/) - 하루 1000건 제한
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

    $result['trends'] = array();
    $result['season'] = 0;
    $result['seasonMonth'] = 0;

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

    // 네이버쇼핑 크롤링
    $oNaverShoppingCrawling = new NaverShoppingCrawling();

    $dataNaverShopping = $oNaverShoppingCrawling->collectByKeyword(KEYWORD);

    if (!$dataNaverShopping) {
        throw new Exception(null, 204);
    }

    $result = array_merge($result, $dataNaverShopping);

    # 쇼핑연관 키워드 수집
    if (!empty($result['relKeywords'])) {
        foreach ($result['relKeywords'] as $relKeyword) {
            $db->query("INSERT IGNORE INTO KEYWORDS (keyword) VALUES(?)", array($relKeyword));
        }
    }

    // 추가 데이터 정리
    $result['monthlyAveMobileClkCnt'] = @ceil($result['monthlyAveMobileClkCnt']);
    $result['monthlyAvePcClkCnt'] = @ceil($result['monthlyAvePcClkCnt']);
    $result['monthlyMobileQcCnt'] = @ceil($result['monthlyMobileQcCnt']);
    $result['monthlyPcQcCnt'] = @ceil($result['monthlyPcQcCnt']);
    $result['monthlyQcCnt'] = @ceil($result['monthlyMobileQcCnt'] + $result['monthlyPcQcCnt']);
    $result['raceIndex'] = @round($result['totalItems'] / $result['monthlyQcCnt'], 3);
    $result['saleIndex'] = $result['avgReview'] + $result['avgSell'];
    $result['relKeywords'] = @implode(',', $result['relKeywords']);
    $result['modDate'] = date('Y-m-d H:i:s');

    if (!empty($row)) {
        $dbUpdate = array_map(function ($val) {
            return $val.' = :'.$val;
        }, $result);
        $dbResult = $db->query("UPDATE KEYWORDS SET {$dbUpdate} WHERE keyword = :keyword", $result);
    } else {
        $dbName = array_keys($result);
        $dbValues = array_map(function ($val) {
            return ':'.$val;
        }, $dbName);
        $dbName = implode(',', $dbName);
        $dbValues = implode(',', $dbValues);

        $dbResult = $db->query("INSERT INTO KEYWORDS ({$dbName}) VALUES({$dbValues})", $result);
    }

    $db->CloseConnection();
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code($e->getCode());
}
?>
