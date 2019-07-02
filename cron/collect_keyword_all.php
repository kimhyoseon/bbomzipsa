<?php
/**
 * 수동으로 여러개의 키워드를 입력해서 수집
 */
try {
    ini_set("display_errors", 1);
    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');        
    
    $KEYWORD = '니플패치';
    $REL_KEYWORD = '유두패치,유두패드,유두밴드,유두커버,유두가리개,꼭지패치,꼭지패드,꼭지밴드,꼭지커버,꼭지가리개,니플패치,니플패드,니플밴드,니플커버,니플가리개,여자유두패치,여자유두패드,여자유두밴드,여자유두커버,여자유두가리개,여자꼭지패치,여자꼭지패드,여자꼭지밴드,여자꼭지커버,여자꼭지가리개,여자니플패치,여자니플패드,여자니플밴드,여자니플커버,여자니플가리개,실리콘유두패치,실리콘유두패드,실리콘유두밴드,실리콘유두커버,실리콘유두가리개,실리콘꼭지패치,실리콘꼭지패드,실리콘꼭지밴드,실리콘꼭지커버,실리콘꼭지가리개,실리콘니플패치,실리콘니플패드,실리콘니플밴드,실리콘니플커버,실리콘니플가리개';

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

            // 수집된 정보를 검색 키워드와 연결
            if ($colletResult['id']) {
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