<?php
/**
 * 지정된 키워드를 갱신
 */
ini_set("display_errors", 1);
ini_set('max_execution_time', '0');
ini_set('memory_limit', '-1');

$accountDb = parse_ini_file("/home/dev/ppomzipsa/config/db.ini");

require_once '/home/dev/ppomzipsa/class/pdo.php';
require_once '/home/dev/ppomzipsa/class/category.php';
require_once '/home/dev/ppomzipsa/class/curl_async.php';

$queryWheres = $queryParams = array();

/**
 * 제외 카테고리 설정
 */
$category = new Category();
$curlAsync = new CurlAsync();
$db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

$exceptCategoryAll = $category->getExceptCategories();

$queryWheres[] = "category NOT IN (:category)";
$queryParams['category'] = $exceptCategoryAll;

// 임시로 9999 카테고리 되살리기
// $queryWheres[] = "category IN (:category)";
// $queryParams['category'] = '9999';

// echo '<pre>';
// print_r($exceptCategoryAll);
// echo '</pre>';
// exit();

/**
 * 제외키워드 제외
 */
$queryWheres[] = "ignored != :ignored";
$queryParams['ignored'] = 1;

/**
 * 경쟁률 2 이하
 */
$queryWheres[] = "raceIndex < :raceIndex";
$queryParams['raceIndex'] = 2;

/**
 * 경쟁률 0이 아닌
 */
// $queryWheres[] = "raceIndex > :raceIndexNot";
// $queryParams['raceIndexNot'] = 0;

/**
 * 5000개 제한
 */
$queryParams['limit'] = 5;

/**
 * 쿼리 정리
 */
if (!empty($queryWheres)) {
    $queryWheres = implode(' AND ', $queryWheres);
} else {
    $queryWheres = '';
}

// DB 조회
$keywords = $db->query("SELECT id, keyword, modDate, raceIndex FROM keywords WHERE {$queryWheres} ORDER BY modDate ASC, raceIndex ASC LIMIT :limit", $queryParams);

echo '<pre>';
print_r($keywords);
echo '</pre>';
exit();

if (!empty($keywords)) {
    echo sizeof($keywords).'개 키워드 수집 시작'.PHP_EOL;

    foreach (array_chunk($keywords, 5) as $keywordChunk) {
        foreach ($keywordChunk as $keyword) {
            $id = $keyword['keyword'];
            $curlAsync->$id(array(
                //'url' => 'http://localhost/api/keyword.php',
                'url' => 'http://ppomzipsa.com/api/keyword.php',
                'post' => array(
                    'keyword' => $id,
                    'refresh' => 1,
                )
            ));
        }

        foreach ($keywordChunk as $keyword) {
            $id = $keyword['keyword'];
            $collectResultJSON = $curlAsync->$id();
            // $collectResult = json_decode($collectResultJSON, true);

            // $changeRaceIndex = $keyword['raceIndex'] - $collectResult['raceIndex'];

            // echo '<pre>';
            // print_r($keyword);
            // print_r($collectResult);
            // echo '</pre>';
            // exit();

            echo $id.PHP_EOL;
            echo $collectResultJSON.PHP_EOL;
        }

        sleep(2);
    }
}

$db->CloseConnection();
?>