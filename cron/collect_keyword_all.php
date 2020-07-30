<?php
/**
 * 수동으로 여러개의 키워드를 입력해서 수집
 */
try {
    ini_set("display_errors", 1);
    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');

    $KEYWORD = '요가링';
    $REL_KEYWORD = '종아리링,소프트요가링,요가용품,마사지링,종아리요가링,종아리마사지링,종아리,다리알링,요가도구,종아리알,종아리알빼는기구,요가링소프트,다리링,소프트링,요가링사이즈,종아리알빼는법,하드요가링,요가링하드,종아리알빼기,소프트종아리링,마사지요가링,리커버리링,요가링종아리,파스텔요가링,다리알빼기,다리요가링,종아리알링';

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
    $collectRelkeywords[] = $KEYWORD; // 자기자신도 포함
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