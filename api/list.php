<?php
try {
    ini_set('memory_limit', '-1');
    ini_set("display_errors", 1);
    ini_set("default_socket_timeout", 30);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');

    define('DEBUG', filter_input(INPUT_GET, 'debug'));

    if (DEBUG == true) define('PAGE', 1);
    else define('PAGE', filter_input(INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT));

    define('PAGING', 100);
    define('CATEGORY', filter_input(INPUT_POST, 'category', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY));

    if (!PAGE) throw new Exception(null, 400);

    $accountDb = parse_ini_file("../config/db.ini");

    require_once '../class/pdo.php';
    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

    $queryWheres = array();
    $queryParams = array(
        'offset' => (PAGE - 1) * PAGING,
        'paging' => PAGING,
    );

    if (!empty(CATEGORY)) {
        $queryWheres[] = 'category IN (:category)';
        $queryParams['category'] = CATEGORY;
    }

    if (!empty($queryWheres)) {
        $queryWheres = implode(' AND ', $queryWheres);
        $queryWheres = 'AND '.$queryWheres;
    } else {
        $queryWheres = '';
    }

    // DB 조회
    $list = $db->query("SELECT * FROM keywords WHERE raceIndex != 0 {$queryWheres} ORDER BY raceIndex ASC LIMIT :offset, :paging", $queryParams);

    if (empty($list)) throw new Exception(null, 204);

    $db->CloseConnection();
    echo json_encode($list);
} catch (Exception $e) {
    if (DEBUG == true) print_r('exception: '.$e->getCode());

    http_response_code($e->getCode());
}
?>