<?php
ini_set("display_errors", 1);
ini_set('max_execution_time', '0'); 
ini_set('memory_limit', '-1');

$accountDb = parse_ini_file("../config/db.ini");

require_once '../class/pdo.php';
require_once '../class/curl_async.php';

$curlAsync = new CurlAsync();
$db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

// DB 조회
$keywords = $db->column("SELECT keyword FROM KEYWORDS WHERE category=0 LIMIT 99999999");

if (!empty($keywords)) {
    echo sizeof($keywords).'개 키워드 수집 시작'.PHP_EOL;    

    foreach (array_chunk($keywords, 5) as $keywordChunk) {        
        foreach ($keywordChunk as $keyword) {        
            $curlAsync->$keyword(array(
                'url' => 'http://localhost/api/keyword.php',
                'post' => array(
                    'keyword' => $keyword,
                )
            ));        
        }

        foreach ($keywordChunk as $keyword) { 
            echo $keyword.PHP_EOL;                       
            echo $curlAsync->$keyword().PHP_EOL;                    
        }

        sleep(1);
    }
}

$db->CloseConnection();
?>