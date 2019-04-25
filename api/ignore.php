<?php
try {            
    ini_set('memory_limit', '-1');
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');        
    
    define('DEBUG', filter_input(INPUT_GET, 'debug'));
    define('IDS', filter_input(INPUT_POST, 'ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY));        

    if (!IDS) throw new Exception(null, 400);

    $accountDb = parse_ini_file("../config/db.ini");

    require_once '../class/pdo.php';
    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

    $queryWheres = array();
    $queryParams = array();
    
    if (!empty(IDS)) {
        $queryWheres[] = "id IN (:id)";        
        $queryParams['id'] = IDS;
    }     

    if (!empty($queryWheres)) {
        $queryWheres = implode(' AND ', $queryWheres);        
    } else {
        $queryWheres = '';
    }    

    // DB 갱신
    $dbResult = $db->query("UPDATE keywords SET ignored=1 WHERE {$queryWheres}", $queryParams);    

    if ($dbResult != true) throw new Exception(null, 204);

    $db->CloseConnection();
    
    echo json_encode(array(
        'result' => $dbResult,
        'query' => $queryParams,
    ));
} catch (Exception $e) {
    if (DEBUG == true) print_r('exception: '.$e->getCode());

    http_response_code($e->getCode());
}
?>