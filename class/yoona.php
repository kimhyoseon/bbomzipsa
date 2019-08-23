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
        
        $inputFileType = PHPExcel_IOFactory::identify($inputFile);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFile);
        $objPHPExcel->setActiveSheetIndex($sheetIndex);
        $inputSheetData = $objPHPExcel->getActiveSheet()->toArray();        

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

        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_3&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_4&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_5&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_6&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_7&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_8&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_9&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_10&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_11&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_12&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_13&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_14&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_15&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_16&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_17&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_18&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);
        $mibbonyang[] = json_decode(file_get_contents('http://kosis.kr/openapi/statisticsData.do?method=getList&apiKey=NjZkOTczOWMzNWNkNmRlMjAzY2ZjYjNjYjY2YjNjMDY=&format=json&jsonVD=Y&userStatsId=khs4473/116/DT_MLTM_2082/2/1/20190823170253_19&prdSe=M&startPrdDe=200701&endPrdDe=210012'), true);        

        return $mibbonyang;
    }
}
