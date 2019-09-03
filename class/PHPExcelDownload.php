<?php
include_once('./class/PHPExcel.php');

class PHPExcelDownload {
    CONST SHORTNAME = array(
        '두피마사지기' => '샴푸브러쉬',
        '페이스롤러' => '마사지롤러',
        '바른자세밴드' => '바른자세밴드',
        '토삭스' => '요가양말',
        '리프팅' => '리프팅밴드',
        '요가링' => '요가링',
        '땅콩볼' => '땅콩볼',    
        '롤빗' => '롤빗',
        '수면안대' => '레드썬 수면안대',
        '뽀송양말' => '뽀송양말',
        '좌욕기' => '가정용대야',
        '심리스팬티' => '심리스의류',
        '천연약쑥' => '천연약쑥',
        'NBR' => 'NBR 요가매트',
        'TPE' => 'TPE 요가매트',        
        '발가락교정기' => '발가락교정기',
        '아치보호대' => '아치보호대',
        '압박양말' => '압박양말',
        '귀지압패치' => '귀지압패치',
        '니플패치' => '실리콘패치',
        '헤어끈통' => '머리끈세트',
    );

    public function __construct() {
    }

    public function __destruct() {
    }

    /**
     * 코리아스포츠
     */
    public function korspo($files) {        
        if (empty($files)) return false;

        list($header, $bodyOptimized) = $this->korspoDataFilter($files);

        $data = array_merge(array($header), $bodyOptimized);
        $cntRow = sizeof($bodyOptimized) + 1;
        $bgColumn = array('우편번호', '수량', '구분', '운임');
        $bgColor = 'FFD8D8D8';                
        $lastChar = PHPExcel_Cell::stringFromColumnIndex((count($header) - 1));

        $excel = new PHPExcel();
        // 헤더 진하게
        $excel->setActiveSheetIndex(0)->getStyle("A1:${lastChar}1")->getFont()->setBold(true);
        
        foreach ($bgColumn as $value) {
            $bgIndex = array_search($value, $header);    
            // 입력할 수 없는 부분 배경색
            $bgChar = $this->columnChar($bgIndex);
            $excel->setActiveSheetIndex(0)->getStyle("{$bgChar}1:{$bgChar}{$cntRow}")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($bgColor);
        }        
        
        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}{$cntRow}")->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        
        // 셀 폭 최적화
        $widths = array(6, 10, 10, 80, 15, 15, 6, 6, 6, 6, 40, 10, 80);
        foreach ($widths as $i => $w) {
            $excel->setActiveSheetIndex(0)->getColumnDimension($this->columnChar($i))->setWidth($w);
        }
        
        // 시트명 변경
        $excel->setActiveSheetIndex(0)->setTitle('출력용');

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');

        // 양식 설정
        ob_end_clean();        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');        

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="택배양식(쿠힛)_'.date('Ymd').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        // 다운로드
        $writer->save('php://output');
        die();

        /*echo '<pre>';
        print_r($data);
        print_r($filterArray);
        print_r($filterIndex);
        print_r($filterIndexReverse);
        echo '</pre>';*/
    }

    /**
     * 코리아스포츠 가격계산
     */
    public function korspoPrice($files) {
        if (empty($files)) return false;

        list($header, $bodyOptimized) = $this->korspoDataFilter($files);
        $header = array('E-count 품명', '내품수량', '단가', '총액', '수취인');        

        $priceProduct = 0;
        $priceDelivery = 0;
        $bodyPrice = array();

        $prices = array(
            '짐볼 55' => array(5000, 3000),
            '짐볼 65' => array(5900, 3000),
            '짐볼 75' => array(7900, 3000),
            
            '8mm' => array(5900, 4000),
            '10mm' => array(6200, 4000),
            '16mm 와이드' => array(14800, 4000),
            '16mm' => array(8000, 4000),
            '20mm' => array(11200, 4000),
            
            '4mm' => array(9000, 4000),
            '6mm' => array(11500, 4000),            
        );

        foreach ($bodyOptimized as $key => $value) {
            $item = $value[10];

            foreach ($prices as $k => $v) {            
                if (strpos($item, $k) !== false) {
                    $priceProduct = $v[0] * $value[11];
                    $priceDelivery = $v[1];  
                    break;              
                }
            }
            
            if (!empty($bodyPrice['총액'])) {            
                $bodyPrice['총액'][3] = $bodyPrice['총액'][3] + $priceProduct;
            } else {
                $bodyPrice['총액'] = array('총액', '', '', $priceProduct, '');
            }
    
            if (!empty($bodyPrice['배송비'])) {  
                if (strpos($bodyPrice['배송비'][4], $value[1]) === false) {
                    $bodyPrice['배송비'][1]++;
                    $bodyPrice['배송비'][3] = $bodyPrice['배송비'][3] + $priceDelivery; 
                    $bodyPrice['배송비'][4] = $bodyPrice['배송비'][4].' '.$value[1];                            
                    
                    $bodyPrice['총액'][3] = $bodyPrice['총액'][3] + $priceDelivery;
                }
            } else {
                $bodyPrice['배송비'] = array('배송비', 1, '', $priceDelivery, $value[1]);
                $bodyPrice['총액'][3] = $bodyPrice['총액'][3] + $priceDelivery;
            }
    
            if (!empty($bodyPrice[$item])) {
                $bodyPrice[$item][1] = $bodyPrice[$item][1] + $value[11];
                $bodyPrice[$item][3] = $bodyPrice[$item][3] + $priceProduct;
                $bodyPrice[$item][4] = $bodyPrice[$item][4].' '.$value[1];            
            } else {
                $bodyPrice[$item] = array($item, $value[11], $priceProduct, $priceProduct, $value[1]);
            }      
        }

        // echo '<pre>';        
        // print_r($bodyOptimized);        
        // print_r($bodyPrice);        
        // echo '</pre>';
        // exit();

        $data = array_merge(array($header), $bodyPrice);
        $cntRow = sizeof($bodyPrice) + 1;        
        $lastChar = PHPExcel_Cell::stringFromColumnIndex((count($header) - 1));

        $excel = new PHPExcel();

        // 헤더 진하게
        $excel->setActiveSheetIndex(0)->getStyle("A1:${lastChar}1")->getFont()->setBold(true);
        
        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}{$cntRow}")->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        
        // 셀 폭 최적화
        $widths = array(80, 10, 10, 10, 200);
        foreach ($widths as $i => $w) {
            $excel->setActiveSheetIndex(0)->getColumnDimension($this->columnChar($i))->setWidth($w);
        }
        
        // 시트명 변경
        $excel->setActiveSheetIndex(0)->setTitle('출력용');

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');

        // 양식 설정
        ob_end_clean();        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');        

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="택배양식(쿠힛)_'.date('Ymd').'_계산서.xlsx"');
        header('Cache-Control: max-age=0');
        
        // 다운로드
        $writer->save('php://output');
        die();
    }

    /**
     * 코리아스포츠 데이터 정제
     */
    public function korspoDataFilter($files) {
        if (empty($files)) return false;

        $inputFile = $files['tmp_name'];
        $inputFileType = PHPExcel_IOFactory::identify($inputFile);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFile);
        $objPHPExcel->setActiveSheetIndex(0);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray();

        $filter = array(
            '품번' => '품번',
            '수취인명' => '수령자명',
            '_우편번호' => '우편번호', // 내용이 없어야 하는 값
            '배송지' => '주소',
            '수취인연락처1' => '수령자TEL1',
            '수취인연락처2' => '수령자TEL2',
            '_수량' => '수량', // 내용이 없어야 하는 값            
            '구분' => '구분',
            '운임' => '운임',
            '선/착' => '선/착',
            '옵션정보' => 'E-count 품명',
            '수량' => '내품수량',
            '배송메세지' => '보내는 이',
        );

        $filterTmp = array(
            '상품명' => '상품명',
            '상품번호' => '상품번호',
        );

        $filterMerged = array_merge($filter, $filterTmp);

        $filterArray = array();
        $filterIndex = array();
        $filterIndexReverse = array();
        $header = array_values($filter);
        $body = array();

        if (empty($sheetData)) {
            echo '엑셀 데이터를 확인할 수 없습니다.';
            return false;
        }
        
        foreach ($sheetData as $key => $value) {
            if (empty($value[1])) continue;

            if ($key == 1) {
                foreach ($value as $k => $v) {
                    if (empty($v)) continue;

                    if (!empty($filterMerged[$v])) {
                        $filterArray[] = $k;
                        $filterIndex[$v] = $k;
                        $filterIndexReverse[$k] = $v;
                    }
                }
            } else {
                if (!in_array($value[$filterIndex['상품번호']], array('4324723046', '4529428871', '4530770714'))) continue;

                $row = array();

                foreach ($value as $k => $v) {
                    if (empty($v)) continue;

                    if (in_array($k, $filterArray)) {
                        $row[array_search($filterIndexReverse[$k], array_keys($filterMerged))] = $v;
                    }
                }

                $body[] = $row;
            }
        }

        if (empty($body)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }        
        
        $bodyOptimized = array();
        $index = 1;

        foreach ($body as $key => $value) {
            $bodyRow = array();
            $bodyRow[0] = $index;

            for ($i = 1; $i < sizeof($filter); $i++) {
                $bodyRow[$i] = (!empty($value[$i])) ? $value[$i] : '';
            }

            $index++;

            $bodyOptimized[] = $bodyRow;
        }

        if (empty($bodyOptimized)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }
        
        foreach ($bodyOptimized as $key => $value) {
            $optionIndex = array_search('옵션정보', array_keys($filter));                        

            if (!empty($value[$optionIndex])) {                
                if (strpos($value[$optionIndex], '타입') !== false) {
                    $value[$optionIndex] = explode(':', $value[$optionIndex])[1];
                    $value[$optionIndex] = trim($value[$optionIndex]);
                    $optionInfo = explode(' ', $value[$optionIndex]);
                    $optionInfo[0] = str_replace('cm', '', $optionInfo[0]);
                    $optionInfo[1] = str_replace('(', '', $optionInfo[1]);
                    $optionInfo[1] = str_replace(')', '', $optionInfo[1]);
                    $option = '';

                    if (in_array($optionInfo[1], array('블루', '레드'))) {
                        $option = "안티버스트 짐볼 {$optionInfo[0]} ({$optionInfo[1]})";
                    } else {
                        $option = "안티버스트 파스텔 짐볼 {$optionInfo[0]} ({$optionInfo[1]})";
                    }
                    $bodyOptimized[$key][$optionIndex] = $option;                       
                } else if (strpos($value[$optionIndex], '두께') !== false) {                    
                    $options = explode(' / ', $value[$optionIndex]);
                    $thick = trim(explode(':', $options[0])[1]);
                    $color = trim(explode(':', $options[1])[1]);
                    $option = '';

                    $titleIndex = array_search('상품명', array_keys($filterMerged));
                    $title = $body[$key][$titleIndex];

                    if (strpos($title, 'TPE') !== false) {
                        $option = "TPE {$thick} 매트 ({$color})";
                    } else if (strpos($title, 'NBR') !== false) {
                        $option = "NBR {$thick} 매트 ({$color})";
                    }
                    
                    $bodyOptimized[$key][$optionIndex] = $option;                       
                }                
            }


            $messageIndex = array_search('배송메세지', array_keys($filter));
            if (!empty($bodyOptimized[$key][$messageIndex])) {
                $bodyOptimized[$key][$messageIndex] = ' / '.$bodyOptimized[$key][$messageIndex];
            }
            $bodyOptimized[$key][$messageIndex] = '보내는이:쿠힛 010-6608-2359'.$bodyOptimized[$key][$messageIndex];

            $prevIndex = array_search('선/착', array_keys($filter));
            $bodyOptimized[$key][$prevIndex] = '선불';
        } 

        return array($header, $bodyOptimized);
    }

    /**
     * cj택배
     */
    public function cj($files) {
        if (empty($files)) return false;        

        $inputFile = $files['tmp_name'];
        $inputFileType = PHPExcel_IOFactory::identify($inputFile);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFile);
        $objPHPExcel->setActiveSheetIndex(0);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray();

        $filter = array(
            '수취인명' => '받으시는분',
            '수취인연락처2' => '전화',
            '_받으시는분 담당자' => '받으시는분 담당자',            
            '수취인연락처1' => '받으시는분 핸드폰',            
            '우편번호' => '우편번호',
            '배송지' => '총주소',            
            '수량' => '수량',            
            '상품명' => '품목명',
            '_운임타입' => '운임타입',
            '_지불조건' => '지불조건',
            '_출고번호' => '출고번호',
            '배송메세지' => '특기사항',
        );

        $filterTmp = array(
            '상품번호' => '상품번호',
            '옵션정보' => '옵션정보',
            '구매자ID' => '구매자ID',
            '구매자명' => '구매자명',
            '주문번호' => '주문번호',
            '상품별 총 주문금액' => '상품별 총 주문금액',
        );

        $filterMerged = array_merge($filter, $filterTmp);

        $filterArray = array();
        $filterIndex = array();
        $filterIndexReverse = array();
        $header = array_values($filter);
        $body = array();        

        if (empty($sheetData)) {
            echo '엑셀 데이터를 확인할 수 없습니다.';
            return false;
        }
        
        foreach ($sheetData as $key => $value) {
            if (empty($value[1])) continue;

            if ($key == 1) {
                foreach ($value as $k => $v) {
                    if (empty($v)) continue;

                    if (!empty($filterMerged[$v])) {
                        $filterArray[] = $k;
                        $filterIndex[$v] = $k;
                        $filterIndexReverse[$k] = $v;
                    }
                }
            } else {
                // 짐볼, 폼롤러 제외
                if (in_array($value[$filterIndex['상품번호']], array('4324723046', '4529428871', '4530770714', '4318623001'))) continue;

                $row = array();                

                foreach ($value as $k => $v) {
                    if (empty($v)) continue;                    
                    if (in_array($k, $filterArray)) {    
                        if ($filterIndexReverse[$k] == '상품명') {                                                        
                            $v = $this->getShortName($v);
                        }

                        if ($filterIndexReverse[$k] == '옵션정보') {      
                            $_v = $this->getShortOption($v);
                            $row[array_search('상품명', array_keys($filterMerged))] = $row[array_search('상품명', array_keys($filterMerged))].' ['.$_v.']';   
                        }

                        if ($filterIndexReverse[$k] == '수량') {      
                            if ($v > 1) {
                                $_v = '*'.$v.'개';
                            } else {
                                $_v = $v.'개';
                            }
                            
                            $row[array_search('상품명', array_keys($filterMerged))] = $_v.' '.$row[array_search('상품명', array_keys($filterMerged))];
                            
                            // 택배박스는 무조건 1개로..
                            $v = 1;
                        }

                        $row[array_search($filterIndexReverse[$k], array_keys($filterMerged))] = $v;
                    }
                }                
                
                // 2건 이상 주문자인 경우 처리
                $isOverwrite = false;

                if ($body) {
                    foreach ($body as $k => $v) {                        
                        $index1 = array_search('수취인명', array_keys($filterMerged));                        
                        $index2 = array_search('배송지', array_keys($filterMerged));
                        $index3 = array_search('상품명', array_keys($filterMerged));                        
                        $index4 = array_search('수량', array_keys($filterMerged));

                        if ($row[$index1] == $v[$index1] && $row[$index2] == $v[$index2]) {
                            $isOverwrite = true;                            
                            
                            $body[$k][$index3] = $body[$k][$index3].' / '.$row[$index3];
                            $body[$k][$index4] = 1;
                        }
                    }
                }

                if (!$isOverwrite) {
                    $body[] = $row;
                }
            }
        }   

        if (empty($body)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }
        
        $bodyOptimized = array();  
        
        // DB
        $accountDb = parse_ini_file("./config/db.ini");
        require_once dirname(__FILE__).'/../class/pdo.php';
        $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

        // filter 값만 다시 담기
        foreach ($body as $key => $value) {
            $bodyRow = array();            

            for ($i = 0; $i < sizeof($filter); $i++) {
                $bodyRow[$i] = '';
                
                if (!empty($value[$i])) {
                    $bodyRow[$i] = $value[$i];
                    // print_r(array_search($filterIndexReverse[$i], array_keys($filterMerged)));
                    // exit;            
                }
            }     
            
            // 지난 주문 조회
            $dbData = array(
                'user_key' => $value[array_search('구매자명', array_keys($filterMerged))].'|'.$value[array_search('구매자ID', array_keys($filterMerged))],
                'order_id' => $value[array_search('주문번호', array_keys($filterMerged))],
                'price' => str_replace(',', '', $value[array_search('상품별 총 주문금액', array_keys($filterMerged))]),
            );                        
            
            if (!empty($dbData['user_key']) && !empty($dbData['order_id'])) {
                $row = $db->row("SELECT COUNT(*) AS total, SUM(price) AS total_price FROM smartstore_order WHERE user_key=? AND order_id!=?", array($dbData['user_key'], $dbData['order_id']));
                
                // 지난 주문횟수 포함                
                if (!empty($row) && $row['total'] > 0) {
                    $indexName = array_search('상품명', array_keys($filterMerged));
                    $bodyRow[$indexName] = $bodyRow[$indexName]." ({$row['total']})";
                }                

                // 주문정보 삽입
                $rowExist = $db->row("SELECT * FROM smartstore_order WHERE order_id=?", array($dbData['order_id']));
                
                if (empty($rowExist)) {
                    $dbName = array_keys($dbData, true);
                    $dbValues = array_map(function ($val) {
                        return ':'.$val;
                    }, $dbName);
                    $dbName = implode(',', $dbName);
                    $dbValues = implode(',', $dbValues);                

                    $db->query("INSERT INTO smartstore_order ({$dbName}) VALUES({$dbValues})", $dbData);
                }                                
            }            

            $bodyOptimized[] = $bodyRow;            
        }

        if (empty($bodyOptimized)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }   
        
        foreach ($bodyOptimized as $key => $value) {
            $prevIndex = array_search('_운임타입', array_keys($filter));
            $nameIndex = array_search('상품명', array_keys($filter));

            if (strpos($value[$nameIndex], '가정용대야') !== false) {                            ;
                $bodyOptimized[$key][$prevIndex] = '2';
            } else {            
                $bodyOptimized[$key][$prevIndex] = '1';
            }

            $payIndex = array_search('_지불조건', array_keys($filter));
            $bodyOptimized[$key][$payIndex] = '1';
        }
        
        // echo '<pre>';        
        // print_r($bodyOptimized);
        // echo '</pre>';
        // exit();
        
        $data = $bodyOptimized;
        $cntRow = sizeof($bodyOptimized);                
        $lastChar = PHPExcel_Cell::stringFromColumnIndex((count($bodyOptimized[0]) - 1));

        $excel = new PHPExcel();        
        
        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}{$cntRow}")->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        
        // 셀 폭 최적화
        $widths = array(10, 15, 5, 15, 8, 80, 6, 50, 6, 6, 6, 50);
        foreach ($widths as $i => $w) {
            $excel->setActiveSheetIndex(0)->getColumnDimension($this->columnChar($i))->setWidth($w);
        }
        
        // 시트명 변경
        $excel->setActiveSheetIndex(0)->setTitle('폼');

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');

        // 양식 설정
        ob_end_clean();        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');        

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="CJ대한통운_운송장출력폼_'.date('Ymd').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        // 다운로드
        $writer->save('php://output');
        die();

        /*echo '<pre>';
        print_r($data);
        print_r($filterArray);
        print_r($filterIndex);
        print_r($filterIndexReverse);
        echo '</pre>';*/
    }

    /**
     * 한진택배 (사용X)
     */
    public function hanjin($files) {
        if (empty($files)) return false;

        $inputFile = $files['tmp_name'];
        $inputFileType = PHPExcel_IOFactory::identify($inputFile);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFile);
        $objPHPExcel->setActiveSheetIndex(0);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray();

        $filter = array(
            '수취인명' => '받으시는분',
            '수취인연락처2' => '전화',
            '_받으시는분 담당자' => '받으시는분 담당자',            
            '수취인연락처1' => '받으시는분 핸드폰',            
            '우편번호' => '우편번호',
            '배송지' => '총주소',            
            '수량' => '수량',            
            '상품명' => '품목명',
            '_운임타입' => '운임타입',
            '_지불조건' => '지불조건',
            '_출고번호' => '출고번호',
            '배송메세지' => '특기사항',
        );

        $filterTmp = array(
            '상품번호' => '상품번호',
            '옵션정보' => '옵션정보',
        );

        $filterMerged = array_merge($filter, $filterTmp);

        $filterArray = array();
        $filterIndex = array();
        $filterIndexReverse = array();
        $header = array_values($filter);
        $body = array();        

        if (empty($sheetData)) {
            echo '엑셀 데이터를 확인할 수 없습니다.';
            return false;
        }
        
        foreach ($sheetData as $key => $value) {
            if (empty($value[1])) continue;

            if ($key == 1) {
                foreach ($value as $k => $v) {
                    if (empty($v)) continue;

                    if (!empty($filterMerged[$v])) {
                        $filterArray[] = $k;
                        $filterIndex[$v] = $k;
                        $filterIndexReverse[$k] = $v;
                    }
                }
            } else {
                // 짐볼, 폼롤러 제외
                if (in_array($value[$filterIndex['상품번호']], array('4324723046', '4529428871', '4530770714', '4318623001'))) continue;

                $row = array();                

                foreach ($value as $k => $v) {
                    if (empty($v)) continue;                    
                    if (in_array($k, $filterArray)) {    
                        if ($filterIndexReverse[$k] == '상품명') {                                                        
                            $v = $this->getShortName($v);
                        }

                        if ($filterIndexReverse[$k] == '옵션정보') {      
                            $_v = $this->getShortOption($v);
                            $row[array_search('상품명', array_keys($filterMerged))] = $row[array_search('상품명', array_keys($filterMerged))].' ['.$_v.']';   
                        }

                        if ($filterIndexReverse[$k] == '수량') {      
                            if ($v > 1) {
                                $_v = '*'.$v.'개';
                            } else {
                                $_v = $v.'개';
                            }
                            
                            $row[array_search('상품명', array_keys($filterMerged))] = $_v.' '.$row[array_search('상품명', array_keys($filterMerged))];
                            
                            // 택배박스는 무조건 1개로..
                            $v = 1;
                        }

                        $row[array_search($filterIndexReverse[$k], array_keys($filterMerged))] = $v;
                    }
                }                
                
                // 2건 이상 주문자인 경우 처리
                $isOverwrite = false;

                if ($body) {
                    foreach ($body as $k => $v) {                        
                        $index1 = array_search('수취인명', array_keys($filterMerged));                        
                        $index2 = array_search('배송지', array_keys($filterMerged));
                        $index3 = array_search('상품명', array_keys($filterMerged));                        
                        $index4 = array_search('수량', array_keys($filterMerged));

                        if ($row[$index1] == $v[$index1] && $row[$index2] == $v[$index2]) {
                            $isOverwrite = true;                            
                            
                            $body[$k][$index3] = $body[$k][$index3].' / '.$row[$index3];
                            $body[$k][$index4] = 1;
                        }
                    }
                }

                if (!$isOverwrite) {
                    $body[] = $row;
                }
            }
        }   

        if (empty($body)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }
        
        $bodyOptimized = array();        

        foreach ($body as $key => $value) {
            $bodyRow = array();            

            for ($i = 0; $i < sizeof($filter); $i++) {
                $bodyRow[$i] = '';
                
                if (!empty($value[$i])) {
                    $bodyRow[$i] = $value[$i];
                    // print_r(array_search($filterIndexReverse[$i], array_keys($filterMerged)));
                    // exit;            
                }
            }            

            $bodyOptimized[] = $bodyRow;
        }

        if (empty($bodyOptimized)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }

        // echo '<pre>';
        // print_r($bodyOptimized);        
        // echo '</pre>';
        // exit();
        
        foreach ($bodyOptimized as $key => $value) {
            $prevIndex = array_search('_운임타입', array_keys($filter));
            $bodyOptimized[$key][$prevIndex] = 'a';

            $payIndex = array_search('_지불조건', array_keys($filter));
            $bodyOptimized[$key][$payIndex] = '선불';
        }                                     

        
        $data = $bodyOptimized;
        $cntRow = sizeof($bodyOptimized);                
        $lastChar = PHPExcel_Cell::stringFromColumnIndex((count($bodyOptimized[0]) - 1));

        $excel = new PHPExcel();        
        
        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}{$cntRow}")->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        
        // 셀 폭 최적화
        $widths = array(10, 15, 5, 15, 8, 80, 6, 50, 6, 6, 6, 50);
        foreach ($widths as $i => $w) {
            $excel->setActiveSheetIndex(0)->getColumnDimension($this->columnChar($i))->setWidth($w);
        }
        
        // 시트명 변경
        $excel->setActiveSheetIndex(0)->setTitle('폼');

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');

        // 양식 설정
        ob_end_clean();        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');        

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="한진-포커스운송장출력폼_'.date('Ymd').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        // 다운로드
        $writer->save('php://output');
        die();

        /*echo '<pre>';
        print_r($data);
        print_r($filterArray);
        print_r($filterIndex);
        print_r($filterIndexReverse);
        echo '</pre>';*/
    }

    /**
     * 엑셀일괄발송 (CJ용)
     */
    public function sendall($filesInput, $filesOutput, $type = null) {
        if (empty($filesInput) || empty($filesOutput)) {
            echo '파일을 첨부해주세요.';
            return false;
        }        

        if (!empty($filesInput['tmp_name'])) {
            $inputFile = $filesInput['tmp_name'];
            $inputFileType = PHPExcel_IOFactory::identify($inputFile);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFile);
            $objPHPExcel->setActiveSheetIndex(0);
            $inputSheetData = $objPHPExcel->getActiveSheet()->toArray();
        // 직접입력
        } else {
            $inputSheetData = $filesInput;
        }        
        
        $outputFile = $filesOutput['tmp_name'];
        $outputFileType = PHPExcel_IOFactory::identify($outputFile);
        $objReader = PHPExcel_IOFactory::createReader($outputFileType);
        $objPHPExcel = $objReader->load($outputFile);
        $objPHPExcel->setActiveSheetIndex(0);
        $outputSheetData = $objPHPExcel->getActiveSheet()->toArray();        

        $filter = array(
            '운송장번호' => '송장번호',            
            '받는분' => '수취인명',
            '받는분 전화번호' => '수취인연락처1',
            '받는분  우편번호' => '우편번호',
            '택배사' => '택배사',            
        );     

        $filterTmp = array(
            '상품번호' => '상품번호',            
        );

        $filterMerged = array_merge($filter, $filterTmp);

        $filterArray = array();
        $filterIndex = array();
        $filterIndexReverse = array();                
        $body = array();        

        if (empty($inputSheetData)) {
            echo '엑셀 데이터를 확인할 수 없습니다.';
            return false;
        }
        
        foreach ($inputSheetData as $key => $value) {
            if ($key == 0) {
                foreach ($value as $k => $v) {
                    if (empty($v)) continue;

                    if (!empty($filterMerged[$v])) {
                        $filterArray[] = $k;
                        $filterIndex[$v] = $k;
                        $filterIndexReverse[$k] = $v;
                    }
                }
            } else {                                
                $row = array();                

                foreach ($value as $k => $v) {
                    if (empty($v)) continue;                    
                    if (in_array($k, $filterArray)) {                          
                        $row[$filterMerged[$filterIndexReverse[$k]]] = str_replace('*', '', $v);
                    }
                }              
                
                $body[] = $row;
            }
        }    

        if (empty($body)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }        

        // 출력데이터 생성
        $filterArray2 = array(); 
        $filterIndex2 = array();     
        $body2 = array();  
        $filterIndexReverse2 = array();        
        $filterValues = array_values($filterMerged);

        unset($outputSheetData[0]);        
        
        foreach ($outputSheetData as $key => $value) {  
            if ($key == 1) {                
                foreach ($value as $k => $v) {
                    if (in_array($v, $filterValues)) {
                        $filterArray2[] = $k;
                        $filterIndex2[$v] = $k;
                        $filterIndexReverse2[$k] = $v;
                    }
                }
            } else {
                // echo '<pre>';
                // print_r($type);
                // print_r($filterIndex2);
                // print_r($value[$filterIndex2['상품번호']]);
                // echo '</pre>';
                if ($type == 'kospo') {
                    if (!in_array($value[$filterIndex2['상품번호']], array('4324723046', '4529428871', '4530770714'))) continue;
                } else {
                    if (in_array($value[$filterIndex2['상품번호']], array('4324723046', '4529428871', '4530770714', '4318623001'))) continue;
                }

                foreach ($body as $kbody => $vbody) {
                    $isSame = true;
                    $hanjinNuber = '';                    

                    foreach ($vbody as $kb => $vb) {
                        if ($kb == '송장번호') continue;
                        if ($kb == '택배사') continue;
                        if ($kb == '우편번호') continue; // CJ 우편번호 최적화로 변경이 될 수 있음
                        
                        // 하나의 항목이라도 다르다면 continue;   
                        $v1 = trim(str_replace('-', '', $value[$filterIndex2[$kb]]));
                        $v2 = trim(str_replace('-', '', $vb));

                        if (empty($v2)) continue;
                        
                        // 데이터 비교
                        // echo '<pre>';
                        // print_r($kb.'/'.$v1.'/'.$v2);
                        // echo '</pre>';

                        if (strpos($v1, $v2) === false) {                            ;
                            $isSame = false;
                        }

                        //if (in_array($value[$filterIndex['상품번호']], array('4324723046', '4529428871', '4530770714', '4318623001'))) continue;
                    }
                    //echo '<br/>';

                    // 모두 일치하는 경우
                    if ($isSame) {
                        $outputSheetData[$key][$filterIndex2['택배사']] = 'CJ대한통운';
                        $outputSheetData[$key][$filterIndex2['송장번호']] = $vbody['송장번호'];
                    } 
                }
            }
        }      

        // 송장번호가 입력되지 않은 주문건은 삭제
        $outputSheetDataNew = array();
        $newIndex = 1;
        foreach ($outputSheetData as $key => $value) {
            if (empty($outputSheetData[$key][$filterIndex2['택배사']]) && empty($outputSheetData[$key][$filterIndex2['송장번호']])) {
                continue;
            }

            $outputSheetDataNew[$newIndex] = $value;
            $newIndex++;
        }
        
        $outputSheetData = $outputSheetDataNew;

        // echo '<pre>';        
        // print_r($filterArray);        
        // print_r($filterIndexReverse);        
        // print_r($filterValues);                
        // print_r($filterArray2);                
        // print_r($filterIndex2);        
        // print_r($filterIndexReverse2);        
        // print_r($body);        
        // print_r($outputSheetData);
        // echo '</pre>';
        // exit(); 
        
        $data = $outputSheetData;
        $cntRow = sizeof($outputSheetData);        
        $lastChar = PHPExcel_Cell::stringFromColumnIndex((count($outputSheetData[1]) - 1));

        $excel = new PHPExcel();  
        
        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}{$cntRow}")->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        
        // 셀 폭 최적화
        /*$widths = array(10, 15, 5, 15, 8, 80, 6, 50, 6, 6, 6, 50);
        foreach ($widths as $i => $w) {
            $excel->setActiveSheetIndex(0)->getColumnDimension($this->columnChar($i))->setWidth($w);
        }*/
        
        // 시트명 변경
        $excel->setActiveSheetIndex(0)->setTitle('발송처리');

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');        

        // format -> text
        foreach ($data  as $key => $value) {                        
            if ($key < 2) continue;
            if (!empty($value)) {
                foreach ($value as $k => $v) {
                    if (is_numeric($v) && strlen($v) > 9) {   
                        $cellPos = PHPExcel_Cell::stringFromColumnIndex($k).$key;
                        // echo '<pre>';
                        // print_r(PHPExcel_Cell::stringFromColumnIndex($k).$key);                        
                        // echo '</pre>';  
                        // echo '<pre>';                        
                        // print_r($excel->getActiveSheet()->getCell(PHPExcel_Cell::stringFromColumnIndex($k).$key)->getValue());
                        // echo '</pre>';                         
                        // echo '<pre>';                        
                        // print_r($v);
                        // echo '</pre>';     
                        
                        // 이전값과 현재값이 같을때만 변경
                        if ($excel->getActiveSheet()->getCell(PHPExcel_Cell::stringFromColumnIndex($k).$key)->getValue() == $v) {                   
                            $excel->setActiveSheetIndex(0)->setCellValueExplicit($cellPos, $v, PHPExcel_Cell_DataType::TYPE_STRING);
                        }
                    }
                }
            }            
        }                

        // 양식 설정
        ob_end_clean();        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');        

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="엑셀일괄발송_'.date('Ymd').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        // 다운로드
        $writer->save('php://output');
        die();

        /*echo '<pre>';
        print_r($data);
        print_r($filterArray);
        print_r($filterIndex);
        print_r($filterIndexReverse);
        echo '</pre>';*/
    }

    /**
     * 엑셀일괄발송 (한진용)
     */
     public function sendallHanjin($filesInput, $filesOutput, $type = null) {
        if (empty($filesInput) || empty($filesOutput)) {
            echo '파일을 첨부해주세요.';
            return false;
        }        

        if (!empty($filesInput['tmp_name'])) {
            $inputFile = $filesInput['tmp_name'];
            $inputFileType = PHPExcel_IOFactory::identify($inputFile);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFile);
            $objPHPExcel->setActiveSheetIndex(0);
            $inputSheetData = $objPHPExcel->getActiveSheet()->toArray();
        // 직접입력
        } else {
            $inputSheetData = $filesInput;
        }        
        
        $outputFile = $filesOutput['tmp_name'];
        $outputFileType = PHPExcel_IOFactory::identify($outputFile);
        $objReader = PHPExcel_IOFactory::createReader($outputFileType);
        $objPHPExcel = $objReader->load($outputFile);
        $objPHPExcel->setActiveSheetIndex(0);
        $outputSheetData = $objPHPExcel->getActiveSheet()->toArray();        

        $filter = array(
            '운송장번호' => '송장번호',            
            '수하인명' => '수취인명',
            '수하인 핸드폰' => '수취인연락처1',
            '수하인 우편번호' => '우편번호',
            '택배사' => '택배사',            
        );     

        $filterTmp = array(
            '상품번호' => '상품번호',            
        );

        $filterMerged = array_merge($filter, $filterTmp);

        $filterArray = array();
        $filterIndex = array();
        $filterIndexReverse = array();                
        $body = array();        

        if (empty($inputSheetData)) {
            echo '엑셀 데이터를 확인할 수 없습니다.';
            return false;
        }
        
        foreach ($inputSheetData as $key => $value) {
            if ($key == 0) {
                foreach ($value as $k => $v) {
                    if (empty($v)) continue;

                    if (!empty($filterMerged[$v])) {
                        $filterArray[] = $k;
                        $filterIndex[$v] = $k;
                        $filterIndexReverse[$k] = $v;
                    }
                }
            } else {                                
                $row = array();                

                foreach ($value as $k => $v) {
                    if (empty($v)) continue;                    
                    if (in_array($k, $filterArray)) {                          
                        $row[$filterMerged[$filterIndexReverse[$k]]] = str_replace('*', '', $v);
                    }
                }              
                
                $body[] = $row;
            }
        }    

        if (empty($body)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }        

        // 출력데이터 생성
        $filterArray2 = array(); 
        $filterIndex2 = array();     
        $body2 = array();  
        $filterIndexReverse2 = array();        
        $filterValues = array_values($filterMerged);

        unset($outputSheetData[0]);        
        
        foreach ($outputSheetData as $key => $value) {  
            if ($key == 1) {                
                foreach ($value as $k => $v) {
                    if (in_array($v, $filterValues)) {
                        $filterArray2[] = $k;
                        $filterIndex2[$v] = $k;
                        $filterIndexReverse2[$k] = $v;
                    }
                }
            } else {
                // echo '<pre>';
                // print_r($type);
                // print_r($filterIndex2);
                // print_r($value[$filterIndex2['상품번호']]);
                // echo '</pre>';
                if ($type == 'kospo') {
                    if (!in_array($value[$filterIndex2['상품번호']], array('4324723046', '4529428871', '4530770714'))) continue;
                } else {
                    if (in_array($value[$filterIndex2['상품번호']], array('4324723046', '4529428871', '4530770714', '4318623001'))) continue;
                }

                foreach ($body as $kbody => $vbody) {
                    $isSame = true;
                    $hanjinNuber = '';

                    foreach ($vbody as $kb => $vb) {
                        if ($kb == '송장번호') continue;
                        if ($kb == '택배사') continue;
                        
                        // 하나의 항목이라도 다르다면 continue;   
                        $v1 = str_replace('-', '', $value[$filterIndex2[$kb]]);
                        $v2 = $vb;

                        if (empty($v2)) continue;

                        // echo '<pre>';
                        // print_r($kb.'/'.$v1.'/'.$v2);
                        // echo '</pre>';                            

                        if (strpos($v1, $v2) === false) {                            ;
                            $isSame = false;
                        }

                        //if (in_array($value[$filterIndex['상품번호']], array('4324723046', '4529428871', '4530770714', '4318623001'))) continue;
                    }
                    //echo '<br/>';

                    // 모두 일치하는 경우
                    if ($isSame) {
                        if ($type == 'kospo') {
                            $outputSheetData[$key][$filterIndex2['택배사']] = 'CJ대한통운';
                        } else {
                            $outputSheetData[$key][$filterIndex2['택배사']] = '한진택배';
                        }
                        
                        $outputSheetData[$key][$filterIndex2['송장번호']] = $vbody['송장번호'];
                    } 
                }
            }
        }        

        // 송장번호가 입력되지 않은 주문건은 삭제
        $outputSheetDataNew = array();
        $newIndex = 1;
        foreach ($outputSheetData as $key => $value) {
            if (empty($outputSheetData[$key][$filterIndex2['택배사']]) && empty($outputSheetData[$key][$filterIndex2['송장번호']])) {
                continue;
            }

            $outputSheetDataNew[$newIndex] = $value;
            $newIndex++;
        }
        
        $outputSheetData = $outputSheetDataNew;

        // echo '<pre>';        
        // print_r($filterArray);        
        // print_r($filterIndexReverse);        
        // print_r($filterValues);                
        // print_r($filterArray2);                
        // print_r($filterIndex2);        
        // print_r($filterIndexReverse2);        
        // print_r($body);        
        // print_r($outputSheetData);
        // echo '</pre>';
        // exit(); 
        
        $data = $outputSheetData;
        $cntRow = sizeof($outputSheetData);        
        $lastChar = PHPExcel_Cell::stringFromColumnIndex((count($outputSheetData[1]) - 1));

        $excel = new PHPExcel();  
        
        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}{$cntRow}")->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        ));
        
        // 셀 폭 최적화
        /*$widths = array(10, 15, 5, 15, 8, 80, 6, 50, 6, 6, 6, 50);
        foreach ($widths as $i => $w) {
            $excel->setActiveSheetIndex(0)->getColumnDimension($this->columnChar($i))->setWidth($w);
        }*/
        
        // 시트명 변경
        $excel->setActiveSheetIndex(0)->setTitle('발송처리');

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');        

        // format -> text
        foreach ($data  as $key => $value) {                        
            if ($key < 2) continue;
            if (!empty($value)) {
                foreach ($value as $k => $v) {
                    if (is_numeric($v) && strlen($v) > 9) {   
                        $cellPos = PHPExcel_Cell::stringFromColumnIndex($k).$key;
                        // echo '<pre>';
                        // print_r(PHPExcel_Cell::stringFromColumnIndex($k).$key);                        
                        // echo '</pre>';  
                        // echo '<pre>';                        
                        // print_r($excel->getActiveSheet()->getCell(PHPExcel_Cell::stringFromColumnIndex($k).$key)->getValue());
                        // echo '</pre>';                         
                        // echo '<pre>';                        
                        // print_r($v);
                        // echo '</pre>';     
                        
                        // 이전값과 현재값이 같을때만 변경
                        if ($excel->getActiveSheet()->getCell(PHPExcel_Cell::stringFromColumnIndex($k).$key)->getValue() == $v) {                   
                            $excel->setActiveSheetIndex(0)->setCellValueExplicit($cellPos, $v, PHPExcel_Cell_DataType::TYPE_STRING);
                        }
                    }
                }
            }            
        }                

        // 양식 설정
        ob_end_clean();        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');        

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="엑셀일괄발송_'.date('Ymd').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        // 다운로드
        $writer->save('php://output');
        die();

        /*echo '<pre>';
        print_r($data);
        print_r($filterArray);
        print_r($filterIndex);
        print_r($filterIndexReverse);
        echo '</pre>';*/
    }

    public function getShortName($name) {
        foreach (self::SHORTNAME as $key => $value) {
            if (strpos($name, $key) !== false) {
                return $value;
            }
        }

        return $name;
    }

    public function getShortOption($option) {
        if (empty($option)) return '';
        
        if (strpos($option, '/') !== false) {
            $op = [];
            foreach (explode('/', $option) as $key => $value) {
                $op[] = trim(explode(':', $value)[1]);
            }
            return implode('/', $op);
        } else {
            return trim(explode(':', $option)[1]);
        }
    }

    public function columnChar($i) {
        return chr(65 + $i); 
    }

    public function convertDirectDataToInputExcelData($data) {
        if (empty($data)) return false;
        
        $postInputDirect = preg_split('/[\s]+/', $data);
        $inputDirect = array(array('운송장번호', '받는분'));
        $rowIndex = 1;
        $rowArray = array();

        foreach ($postInputDirect as $value) {            
            if (strpos($value, '/') !== false) continue;
            else if (preg_replace('/[0-9]+/', '', $value) == '개') {                            
                $inputDirect[$rowIndex] = $rowArray;
                $rowArray = array();
                $rowIndex++;
                continue;
            }
            
            $rowArray[] = $value;
        }        

        // echo '<pre>';
        // print_r($postInputDirect);
        // print_r($inputDirect);
        // echo '</pre>';
        // exit;

        return $inputDirect;
    }
}
