<?php
/**
 * 한개의 키워드에서 연관된 모든 키워드를 묶어서 검색 (옵션에 연관키워드 버튼이 생김)
 */
try {
    ini_set("display_errors", 1);
    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');

    if (empty($argv[1])) {
        throw new Exception(null, 400);
    }

    $KEYWORD = $argv[1];
    // $KEYWORD = '롤빗';

    $db = null;

    if (empty($KEYWORD)) {
        throw new Exception(null, 400);
    }

    // 계정
    $accountNaver = parse_ini_file("../config/naver.ini");
    $accountDb = parse_ini_file("../config/db.ini");

    require_once '../class/pdo.php';
    require_once '../api/naver/restapi.php';
    require_once '../api/naver/NaverShopping.php';
    require_once '../class/curl_async.php';

    $curlAsync = new CurlAsync();

    $apiNaver = new RestApi($accountNaver['API_KEY'], $accountNaver['SECRET_KEY'], $accountNaver['CUSTOMER_ID'], $accountNaver['CLIENT_ID'], $accountNaver['CLIENT_SECRET']);

    $oNaverShopping = new NaverShopping();
    $oNaverShopping->setKeyword($KEYWORD);

    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

    // 검색 키워드 정보 가져오기
    $curlAsync->$KEYWORD(array(
        'url' => 'http://localhost/api/keyword.php',
        //'url' => 'https://ppomzipsa.com/api/keyword.php',
        'post' => array(
            'keyword' => $KEYWORD,
        )
    ));

    $keywordResult = json_decode($curlAsync->$KEYWORD(), true);

    if (!$keywordResult || !$keywordResult['id']) {
        throw new Exception(null, 400);
    }

    // 연관 키워드 모두 수집
    $allRelkeywords = $oNaverShopping->requestKeywordSearchAdAll($apiNaver);

    // if (($key = array_search($KEYWORD, $allRelkeywords)) !== false) {
    //     unset($allRelkeywords[$key]);
    // }

    if (empty($allRelkeywords)) throw new Exception(null, $oNaverShopping->getCode());

    // 테스트 2개
    // $allRelkeywords = array_slice($allRelkeywords, 0, 2);

    $collectRelkeywords = $allRelkeywords;
    $addRelkeywords = array();
    $times = 0;
    $total = 0;

    while (true) {
        if (empty($collectRelkeywords)) break;
        // if ($times > 2) break;

        foreach (array_chunk($collectRelkeywords, 5) as $keywordChunk) {
            foreach ($keywordChunk as $keyword) {
                $curlAsync->$keyword(array(
                    'url' => 'http://localhost/api/keyword.php',
                    //'url' => 'https://ppomzipsa.com/api/keyword.php',
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

                // 수집된 정보를 검색 키워드와 연결 (네이버쇼핑검색인 경우만)
                if ($colletResult['id'] && $colletResult['hasMainShoppingSearch'] == 1) {
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

                // 쇼핑 연관 검색어를 추가로 수집
                // if (!empty($colletResult['relKeywords'])) {
                //     foreach (explode(',', $colletResult['relKeywords']) as $key => $relk) {
                //         if (!in_array($relk, $allRelkeywords)) {
                //             $allRelkeywords[] = $relk;
                //             $addRelkeywords[] = $relk;
                //         }
                //     }
                // }
            }

            sleep(1);
        }

        // 새로 수집된 키워드가 없다면 break;
        if (empty($addRelkeywords)) break;

        // 새로 수집된 키워드로 반복 수집
        $collectRelkeywords = $addRelkeywords;

        $times++;
    }

    $db->CloseConnection();

    echo "[{$KEYWORD}]와 연관된 {$total}개의 키워드 수집완료.".PHP_EOL;
    echo implode(', ', $allRelkeywords).PHP_EOL;
} catch (Exception $e) {
    if (!empty($db)) $db->CloseConnection();
    echo $e->getCode().PHP_EOL;
    echo $e->getMessage().PHP_EOL;
}
?>