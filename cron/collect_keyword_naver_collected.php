<?php
/**
 * 네이버 top 500 키워드 수집 후 정보 획득
 */
try {
    ini_set("display_errors", 1);
    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');

    // 수집된 키워드 가져오기
    $keywordsBest = file_get_contents('C:/dev/crawler/log/naverkeyword.json');
    $keywordsBest = json_decode($keywordsBest, true);

    $keywordsBest = array_unique($keywordsBest);

    require_once '../api/naver/restapi.php';
    require_once '../api/naver/NaverShopping.php';
    require_once '../class/curl_async.php';

    $curlAsync = new CurlAsync();

    if (empty($keywordsBest)) throw new Exception(null, 400);

    $collectRelkeywords = $keywordsBest;
    $total = 0;

    foreach (array_chunk($collectRelkeywords, 5) as $keywordChunk) {
        foreach ($keywordChunk as $keyword) {
            // 뽐집사 서버에 호출..
            $curlAsync->$keyword(array(
                // 'url' => 'http://localhost/api/keyword.php',
                'url' => 'http://ppomzipsa.com/api/keyword.php',
                'post' => array(
                    'keyword' => $keyword,
                    'refresh' => 1,
                )
            ));

            sleep(2);
        }

        foreach ($keywordChunk as $keyword) {
            $colletResult = $curlAsync->$keyword();
            echo $colletResult.PHP_EOL;
            $total++;

            $colletResult = json_decode($colletResult, true);

            // 실패시
            if (empty($colletResult['id'])) {
                echo '[Fail]'.$keyword.PHP_EOL;
            }
        }

        sleep(1);
    }

    echo "[{$total}개의 키워드 수집완료.".PHP_EOL;
} catch (Exception $e) {
    echo $e->getCode().PHP_EOL;
    echo $e->getMessage().PHP_EOL;
}
?>