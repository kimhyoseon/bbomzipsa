<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/class/PHPExcel.php');

class Yoona {
    public function __construct() {
    }

    public function __destruct() {
    }       

    /**
     * 엑셀데이터를 JSON으로 얻기
     */
    public function getArrayFromExcel($inputFile, $sheetIndex = 0) {        
        if (empty($inputFile)) {                        
            return false;
        }            
        
        $inputFile = $_SERVER['DOCUMENT_ROOT'].'/data/yoona/'.$inputFile;

        // json 파일 체크        
        $inputFileJson = str_replace('.xlsx', '_'.$sheetIndex.'.json', $inputFile);      
        
        if (file_exists($inputFileJson)) {            
            return json_decode(file_get_contents($inputFileJson), true);
        }                

        if (!file_exists($inputFile)) {            
            return false;
        }                
        
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFile);                
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);            
            $objPHPExcel = $objReader->load($inputFile);                        
            $objPHPExcel->setActiveSheetIndex($sheetIndex);                         
            $inputSheetData = $objPHPExcel->getActiveSheet()->toArray();                
        } catch (Exception $e) {
            echo "PARSER ERROR: ".$e->getMessage()."<br />\n";                    
        } 

        if (empty($inputSheetData)) {
            return false;
        }        

        $fp = fopen($inputFileJson, 'w');
        fwrite($fp, json_encode($inputSheetData));
        fclose($fp);

        return $inputSheetData;
    }
    
    /**
     * M1/M2 가져오기
     * https://kosis.kr/openapi/parameter/parameter_02List.jsp 
     */
    public function getM1M2() {
        $m1m2 = array();

        $m1 = file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=13103160026999+&objL1=13102160026ACNT_CODE.AAAA16+&objL2=&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=M&startPrdDe=199501&endPrdDe=210006&loadGubun=1&orgId=301&tblId=DT_010Y002');
        $m2 = file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=13103160026999+&objL1=13102160026ACNT_CODE.AAAA18+&objL2=&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=M&startPrdDe=199501&endPrdDe=210006&loadGubun=1&orgId=301&tblId=DT_010Y002');        

        $m1m2['m1'] = json_decode($m1, true);
        $m1m2['m2'] =  json_decode($m2, true);

        return $m1m2;
    } 

    /**
     * 미분양 가져오기
     * https://kosis.kr/openapi/serviceUse/serviceUse_0203List.jsp?serviceCD=2&serviceIdx=1
     */
    public function getMiboonyang() {
        $mibbonyang = array();

        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_3&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_4&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_5&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_6&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_7&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_8&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_9&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_10&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_11&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_12&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_13&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_14&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_15&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_16&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_17&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_18&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        // $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_19&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);        

        $mibbonyang = file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=13103871087T1+&objL1=ALL&objL2=ALL&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=M&newEstPrdCnt=1&loadGubun=2&orgId=116&tblId=DT_MLTM_2082');
        $mibbonyang = json_decode($mibbonyang, true);                

        return $mibbonyang;
    }        

    /**
     * 미분양 가져오기 (지역상세)     
     */
    public function getMiboonyangDetail($code) {
        if (empty($code)) return false;
        $L1 = $L2 = '';

        $code = explode('||', $code);
        
        $L1 = $code[0];
        $L2 = $code[1];        

        $mibbonyangDetail = json_decode(file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=13103871087T1+&objL1='.$L1.'+&objL2='.$L2.'+&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=M&startPrdDe=200701&endPrdDe=210012&loadGubun=1&orgId=116&tblId=DT_MLTM_2082'), true);        

        return $mibbonyangDetail;
    }

    /**
     * 인구 수 가져오기 (최근 1달 내 데이터)
     */
    public function getIngoo() {
        $ingoo = file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=T20+&objL1=ALL&objL2=&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=M&newEstPrdCnt=1&loadGubun=2&orgId=101&tblId=DT_1B040A3');                      
        $ingoo = json_decode($ingoo, true);        

        return $ingoo;
    }

    /**
     * 인구 수 가져오기 (지역상세)     
     */
    public function getIngooDetail($code) {
        if (empty($code)) return false;

        $ingooDetail = array();
        $ingooDetail['ingoo'] = json_decode(file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=T20+&objL1='.$code.'+&objL2=&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=M&startPrdDe=201101&endPrdDe=210007&loadGubun=1&orgId=101&tblId=DT_1B040A3'), true);
        $ingooDetail['sedae'] = json_decode(file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=T1+&objL1='.$code.'+&objL2=&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=M&startPrdDe=201101&endPrdDe=210007&loadGubun=1&orgId=101&tblId=DT_1B040B3'), true);        

        return $ingooDetail;
    }

    /**
     * 인구이동 가져오기
     * https://kosis.kr/openapi/parameter/parameter_02List.jsp#urlMakeSet_iframe
     */
    public function getIngooidong() {
        $ingooidong = file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=T25+&objL1=ALL&objL2=&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=Q&newEstPrdCnt=1&loadGubun=2&orgId=101&tblId=DT_1B26001_A01');                      
        $ingooidong = json_decode($ingooidong, true);        

        return $ingooidong;
    } 

    /**
     * 인구이동 가져오기 (지역상세)
     * https://kosis.kr/openapi/parameter/parameter_02List.jsp#urlMakeSet_iframe
     */
    public function getIngooidongDetail($code) {
        if (empty($code)) return false;

        $ingooidongDetail = file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=T25+&objL1='.$code.'+&objL2=&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=M&startPrdDe=197001&endPrdDe=210012&loadGubun=1&orgId=101&tblId=DT_1B26001_A01');                                                                      
        $ingooidongDetail = json_decode($ingooidongDetail, true);        

        return $ingooidongDetail;
    } 

    /**
     * 시군구 가져오기     
     */
    public function getSigoongoo() {
        $sigoongoo = file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=13103890822T1+&objL1=ALL&objL2=1310289082201+&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=Q&newEstPrdCnt=1&loadGubun=2&orgId=408&tblId=DT_PLCAHTUSE');                      
        $sigoongoo = json_decode($sigoongoo, true);        

        return $sigoongoo;
    }

    /**
     * 평균연령 가져오기     
     */
    public function getAgeDetail($code) {
        if (empty($code)) return false;

        $age = file_get_contents('http://kosis.kr/openapi/Param/statisticsParameterData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&itmId=T2+&objL1=11200+&objL2=ALL&objL3=&objL4=&objL5=&objL6=&objL7=&objL8=&format=json&jsonVD=Y&prdSe=M&newEstPrdCnt=1&loadGubun=2&orgId=101&tblId=DT_1B04005N');                                                                      
        $age = json_decode($age, true);        

        return $age;
    }

    /**
     * 아파트 시군구 가져오기
     */
    public function getAptSigoongoo() {
        // 계정    
        $accountDb = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config/db.ini');
        require_once $_SERVER['DOCUMENT_ROOT'].'/class/pdo.php';
        $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

        $list = $db->query("SELECT code_sigoongoo, sigoongoo FROM yoona_apt GROUP BY code_sigoongoo, sigoongoo");

        $db->CloseConnection(); 

        return $list;
    }

    /**
     * 아파트 랭킹 가져오기
     */
    public function getAptRank($code) {
        if (empty($code)) return false;

        // 계정    
        $accountDb = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config/db.ini');
        require_once $_SERVER['DOCUMENT_ROOT'].'/class/pdo.php';
        $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

        $list = $db->query("SELECT * FROM yoona_apt WHERE code_sigoongoo=?", array($code));

        if (empty($list)) return false;

        $ids = array();
        $apts = array();

        foreach ($list as $value) {
            $ids[] = $value['id'];
            $apts[$value['id']] = $value;
            $apts[$value['id']]['detail'] = array();
        }

        $list = $db->query("SELECT yoona_apt_id, size FROM yoona_apt_deal WHERE yoona_apt_id IN (:ids) GROUP BY yoona_apt_id, size", array('ids' => $ids));                        
        

        if (empty($list)) return false;        

        foreach ($list as $key => $value) {
            $row = $db->row("SELECT sale_price, date FROM yoona_apt_deal WHERE yoona_apt_id=? AND size=? AND sale_count > 0 ORDER BY date DESC", array($value['yoona_apt_id'], $value['size']));                    
            $row2 = $db->row("SELECT jeonse_price, date FROM yoona_apt_deal WHERE yoona_apt_id=? AND size=? AND jeonse_count > 0 ORDER BY date DESC", array($value['yoona_apt_id'], $value['size']));                                
            $values = $db->query("SELECT avg(sale_price) as year_price, year FROM yoona_apt_deal WHERE yoona_apt_id=? AND size=? AND sale_count > 0 GROUP BY yoona_apt_id, size, year", array($value['yoona_apt_id'], $value['size']));                                            

            if (!empty($values)) {
                $energies = array();
                $energyThisYear = 0;
                foreach ($values as $v) {
                    if ($v['year'] == date('Y')) {
                        $energyThisYear = $this->getEnergy($v['year'], $row['sale_price']);
                        $energies[] = $energyThisYear;
                        
                    } else {
                        $energies[] = $this->getEnergy($v['year'], $v['year_price']);
                    }                    
                }                
                
                $value['values'] = array($energyThisYear, round(array_sum($energies) / count($energies), 1));
            }            

            $pyeong = floor(($value['size'] / 3.3) + 10);
            $pricePerPyeong = floor($row['sale_price'] / $pyeong);
            $value['date'] = max(array($row['date'], $row2['date']));
            $value['pyeong'] = $pyeong;
            $value['sale_price'] = $row['sale_price'];
            $value['jeonse_price'] = $row2['jeonse_price'];
            $apts[$value['yoona_apt_id']]['detail'][] = $value;
            
            if (empty($apts[$value['yoona_apt_id']]['price']) || $apts[$value['yoona_apt_id']]['price'] < $pricePerPyeong) {
                $apts[$value['yoona_apt_id']]['price'] = $pricePerPyeong;
                $apts[$value['yoona_apt_id']]['pyeong'] = $pyeong;
                $apts[$value['yoona_apt_id']]['size'] = $value['size'];                
            }            
        }        

        $sort = array();

        foreach ($apts as $key => $value) {
            if (empty($value['price'])) {
                unset($apts[$key]);
                continue;
            }

            $sort[] = $value['price'];
        }

        array_multisort($sort, SORT_DESC, $apts);

        $apts = array_slice($apts, 0, 70);

        $db->CloseConnection(); 

        return $apts;
    }

    /**
     * 아파트 매매/전세 가져오기
     */
    public function getAptDetail($aptId) {
        if (empty($aptId)) return false;

        $apt = array();

        // 계정    
        $accountDb = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config/db.ini');
        require_once $_SERVER['DOCUMENT_ROOT'].'/class/pdo.php';
        $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

        $row = $db->row("SELECT * FROM yoona_apt WHERE id=?", array($aptId));

        if (empty($row)) return false;

        $apt['info'] = $row;        

        $list = $db->query("SELECT * FROM yoona_apt_deal WHERE yoona_apt_id=? ORDER BY date ASC", array($aptId));
        
        if (empty($list)) return false;

        $apt['price'] = $list;                

        $db->CloseConnection(); 

        return $apt;
    }

    /**
     * 아파트 가치 획득
     */
    public function getEnergy($year, $price) {        
        $energy = 0;

        // 연평균소득
        if ($year == '2004') $energy = $price / 37349688;
        else if ($year == '2005') $energy = $price / 39025080;
        else if ($year == '2006') $energy = $price / 41328648;
        else if ($year == '2007') $energy = $price / 43874412;
        else if ($year == '2008') $energy = $price / 46807464;
        else if ($year == '2009') $energy = $price / 46238268;
        else if ($year == '2010') $energy = $price / 48092052;
        else if ($year == '2011') $energy = $price / 50983428;
        else if ($year == '2012') $energy = $price / 53908368;
        else if ($year == '2013') $energy = $price / 55274592;
        else if ($year == '2014') $energy = $price / 56815236;
        else if ($year == '2015') $energy = $price / 57799980;
        else if ($year == '2016') $energy = $price / 58613376;
        else if ($year == '2017') $energy = $price / 60031080;
        else if ($year == '2018') $energy = $price / 61531857;
        else if ($year == '2019') $energy = $price / 63070153;    
        
        return round($energy * 10000, 1);
    }
    
    
    



    /**
     * CRON *
     */

    /**
     * 아파트 매매 가져오기     
     */
    public function getAptSale($lawdCd, $date) {
        if (empty($lawdCd)) return false;
        if (empty($date)) return false;

        $ch = curl_init();
        $url = 'http://openapi.molit.go.kr/OpenAPI_ToolInstallPackage/service/rest/RTMSOBJSvc/getRTMSDataSvcAptTradeDev';
        $queryParams  = '?' . urlencode('ServiceKey') . '=' . 'N5FupqoyFxqwcuyheudquznCCBi6IjOliKOT5DpHhTmomTde1WgpW4EkXwCZQ777CmYfcBbtgf%2FBuUqFwbEg2Q%3D%3D'; /*Service Key*/    
        $queryParams .= '&' . urlencode('pageNo') . '=' . urlencode('1'); /*페이지번호*/
        $queryParams .= '&' . urlencode('numOfRows') . '=' . urlencode('999'); /*한 페이지 결과 수*/
        $queryParams .= '&' . urlencode('LAWD_CD') . '=' . urlencode($lawdCd); /*지역코드*/
        $queryParams .= '&' . urlencode('DEAL_YMD') . '=' . urlencode($date); /*계약월*/
        $queryParams .= '&' . urlencode('_type') . '=' . urlencode('json'); /*타입*/    

        // print_r($url.$queryParams);        

        curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $response = curl_exec($ch);        
        curl_close($ch);

        if (empty($response)) return false;

        $response = json_decode($response, true); 

        if (empty($response['response']['body']['items']['item'])) return false;

        return $response['response']['body']['items']['item'];          
    } 

    /**
     * 아파트 전세 가져오기     
     */
    public function getAptJeonse($lawdCd, $date) {
        if (empty($lawdCd)) return false;
        if (empty($date)) return false;

        $ch = curl_init();
        $url = 'http://openapi.molit.go.kr:8081/OpenAPI_ToolInstallPackage/service/rest/RTMSOBJSvc/getRTMSDataSvcAptRent';
        $queryParams  = '?' . urlencode('ServiceKey') . '=' . 'N5FupqoyFxqwcuyheudquznCCBi6IjOliKOT5DpHhTmomTde1WgpW4EkXwCZQ777CmYfcBbtgf%2FBuUqFwbEg2Q%3D%3D'; /*Service Key*/    
        $queryParams .= '&' . urlencode('LAWD_CD') . '=' . urlencode($lawdCd); /*지역코드*/
        $queryParams .= '&' . urlencode('DEAL_YMD') . '=' . urlencode($date); /*계약월*/
        $queryParams .= '&' . urlencode('_type') . '=' . urlencode('json'); /*타입*/    

        curl_setopt($ch, CURLOPT_URL, $url . $queryParams);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $response = curl_exec($ch);
        curl_close($ch);

        if (empty($response)) return false;

        $response = json_decode($response, true);    

        if (empty($response['response']['body']['items']['item'])) return false;

        return $response['response']['body']['items']['item'];          
    }

    /**
     * 아파트 거래데이터 setter
     */
    public function setAptData($result, $data, $type, $date) {
        if (empty($data)) return false;
        if (empty($type)) return false;
        if (empty($date)) return false;        

        // 1, 2층이라면 continue
        if (!empty($data['층'])) {
            if ($data['층'] == 1 || $data['층'] == 2) return $result;
        }

        // 월세라면 continue
        if ($type == 'jeonse') {
            if ($data['월세금액'] > 0) return $result;
            
            $price = $data['보증금액'];         
        } else {
            $price = $data['거래금액'];        
        }     
        
        $code = "{$data['지역코드']}||{$data['아파트']}";
        $data['전용면적'] = floor($data['전용면적']);
        
        if (empty($result[$code])) {
            $result[$code] = array();            
        }
        
        if (empty($result[$code][$date])) {            
            $result[$code][$date] = array();
        }

        if (empty($result[$code][$date][$data['전용면적']])) {            
            $result[$code][$date][$data['전용면적']] = array('sale' => array(), 'jeonse' => array());
        }

        $result[$code][$date][$data['전용면적']][$type][] = intval(str_replace(',', '', $price));

        return $result;
    }

    /**
     * 아파트 ID 회득 (없을 시 저장)
     */
    public function getAptId($db, $aptData, $sigoongoo) {
        if (empty($db)) return false;
        if (empty($aptData)) return false;                
        if (empty($sigoongoo)) return false;                  

        // 매매정보
        if (!empty($aptData['법정동시군구코드'])) $isMaemae = true;
        else if (!empty($aptData['지역코드'])) $isMaemae = false;
        else return false;

        // 아파트 조회                
        $row = $db->row("SELECT * FROM yoona_apt WHERE code_sigoongoo=? AND name_apt=?", array($aptData['지역코드'], $aptData['아파트']));        
        
        // 있다면 id 리턴
        if (!empty($row)) {
            $aptData['id'] = $row['id'];
            return $aptData;
        }

        // 전세정보라면 저장하지 말고 return false
        if ($isMaemae == false) return false;
        
        // 없다면 insert 후 id 리턴
        $aptData['법정동코드'] = $aptData['법정동시군구코드'].$aptData['법정동읍면동코드'];

        // 시군구가 동일하게 유지되기 위해서 DB에서 기존 시군구 가져오기
        $row = $db->row("SELECT * FROM yoona_apt WHERE code_sigoongoo=?", array($aptData['법정동시군구코드']));

        if (!empty($row)) {
            $sigoongoo = $row['sigoongoo'];
        }        

        $insertData = array(
            'year_build' => $aptData['건축년도'],
            'name_apt' => $aptData['아파트'],            
            'sigoongoo' => $sigoongoo,
            'upmyeondong' => $aptData['법정동'],
            'code_beopjeongdong' => $aptData['법정동코드'],
            'code_sigoongoo' => $aptData['법정동시군구코드'],
            'code_eupmyeondong' => $aptData['법정동읍면동코드'],            
        );

        if (!empty($aptData['일련번호'])) $insertData['number_apt'] = $aptData['일련번호'];
        if (!empty($aptData['도로명'])) $insertData['road'] = $aptData['도로명'];
        if (!empty($aptData['도로명코드'])) $insertData['code_road'] = $aptData['도로명코드'];        

        $dbName = array_keys($insertData, true);
        $dbValues = array_map(function ($val) {
            return ':'.$val;
        }, $dbName);
        $dbName = implode(',', $dbName);
        $dbValues = implode(',', $dbValues);

        $dbResult = $db->query("INSERT INTO yoona_apt ({$dbName}) VALUES({$dbValues})", $insertData);
        
        $aptData['id'] = $db->lastInsertId();
        return $aptData;        
    }

    /**
     * 아파트 거래 저장
     */
    public function setAptDeal($db, $aptInfo, $aptDealData) {
        if (empty($db)) return false;
        if (empty($aptInfo)) return false;                
        if (empty($aptDealData)) return false;            

        foreach ($aptDealData as $date => $value1) {
            foreach ($value1 as $size => $value2) {
                $insertData = array(
                    'yoona_apt_id' => $aptInfo['id'],
                    'date' => $date,
                    'year' => substr($date, 0, 4),
                    'month' => substr($date, 4, 2),                    
                    'size' => $size,                    
                );

                if (!empty($value2['sale'])) {
                    $insertData['sale_count'] = count($value2['sale']);
                    $insertData['sale_price'] = floor(array_sum($value2['sale']) / $insertData['sale_count']);
                    $insertData['sale_price_max'] = max($value2['sale']);
                    $insertData['sale_price_min'] = min($value2['sale']);                    
                }

                if (!empty($value2['jeonse'])) {
                    $insertData['jeonse_count'] = count($value2['jeonse']);
                    $insertData['jeonse_price'] = floor(array_sum($value2['jeonse']) / $insertData['jeonse_count']);
                    $insertData['jeonse_price_max'] = max($value2['jeonse']);
                    $insertData['jeonse_price_min'] = min($value2['jeonse']);                    
                }

                // 데이터가 있는지 확인
                $row = $db->row("SELECT * FROM yoona_apt_deal WHERE yoona_apt_id=? AND date=? AND size=?", array($insertData['yoona_apt_id'], $insertData['date'], $insertData['size']));
                
                // print_r(array($insertData['yoona_apt_id'], $insertData['date'], $insertData['size']));
                // print_r($row);

                // 있다면 UPDATE
                if (!empty($row)) {
                    $isPass = true;
                    // 가격이 같은 경우 continue;
                    if (!empty($insertData['jeonse_price']) && $row['jeonse_price'] != $insertData['jeonse_price']) $isPass = false;
                    if (!empty($insertData['sale_price']) && $row['sale_price'] != $insertData['sale_price']) $isPass = false;                    
                    if ($isPass == true) continue;

                    unset($insertData['yoona_apt_id']);
                    unset($insertData['date']);
                    unset($insertData['size']);                    

                    $dbUpdate = array_map(function ($key) {
                        return $key.' = :'.$key;
                    }, array_keys($insertData, true));
                    $dbUpdate = implode(', ', $dbUpdate);                   

                    echo 'UPDATE '.implode(',', $insertData).PHP_EOL;    
                    // print_r("UPDATE yoona_apt_deal SET {$dbUpdate} WHERE id = {$row['yoona_apt_id']}");
                    // print_r($insertData);

                    $dbResult = $db->query("UPDATE yoona_apt_deal SET {$dbUpdate} WHERE yoona_apt_id = {$row['yoona_apt_id']} AND date = {$row['date']} AND size = {$row['size']}", $insertData);
                // 없다면 INSERT
                } else {
                    $dbName = array_keys($insertData, true);
                    $dbValues = array_map(function ($val) {
                        return ':'.$val;
                    }, $dbName);
                    $dbName = implode(',', $dbName);
                    $dbValues = implode(',', $dbValues);

                    echo 'INSERT '.implode(',', $insertData).PHP_EOL;    
                    // print_r("INSERT INTO yoona_apt_deal ({$dbName}) VALUES({$dbValues})");
                    // print_r($insertData);
            
                    $dbResult = $db->query("INSERT INTO yoona_apt_deal ({$dbName}) VALUES({$dbValues})", $insertData);                    
                }     
            }            
        }     
        
        return true;
    }    
}