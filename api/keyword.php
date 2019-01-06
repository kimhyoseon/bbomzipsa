<?php
try {            
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');            

    define('KEYWORD', (filter_input(INPUT_POST, 'keyword', FILTER_SANITIZE_STRING)));    

    if (!KEYWORD) throw new Exception(null, 400);    

    require_once './naver/restapi.php';

    $config = parse_ini_file("./naver/sample.ini");

    $api = new RestApi($config['API_KEY'], $config['SECRET_KEY'], $config['CUSTOMER_ID'], $config['CLIENT_ID'], $config['CLIENT_SECRET']);

    $result = array();

    // 키워드 검색 api
    $keywordstool = $api->GET("https://api.naver.com/keywordstool", array(
        'hintKeywords' => KEYWORD,        
        'showDetail' => 1
    ));    
    
    if (!empty($keywordstool) && !empty($keywordstool['keywordList'])) {
        $result['keywordstool'] = $keywordstool['keywordList'][0];
    }

    // 네이버쇼핑 검색 api    
    $shopping = $api->GET("https://openapi.naver.com/v1/search/shop.json", array(
        'query' => KEYWORD,        
        'display' => 40,
    ));    

    if (!empty($shopping) && !empty($shopping['items'])) {
        $result['shopping']['total'] = $shopping['total'];
        $result['shopping']['lprice'] = 10000000000;
        $result['shopping']['hprice'] = 0;
        $result['shopping']['hotkeyword'] = array();

        foreach ($shopping['items'] as $value) {
            if ($result['shopping']['lprice'] > $value['lprice']) $result['shopping']['lprice'] = $value['lprice'];  
            if ($result['shopping']['hprice'] < $value['hprice']) $result['shopping']['hprice'] = $value['hprice'];  
            
            if (!empty($value['title'])) {
                $value['title'] = strip_tags($value['title']);
                $value['title'] = str_replace(array('_', ',', '/', '(', ')', '[', ']'), ' ', $value['title']);                
                $titles = explode(' ', $value['title']);
                
                if (!empty($titles)) {
                    $result['shopping']['hotkeyword'] = array_merge($result['shopping']['hotkeyword'], $titles);                    
                }
            }
        }

        $result['shopping']['hotkeyword'] = array_count_values($result['shopping']['hotkeyword']);
        unset($result['shopping']['hotkeyword']['']);
        arsort($result['shopping']['hotkeyword']);
        $result['shopping']['hotkeyword'] = array_slice($result['shopping']['hotkeyword'], 0, 10);
    }    

    # 연관 검색어
    require_once './class/parser.php';
    
    $parser = new Parser();    
    $xpath = $parser->load("https://search.shopping.naver.com/search/all.nhn?query=".KEYWORD."&cat_id=&frm=NVSHATC");    
    $relKeywords = $xpath->query("//div[@class='co_relation_srh']/ul/li/a");

    if (!empty($relKeywords)) {
        $result['relkeyword'] = array();

        foreach ($relKeywords as $value) {
            if ($value->nodeValue) {
                $result['relkeyword'][] = trim($value->nodeValue);
            }
        }
    }

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code($e->getCode());
}
?>