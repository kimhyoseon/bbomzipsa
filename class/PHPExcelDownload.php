<?php
include_once('./class/PHPExcel.php');

class PHPExcelDownload {
    public function __construct() {
    }

    public function __destruct() {
    }

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

        $filterMerged = array_merge($filterTmp, $filter);

        $filterArray = array();
        $filterIndex = array();
        $filterIndexReverse = array();
        $header = array_values($filter);
        $body = array();

        if (!empty($sheetData)) {
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
                            $row[array_search($filterIndexReverse[$k], array_keys($filter))] = $v;
                        }
                    }

                    $body[] = $row;
                }
            }

            if (!empty($body)) {
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

                if (!empty($bodyOptimized)) {
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
                }
            }
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

    function columnChar($i) {
        return chr(65 + $i); 
    }
}