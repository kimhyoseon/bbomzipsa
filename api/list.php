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
    
    define('CATEGORY', filter_input(INPUT_POST, 'category', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY));

    if (DEBUG == true) define('DETAILID', filter_input(INPUT_GET, 'detailId'));
    else define('DETAILID', filter_input(INPUT_POST, 'detailId', FILTER_SANITIZE_NUMBER_INT));        

    if (!PAGE) throw new Exception(null, 400);    

    $accountDb = parse_ini_file("../config/db.ini");

    require_once '../class/pdo.php';
    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

    $queryWheres = array();
    $queryParams = array();

    if (empty(DETAILID)) {
        define('PAGING', 100);

        /**
         * 선택된 카테고리가 있는 경우
         */
        if (!empty(CATEGORY)) {
            $queryWheres[] = 'category IN (:category)';
            $queryParams['category'] = CATEGORY;
        /**
         * 선택된 카테고리가 없는 경우 예외 카테고리 제외
         */
        } else { 
            require_once '../class/category.php';
            $category = new Category();        
            $exceptCategoryAll = $category->getExceptCategories();

            $queryWheres[] = "category NOT IN (:category)";
            $queryParams['category'] = $exceptCategoryAll;
        }
    } else {
        define('PAGING', 9999);                

        $relIds = $db->query("SELECT keywords_rel_id FROM keywords_rel WHERE keywords_id=?", array(DETAILID));                

        if (empty($relIds)) throw new Exception(null, 204);
        
        $relIdsArray = [];

        foreach ($relIds as $value) {
            $relIdsArray[] = $value['keywords_rel_id'];
        }                

        $queryWheres[] = "id IN (:id)";
        $queryParams['id'] = $relIdsArray;
    }    

    /**
     * ignore 키워드 제외
     */
    $queryWheres[] = "ignored != :ignored";
    $queryParams['ignored'] = 1;    

    if (!empty($queryWheres)) {
        $queryWheres = implode(' AND ', $queryWheres);
        $queryWheres = 'AND '.$queryWheres;
    } else {
        $queryWheres = '';
    }

    $queryParams['offset'] = (PAGE - 1) * PAGING;
    $queryParams['paging'] = PAGING;            

    // DB 조회
    $list = $db->query("SELECT * FROM keywords WHERE raceIndex != 0 AND saleIndex != 0 {$queryWheres} ORDER BY raceIndex ASC LIMIT :offset, :paging", $queryParams);    

    if (empty($list)) throw new Exception(null, 204);

    $db->CloseConnection();
    echo json_encode($list);
} catch (Exception $e) {
    if (DEBUG == true) print_r('exception: '.$e->getCode());

    http_response_code($e->getCode());
}
?>