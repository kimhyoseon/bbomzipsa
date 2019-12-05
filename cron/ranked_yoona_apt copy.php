<?php
/**
 * 아파트 랭킹(보정값) 만들기 
 */
try {
    ini_set("display_errors", 1);
    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');         

    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(__FILE__));              

    // 지역번호
    $lawdCd = '30170';    

    // 수집기간
    $datePrev = '201905';
    $date = '201912';  

    // 계정    
    $accountDb = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config/db.ini');

    require_once $_SERVER['DOCUMENT_ROOT'].'/class/pdo.php';

    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);    

    $data = array();
    $apt = array();

    include_once($_SERVER['DOCUMENT_ROOT'].'/class/yoona.php');
    $yoona = new Yoona();       
    
    $queryString = '';
    $queryString .= 'SELECT yoona_apt_deal.*, ya.* FROM yoona_apt_deal ';    
    $queryString .= 'LEFT JOIN yoona_apt AS ya on ya.id = yoona_apt_deal.yoona_apt_id ';        
    $queryString .= 'WHERE yoona_apt_id IN (SELECT id FROM yoona_apt WHERE code_sigoongoo = ?) AND sale_price > 0 AND jeonse_price > 0 AND date >= ? AND date <= ? ';    
    // $queryString .= 'WHERE yoona_apt_id IN (SELECT id FROM yoona_apt WHERE code_sigoongoo = ?) AND sale_price > 0 AND date >= ? AND date <= ? ';    
    // $queryString .= 'GROUP BY yoona_apt_id, size; ';    
    // $queryString .= 'ORDER BY yoona_apt_id, date DESC; ';    
    
    $dealList = $db->query($queryString, array($lawdCd, $datePrev, $date));       
    $sort = [];
    
    // $upmyeondong = $count = [];
    // $upmyeondongPrice = $upmyeondongIndex =[];

    // // 아파트의 증감액을 동별 구분
    // foreach ($dealList as $key => $value) {
    //     $value['upmyeondong'] = trim($value['upmyeondong']);  
    //     $index = $value['yoona_apt_id'].$value['size'];
    //     if (empty($upmyeondongIndex[$index])) {
    //         $upmyeondongIndex[$index] = $value['sale_price'];
    //     } else {
    //         if (empty($upmyeondongPrice[$value['upmyeondong']])) {
    //             $upmyeondongPrice[$value['upmyeondong']] = 0;                
    //         }

    //         $gap = $upmyeondongIndex[$index] - $value['sale_price'];
    //         echo ("{$value['upmyeondong']} [{$value['date']}] {$value['name_apt']}({$value['size']}) {$gap}").PHP_EOL;
            
    //         $upmyeondongPrice[$value['upmyeondong']] += $gap;
    //         $upmyeondongIndex[$index] = $value['sale_price'];
    //     }
    // }

    // array_multisort(array_values($upmyeondongPrice), SORT_DESC, $upmyeondongPrice);    
    // print_r($upmyeondongPrice);                    
    // exit();

    // 평당가격으로 정렬
    foreach ($dealList as $key => $value) {
        $sort[] = $dealList[$key]['price_per_price'] = ceil($value['sale_price'] / $value['size']);         

        // 동별 정보
        $value['upmyeondong'] = trim($value['upmyeondong']);        
        if (empty($upmyeondong[$value['upmyeondong']])) {
            $upmyeondong[$value['upmyeondong']] = 0;
            $count[$value['upmyeondong']] = 0;
        }
        
        $upmyeondong[$value['upmyeondong']]++;
        $count[$value['upmyeondong']] += $value['sale_count'];
    }    

    foreach ($upmyeondong as $key => $value) {
        $upmyeondong[$key] = $count[$key] / $value;
    }

    array_multisort(array_values($upmyeondong), SORT_DESC, $upmyeondong);    
    print_r($upmyeondong);                
    exit();

    array_multisort($sort, SORT_DESC, $dealList);

    $sortedDealList = $sortedDealListId = [];    

    foreach ($dealList as $key => $value) {
        if (in_array($value['yoona_apt_id'], $sortedDealListId) == true) continue;
        
        $sortedDealListId[] = $value['yoona_apt_id'];        
        $sortedDealList[] = $value;        
    }    

    // 랭킹(보정값 만들기)
    $total = sizeof($sortedDealList);
    $top5pro = ceil($total * 0.05);
    $top10pro = ceil($total * 0.1);
    $top20pro = ceil($total * 0.2);
    $top30pro = ceil($total * 0.3);
    $top40pro = ceil($total * 0.4);
    $top60pro = ceil($total * 0.6);
    
    foreach ($sortedDealList as $key => $value) {        
        $point = 1.0;
        
        if ($key < $top5pro) {
            $point = 1.7;
        } else if ($key < $top10pro) {
            $point = 1.5;
        } else if ($key < $top20pro) {
            $point = 1.3;
        } else if ($key < $top30pro) {
            $point = 1.3;
        } else if ($key < $top40pro) {
            $point = 1.3;
        } else if ($key < $top60pro) {
            $point = 1.2;
        } else {
            $point = 1.1;
        }

        $sortedDealList[$key]['rank_index'] = $point;
    }    

    print_r($sortedDealList);            
    exit();

    foreach ($sortedDealList as $key => $value) {                
        $dbResult = $db->query("UPDATE yoona_apt SET rank_index={$value['rank_index']} WHERE id = {$value['yoona_apt_id']}");
    }    
    
    echo "[{$lawdCd}] {$date}기준 랭킹(보정값) 계산 완료.".PHP_EOL;    

    $db->CloseConnection();    
} catch (Exception $e) {
    if (!empty($db)) $db->CloseConnection();
    echo $e->getCode().PHP_EOL;
    echo $e->getMessage().PHP_EOL;
}