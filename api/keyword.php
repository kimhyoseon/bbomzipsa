<?php
try {
    ini_set('memory_limit', '-1');
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');

    // define('KEYWORD', '리프팅밴드');
    // define('DEBUG', 1);
    // define('REFRESH', 1);

    define('DEBUG', filter_input(INPUT_GET, 'debug'));
    define('REFRESH', filter_input(INPUT_POST, 'refresh'));

    if (DEBUG == true) define('KEYWORD', (filter_input(INPUT_GET, 'keyword', FILTER_SANITIZE_STRING)));
    else define('KEYWORD', (filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING)));

    $db = null;

    if (!KEYWORD) {
        throw new Exception(null, 400);
    }

    // 계정
    $accountNaver = parse_ini_file("../config/naver.ini");
    $accountDb = parse_ini_file("../config/db.ini");

    require_once '../class/pdo.php';
    require_once './naver/restapi.php';
    require_once './naver/NaverShopping.php';

    $apiNaver = new RestApi($accountNaver['API_KEY'], $accountNaver['SECRET_KEY'], $accountNaver['CUSTOMER_ID'], $accountNaver['CLIENT_ID'], $accountNaver['CLIENT_SECRET']);

    $oNaverShopping = new NaverShopping();
    $oNaverShopping->setKeyword(KEYWORD);
    $oNaverShopping->setDebug(DEBUG);
    $oNaverShopping->setRefresh(REFRESH);

    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

    // DB 조회
    $row = $db->row("SELECT * FROM keywords WHERE keyword=?", array(KEYWORD));
    $oNaverShopping->setData($row);

    // 상세보기 여부
    if (!empty($row)) {
        $hasDetail = $db->row("SELECT * FROM keywords_rel WHERE keywords_id=? LIMIT 1", array($row['id']));

        if (!empty($hasDetail)) $oNaverShopping->setData(array(
            'hasDetail' => 1
        ));
    }

    if (!$oNaverShopping->needUpdate()) {
        echo json_encode($oNaverShopping->getData());
        $db->CloseConnection();
        exit();
    }

    // 키워드 검색량 api
    if (!$oNaverShopping->requestKeywordSearchAd($apiNaver)) throw new Exception(null, $oNaverShopping->getCode());

    // 키워드 트렌드 api
    if (!$oNaverShopping->requestKeywordTrend($apiNaver)) throw new Exception(null, $oNaverShopping->getCode());

    // 네이버쇼핑 크롤링
    if (!$oNaverShopping->crawlingNaverShopping()) throw new Exception(null, $oNaverShopping->getCode());

    // 수집된 정보 획득
    $result = $oNaverShopping->getData();

    // 쇼핑연관 키워드 수집
    if (!empty($result['relKeywords'])) {
        $relKeywords = explode(',', $result['relKeywords']);

        $keywordsExist = $db->column("SELECT keyword FROM keywords WHERE keyword IN (:keywords)", array('keywords' => $relKeywords));

        foreach ($relKeywords as $relKeyword) {
            if (empty($relKeyword)) continue;
            if (!empty($keywordsExist) && in_array($relKeyword, $keywordsExist)) continue;

            $db->query("INSERT INTO keywords (keyword) VALUES(?)", array($relKeyword));
            if (DEBUG == true) {
                print_r('[New rel keyword]');
                print_r($relKeyword);
            }
        }
    }

    $result['modDate'] = date('Y-m-d H:i:s');
    $resultForDb = array_filter($result);
    unset($resultForDb['hasDetail']);

    // 업데이트
    if (!empty($row) && !empty($row['id'])) {
        // raceIndex 증감량
        if ($row['raceIndex'] > 0) {
            $raceIndexChange = $row['raceIndex'] - $resultForDb['raceIndex'];
            if ($raceIndexChange != 0) {
                $resultForDb['raceIndexChange'] = $row['raceIndex'] - $resultForDb['raceIndex'];
                $result['raceIndexChange'] = $resultForDb['raceIndexChange'];
            }
        }

        $dbUpdate = array_map(function ($key) {
            return $key.' = :'.$key;
        }, array_keys($resultForDb, true));
        $dbUpdate = implode(', ', $dbUpdate);

        if (DEBUG == true) {
            print_r('[Keyword updated]');
            print_r("UPDATE keywords SET {$dbUpdate} WHERE id = {$row['id']}");
            print_r($resultForDb);
        }

        $dbResult = $db->query("UPDATE keywords SET {$dbUpdate} WHERE id = {$row['id']}", $resultForDb);
    // 새로 추가
    } else {
        $dbName = array_keys($resultForDb, true);
        $dbValues = array_map(function ($val) {
            return ':'.$val;
        }, $dbName);
        $dbName = implode(',', $dbName);
        $dbValues = implode(',', $dbValues);

        if (DEBUG == true) {
            print_r('[Keyword inserted]');
            print_r("INSERT INTO keywords ({$dbName}) VALUES({$dbValues})");
            print_r($result);
        }

        $dbResult = $db->query("INSERT INTO keywords ({$dbName}) VALUES({$dbValues})", $resultForDb);
        $result['id'] = $db->lastInsertId();
    }

    $db->CloseConnection();
    echo json_encode($result);
} catch (Exception $e) {
    if (DEBUG == true) print_r('exception: '.$e->getCode());

    if (!empty($row) && !empty($row['id']) && $e->getCode() == 204 && $row['raceIndex'] <= 0) {
        // 수집된 정보 획득
        $result = $oNaverShopping->getData();
        $result['modDate'] = date('Y-m-d H:i:s', strtotime('+1 year'));
        $result['category'] = '9999';
        $result = array_filter($result);

        $dbUpdate = array_map(function ($key) {
            return $key.' = :'.$key;
        }, array_keys($result, true));
        $dbUpdate = implode(', ', $dbUpdate);

        if (DEBUG == true) {
            print_r('[Exception updated]');
            print_r("UPDATE keywords SET {$dbUpdate} WHERE id = {$row['id']}");
            print_r($result);
        }

        $db->query("UPDATE keywords SET {$dbUpdate} WHERE id = {$row['id']}", $result);
    }

    if (!empty($db)) $db->CloseConnection();
    http_response_code($e->getCode());
}
?>
