<?php
$accountDb = parse_ini_file("../config/db.ini");

require_once '../class/pdo.php';
$db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

// DB 조회
$keywords = $db->column("SELECT keyword FROM KEYWORDS WHERE modDate IS NULL");

if (!empty($keywords)) {
     foreach ($keywords as $keyword) {
         print_r($keyword);
         $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"http://localhost/api/keyword.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "keyword={$keyword}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        print_r($server_output);

        curl_close ($ch);
        exit;
     }
}

$db->CloseConnection();
?>
