<?php
try {
    ini_set('memory_limit', '-1');
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');   
    
    // define('MENU', (filter_input(INPUT_POST, 'menu', FILTER_SANITIZE_STRING)));

    // if (!MENU) {
    //     throw new Exception(null, 400);
    // }        

    include_once($_SERVER['DOCUMENT_ROOT'].'/class/yoona.php');
    $yoona = new Yoona();    

    // if (MENU == 'simri') {            
        // $data = json_encode($yoona->getArrayFromExcel('kb.xlsx', 10));    
    // } else if (MENU == 'm1m2') {
    //     $data = json_encode($yoona->getM1M2());    
    // } else if (MENU == 'jeonmang') {
        // $data = array();
        // $data['maemae'] = $yoona->getArrayFromExcel('kbmonth.xlsx', 25);
        // $data['jeonse'] = $yoona->getArrayFromExcel('kbmonth.xlsx', 26);
        // $data = json_encode($data);    
    // } else if (MENU == 'choongjeon') {
        // $data = array();
        // $data['maemae'] = $yoona->getArrayFromExcel('kb.xlsx', 4);
        // $data['jeonse'] = $yoona->getArrayFromExcel('kb.xlsx', 5);
        // $data = json_encode($data);
    // }   

    $data = $yoona->getMiboonyang();    

    echo '<pre>';
    print_r($data);
    echo '</pre>';
    exit();

    if (empty($data)) {
        throw new Exception(null, 400);
    }        

    echo $data;
} catch (Exception $e) {
    http_response_code($e->getCode());
}
?>
