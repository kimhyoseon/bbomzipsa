<?php
/**
 * 수동으로 여러개의 키워드를 입력해서 수집
 */
try {
    ini_set("display_errors", 1);
    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '-1');        
    
    $KEYWORD = '미용가위';
    $REL_KEYWORD = '애견강아지,애견숱,애견미용,애견머리,애견헤어,애견커트,애견장,애견숯,애견앞머리,애견컷트,애견일본,애견일제,애견숱치는,애견미용실,애견이발소,애견셀프,애견가정용,애견어린이,애견틴닝,애견유아,애견안전,애견스트록,애견블런트,강아지애견,강아지숱,강아지미용,강아지머리,강아지헤어,강아지커트,강아지장,강아지숯,강아지앞머리,강아지컷트,강아지일본,강아지일제,강아지숱치는,강아지미용실,강아지이발소,강아지셀프,강아지가정용,강아지어린이,강아지틴닝,강아지유아,강아지안전,강아지스트록,강아지블런트,숱애견,숱강아지,숱미용,숱머리,숱헤어,숱커트,숱장,숱숯,숱앞머리,숱컷트,숱일본,숱일제,숱숱치는,숱미용실,숱이발소,숱셀프,숱가정용,숱어린이,숱틴닝,숱유아,숱안전,숱스트록,숱블런트,미용애견,미용강아지,미용숱,미용머리,미용헤어,미용커트,미용장,미용숯,미용앞머리,미용컷트,미용일본,미용일제,미용숱치는,미용미용실,미용이발소,미용셀프,미용가정용,미용어린이,미용틴닝,미용유아,미용안전,미용스트록,미용블런트,머리애견,머리강아지,머리숱,머리미용,머리헤어,머리커트,머리장,머리숯,머리앞머리,머리컷트,머리일본,머리일제,머리숱치는,머리미용실,머리이발소,머리셀프,머리가정용,머리어린이,머리틴닝,머리유아,머리안전,머리스트록,머리블런트,헤어애견,헤어강아지,헤어숱,헤어미용,헤어머리,헤어커트,헤어장,헤어숯,헤어앞머리,헤어컷트,헤어일본,헤어일제,헤어숱치는,헤어미용실,헤어이발소,헤어셀프,헤어가정용,헤어어린이,헤어틴닝,헤어유아,헤어안전,헤어스트록,헤어블런트,커트애견,커트강아지,커트숱,커트미용,커트머리,커트헤어,커트장,커트숯,커트앞머리,커트컷트,커트일본,커트일제,커트숱치는,커트미용실,커트이발소,커트셀프,커트가정용,커트어린이,커트틴닝,커트유아,커트안전,커트스트록,커트블런트,장애견,장강아지,장숱,장미용,장머리,장헤어,장커트,장숯,장앞머리,장컷트,장일본,장일제,장숱치는,장미용실,장이발소,장셀프,장가정용,장어린이,장틴닝,장유아,장안전,장스트록,장블런트,숯애견,숯강아지,숯숱,숯미용,숯머리,숯헤어,숯커트,숯장,숯앞머리,숯컷트,숯일본,숯일제,숯숱치는,숯미용실,숯이발소,숯셀프,숯가정용,숯어린이,숯틴닝,숯유아,숯안전,숯스트록,숯블런트,앞머리애견,앞머리강아지,앞머리숱,앞머리미용,앞머리머리,앞머리헤어,앞머리커트,앞머리장,앞머리숯,앞머리컷트,앞머리일본,앞머리일제,앞머리숱치는,앞머리미용실,앞머리이발소,앞머리셀프,앞머리가정용,앞머리어린이,앞머리틴닝,앞머리유아,앞머리안전,앞머리스트록,앞머리블런트,컷트애견,컷트강아지,컷트숱,컷트미용,컷트머리,컷트헤어,컷트커트,컷트장,컷트숯,컷트앞머리,컷트일본,컷트일제,컷트숱치는,컷트미용실,컷트이발소,컷트셀프,컷트가정용,컷트어린이,컷트틴닝,컷트유아,컷트안전,컷트스트록,컷트블런트,일본애견,일본강아지,일본숱,일본미용,일본머리,일본헤어,일본커트,일본장,일본숯,일본앞머리,일본컷트,일본일제,일본숱치는,일본미용실,일본이발소,일본셀프,일본가정용,일본어린이,일본틴닝,일본유아,일본안전,일본스트록,일본블런트,일제애견,일제강아지,일제숱,일제미용,일제머리,일제헤어,일제커트,일제장,일제숯,일제앞머리,일제컷트,일제일본,일제숱치는,일제미용실,일제이발소,일제셀프,일제가정용,일제어린이,일제틴닝,일제유아,일제안전,일제스트록,일제블런트,숱치는애견,숱치는강아지,숱치는숱,숱치는미용,숱치는머리,숱치는헤어,숱치는커트,숱치는장,숱치는숯,숱치는앞머리,숱치는컷트,숱치는일본,숱치는일제,숱치는미용실,숱치는이발소,숱치는셀프,숱치는가정용,숱치는어린이,숱치는틴닝,숱치는유아,숱치는안전,숱치는스트록,숱치는블런트,미용실애견,미용실강아지,미용실숱,미용실미용,미용실머리,미용실헤어,미용실커트,미용실장,미용실숯,미용실앞머리,미용실컷트,미용실일본,미용실일제,미용실숱치는,미용실이발소,미용실셀프,미용실가정용,미용실어린이,미용실틴닝,미용실유아,미용실안전,미용실스트록,미용실블런트,이발소애견,이발소강아지,이발소숱,이발소미용,이발소머리,이발소헤어,이발소커트,이발소장,이발소숯,이발소앞머리,이발소컷트,이발소일본,이발소일제,이발소숱치는,이발소미용실,이발소셀프,이발소가정용,이발소어린이,이발소틴닝,이발소유아,이발소안전,이발소스트록,이발소블런트,셀프애견,셀프강아지,셀프숱,셀프미용,셀프머리,셀프헤어,셀프커트,셀프장,셀프숯,셀프앞머리,셀프컷트,셀프일본,셀프일제,셀프숱치는,셀프미용실,셀프이발소,셀프가정용,셀프어린이,셀프틴닝,셀프유아,셀프안전,셀프스트록,셀프블런트,가정용애견,가정용강아지,가정용숱,가정용미용,가정용머리,가정용헤어,가정용커트,가정용장,가정용숯,가정용앞머리,가정용컷트,가정용일본,가정용일제,가정용숱치는,가정용미용실,가정용이발소,가정용셀프,가정용어린이,가정용틴닝,가정용유아,가정용안전,가정용스트록,가정용블런트,어린이애견,어린이강아지,어린이숱,어린이미용,어린이머리,어린이헤어,어린이커트,어린이장,어린이숯,어린이앞머리,어린이컷트,어린이일본,어린이일제,어린이숱치는,어린이미용실,어린이이발소,어린이셀프,어린이가정용,어린이틴닝,어린이유아,어린이안전,어린이스트록,어린이블런트,틴닝애견,틴닝강아지,틴닝숱,틴닝미용,틴닝머리,틴닝헤어,틴닝커트,틴닝장,틴닝숯,틴닝앞머리,틴닝컷트,틴닝일본,틴닝일제,틴닝숱치는,틴닝미용실,틴닝이발소,틴닝셀프,틴닝가정용,틴닝어린이,틴닝유아,틴닝안전,틴닝스트록,틴닝블런트,유아애견,유아강아지,유아숱,유아미용,유아머리,유아헤어,유아커트,유아장,유아숯,유아앞머리,유아컷트,유아일본,유아일제,유아숱치는,유아미용실,유아이발소,유아셀프,유아가정용,유아어린이,유아틴닝,유아안전,유아스트록,유아블런트,안전애견,안전강아지,안전숱,안전미용,안전머리,안전헤어,안전커트,안전장,안전숯,안전앞머리,안전컷트,안전일본,안전일제,안전숱치는,안전미용실,안전이발소,안전셀프,안전가정용,안전어린이,안전틴닝,안전유아,안전스트록,안전블런트,스트록애견,스트록강아지,스트록숱,스트록미용,스트록머리,스트록헤어,스트록커트,스트록장,스트록숯,스트록앞머리,스트록컷트,스트록일본,스트록일제,스트록숱치는,스트록미용실,스트록이발소,스트록셀프,스트록가정용,스트록어린이,스트록틴닝,스트록유아,스트록안전,스트록블런트,블런트애견,블런트강아지,블런트숱,블런트미용,블런트머리,블런트헤어,블런트커트,블런트장,블런트숯,블런트앞머리,블런트컷트,블런트일본,블런트일제,블런트숱치는,블런트미용실,블런트이발소,블런트셀프,블런트가정용,블런트어린이,블런트틴닝,블런트유아,블런트안전,블런트스트록';

    $db = null;    

    if (empty($KEYWORD)) {
        throw new Exception(null, 400);
    }

    if (empty($REL_KEYWORD)) {
        throw new Exception(null, 400);
    }

    // 계정    
    $accountDb = parse_ini_file("../config/db.ini");

    require_once '../class/pdo.php';
    require_once '../api/naver/restapi.php';
    require_once '../api/naver/NaverShopping.php';
    require_once '../class/curl_async.php';

    $curlAsync = new CurlAsync();        

    $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);    

    // 검색 키워드 정보 가져오기
    $curlAsync->$KEYWORD(array(
        'url' => 'http://localhost/api/keyword.php',        
        // 'url' => 'http://ppomzipsa.com/api/keyword.php',
        'post' => array(
            'keyword' => $KEYWORD,
        )
    ));
    
    $keywordResult = json_decode($curlAsync->$KEYWORD(), true);    

    if (!$keywordResult || !$keywordResult['id']) {
        throw new Exception(null, 400);
    }

    // 연관 키워드 모두 수집
    $allRelkeywords = @explode(',', $REL_KEYWORD);

    if (empty($allRelkeywords)) throw new Exception(null, 400);
    
    // 테스트 2개
    $allRelkeywords = array_slice($allRelkeywords, 0, 2);        
    
    $collectRelkeywords = $allRelkeywords;
    $addRelkeywords = array();    
    $total = 0;    
        
    foreach (array_chunk($collectRelkeywords, 5) as $keywordChunk) {
        foreach ($keywordChunk as $keyword) {
            $curlAsync->$keyword(array(
                'url' => 'http://localhost/api/keyword.php',                    
                // 'url' => 'http://ppomzipsa.com/api/keyword.php',
                'post' => array(
                    'keyword' => $keyword,
                )
            ));
        }

        foreach ($keywordChunk as $keyword) {
            $colletResult = $curlAsync->$keyword();
            echo $colletResult.PHP_EOL;
            $total++;

            $colletResult = json_decode($colletResult, true);

            // 수집된 정보를 검색 키워드와 연결
            if ($colletResult['id']) {
                // 연결여부 확인
                $queryWheres = array(
                    'keywords_id = :keywords_id',
                    'keywords_rel_id = :keywords_rel_id'
                );          

                $queryParams = array(
                    'keywords_id' => $keywordResult['id'],
                    'keywords_rel_id' => $colletResult['id'],
                );

                if (!empty($queryWheres)) {
                    $queryWheres = implode(' AND ', $queryWheres);                        
                } else {
                    $queryWheres = '';
                }

                $exist = $db->single("SELECT 1 FROM keywords_rel WHERE {$queryWheres}", $queryParams);

                if (empty($exist)) {
                    $dbName = array_keys($queryParams, true);
                    $dbValues = array_map(function ($val) {
                        return ':'.$val;
                    }, $dbName);
                    $dbName = implode(',', $dbName);
                    $dbValues = implode(',', $dbValues);                        

                    $dbResult = $db->query("INSERT INTO keywords_rel ({$dbName}) VALUES({$dbValues})", $queryParams);
                }

                // echo '<pre>';
                // print_r($dbResult);                        
                // echo '</pre>';
                // exit();    
            }
        }
        
        sleep(1);
    }        
    
    $db->CloseConnection();    

    echo "[{$KEYWORD}]와 연관된 {$total}개의 키워드 수집완료.".PHP_EOL;    
} catch (Exception $e) {
    if (!empty($db)) $db->CloseConnection();
    echo $e->getCode().PHP_EOL;
    echo $e->getMessage().PHP_EOL;
}
?>