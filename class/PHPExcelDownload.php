<?php
include_once('./class/PHPExcel.php');

class PHPExcelDownload {
    CONST TEST = false;
    CONST SHORTNAME = array(
        '두피마사지기' => '샴푸브러쉬',
        '페이스롤러' => '마사지롤러',
        '마사지롤러' => '마사지롤러',
        '마사지기구' => '마사지롤러',
        '바른자세' => '바른자세밴드',
        '토삭스' => '요가양말',
        '턱살' => '리프팅밴드',
        '요가링' => '요가링',
        '땅콩볼' => '땅콩볼',
        '롤빗' => '롤빗',
        '수면안대' => '수면안대',
        '뽀송양말' => '뽀송양말',
        '좌욕기' => '가정용대야',
        '심리스팬티' => '심리스의류',
        '천연약쑥' => '천연약쑥',
        '발가락교정기' => '발가락교정기',
        '아치보호대' => '아치보호대',
        '압박양말' => '압박양말',
        '귀지압패치' => '귀지압패치',
        '니플패치' => '실리콘패치',
        '헤어끈통' => '머리끈세트',
        'MTS' => 'MTS롤러',
        '아이스롤러' => '아이스롤러',
        '굳은살' => '발케어세트',
        '발목보호대' => '발목보호대',
        '니슬리브' => '무릎보호대',
        '타투' => '타투스티커',
        '혈압계' => '가정용혈압계',
        '왁싱' => '왁스빈',
        '여드름' => '여드름패치',
        '코세척기' => '코세척기',
        '미마마스크' => '미마마스크',
        '방석' => '도넛방석',
        '짐볼' => '짐볼',
        '루프' => '아이워너 루프밴드',
        '유리창' => '유리창청소',
        '방충망' => '방충망청소',
    );

    public function __construct() {
        $this->jshkDate = date('Ymd', strtotime('+1 days')); // ** 특정날짜로 조회하기 (조리일기준) **
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
        $bgColumn = array('우편번호', '내품수량', '구분', '운임');
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
        header('Content-Disposition: attachment; filename="코리아스포츠_이김_주문서_'.date('Ymd').'.xlsx"');
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
            '10mm' => array(6000, 4000),
            '16mm 와이드' => array(14800, 4000),
            '16mm' => array(8000, 4000),
            '20mm' => array(11000, 4000),

            '4mm' => array(9000, 4000),
            '6mm' => array(11500, 4000),
        );

        foreach ($bodyOptimized as $key => $value) {
            $item = $value[10];

            foreach ($prices as $k => $v) {
                if (strpos($item, $k) !== false) {
                    $priceProduct = $v[0] * $value[6];
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
                $bodyPrice[$item][1] = $bodyPrice[$item][1] + $value[6];
                $bodyPrice[$item][3] = $bodyPrice[$item][3] + $priceProduct;
                $bodyPrice[$item][4] = $bodyPrice[$item][4].' '.$value[1];
            } else {
                $bodyPrice[$item] = array($item, $value[6], $priceProduct, $priceProduct, $value[1]);
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
        header('Content-Disposition: attachment; filename="코리아스포츠_이김_요금계산서_'.date('Ymd').'.xlsx"');
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
            '수량' => '수량', // 내용이 없어야 하는 값
            '구분' => '구분',
            '운임' => '운임',
            '선/착' => '선/착',
            '옵션정보' => 'E-count 품명',
            '_수량' => '내품수량',
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
                if (!in_array($value[$filterIndex['상품번호']], array('4529428871', '4530770714', '4324723046', '5186994701'))) continue;

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

                    if ($optionInfo[1] == '무드인디고') $optionInfo[1] = '블루인디고';

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
                } else if (strpos($value[$optionIndex], '강도') !== false) {
                    $option = trim(explode(':', $value[$optionIndex])[1]);

                    $titleIndex = array_search('상품명', array_keys($filterMerged));
                    $title = $body[$key][$titleIndex];
                    $title = $this->getShortName($title);
                    $option = "{$title} ({$option})";

                    $bodyOptimized[$key][$optionIndex] = $option;
                }
            }

            $messageIndex = array_search('배송메세지', array_keys($filter));
            if (!empty($bodyOptimized[$key][$messageIndex])) {
                $bodyOptimized[$key][$messageIndex] = ' / '.$bodyOptimized[$key][$messageIndex];
            }
            $bodyOptimized[$key][$messageIndex] = '보내는이:쿠힛 0507-1366-7515'.$bodyOptimized[$key][$messageIndex];

            $prevIndex = array_search('선/착', array_keys($filter));
            $bodyOptimized[$key][$prevIndex] = '선불';
        }

        return array($header, $bodyOptimized);
    }

    /**
     * cj택배
     */
    public function cj($files, $isOk = false) {
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
        $bodyOver = array();
        $stock = array();
        $sort = array();
        $addrMerge = array();

        if (empty($sheetData)) {
            echo '엑셀 데이터를 확인할 수 없습니다.';
            return false;
        }

        foreach ($sheetData as $key => $value) {
            if (empty($value[1]) && empty($value[9])) continue;

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
                if (!empty($value[$filterIndex['상품번호']])) {
                    if (in_array($value[$filterIndex['상품번호']], array('4529428871', '4530770714', '4318623001'))) continue;
                }

                $row = array();

                foreach ($value as $k => $v) {
                    if (empty($v)) continue;
                    if (in_array($k, $filterArray)) {
                        if ($filterIndexReverse[$k] == '상품명') {
                            $v = $this->getShortName($v);
                        }

                        // 2020-12-24 우편번호 오류
                        if ($filterIndexReverse[$k] == '우편번호') {
                            continue;
                        }

                        if ($filterIndexReverse[$k] == '옵션정보') {
                            if ($row[array_search('상품명', array_keys($filterMerged))] == '신선식품') {
                                $_v = $this->getShortOptionJshk($v);
                                $row[array_search('상품명', array_keys($filterMerged))] = '['.$row[array_search('상품명', array_keys($filterMerged))].'] '.$_v;
                            } else {
                                // echo '<pre>';
                                // print_r($v);
                                // echo '</pre>';
                                // exit();

                                // 예외옵션상품 처리
                                // 리프팅밴드 > 마사지롤러
                                if (strpos($v, '롤러') !== false) {
                                    if (strpos($row[array_search('상품명', array_keys($filterMerged))], '리프팅밴드') !== false) {
                                        $row[array_search('상품명', array_keys($filterMerged))] = '마사지롤러';
                                    }
                                // 좌욕기 > 약쑥
                                } else if (strpos($v, '약쑥') !== false) {
                                    if (strpos($row[array_search('상품명', array_keys($filterMerged))], '가정용대야') !== false) {
                                        $row[array_search('상품명', array_keys($filterMerged))] = '천연약쑥';
                                    }
                                }

                                // 특이옵션 처리
                                // if (in_array($value, array('리프팅밴드') == true)) {
                                //     if (strpos($title, '수면안대') !== false) {
                                //     }
                                // }

                                $_v = $this->getShortOption($v);
                                $row[array_search('상품명', array_keys($filterMerged))] = $row[array_search('상품명', array_keys($filterMerged))].' ['.$_v.']';
                            }
                        }

                        if ($filterIndexReverse[$k] == '수량') {
                            if (strpos($row[array_search('상품명', array_keys($filterMerged))], '신선식품') !== false) {

                            } else {
                                // 신선식품이 아니라면 재고관리를 위한 배열 생성
                                if (empty($stock[$row[array_search('상품명', array_keys($filterMerged))]])) {
                                    $stock[$row[array_search('상품명', array_keys($filterMerged))]] = $v;
                                } else {
                                    $stock[$row[array_search('상품명', array_keys($filterMerged))]] += $v;
                                }
                            }
                        }

                        if ($filterIndexReverse[$k] == '수취인명') {
                            if (mb_strlen($v) < 2) {
                                $v = $v.'님';
                            }
                        }

                        $row[array_search($filterIndexReverse[$k], array_keys($filterMerged))] = $v;
                    }
                }

                // 2건 이상 주문자인 경우 처리
                $isOverwrite = false;

                if ($bodyOver) {
                    foreach ($bodyOver as $k => $v) {
                        $index1 = array_search('수취인명', array_keys($filterMerged));
                        $index2 = array_search('배송지', array_keys($filterMerged));
                        $index3 = array_search('상품명', array_keys($filterMerged));
                        $index4 = array_search('수량', array_keys($filterMerged));

                        // 수취인명과 배송지가 같다면
                        if ($row[$index1] == $v[$index1] && $row[$index2] == $v[$index2]) {
                            $isOverwrite = true;

                            // if (strpos($row[$index3], '신선식품') !== false) {
                            //     $row[$index3] = str_replace('[신선식품] ', '', $row[$index3]);
                            // }

                            $bodyOver[$k][$index3] = $bodyOver[$k][$index3].'__'.$row[$index3];
                            $bodyOver[$k][$index4] = 1;
                        }
                    }
                }

                if (!$isOverwrite) {
                    $bodyOver[] = $row;
                }

                // 주소를 기준으로 이름과 전화번호 정보 통일
                $indexName = array_search('수취인명', array_keys($filterMerged));
                $indexPhone1 = array_search('수취인연락처1', array_keys($filterMerged));
                $indexPhone2 = array_search('수취인연락처2', array_keys($filterMerged));
                $indexAddr = array_search('배송지', array_keys($filterMerged));

                // 저장된 주소정보가 없다면 넣어 줌
                if (empty($addrMerge[$row[$indexAddr]])) {
                    if (empty($row[$indexPhone2])) $row[$indexPhone2] = '';
                    $addrMerge[$row[$indexAddr]] = array($row[$indexName], $row[$indexPhone1], $row[$indexPhone2]);
                // 중복되는 주소가 존재하는 경우 이전 정보로 덮어쓰기
                } else {
                    $row[$indexName] = $addrMerge[$row[$indexAddr]][0];
                    $row[$indexPhone1] = $addrMerge[$row[$indexAddr]][1];
                    $row[$indexPhone2] = $addrMerge[$row[$indexAddr]][2];
                }

                $body[] = $row;
            }
        }

        if (empty($body)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }

        // echo '<pre>';
        // print_r($addrMerge);
        // print_r($bodyOver);
        // print_r($body);
        // echo '</pre>';
        // exit();

        // 합쳐지지 않은 배송건이 있는지 확인
        if (!self::TEST && $isOk == false) {
            $deleveryCheck = @json_decode(file_get_contents('./data/delevery_check.json'), true);

            // 기존 내역이 없거나 과거인 경우 새로 만듬
            if (empty($deleveryCheck) || $deleveryCheck['date'] != date('Ymd')) {
                $deleveryCheck = array('date' => date('Ymd'));
            }

            $checkIndexes = array(array_search('배송지', array_keys($filterMerged)), array_search('수취인연락처1', array_keys($filterMerged)));

            foreach ($bodyOver as $key => $value) {
                // 중복된 내용이 존재한다면 경고
                foreach ($checkIndexes as $index) {
                    if (!empty($value[$index])) {
                        if (!empty($deleveryCheck[$value[$index]])) {
                            exit("[{$value[$index]}] 내용으로 중복된 내용이 존재합니다. 확인해주세요.");
                        } else {
                            $deleveryCheck[$value[$index]] = 1;
                        }
                    }
                }
            }

            $fp = fopen('./data/delevery_check.json', 'w');
            fwrite($fp, json_encode($deleveryCheck));
            fclose($fp);
        }

        $bodyOptimized = array();

        // 쿠힛 재고 업데이트 (2020.01.07)
        if (!self::TEST) {
            if (!empty($stock)) {
                $indexTitle = array_search('상품명', array_keys($filterMerged));
                $countMemo = $countMemoBand = 0;

                foreach ($bodyOver as $value) {
                    if (strpos($value[$indexTitle], '리프팅밴드') !== false) {
                        $countMemoBand++;
                    } else {
                        $countMemo++;
                    }
                }

                if ($countMemoBand > 0) $stock['안내장밴드'] = $countMemoBand;
                if ($countMemo > 0) $stock['안내장'] = $countMemo;

                $this->setStockUpdate($stock);
            }
        }

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

            // // 지난 주문 조회
            // $dbData = array(
            //     'user_key' => $value[array_search('구매자명', array_keys($filterMerged))].'|'.$value[array_search('구매자ID', array_keys($filterMerged))],
            //     'order_id' => $value[array_search('주문번호', array_keys($filterMerged))],
            //     'price' => str_replace(',', '', $value[array_search('상품별 총 주문금액', array_keys($filterMerged))]),
            // );

            // if (!empty($dbData['user_key']) && !empty($dbData['order_id'])) {
            //     $row = $db->row("SELECT COUNT(*) AS total, SUM(price) AS total_price FROM smartstore_order WHERE user_key=? AND order_id!=?", array($dbData['user_key'], $dbData['order_id']));

            //     // 지난 주문횟수 포함
            //     if (!empty($row) && $row['total'] > 0) {
            //         $indexName = array_search('상품명', array_keys($filterMerged));
            //         $bodyRow[$indexName] = $bodyRow[$indexName]." ({$row['total']})";
            //     }

            //     // 주문정보 삽입
            //     $rowExist = $db->row("SELECT * FROM smartstore_order WHERE order_id=?", array($dbData['order_id']));

            //     if (empty($rowExist)) {
            //         $dbName = array_keys($dbData, true);
            //         $dbValues = array_map(function ($val) {
            //             return ':'.$val;
            //         }, $dbName);
            //         $dbName = implode(',', $dbName);
            //         $dbValues = implode(',', $dbValues);

            //         $db->query("INSERT INTO smartstore_order ({$dbName}) VALUES({$dbValues})", $dbData);
            //     }
            // }

            $bodyOptimized[] = $bodyRow;
        }

        if (empty($bodyOptimized)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }

        foreach ($bodyOptimized as $key => $value) {
            $prevIndex = array_search('_운임타입', array_keys($filter));
            $nameIndex = array_search('상품명', array_keys($filter));
            $amountIndex = array_search('수량', array_keys($filter));

            if (strpos($value[$nameIndex], '가정용대야') !== false) {                            ;
                $bodyOptimized[$key][$prevIndex] = '2';
            } else if (strpos($value[$nameIndex], '신선식품') !== false) {                            ;
                $bodyOptimized[$key][$prevIndex] = '2';
            } else {
                $bodyOptimized[$key][$prevIndex] = '1';
            }

            $payIndex = array_search('_지불조건', array_keys($filter));
            $bodyOptimized[$key][$payIndex] = '1';
            $title = explode(' ', $value[$nameIndex]);

            // 상품명 줄내림 처리 (2020.01.19)
            // $titleLine = explode('__', $value[$nameIndex]);
            // $bodyOptimized[$key][$nameIndex] = implode(chr(10), $titleLine);

            $sort[] = implode($title);

            // 수량 2개인 경우 * 표시
            if ($value[$amountIndex] > 1) {
                $bodyOptimized[$key][$nameIndex] = '*'.$value[$nameIndex];
            }
        }

        // 상품명으로 정렬
        array_multisort($sort, SORT_ASC, $bodyOptimized);

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

        // 상품명 줄내림
        // $excel->getActiveSheet()->getStyle("H1:H{$cntRow}")->getAlignment()->setWrapText(true);

        // 시트명 변경
        $excel->setActiveSheetIndex(0)->setTitle('폼');

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');

        // 양식 설정
        ob_end_clean();
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="박동훈_이김_주문서_'.date('Ymd').'.xlsx"');
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
                    if (!in_array($value[$filterIndex2['상품번호']], array('4529428871', '4530770714'))) continue;
                } else {
                    if (in_array($value[$filterIndex2['상품번호']], array('4529428871', '4530770714', '4318623001'))) continue;
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
        } else if (strpos($option, ':') !== false) {
            return trim(explode(':', $option)[1]);
        } else {
            return $option;
        }
    }

    public function columnChar($i) {
        return chr(65 + $i);
    }

    public function convertDirectDataToInputExcelData($data) {
        if (empty($data)) return false;

        $data = str_replace(' + ', "+", $data);
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

    // 재고관리
    public function setStockUpdate($stock) {
        // DB
        $accountDb = parse_ini_file("./config/db.ini");
        require_once dirname(__FILE__).'/../class/pdo.php';
        $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

        $newStock1 = $newStock2 = array();

        // 상품명 정리
        foreach ($stock as $key => $amount) {
            $items = explode(' ', $key, 2);
            $title = '';
            $opt = '';

            $title = $items[0];

            // 박스 계산
            if (in_array($title, array('가정용대야')) == true) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, '박스(C-197)', $amount);
            } else if (in_array($title, array('샴푸브러쉬', '요가링', '땅콩볼', '롤빗', '머리끈세트', '아이스롤러', '천연약쑥')) == true) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, '박스(C-31)', $amount);
            } else if (strpos($key, '다용도') !== false) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, '박스(C-31)', $amount);
            }

            // 더스트백 계산
            if (in_array($title, array('가정용대야')) == true) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, '더스트백(530)', $amount);
            } else if (in_array($title, array('요가링')) == true) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, '더스트백(320)', $amount);
            } else if (in_array($title, array('마사지롤러', '리프팅밴드', '땅콩볼', '발케어세트', '발목보호대')) == true) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, '더스트백(250)', $amount);
            } else if (in_array($title, array('수면안대', '발가락교정기', '아치보호대', '귀지압패치', '타투스티커')) == true) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, '더스트백(150)', $amount);
            } else if (in_array($title, array('천연약쑥')) == true) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, '더스트백(150)', ($amount * 2));
            } else if (in_array($title, array('바른자세밴드')) == true) {
                if (strpos($key, 'L') !== false) {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '더스트백(250)', $amount);
                } else {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '더스트백(150)', $amount);
                }
            }

            // 태그 계산
            if (in_array($title, array('리프팅밴드', '롤빗', '바른자세밴드', '땅콩볼', '귀지압패치', '아치보호대', '발케어세트', '발목보호대', '타투스티커')) == true) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, '태그(120)', $amount);
            } else if (in_array($title, array('요가링', '압박양말')) == true) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, '태그(170)', $amount);
            }

            if (!empty($items[1])) {
                $opt = substr($items[1], 1, -1);
                $title = $title.'__'.$opt;
            }

            $newStock1[$title] = $amount;
        }

        // 특이한 옵션 처리
        foreach ($newStock1 as $title => $amount) {
            if (strpos($title, '마사지롤러') !== false) {
                if (strpos($title, '미니+다용도 롤러세트') !== false) {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '마사지롤러__미니롤러(얼굴전용)', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '마사지롤러__다용도롤러', $amount);
                } else {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                }
            // -- A타입 3켤레 (어두운회색), ABC타입 골고루 3켤레
            } else if (strpos($title, '요가양말') !== false) {
                if (strpos($title, '골고루') !== false) {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '요가양말__A타입(검정)', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '요가양말__B타입(검정)', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '요가양말__C타입(검정)', $amount);
                } else {
                    // 켤레 제거 및 수량 변경
                    $title = explode(' ', $title);
                    unset($title[1]);
                    $title = implode('', $title);
                    $amount *= 3;
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                }

                $newStock2 = $this->setStockUpdateAdd($newStock2, '태그(120)', ($amount * 3));
            } else if (strpos($title, '뽀송양말') !== false) {
                // 혼합 3켤레
                if (strpos($title, '혼합') !== false) {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '뽀송양말__검정', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '뽀송양말__회색', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '뽀송양말__흰색', $amount);
                } else {
                    // 켤레 제거 및 수량 변경
                    $title = explode(' ', $title);
                    unset($title[1]);
                    $title = implode('', $title);
                    $amount *= 3;
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                }

                $newStock2 = $this->setStockUpdateAdd($newStock2, '태그(120)', ($amount * 3));
            } else if (strpos($title, '수면안대') !== false) {
                // 수면안대__검정 2개
                if (strpos($title, '개') !== false) {
                    $title = explode(' ', $title);
                    unset($title[1]);
                    $title = implode('', $title);
                    $amount *= 2;
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '태그(120)', ($amount * 2));
                // 수면안대__검정 + 회색
                } else if (strpos($title, '+') !== false) {
                    $opt = explode('__', $title);
                    $opt = explode(' + ', $opt[1]);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '수면안대__'.$opt[0], $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '수면안대__'.$opt[1], $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '태그(120)', ($amount * 2));
                } else {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                }
            } else if (strpos($title, '가정용대야') !== false) {
                // 가정용대야__벚꽃색 + 실리콘마개
                if (strpos($title, '벚꽃색 + 실리콘마개') !== false) {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '가정용대야__벚꽃색', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '가정용대야__실리콘마개', $amount);
                } else if (strpos($title, '벚꽃색+마개') !== false) {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '가정용대야__벚꽃색', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '가정용대야__실리콘마개', $amount);
                } else {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                }
            } else if (strpos($title, '심리스의류') !== false) {
                // 심리스의류__XL(95)/골고루 4개, XL(95)/스킨 4개
                if (strpos($title, '골고루') !== false) {
                    $opt = explode('__', $title);
                    $opt = explode('/', $opt[1]);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '심리스의류__'.$opt[0].'/검정', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '심리스의류__'.$opt[0].'/흰색', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '심리스의류__'.$opt[0].'/밀크티', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '심리스의류__'.$opt[0].'/스킨', $amount);
                // 심리스의류__XL(95)/스킨 4개
                } else {
                    $title = explode(' ', $title);
                    unset($title[1]);
                    $title = implode('', $title);
                    $amount *= 4;
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                }
            } else if (strpos($title, '천연약쑥') !== false) {
                // 천연약쑥__20개입 (1박스), 10개입, 5개입
                $title = explode('__', $title);
                $title = $title[0];
                $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
            } else if (strpos($title, '발가락교정기') !== false) {
                // 발가락교정기__스트레칭 + 발가락링 세트
                if (strpos($title, '스트레칭 + 발가락링 세트') !== false) {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '발가락교정기__스트레칭 교정기', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '발가락교정기__발가락링 교정기', $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '태그(120)', ($amount * 2));
                } else {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '태그(120)', $amount);
                }
            } else if (strpos($title, '무릎보호대') !== false) {
                // 무릎보호대__M 2개(양무릎세트할인)
                if (strpos($title, '세트할인') !== false) {
                    $title = explode(' ', $title);
                    $title = $title[0];
                    $amount *= 2;
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                } else {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                }
            } else if (strpos($title, '발목보호대') !== false) {
                // 발목보호대__S(양발세트할인)
                if (strpos($title, '세트할인') !== false) {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, str_replace('양발세트할인', '좌', $title), $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, str_replace('양발세트할인', '우', $title), $amount);
                } else {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                }
            } else if (strpos($title, '코세척기') !== false) {
                // 코세척기__분말200포 + 코세척기+분말100포
                if (strpos($title, '분말200포') !== false) {
                    $amount *= 2;
                    $newStock2 = $this->setStockUpdateAdd($newStock2, str_replace('분말200포', '분말100포', $title), $amount);
                } else {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, str_replace('코세척기＋분말100포', '코세척기', $title), $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, str_replace('코세척기＋분말100포', '분말100포', $title), $amount);
                }
            } else if (strpos($title, '귀지압패치') !== false) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, $title, ($amount * 2));
                $newStock2 = $this->setStockUpdateAdd($newStock2, '귀혈자리지도', $amount);
            } else if (strpos($title, '유리창청소') !== false) {
                if (strpos($title, '방충망') !== false) {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, str_replace('유리창청소', '방충망청소', $title), $amount);
                } else {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
                }
            } else if (strpos($title, '방충망청소') !== false) {
                if (strpos($title, '유리창') !== false) {
                    $newStock2 = $this->setStockUpdateAdd($newStock2, '유리창청소__5-30mm （단계조절）', $amount);
                }
            // 나머지
            } else {
                $newStock2 = $this->setStockUpdateAdd($newStock2, $title, $amount);
            }
        }

        $today = date('Ymd');

        $db->beginTransaction();

        try {
            foreach ($newStock2 as $title => $amount) {
                // DB 찾기
                $titles = explode('__', $title);

                if (!empty($titles[1])) {
                    $row = $db->row("SELECT * FROM smartstore_stock WHERE title=? AND opt=?", array($titles[0], $titles[1]));
                } else {
                    $row = $db->row("SELECT * FROM smartstore_stock WHERE title=?", array($titles[0]));
                }

                // 재고정보가 없는 것은 오류 종료
                if (empty($row)) {
                    throw new Exception('['.$title.'] 상품의 재고 정보를 찾을 수 없습니다.');
                }

                // 오늘자 주문갯수 등록
                $rowOrder = $db->row("SELECT * FROM smartstore_order WHERE id=? and date=?", array($row['id'], $today));

                if (!empty($rowOrder)) {
                    $minusAmount = $rowOrder['sale_cnt'] + $amount;
                    $result = $db->query("UPDATE smartstore_order SET sale_cnt=? WHERE id=? and date=?", array($minusAmount, $row['id'], $today));
                } else {
                    $result = $db->query("INSERT INTO smartstore_order (sale_cnt, id, date) VALUES(?, ?, ?)", array($amount, $row['id'], $today));
                }

                if ($result != true) {
                    throw new Exception('['.$title.'] 상품의 일별 주문정보 업데이트에 실패했습니다.');
                }

                // 재고 차감
                $minusAmount = $row['amount'] - $amount;
                $result = $db->query("UPDATE smartstore_stock SET amount=? WHERE id=?", array($minusAmount, $row['id']));

                if ($result != true) {
                    throw new Exception('['.$title.'] 상품의 재고정보 업데이트에 실패했습니다.');
                }

                // echo '<pre>';
                // print_r($row);
                // echo '</pre>';
            }

            $db->commit();
            if (!empty($db)) $db->CloseConnection();
        } catch(Exception $e) {
            $db->rollBack();
            if (!empty($db)) $db->CloseConnection();
            exit($e->getMessage());
        }

        // echo '<pre>';
        // print_r($newStock1);
        // echo '</pre>';

        // echo '<pre>';
        // print_r($newStock2);
        // echo '</pre>';

        // exit();
    }

    public function setStockUpdateAdd($array, $title, $amount) {
        if (!empty($array[$title])) {
            $array[$title] += $amount;
        } else {
            $array[$title] = $amount;
        }

        return $array;
    }
}
