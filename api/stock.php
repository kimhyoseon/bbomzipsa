<?php
try {
    ini_set('memory_limit', '-1');
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');       

    // define('MENU', 'list');    
    
    define('MENU', (filter_input(INPUT_POST, 'menu', FILTER_SANITIZE_STRING)));    

    if (!MENU) {
        throw new Exception(null, 400);
    }   

    $aWeekAgo = date('Ymd', strtotime('-7 days'));    
    
    // 계정    
    $accountDb = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config/db.ini');
    require_once $_SERVER['DOCUMENT_ROOT'].'/class/pdo.php';
    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);
    
    if (MENU == 'list') {
        $result = $db->query("SELECT smartstore_stock.*, smartstore_order.sale_cnt, ROUND((smartstore_stock.amount / smartstore_order.sale_cnt) - smartstore_stock.period) AS remain FROM smartstore_stock LEFT JOIN (SELECT id, ROUND(SUM(sale_cnt) / 7, 1) AS sale_cnt FROM smartstore_order WHERE date >=? GROUP BY smartstore_order.id) AS smartstore_order ON smartstore_order.id = smartstore_stock.id ORDER BY title DESC", array($aWeekAgo));
    } else if (MENU == 'edit') {        
        $data = $_POST['data'];
        unset($data['id']);
        $dbUpdate = array_map(function ($key) {
            return $key.' = :'.$key;
        }, array_keys($data));
        $dbUpdate = implode(', ', $dbUpdate);       
        
        $result = array($dbUpdate,  $data);
        $result['result'] = $db->query("UPDATE smartstore_stock SET {$dbUpdate} WHERE id = {$_POST['data']['id']}", $data);        
    } else if (MENU == 'add') {        
        $data = $_POST['data'];        
        $dbName = array_keys($data);
        $dbValues = array_map(function ($val) {
            return ':'.$val;
        }, $dbName);
        $dbName = implode(',', $dbName);
        $dbValues = implode(',', $dbValues);       

        $result = array($dbName, $dbValues, $data);
        $result['result'] = $db->query("INSERT INTO smartstore_stock ({$dbName}) VALUES({$dbValues})", $data);        
    } else if (MENU == 'del') {        
        $data = $_POST['data'];                

        $result = array($data);
        $result['result'] = $db ->query("DELETE FROM smartstore_stock WHERE id = :id", $data);        
    }

    // echo '<pre>';
    // print_r($data);
    // echo '</pre>';
    // exit();

    if (empty($result)) {
        throw new Exception(null, 400);
    }        

    if (!empty($db)) $db->CloseConnection();     

    echo json_encode($result);
} catch (Exception $e) {
    if (!empty($db)) $db->CloseConnection(); 
    http_response_code($e->getCode());
}
?>
