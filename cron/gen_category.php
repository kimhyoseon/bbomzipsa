<?php
ini_set("display_errors", 1);
ini_set('max_execution_time', '0'); 
ini_set('memory_limit', '-1');
header('Content-Type: application/json');

$accountDb = parse_ini_file("../config/db.ini");

require_once '../class/pdo.php';

$db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

$categories = $db->query("SELECT category, categoryTexts FROM KEYWORDS WHERE 1 GROUP BY category, categoryTexts");

//echo '<pre>';print_r($categories);echo '</pre>';

$categoryId = array();
$result = array(
    'categoryDepthText' => array(),
    'categoryDepthId' => array(),
);

if (!empty($categories)) {
    foreach ($categories as $category) {        
        if (empty($category['categoryTexts'])) continue;
        if (empty($categoryId[$category['category']])) $categoryId[$category['category']] = $category['categoryTexts'];
    }

    foreach ($categoryId as $category => $categoryTexts) {
        $categoryTexts = explode(',', $categoryTexts);                
        $categoryTextsBefore = array();
        $parentId = 0;
        
        foreach ($categoryTexts as $caterogyText) {
            $categoryTextsBefore[] = $caterogyText;
            $caterogyTextBeforeString = implode(',', $categoryTextsBefore);

            if (!in_array($caterogyTextBeforeString, $categoryId)) $categoryId[] = $caterogyTextBeforeString;

            $currentcategoryId = array_search($caterogyTextBeforeString, $categoryId);

            if (empty($result['categoryDepthText'][$parentId])) $result['categoryDepthText'][$parentId] = array('전체');
            if (empty($result['categoryDepthText'][$parentId][$currentcategoryId])) $result['categoryDepthText'][$parentId][$currentcategoryId] = $caterogyText;

            if ($parentId != 0) {
                if (empty($result['categoryDepthId'][$parentId])) $result['categoryDepthId'][$parentId] = array();
                if (!in_array($currentcategoryId, $result['categoryDepthId'][$parentId])) $result['categoryDepthId'][$parentId][] = $currentcategoryId;
            }

            $parentId = $currentcategoryId;
        }
    }
}

// echo '<pre>';print_r($categoryId);echo '</pre>';
// echo '<pre>';print_r($result['categoryDepthId']);echo '</pre>';
// echo '<pre>';print_r($result['categoryDepthText']);echo '</pre>';
//echo json_encode($result);

$db->CloseConnection();

$fp = fopen('../data/category.json', 'w');
fwrite($fp, json_encode($result));
fclose($fp);
?>