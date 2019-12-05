<?php
/**
 * 아파트 랭킹(보정값) 만들기 
 */
try {
    ini_set("display_errors", 1);
    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');         

    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(__FILE__));       

    // 평균상승률
    $AvgInc = 20;

    // 지역번호    
    $lawdCd = '30170';
    // $lawdCd = '41430';

    // 수집기간
    $datePrev = '201609';
    $date = '201611'; 
    
    // // 지역번호
    // $lawdCd = '41465';    

    // // 수집기간
    // $datePrev = '201307';
    // $date = '201312';        

    // 지역번호
    // $lawdCd = '30170';    

    // // 수집기간
    // $datePrev = '201609';
    // $date = '201612';      

    // 계정    
    $accountDb = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config/db.ini');

    require_once $_SERVER['DOCUMENT_ROOT'].'/class/pdo.php';

    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);    

    $data = array();
    $apt = array();

    include_once($_SERVER['DOCUMENT_ROOT'].'/class/yoona.php');
    $yoona = new Yoona();       
    
    $queryString = '';
    $queryString .= 'SELECT yoona_apt_id, size, AVG(sale_price) AS sale_price, AVG(jeonse_price) AS jeonse_price, AVG(sale_count) AS sale_count, AVG(jeonse_count) AS jeonse_count, ya.* FROM yoona_apt_deal ';    
    $queryString .= 'LEFT JOIN yoona_apt AS ya on ya.id = yoona_apt_deal.yoona_apt_id ';        
    $queryString .= 'WHERE yoona_apt_id IN (SELECT id FROM yoona_apt WHERE code_sigoongoo = ?) AND sale_price > 0 AND jeonse_price > 0 AND date >= ? AND date <= ? ';    
    $queryString .= 'GROUP BY yoona_apt_id, size; ';    
    
    $dealList = $db->query($queryString, array($lawdCd, $datePrev, $date));       
    $sort = [];    
    
    foreach ($dealList as $key => $value) {
        // 평당가격으로 정렬
        $sort[] = $dealList[$key]['price_per_price'] = ceil($value['sale_price'] / $value['size']);                 
    }        

    array_multisort($sort, SORT_DESC, $dealList);

    $sortedDealList = $sortedDealListId = [];            

    foreach ($dealList as $key => $value) {
        if (in_array($value['yoona_apt_id'], $sortedDealListId) == true) continue;
        
        $sortedDealListId[] = $value['yoona_apt_id'];        
        $sortedDealList[] = $value;        
    }        

    // 랭킹
    $upmyeondong = [];
    $upmyeondongPrice = [];
    $upmyeondongPrices = [];
    $upmyeondongPriceMax = [];
    $upmyeondongCount = [];
    $upmyeondongPoint = [];    

    $total = sizeof($sortedDealList);
    $quart1 = ceil($total * 0.05);
    $quart2 = ceil($total * 0.15);
    $quart3 = ceil($total * 0.6);  
    
    // echo($total).PHP_EOL;   
    // echo($quart1).PHP_EOL;   
    // echo($quart2).PHP_EOL;   
    // echo($quart3).PHP_EOL;   
    
    /**
     * 동별 지수 계산
     */
    foreach ($sortedDealList as $key => $value) {        
        $point = 1;
        $rank_index = 0.1;
        
        if ($key < $quart1) {
            $point = 8;
            $rank_index = 0.5;
        } else if ($key < $quart2) {
            $point = 2;
            $rank_index = 0.3;
        } else if ($key < $quart3) {
            $point = 1;       
            $rank_index = 0.1; 
        }

        $sortedDealList[$key]['point'] = $point;        
        // $sortedDealList[$key]['rank_index'] = $rank_index;        

        // 동 랭킹을 위한 분류
        $value['upmyeondong'] = $sortedDealList[$key]['upmyeondong'] = trim($value['upmyeondong']);        
        
        if (empty($upmyeondong[$value['upmyeondong']])) {
            $upmyeondong[$value['upmyeondong']] = 0;
            $upmyeondongCount[$value['upmyeondong']] = 0;
            $upmyeondongPrice[$value['upmyeondong']] = 0;
            $upmyeondongPriceMax[$value['upmyeondong']] = 0;
            $upmyeondongPrices[$value['upmyeondong']] = [];
        }
        $sortedDealList[$key]['price_per'] = $pricePer = ceil($value['sale_price'] / $value['size']);
        $upmyeondongPrice[$value['upmyeondong']] += $pricePer;   
        $upmyeondongPrices[$value['upmyeondong']][] = $pricePer;
        $upmyeondong[$value['upmyeondong']] += $point;
        $upmyeondongPoint[$value['upmyeondong']] = $point;
        $upmyeondongCount[$value['upmyeondong']]++;

        if ($upmyeondongPriceMax[$value['upmyeondong']] < $pricePer) {
            $upmyeondongPriceMax[$value['upmyeondong']] = $pricePer;
        }

        // if ($value['upmyeondong'] == '산본동') {
        //     echo("{$value['upmyeondong']} {$value['name_apt']} {$pricePer}").PHP_EOL; 
        // }        
    }  
    
    array_multisort(array_values($upmyeondong), SORT_DESC, $upmyeondong);    

    $upmyeondongSumPriceMax = $upmyeondongSumPriceMaxAvg = $upmyeondongSum = $upmyeondongAvg = 0;     
    
    foreach ($upmyeondongPrice as $key => $value) {        
        $upmyeondongPrice[$key] = ceil($value / $upmyeondongCount[$key]);
        $upmyeondongSum += $upmyeondongPrice[$key];
        $upmyeondongSumPriceMax += $upmyeondongPriceMax[$key];
    }

    $upmyeondongAvg = $upmyeondongSum / sizeOf($upmyeondongPrice);
    $upmyeondongSumPriceMaxAvg = $upmyeondongSumPriceMax / sizeOf($upmyeondongPriceMax);        

    $upmyeondongAvgInc = [];    

    foreach ($upmyeondongPrice as $key => $value) {        
        $upmyeondongAvgInc[$key] = ($value / $upmyeondongAvg) * ($upmyeondongPriceMax[$key] / $upmyeondongSumPriceMaxAvg) * $AvgInc; 
        // echo($key).PHP_EOL;;
        // echo(($value / $upmyeondongAvg)).PHP_EOL;;
        // echo(($upmyeondongPriceMax[$key] / $upmyeondongSumPriceMaxAvg)).PHP_EOL;;               
    }    

    array_multisort(array_values($upmyeondongAvgInc), SORT_DESC, $upmyeondongAvgInc);    

    /**
     * 동별 아파트 지수 계산
     */

    $upmyeondongPricesAvg = [];

    foreach ($upmyeondongPrices as $key => $value) { 
        $upmyeondongPricesAvg[$key] = ceil(array_sum($value) / count($value));
    }

    foreach ($sortedDealList as $key => $value) {  
        $aptIndex = $value['price_per'] / $upmyeondongPricesAvg[$value['upmyeondong']];
        $sortedDealList[$key]['rank_index'] = ceil($upmyeondongAvgInc[$value['upmyeondong']] * $aptIndex);
        
        if ($value['upmyeondong'] == '도안동') {
            echo("{$sortedDealList[$key]['rank_index']} {$value['upmyeondong']} {$value['name_apt']} {$upmyeondongAvgInc[$value['upmyeondong']]} {$aptIndex} {$value['price_per']} {$upmyeondongPricesAvg[$value['upmyeondong']]}").PHP_EOL; 
        }        
    }       
    
    foreach ($sortedDealList as $key => $value) {        
        // if (!empty($upmyeondong[$value['upmyeondong']])) {
        //     $sortedDealList[$key]['rank_index'] = $sortedDealList[$key]['rank_index'] + $upmyeondong[$value['upmyeondong']];
        // }

        $sortedDealList[$key]['rank_index'] = ($sortedDealList[$key]['rank_index'] / 100) + 1;
    }  

    // print_r($sortedDealList);    
    print_r($upmyeondongAvgInc);    
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