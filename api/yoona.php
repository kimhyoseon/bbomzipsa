<?php
try {
    ini_set('memory_limit', '-1');
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');

    // include_once($_SERVER['DOCUMENT_ROOT'].'/class/yoona.php');
    // $yoona = new Yoona();
    // $data = json_encode($yoona->getAptRank('44133'));

    // echo '<pre>';
    // print_r($data);
    // echo '</pre>';
    // exit();

    define('MENU', (filter_input(INPUT_POST, 'menu', FILTER_SANITIZE_STRING)));
    define('EXTRA', (filter_input(INPUT_POST, 'extra', FILTER_SANITIZE_STRING)));

    if (!MENU) {
        throw new Exception(null, 400);
    }

    include_once($_SERVER['DOCUMENT_ROOT'].'/class/yoona.php');
    $yoona = new Yoona();

    if (MENU == 'simri') {
        $data = array();
        // 주간 KB 주간 매수우위
        $data['simri'] = $yoona->getArrayFromExcel('kb.xlsx', 8);
        // 주간 KB 주간 전세수급
        $data['jeonse'] = $yoona->getArrayFromExcel('kb.xlsx', 10);
        // 주간 KB 주간 매매증감, 전세증감
        $data['maemae1'] = $yoona->getArrayFromExcel('kb.xlsx', 2);
        $data['jeonse1'] = $yoona->getArrayFromExcel('kb.xlsx', 3);
        $data = json_encode($data);
    } else if (MENU == 'm1m2') {
        $data = json_encode($yoona->getM1M2());
    // 월간 KB부동산 매매가격 전망지수, 전세가격 전망지수(2016~)
    } else if (MENU == 'jeonmang') {
        $data = array();
        $data['maemae'] = $yoona->getArrayFromExcel('kbmonth.xlsx', 25);
        $data['jeonse'] = $yoona->getArrayFromExcel('kbmonth.xlsx', 26);
        $data = json_encode($data);
    // 주간 KB 주간 매매증감, 전세증감
    } else if (MENU == 'jeonmang2') {
        $data = array();
        $data['maemae'] = $yoona->getArrayFromExcel('kb.xlsx', 2);
        $data['jeonse'] = $yoona->getArrayFromExcel('kb.xlsx', 3);
        $data = json_encode($data);
    // 주간 KB 주간 매매지수, 전세지수
    } else if (MENU == 'choongjeon') {
        $data = array();
        $data['maemae'] = $yoona->getArrayFromExcel('kb.xlsx', 4);
        $data['jeonse'] = $yoona->getArrayFromExcel('kb.xlsx', 5);
        $data = json_encode($data);
    } else if (MENU == 'miboonyang') {
        $data = json_encode($yoona->getMiboonyang());
    } else if (MENU == 'miboonyang_detail') {
        $data = json_encode($yoona->getMiboonyangDetail(EXTRA));
    } else if (MENU == 'ingoo') {
        $data = json_encode($yoona->getIngoo());
    } else if (MENU == 'ingoo_detail') {
        $data = json_encode($yoona->getIngooDetail(EXTRA));
    } else if (MENU == 'ingooidong') {
        $data = json_encode($yoona->getIngooidong());
    } else if (MENU == 'ingooidong_detail') {
        $data = json_encode($yoona->getIngooidongDetail(EXTRA));
    } else if (MENU == 'age') {
        $data = json_encode($yoona->getArrayFromExcel('agemonth.xlsx', 0));
    } else if (MENU == 'apt') {
        $data = json_encode($yoona->getAptSigoongoo());
    } else if (MENU == 'apt_rank') {
        $data = json_encode($yoona->getAptRank(EXTRA));
    } else if (MENU == 'apt_detail') {
        $data = json_encode($yoona->getAptDetail(EXTRA));
    } else if (MENU == 'azzi') {
        $data = $yoona->getAzzi();
    }

    if (empty($data)) {
        throw new Exception(null, 400);
    }

    echo $data;
} catch (Exception $e) {
    http_response_code($e->getCode());
}
?>
