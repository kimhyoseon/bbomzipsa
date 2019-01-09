<?php
try {
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');

    define('KEYWORD', (filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING)));
    //define('KEYWORD', '바지');

    if (!KEYWORD) throw new Exception(null, 400);

    require_once './naver/restapi.php';
    require_once './naver/NaverShoppingCrawling.php';

    $config = parse_ini_file("./naver/sample.ini");

    $api = new RestApi($config['API_KEY'], $config['SECRET_KEY'], $config['CUSTOMER_ID'], $config['CLIENT_ID'], $config['CLIENT_SECRET']);

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

    // 네이버쇼핑 크롤링
    $oNaverShoppingCrawling = new NaverShoppingCrawling();

    $dataNaverShopping = $oNaverShoppingCrawling->collectByKeyword(KEYWORD);

    if (!$dataNaverShopping) {
        throw new Exception(null, 204);
    }

    $result = array_merge($result, $dataNaverShopping);

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code($e->getCode());
}
?>