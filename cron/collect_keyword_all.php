<?php
/**
 * 수동으로 여러개의 키워드를 입력해서 수집
 */
try {
    ini_set("display_errors", 1);
    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');

    $KEYWORD = '리프팅밴드';
    $REL_KEYWORD = '얼굴리프팅밴드,브이라인밴드,마사지도구,얼굴밴드,얼굴마사지기구,얼굴마사지기계,v라인리프팅밴드,얼굴땡김이,얼굴비대칭교정,턱밴드,v라인밴드,얼굴마사지,땡기미,이중턱밴드,브이라인,팔자주름없애기,브이밴드,땡김이,턱리프팅밴드,얼굴작아지는기구,얼굴교정기,안면비대칭,v라인,주걱턱교정기,턱교정기,턱교정,얼굴리프팅,얼굴붓기,사각턱교정,턱살밴드,턱살리프팅,얼굴주름리프팅,얼굴처짐방지,얼굴리프팅기구,v밴드,얼굴브이라인,이중턱없애기,얼굴탄력밴드,얼굴축소,얼굴경락,브이라인리프팅밴드,턱교정밴드,V라인리프팅밴드,얼굴주름리프팅 ,처진볼살리프팅,턱살리프팅,V라인리프팅밴드,턱살리프팅';

    $db = null;

    if (empty($KEYWORD)) {
        throw new Exception(null, 400);
    }

    if (empty($REL_KEYWORD)) {
        throw new Exception(null, 400);
    }

    // 계정
    $accountDb = parse_ini_file("../config/db.ini");

    require_once '../class/pdo.php';
    require_once '../api/naver/restapi.php';
    require_once '../api/naver/NaverShopping.php';
    require_once '../class/curl_async.php';

    $curlAsync = new CurlAsync();

    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

    // 검색 키워드 정보 가져오기
    $curlAsync->$KEYWORD(array(
        'url' => 'http://localhost/api/keyword.php',
        // 'url' => 'http://ppomzipsa.com/api/keyword.php',
        'post' => array(
            'keyword' => $KEYWORD,
        )
    ));

    $keywordResult = json_decode($curlAsync->$KEYWORD(), true);

    if (!$keywordResult || !$keywordResult['id']) {
        throw new Exception(null, 400);
    }

    // 연관 키워드 모두 수집
    $allRelkeywords = @explode(',', $REL_KEYWORD);

    if (empty($allRelkeywords)) throw new Exception(null, 400);

    // 테스트 2개
    // $allRelkeywords = array_slice($allRelkeywords, 0, 2);

    $collectRelkeywords = $allRelkeywords;
    $addRelkeywords = array();
    $total = 0;

    foreach (array_chunk($collectRelkeywords, 5) as $keywordChunk) {
        foreach ($keywordChunk as $keyword) {
            $curlAsync->$keyword(array(
                'url' => 'http://localhost/api/keyword.php',
                // 'url' => 'http://ppomzipsa.com/api/keyword.php',
                'post' => array(
                    'keyword' => $keyword,
                )
            ));
        }

        foreach ($keywordChunk as $keyword) {
            $colletResult = $curlAsync->$keyword();
            echo $colletResult.PHP_EOL;
            $total++;

            $colletResult = json_decode($colletResult, true);

            // 수집된 정보를 검색 키워드와 연결 (네이버쇼핑검색인 경우만 + 같은 카테고리만)
            if ($colletResult['id'] && $colletResult['hasMainShoppingSearch'] == 1 && $colletResult['category'] == $keywordResult['category']) {
                // 연결여부 확인
                $queryWheres = array(
                    'keywords_id = :keywords_id',
                    'keywords_rel_id = :keywords_rel_id'
                );

                $queryParams = array(
                    'keywords_id' => $keywordResult['id'],
                    'keywords_rel_id' => $colletResult['id'],
                );

                if (!empty($queryWheres)) {
                    $queryWheres = implode(' AND ', $queryWheres);
                } else {
                    $queryWheres = '';
                }

                $exist = $db->single("SELECT 1 FROM keywords_rel WHERE {$queryWheres}", $queryParams);

                if (empty($exist)) {
                    $dbName = array_keys($queryParams, true);
                    $dbValues = array_map(function ($val) {
                        return ':'.$val;
                    }, $dbName);
                    $dbName = implode(',', $dbName);
                    $dbValues = implode(',', $dbValues);

                    $dbResult = $db->query("INSERT INTO keywords_rel ({$dbName}) VALUES({$dbValues})", $queryParams);
                }

                // echo '<pre>';
                // print_r($dbResult);
                // echo '</pre>';
                // exit();
            }
        }

        sleep(1);
    }

    $db->CloseConnection();

    echo "[{$KEYWORD}]와 연관된 {$total}개의 키워드 수집완료.".PHP_EOL;
} catch (Exception $e) {
    if (!empty($db)) $db->CloseConnection();
    echo $e->getCode().PHP_EOL;
    echo $e->getMessage().PHP_EOL;
}
?>