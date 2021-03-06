<?php
/**
 * selenium으로 추출한 네이버 트렌드 쇼핑인사이트 BEST 500 키워드 추출 결과를 DB에 삽입
 */
ini_set("display_errors", 1);
ini_set('max_execution_time', '0');
ini_set('memory_limit', '-1');

$accountDb = parse_ini_file("../config/db.ini");

require_once '../class/pdo.php';

$keywordsBest = file_get_contents('/home/dev/crawler/log/naverkeyword.json');
$keywordsBest = json_decode($keywordsBest, true);

$keywordsBest = array_unique($keywordsBest);

if (!empty($keywordsBest)) {
    echo sizeof($keywordsBest).'개 저장 시작'.PHP_EOL;
    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

    foreach (array_chunk($keywordsBest, 20) as $keywordChunk) {
        if (!empty($keywordChunk)) {
            print_r($keywordChunk);

            $keywordsExist = $db->column("SELECT keyword FROM keywords WHERE keyword IN (:keywords)", array('keywords' => $keywordChunk));

            foreach ($keywordChunk as $relKeyword) {
                if (empty($relKeyword)) continue;
                if (!empty($keywordsExist) && in_array($relKeyword, $keywordsExist)) continue;

                $reulst = $db->query("INSERT INTO keywords (keyword) VALUES(?)", array($relKeyword));
                echo $reulst.PHP_EOL;
            }
        }

        sleep(1);
    }
}

$db->CloseConnection();
?>