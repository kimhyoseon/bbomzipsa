<?php
include_once('./class/PHPExcel.php');

class PHPExcelDownload {
    CONST SHORTNAME = array(
        '두피마사지기' => '샴푸브러쉬 + 거품망',
        '페이스롤러' => '페이스롤러',
        '바른자세밴드' => '바른자세밴드',
        '토삭스' => '요가양말',
        '리프팅' => '리프팅밴드',
        '요가링' => '요가링',
        '땅콩볼' => '땅콩볼',    
        '롤빗' => '롤빗',    
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
                if (!in_array($value[$filterIndex['상품번호']], array('4324723046'))) continue;

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
            $optionIndex = array_search('옵션정보', array_keys($filter));                            ;

            if (!empty($value[$optionIndex])) {
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
            }


            $messageIndex = array_search('배송메세지', array_keys($filter));
            if (!empty($bodyOptimized[$key][$messageIndex])) {
                $bodyOptimized[$key][$messageIndex] = ' / '.$bodyOptimized[$key][$messageIndex];
            }
            $bodyOptimized[$key][$messageIndex] = '보내는이:쿠힛 010-5154-7515'.$bodyOptimized[$key][$messageIndex];

            $prevIndex = array_search('선/착', array_keys($filter));
            $bodyOptimized[$key][$prevIndex] = '선불';
        }                                     

        $data = array_merge(array($header), $bodyOptimized);
        $cntRow = sizeof($bodyOptimized) + 1;
        $bgColumn = array('_우편번호', '_수량', '구분', '운임');
        $bgColor = 'FFD8D8D8';        
        $lastChar = $this->columnChar(count($header) - 1);

        $excel = new PHPExcel();
        // 헤더 진하게
        $excel->setActiveSheetIndex(0)->getStyle("A1:${lastChar}1")->getFont()->setBold(true);
        
        foreach ($bgColumn as $value) {
            $bgIndex = array_search($value, array_keys($filter));    
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
     * 한진택배
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
                if (in_array($value[$filterIndex['상품번호']], array('4324723046', '4318623001'))) continue;

                $row = array();                

                foreach ($value as $k => $v) {
                    if (empty($v)) continue;                    
                    if (in_array($k, $filterArray)) {    
                        if ($filterIndexReverse[$k] == '상품명') {                                                        
                            $v = $this->getShortName($v);
                        }

                        if ($filterIndexReverse[$k] == '옵션정보') {      
                            $_v = $this->getShortOption($v);
                            $row[array_search('상품명', array_keys($filterMerged))] = $row[array_search('상품명', array_keys($filterMerged))].' '.$_v;   
                        }

                        if ($filterIndexReverse[$k] == '수량') {      
                            $_v = $v.'개';
                            $row[array_search('상품명', array_keys($filterMerged))] = $row[array_search('상품명', array_keys($filterMerged))].' '.$_v;
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
                            $body[$k][$index4] += $row[$index4];
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
        $lastChar = $this->columnChar(count($bodyOptimized) - 1);

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
     * 엑셀일괄발송
     */
     public function sendall($filesInput, $filesOutput) {
        if (empty($filesInput) || empty($filesOutput)) {
            echo '파일을 첨부해주세요.';
            return false;
        }        

        $inputFile = $filesInput['tmp_name'];
        $inputFileType = PHPExcel_IOFactory::identify($inputFile);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFile);
        $objPHPExcel->setActiveSheetIndex(0);
        $inputSheetData = $objPHPExcel->getActiveSheet()->toArray();
        
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

        $filterMerged = $filter;

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
        $filterValues = array_values($filter);

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
                foreach ($body as $kbody => $vbody) {
                    $isSame = true;
                    $hanjinNuber = '';

                    foreach ($vbody as $kb => $vb) {
                        if ($kb == '송장번호') continue;
                        
                        // 하나의 항목이라도 다르다면 continue;   
                        $v1 = str_replace('-', '', $value[$filterIndex2[$kb]]);
                        $v2 = $vb;
                        
                        // echo '<pre>';
                        // print_r($v1.'/'.$v2);
                        // echo '</pre>';

                        if (strpos($v1, $v2) === false) $isSame = false;
                    }
                    //echo '<br/>';

                    // 모두 일치하는 경우
                    if ($isSame) {
                        $outputSheetData[$key][$filterIndex2['택배사']] = '한진택배';
                        $outputSheetData[$key][$filterIndex2['송장번호']] = $vbody['송장번호'];
                    } 
                }
            }
        }

        foreach ($outputSheetData as $key => $value) {
            if (empty($outputSheetData[$key][$filterIndex2['택배사']]) && empty($outputSheetData[$key][$filterIndex2['송장번호']])) {
                unset($outputSheetData[$key]);
            }
        }

        // echo '<pre>';
        // print_r($header);        
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
        //$cntRow = sizeof($outputSheetData);        
        //$lastChar = $this->columnChar(count($outputSheetData) - 1);

        $excel = new PHPExcel();        
        
        // 셀 구분선
        // $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}{$cntRow}")->applyFromArray(array(
        //     'borders' => array(
        //         'allborders' => array(
        //             'style' => PHPExcel_Style_Border::BORDER_THIN
        //         )
        //     )
        // ));
        
        // 셀 폭 최적화
        /*$widths = array(10, 15, 5, 15, 8, 80, 6, 50, 6, 6, 6, 50);
        foreach ($widths as $i => $w) {
            $excel->setActiveSheetIndex(0)->getColumnDimension($this->columnChar($i))->setWidth($w);
        }*/
        
        // 시트명 변경
        $excel->setActiveSheetIndex(0)->setTitle('발송처리');

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');

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
        return trim(explode(':', $option)[1]);
    }

    public function columnChar($i) {
        return chr(65 + $i); 
    }
}
