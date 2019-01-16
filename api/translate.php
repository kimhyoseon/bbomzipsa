<?php
try {
    ini_set('memory_limit', '-1');
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');

    define('DEBUG', filter_input(INPUT_GET, 'debug'));

    // define('KEYWORD', '설화수에센셜립세럼4호');

    if (DEBUG == true) define('KEYWORD', (filter_input(INPUT_GET, 'keyword', FILTER_SANITIZE_STRING)));
    else define('KEYWORD', (filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING)));

    if (!KEYWORD) {
        throw new Exception(null, 400);
    }

    $accountNaver = parse_ini_file("../config/naver.ini");

    require_once './naver/restapi.php';

    $apiNaver = new RestApi($accountNaver['API_KEY'], $accountNaver['SECRET_KEY'], $accountNaver['CUSTOMER_ID'], $accountNaver['CLIENT_ID'], $accountNaver['CLIENT_SECRET']);

    $result = $apiNaver->POST("https://openapi.naver.com/v1/language/translate", array(
        'source' => 'ko',
        'target' => 'zh-CN',
        'text'=> KEYWORD,
    ));

    if (!empty($result) && !empty($result['message']) && !empty($result['message']['result']['translatedText']) && KEYWORD != $result['message']['result']['translatedText']) {
        $result['message']['result']['translatedText'] = @iconv("UTF-8", "EUC-CN", $result['message']['result']['translatedText']);
    }

    if (!empty($result['message']['result']['translatedText'])) {
        $result['message']['result']['translatedText'] = urlencode($result['message']['result']['translatedText']);
    } else {
        $result = $apiNaver->POST("https://openapi.naver.com/v1/language/translate", array(
            'source' => 'ko',
            'target' => 'en',
            'text'=> KEYWORD,
        ));
    }

    echo json_encode($result['message']['result']);
} catch (Exception $e) {
    http_response_code($e->getCode());
}
?>
