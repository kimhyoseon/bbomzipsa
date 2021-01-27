<?php
include_once('./class/PHPExcel.php');

class PHPExcelDownload {
    CONST TEST = false;
    CONST SHORTNAME = array(
        '반찬' => '신선식품',
    );

    CONST COLUMN_NAME = array(
        '상품주문번호' => 'item_order_no',
        '수취인명' => 'name',
        '옵션정보' => 'option',
        '수량' => 'quantity',
        '수취인연락처1' => 'tel1',
        '수취인연락처2' => 'tel2',
        '배송지' => 'address',
        '배송메세지' => 'message',
    );

    public function __construct() {
    }

    public function __destruct() {
    }

    /**
     * 엑셀일괄발송 (롯데택배)
     */
    public function sendallLotte($filesInput, $filesOutput, $type = null) {
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
            if ($key == 0 || $key == 1) {
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

        // echo '<pre>';
        // print_r($body);
        // echo '</pre>';
        // exit();

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

                        //if (in_array($value[$filterIndex['상품번호']], array('4324723046', '4529428871', '4530770714', '4318623001'))) continue;
                    }
                    //echo '<br/>';

                    // 모두 일치하는 경우
                    if ($isSame) {
                        $outputSheetData[$key][$filterIndex2['택배사']] = '롯데택배';
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
     * 정성한끼 엑셀정보 획득
     */
    public function jshkDataFilter($files, $date) {

        if (empty($date)) return false;

        // 날짜를 옵션과 동일하게 변경
        $dateKor = date('n월j일', strtotime($date));

        $accountDb = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config/db.ini');
        require_once $_SERVER['DOCUMENT_ROOT'].'/class/pdo.php';
        $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

        if (!empty($files)) {
            $body = array();
            $bodyTrash = array();
            $orderIds = array();
            $inputFile = $files['tmp_name'];
            $inputFileType = PHPExcel_IOFactory::identify($inputFile);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFile);
            $objPHPExcel->setActiveSheetIndex(0);
            $sheetData = $objPHPExcel->getActiveSheet()->toArray();

            // echo '<pre>';
            // print_r($sheetData);
            // echo '</pre>';
            // exit();

            $filter = array(
                '수취인명' => '받으시는분',
                '수취인연락처2' => '전화',
                '수취인연락처1' => '받으시는분 핸드폰',
                '배송지' => '총주소',
                '수량' => '수량',
                '배송메세지' => '특기사항',
            );

            $filterTmp = array(
                '옵션정보' => '옵션정보',
                '상품주문번호' => '상품주문번호',
            );

            $filterMerged = array_merge($filter, $filterTmp);

            $filterArray = array();
            $filterIndex = array();
            $filterIndexReverse = array();

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
                    $row = array();

                    $isTrash = false;

                    // 출력을 원하는 날짜와 옵션의 날짜가 다르다면 continue
                    if (!empty($value[$filterIndex['옵션정보']])) {
                        if (strpos($value[$filterIndex['옵션정보']], $dateKor) === false) $isTrash = true;
                    }

                    foreach ($value as $k => $v) {
                        if (empty($v)) continue;

                        if (in_array($k, $filterArray)) {
                            // DB조회를 위해 상품Id 저장
                            if ($filterIndexReverse[$k] == '상품주문번호') {
                                $orderIds[] = $v;
                            }

                            // 2020-12-24 우편번호 오류
                            if ($filterIndexReverse[$k] == '우편번호') {
                                continue;
                            }

                            // 옵션은 미리 날짜와 메뉴로 분리하자
                            if ($filterIndexReverse[$k] == '옵션정보') {
                                $options = $v;
                                if (strpos($options, '/') !== false) {
                                    $options = explode('/', $options);

                                    foreach ($options as $key => $value) {
                                        if (strpos($value, ':') !== false) {
                                            $option = trim(explode(':', $value)[1]);
                                            if ($key == 0) $row['date_kor'] = $option;
                                            else $row['menu'] = $option;
                                        }
                                    }
                                }
                            }

                            $row[self::COLUMN_NAME[$filterIndexReverse[$k]]] = $v;
                        }
                    }

                    // 선택한 날짜 엑셀주문만 필터링 되므로 고정으로 넣어주자 (오류날수도 있음)
                    $row['date'] = $date;

                    if ($isTrash == false) {
                        $body[] = $row;
                    } else {
                        $bodyTrash[$row['item_order_no']] = $row;
                    }

                }
            }

            if (empty($body)) {
                return false;
            }
        }

        // DB에서 같은 상품주문번호로 등록된 변경내용이 있는지 확인
        $orderIdsTxt = implode("','", $orderIds);
        $orderIdsTxt = "'{$orderIdsTxt}'";
        $orderFromDb = $db->query("SELECT * FROM smartstore_order_jshk WHERE item_order_no IN ({$orderIdsTxt}) ");

        if (!empty($orderFromDb)) {
            foreach ($orderFromDb as $value) {
                // 주문번호가 있고 동일한 주문번호가 엑셀에도 있다면 DB정보로 덮어쓰기
                if (!empty($value['item_order_no'])) {
                    foreach ($body as $k => $v) {
                        if ($v['item_order_no'] == $value['item_order_no']) {
                            foreach ($value as $kk => $vv) {
                                if (!empty($vv)) {
                                    $body[$k][$kk] = $vv;
                                }
                            }
                        }
                    }
                }
            }
        }

        // DB에서 해당 날짜 주문 가져오기
        $orderFromDb = $db->query("SELECT * FROM smartstore_order_jshk WHERE date=? ", array($date));

        // echo '<pre>';
        // print_r($orderFromDb);
        // print_r($bodyTrash);
        // echo '</pre>';

        if (!empty($orderFromDb)) {
            foreach ($orderFromDb as $value) {
                // 주문번호가 있고 동일한 주문번호가 엑셀에도 엑셀의 정보를 가져와서 빈 정보를 채워주자
                if (!empty($value['item_order_no'])) {
                    if (!empty($bodyTrash[$value['item_order_no']])) {
                        $isExist = false;
                        foreach ($body as $k => $v) {
                            if ($v['item_order_no'] == $value['item_order_no']) {
                                $isExist = True;
                                foreach ($bodyTrash[$value['item_order_no']] as $kk => $vv) {
                                    if (!empty($vv)) {
                                        $body[$k][$kk] = $vv;
                                    }
                                }
                            }
                        }

                        if ($isExist == false) {
                            $body[] = $bodyTrash[$value['item_order_no']];
                        }
                    }
                } else {
                    $body[] = $value;
                }
            }
        }

        // 오늘 날짜가 아닌 것은 body에서 삭제처리
        foreach ($body as $k => $v) {
            if ($v['date'] != $date) {
                unset($body[$k]);
            }
        }

        // echo '<pre>';
        // print_r($body);
        // echo '</pre>';
        // exit();

        // 정기배송 메뉴 추출
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/data/dailychan.json')) {
            $dailyChan = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/data/dailychan.json'), true);
        }

        if (empty($dailyChan)) exit('식단파일을 찾을 수 없습니다.(dailychan.json)');
        if (empty($dailyChan[$date])) exit($date.' 날짜의 식단파일을 찾을 수 없습니다.(dailychan.json)');

        foreach ($body as $key => $value) {
            $menus = array();

            if (strpos($value['menu'], '1인') !== false) $menuAmount = 3;
            else if (strpos($value['menu'], '1.5인') !== false) $menuAmount = 3;
            else if (strpos($value['menu'], '2인') !== false) $menuAmount = 6;
            else if (strpos($value['menu'], '패밀리') !== false) $menuAmount = 8;

            if ($menuAmount == 0) exit($option.'올바르지 않은 옵션입니다.');

            for ($i=0; $i < $menuAmount; $i++) {
                if (empty($dailyChan[$date][$i])) exit($i.'번째 메뉴를 찾을 수 없습니다.');
                $menus[] = $dailyChan[$date][$i];
            }

            // 1.5인 메인+국 처리
            if (strpos($option, '1.5인') !== false) {
                for ($i = 6; $i < 8; $i++) {
                    if (empty($dailyChan[$date][$i])) exit($i.'번째 메뉴를 찾을 수 없습니다.');
                    $menus[] = $dailyChan[$date][$i];
                }
            }

            $body[$key]['chan'] = $menus;
        }

        // 고객 주문반찬별 정렬 (2021-01-13)
        // 복합정렬이라 따로 key만 정렬해서 붙여넣음
        $sortCustomers = array();
        $sortNames = array();
        $frameNames = array();

        foreach ($body as $value) {
            $sortNames[] = $value['name'];
            $frameNames[] = $value['name'];
        }

        array_multisort($sortNames, SORT_ASC, $frameNames);

        foreach ($frameNames as $name) {
            foreach ($body as $key => $value) {
                if ($value['name'] == $name) {
                    if (strpos($value['menu'], '1.5인') !== false) {
                        $sortCustomers[] = 7;
                    } else {
                        $sortCustomers[] = sizeof($value['chan']);;
                    }
                }
            }
        }

        array_multisort($sortCustomers, SORT_ASC, $frameNames);

        $customersSorted = array();

        foreach ($frameNames as $name) {
            foreach ($body as $key => $value) {
                if ($value['name'] == $name) {
                    $customersSorted[] = $value;
                }
            }
        }

        $body = $customersSorted;

        $db->CloseConnection();

        // echo '<pre>';
        // print_r($body);
        // echo '</pre>';
        // exit();

        return array($filter, $filterMerged, $body);
    }

    /**
     * 정성한끼 택배
     */
    public function jshk($files, $date) {
        list($filter, $filterMerged, $body) = $this->jshkDataFilter($files, $date);

        // echo '<pre>';
        // print_r($filter);
        // print_r($filterMerged);
        // print_r($body);
        // echo '</pre>';
        // exit();

        $filterArray = array();
        $filterIndex = array();
        $filterIndexReverse = array();
        $header = array_values($filter);
        $stock = array();
        $sort = array();
        $addrMerge = array();
        $bodyOptimized = array();

        foreach ($body as $key => $value) {
            // 송장 제목 생성
            $body[$key]['title'] = '[신선식품 반찬] '.$value['menu'];
        }

        $filterKeys = ['name', 'tel2', 'tel1', 'address', 'quantity', 'title', 'message'];

        // filter 값만 다시 담기
        foreach ($body as $key => $value) {
            $bodyRow = array();

            foreach ($filterKeys as $k) {
                $bodyRow[$k] = '';

                if (!empty($value[$k])) {
                    $bodyRow[$k] = $value[$k];
                }
            }

            $bodyOptimized[] = $bodyRow;
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
        $widths = array(10, 15, 15, 80, 6, 50, 50);
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
        header('Content-Disposition: attachment; filename="정성한끼_롯데택배_운송장출력폼_'.date('Ymd').'.xlsx"');
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
     * 정성한끼 조리표
     */
    public function jshkBasket($files, $date) {
        list($filter, $filterMerged, $body) = $this->jshkDataFilter($files, $date);

        // echo '<pre>';
        // print_r($filterMerged);
        // print_r($body);
        // echo '</pre>';
        // exit();

        if (empty($body)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }

        $basket = array();
        $basketTotal = array();
        $receipe = array();
        $receipeTotal = array();
        $source = array();
        $sourceTotal = array();
        $customers = array();

        /**
         * 정성한끼 재료
         */
        $jshkData = include 'PHPExcelDataJshk.php';
        $jshkDataCustomer = include 'PHPExcelDataJshkCustomer.php';

        // 테스트 출력
        // foreach ($jshkData as $cookName => $ingredients) {
        //     if (empty($ingredients['_total'])) continue;

        //     echo "{$cookName}";
        //     echo(' ('.$ingredients['_total'][0].$ingredients['_total'][1].')');
        //     echo '</br>';
        //     echo(' - (총무게: '.$ingredients['_total'][0].$ingredients['_total'][1].')');

        //     echo '</br>';
        //     foreach ($ingredients as $ingredientName => $ingredientData) {
        //         if (substr($ingredientName, 0, 1) == '_') continue;
        //         echo($ingredientName);
        //         echo(' '.$ingredientData[0].$ingredientData[1]);
        //         echo '</br>';
        //     }

        //     echo '</br>';
        // }

        // echo '<pre>';
        // print_r($body);
        // echo '</pre>';
        // exit();

        foreach ($body as $items) {
            if (empty($items)) continue;

            $amount = $items['quantity'];
            $addr = $items['address'];
            $addrs = explode(' ', $addr);
            $customer = $items['name'].'('.$addrs[1].')';

            foreach ($items['chan'] as $cookName) {
                // 고객주의점
                if (empty($customers[$customer])) {
                    $customers[$customer] = array('cook' => array());

                    if (!empty($jshkDataCustomer[$customer])) {
                        $customers[$customer]['caution'] = $jshkDataCustomer[$customer];
                    }
                }

                // 레시피에 없다면 오류!
                if (empty($jshkData[$cookName])) {
                    exit('레시피에 해당 반찬이 등록되어 있지 않습니다.('.$cookName.')');
                }

                foreach ($jshkData as $cookNamePart => $ingredients) {
                    // strpos는 위험..
                    // if (strpos($cookName, $cookNamePart) !== false) {
                    if ($cookName == $cookNamePart) {
                        // 레시피 작성
                        if (empty($receipe[$cookName])) {
                            $receipe[$cookName] = array();
                            $receipeTotal[$cookName] = 0;
                        }

                        if (empty($customers[$customer]['cook'][$cookName])) {
                            $customers[$customer]['cook'][$cookName] = 0;
                        }

                        $receipeTotal[$cookName] += $amount;
                        $customers[$customer]['cook'][$cookName] += $amount;

                        foreach ($ingredients as $ingredientName => $ingredientData) {
                            if (substr($ingredientName, 0, 1) == '_') continue;

                            // 2번 값이 true 면 구매내역에 포함
                            if ($ingredientData[2] == true) {
                                // 장바구니에 넣지 않을 재료
                                if (!in_array($ingredientName, array('물'))) {
                                    // 모듬인 경우 모든재료 넣기
                                    if (!empty($jshkData[$ingredientName])) {
                                        foreach ($jshkData[$ingredientName] as $ingredientName2 => $ingredientData2) {
                                            if (!in_array($ingredientName2, array('물'))) {
                                                if (empty($basket[$ingredientName2])) {
                                                    $basket[$ingredientName2] = array();
                                                    $basketTotal[$ingredientName2] = array();
                                                }
                                                if (empty($basket[$ingredientName2][$ingredientData2[1]])) $basket[$ingredientName2][$ingredientData2[1]] = 0;

                                                $basket[$ingredientName2][$ingredientData2[1]] += $ingredientData[0] * $ingredientData2[0] * $amount;
                                                $basketTotal[$ingredientName2][$cookName] = $receipeTotal[$cookName];
                                            }
                                        }
                                    } else {
                                        if (empty($basket[$ingredientName])) {
                                            $basket[$ingredientName] = array();
                                            $basketTotal[$ingredientName] = array();
                                        }
                                        if (empty($basket[$ingredientName][$ingredientData[1]])) $basket[$ingredientName][$ingredientData[1]] = 0;

                                        $basket[$ingredientName][$ingredientData[1]] += $ingredientData[0] * $amount;
                                        $basketTotal[$ingredientName][$cookName] = $receipeTotal[$cookName];
                                    }
                                }
                            }

                            if (empty($receipe[$cookName][$ingredientName])) $receipe[$cookName][$ingredientName] = array();
                            if (empty($receipe[$cookName][$ingredientName][$ingredientData[1]])) $receipe[$cookName][$ingredientName][$ingredientData[1]] = 0;

                            $receipe[$cookName][$ingredientName][$ingredientData[1]] += $ingredientData[0] * $amount;

                            // 소스
                            if (!empty($jshkData[$ingredientName])) {
                                if (empty($sourceTotal[$ingredientName])) {
                                    $source[$ingredientName] = array();
                                    $sourceTotal[$ingredientName] = 0;
                                }

                                // 필요한 소스중량 합계
                                $sourceTotal[$ingredientName] = $sourceTotal[$ingredientName] + ($amount * $ingredientData[0]);
                            }
                        }

                        // echo '<pre>';
                        // print_r($receipe);
                        // print_r($source);
                        // echo '</pre>';
                        // exit();

                        break;
                    }
                }
            }
        }

        // echo '<pre>';
        // print_r($customers);
        // echo '</pre>';
        // exit();

        // 소스상세 계산
        foreach ($sourceTotal as $ingredientName => $weight) {
            $weightNew = 0;
            foreach ($jshkData[$ingredientName] as $sourceName => $sourceData) {
                if (empty($source[$ingredientName][$sourceName][$sourceData[1]])) $source[$ingredientName][$sourceName][$sourceData[1]] = 0;

                $sourceWeight = $sourceData[0] * $weight;
                if (strpos($ingredientName, '소스') !== false) {
                    $sourceWeight = round($sourceWeight * 1.5, -1); // 50% 소스 여유분
                } else {
                    $sourceWeight = round($sourceWeight);
                }
                $source[$ingredientName][$sourceName][$sourceData[1]] += $sourceWeight;
                $weightNew += $sourceWeight;
            }

            $sourceTotal[$ingredientName] = $weightNew;
        }

        // echo '<pre>';
        // print_r($sourceTotal);
        // print_r($source);
        // echo '</pre>';
        // exit();

        $data = array();
        $receipeMerge = $sourceMerge = array();
        $receipeRow = $sourceRow = $customerRow = 0;
        $data[] = array('재료표', '', '', '');

        foreach ($basket as $ingredientName => $ingredientData) {
            $basketDetail = '';

            if (!empty($basketTotal[$ingredientName]) && !in_array($ingredientName, array('다시육수', '당근', '양파', '쪽파'))) {
                foreach ($basketTotal[$ingredientName] as $cookName => $amount) {
                    $basketDetail .= $cookName.'('.$amount.') ';
                }
            }

            $data[] = array($ingredientName, $basketDetail, '', $ingredientData[key($ingredientData)].key($ingredientData));
        }

        // 조리표 순서
        $data[] = array('', '', '', '');
        $data[] = array('조리표', '', '', '');

        $receipeSort = array();
        foreach ($receipe as $cookName => $ingredientDatas) {
            if (in_array($cookName, array('콩나물무침', '숙주나물무침', '시금치무침', '건파래무침', '새우젓애호박볶음', '들깨무나물', '무나물', '간장미역줄기볶음')) == true) $receipeSort[] = 100;
            else if (strpos($cookName, '국') !== false) $receipeSort[] = 2;
            else if (strpos($cookName, '조림') !== false) $receipeSort[] = 3;
            else if (strpos($cookName, '간장') !== false) $receipeSort[] = 4;
            else if (strpos($cookName, '두부') !== false) $receipeSort[] = 5;
            else if (strpos($cookName, '멸치') !== false) $receipeSort[] = 5;
            else if (strpos($cookName, '어묵') !== false) $receipeSort[] = 5;
            else if (strpos($cookName, '매콤') !== false) $receipeSort[] = 6;
            else if (strpos($cookName, '볶음') !== false) $receipeSort[] = 7;
            else if (strpos($cookName, '고기') !== false) $receipeSort[] = 9;
            else if (strpos($cookName, '계란') !== false) $receipeSort[] = 10;
            else $receipeSort[] = 11;
        }

        array_multisort($receipeSort, SORT_ASC, $receipe);

        // echo '<pre>';
        // print_r($receipe);
        // echo '</pre>';
        // exit();

        foreach ($receipe as $cookName => $ingredientDatas) {
            $receipeMerge[] = sizeOf($ingredientDatas);
            foreach ($ingredientDatas as $ingredientName => $ingredientData) {
                // 소스는 계량을 국자로 변경
                $weightCookname = '';
                $weightCook = $ingredientData[key($ingredientData)];

                if (strpos($ingredientName, '소스') !== false) {

                    if ($weightCook >= 90) {
                        $ladle = floor($weightCook / 90);
                        $weightCook = $weightCook % 90;
                        $weightCookname .= $ladle.'(대)';
                    }

                    if ($weightCook > 0) {
                        $ladle = $weightCook / 15;
                        $ladle = round($ladle * 2) / 2;

                        if (!empty($weightCookname)) {
                            $weightCookname .= ' + ';
                        }

                        $weightCookname .= $ladle.'(소)';
                    }

                    $weightCookname .= ' ('.$ingredientData[key($ingredientData)].key($ingredientData).')';
                } else {
                    $weightCookname = $ingredientData[key($ingredientData)].key($ingredientData);
                }

                $data[] = array($cookName.'('.$jshkData[$cookName]['_total'][0].$jshkData[$cookName]['_total'][1].')', $receipeTotal[$cookName].'인분', $ingredientName, $weightCookname);
                $receipeRow++;
            }
        }

        // 고객
        $data[] = array('', '', '', '');
        $data[] = array('고객별 주문', '', '', '');

        // 고객 주문반찬별 정렬 (2021-01-13)
        // 복합정렬이라 따로 key만 정렬해서 붙여넣음
        $sortCustomers = array();
        $sortNames = array();
        $frameNames = array();

        foreach ($customers as $customerName => $customerData) {
            $sortNames[] = $customerName;
            $frameNames[] = $customerName;
        }

        // echo '<pre>';
        // print_r($sortNames);
        // echo '</pre>';
        // exit();

        array_multisort($sortNames, SORT_ASC, $frameNames);

        // echo '<pre>';
        // print_r($frameNames);
        // echo '</pre>';

        foreach ($frameNames as $name) {
            $count = sizeof($customers[$name]['cook']);

            if ($count == 5) {
                $count = 7;
            }

            $sortCustomers[] = $count;
        }

        array_multisort($sortCustomers, SORT_ASC, $frameNames);

        $customersSorted = array();

        foreach ($frameNames as $name) {
            $sets = array();
            foreach ($customers[$name]['cook'] as $chan => $amount) {
                $sets[] = $chan.$amount;
            }

            $set = implode(', ', $sets);

            if (!empty($customers[$name]['caution'])) {
                $frameNames .= ' (※'.implode(', ', $customers[$name]['caution']).')';
            }

            if (empty($customersSorted[$set])) {
                $customersSorted[$set] = array();
            }

            $customersSorted[$set][] = $name;
        }

        // echo '<pre>';
        // print_r($customersSorted);
        // echo '</pre>';
        // exit();

        $customers = $customersSorted;

        foreach ($customers as $set => $customerData) {
            $sets = explode(', ', $set);
            $setName = '';

            if (sizeof($sets) == 3) {
                $setName = '1인세트(3개)';
            } else if (sizeof($sets) == 6) {
                $setName = '2인세트(6개)';
            } else if (sizeof($sets) == 5) {
                $setName = '1.5인세트(5개)';
            } else if (sizeof($sets) == 8) {
                $setName = '패밀리세트(8개)';
            }

            $setName = $setName.' / (총 '.sizeof($customerData).'명)';

            $data[] = array($setName);
            $data[] = array($set);
            $data[] = array(implode(', ', $customerData));
            $customerRow = $customerRow + 3;
        }

        // 소스 없앰
        // $data[] = array('', '', '', '');
        // $data[] = array('소스', '', '', '');

        // foreach ($source as $cookName => $ingredientDatas) {
        //     $sourceMerge[] = sizeOf($ingredientDatas);
        //     $sourceOverload = 0;
        //     foreach ($ingredientDatas as $ingredientName => $ingredientData) {
        //         $sourceOverload += $ingredientData[key($ingredientData)];
        //         $data[] = array($cookName, $sourceTotal[$cookName].'g', $ingredientName, $ingredientData[key($ingredientData)].key($ingredientData).' ('.$sourceOverload.key($ingredientData).')');
        //         $sourceRow++;
        //     }
        // }

        $indexStartBasket = 1;
        $indexEndBasket = sizeof($basket) + 1;

        $indexStartReceipe = $indexEndBasket + 2;
        $indexStartReceipeMerge = $indexStartReceipe + 1;
        $indexEndReceipe = $indexStartReceipe + $receipeRow;

        $indexStartCustomer = $indexEndReceipe + 2;
        $indexStartCustomerMerge = $indexStartCustomer + 1;
        $indexEndCustomer = $indexStartCustomer + $customerRow;

        // 소스 없앰
        // $indexStartSource = $indexEndReceipe + 2;
        // $indexStartSourceMerge = $indexStartSource + 1;
        // $indexEndSource = $indexStartSource + $sourceRow;

        $indexLast = sizeOf($data);

        // echo '<pre>';
        // print_r($data);
        // print_r($customers);
        // print_r($basket);
        // print_r($basketTotal);
        // print_r($receipe);
        // print_r($receipeTotal);
        // print_r($body);
        // print_r($filterArray);
        // print_r($filterIndex);
        // print_r($filterIndexReverse);
        // print_r($receipeMerge);
        // print_r($indexEndBasket);echo '<br/>';
        // print_r($indexStartReceipe);echo '<br/>';
        // print_r($indexEndReceipe);echo '<br/>';
        // print_r($indexStartSource);echo '<br/>';
        // print_r($indexEndSource);echo '<br/>';
        // echo '</pre>';
        // exit();

        $divideHorizontalLines = array();
        $bgColor = 'FFD8D8D8';
        $lastChar = PHPExcel_Cell::stringFromColumnIndex((count($data[0]) - 1));

        $excel = new PHPExcel();

        // 폰트크기
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}{$indexLast}")->getFont()->setSize(14);

        // 헤더 진하게 & 정렬
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}1")->getFont()->setBold(true);
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartReceipe}:{$lastChar}{$indexStartReceipe}")->getFont()->setBold(true);
        $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartReceipe}:{$lastChar}{$indexStartReceipe}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartCustomer}:{$lastChar}{$indexStartCustomer}")->getFont()->setBold(true);
        $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartCustomer}:{$lastChar}{$indexStartCustomer}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 계량 정렬
        $excel->setActiveSheetIndex(0)->getStyle("D1:D{$indexEndReceipe}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        // $excel->setActiveSheetIndex(0)->getStyle("D1:D{$indexEndSource}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A1:{$lastChar}{$indexEndBasket}")->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK
                )
            )
        ));

        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartReceipe}:{$lastChar}{$indexEndReceipe}")->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK
                )
            )
        ));

        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartCustomer}:{$lastChar}{$indexEndCustomer}")->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK
                )
            )
        ));

        // 셀 구분선
        // $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartSource}:{$lastChar}{$indexEndSource}")->applyFromArray(array(
        //     'borders' => array(
        //         'allborders' => array(
        //             'style' => PHPExcel_Style_Border::BORDER_THIN
        //         ),
        //         'outline' => array(
        //             'style' => PHPExcel_Style_Border::BORDER_THICK
        //         )
        //     )
        // ));

        // 머지
        $excel->setActiveSheetIndex(0)->mergeCells("A1:{$lastChar}1");
        $excel->setActiveSheetIndex(0)->mergeCells("A{$indexStartReceipe}:{$lastChar}{$indexStartReceipe}");
        $excel->setActiveSheetIndex(0)->mergeCells("A{$indexStartCustomer}:{$lastChar}{$indexStartCustomer}");
        // $excel->setActiveSheetIndex(0)->mergeCells("A{$indexStartSource}:{$lastChar}{$indexStartSource}");

        for ($i = 2; $i <= $indexEndBasket; $i++) {
            $excel->setActiveSheetIndex(0)->mergeCells("B{$i}:C{$i}");
            $excel->setActiveSheetIndex(0)->getStyle("A{$i}:A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        $lineCustomer = 0;
        for ($i = $indexStartCustomer + 1; $i <= $indexEndCustomer; $i++) {
            $excel->setActiveSheetIndex(0)->mergeCells("A{$i}:D{$i}");
            if ($lineCustomer % 3 == 2) {
                $excel->getActiveSheet()->getRowDimension($i)->setRowHeight(80);
                $excel->setActiveSheetIndex(0)->getStyle("A{$i}:A{$i}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            } else if ($lineCustomer % 3 == 1) {
                $excel->getActiveSheet()->getRowDimension($i)->setRowHeight(40);
                $excel->setActiveSheetIndex(0)->getStyle("A{$i}:A{$i}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            } else if ($lineCustomer % 3 == 0) {
                $excel->setActiveSheetIndex(0)->getStyle("A{$i}:A{$i}")->getFont()->setBold(true);
                $excel->setActiveSheetIndex(0)->getStyle("A{$i}:A{$i}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
            $lineCustomer++;
        }

        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A2:{$lastChar}2")->applyFromArray(array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                )
            )
        ));

        // 셀 구분선
        $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartReceipeMerge}:{$lastChar}{$indexStartReceipeMerge}")->applyFromArray(array(
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                )
            )
        ));

        foreach ($receipeMerge as $value) {
            $rowNum = $indexStartReceipeMerge + $value - 1;
            $excel->setActiveSheetIndex(0)->mergeCells("A{$indexStartReceipeMerge}:A{$rowNum}");
            $excel->setActiveSheetIndex(0)->mergeCells("B{$indexStartReceipeMerge}:B{$rowNum}");
            $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartReceipeMerge}:A{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->getStyle("B{$indexStartReceipeMerge}:B{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartReceipeMerge}:A{$rowNum}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $excel->setActiveSheetIndex(0)->getStyle("B{$indexStartReceipeMerge}:B{$rowNum}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $indexStartReceipeMerge = $rowNum + 1;

            // 셀 구분선
            $excel->setActiveSheetIndex(0)->getStyle("A{$rowNum}:{$lastChar}{$rowNum}")->applyFromArray(array(
                'borders' => array(
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                    )
                )
            ));
        }

        // 셀 구분선
        // $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartSourceMerge}:{$lastChar}{$indexStartSourceMerge}")->applyFromArray(array(
        //     'borders' => array(
        //         'top' => array(
        //             'style' => PHPExcel_Style_Border::BORDER_MEDIUM
        //         )
        //     )
        // ));

        // foreach ($sourceMerge as $value) {
        //     $rowNum = $indexStartSourceMerge + $value - 1;
        //     $excel->setActiveSheetIndex(0)->mergeCells("A{$indexStartSourceMerge}:A{$rowNum}");
        //     $excel->setActiveSheetIndex(0)->mergeCells("B{$indexStartSourceMerge}:B{$rowNum}");
        //     $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartSourceMerge}:A{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //     $excel->setActiveSheetIndex(0)->getStyle("B{$indexStartSourceMerge}:B{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //     $excel->setActiveSheetIndex(0)->getStyle("A{$indexStartSourceMerge}:A{$rowNum}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //     $excel->setActiveSheetIndex(0)->getStyle("B{$indexStartSourceMerge}:B{$rowNum}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //     $indexStartSourceMerge = $rowNum + 1;

        //     // 셀 구분선
        //     $excel->setActiveSheetIndex(0)->getStyle("A{$rowNum}:{$lastChar}{$rowNum}")->applyFromArray(array(
        //         'borders' => array(
        //             'bottom' => array(
        //                 'style' => PHPExcel_Style_Border::BORDER_MEDIUM
        //             )
        //         )
        //     ));
        // }

        // 셀 폭 최적화
        $widths = array(30, 15, 30, 30);
        foreach ($widths as $i => $w) {
            $excel->setActiveSheetIndex(0)->getColumnDimension($this->columnChar($i))->setWidth($w);
        }

        // chr(10) 줄내림 처리
        $excel->getActiveSheet()->getStyle("A{$indexStartCustomer}:{$lastChar}{$indexEndCustomer}")->getAlignment()->setWrapText(true);

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');

        // 양식 설정
        ob_end_clean();
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="정성한끼_재료표_'.date('Ymd').'.xlsx"');
        header('Cache-Control: max-age=0');

        // 다운로드
        $writer->save('php://output');
        die();
    }

    /**
     * 정성한끼 스티커
     */
    public function jshkSticker($files, $date) {
        list($filter, $filterMerged, $body) = $this->jshkDataFilter($files, $date);

        if (empty($body)) {
            echo '내역이 존재하지 않습니다.';
            return false;
        }

        /**
         * 정성한끼 재료
         */
        $jshkData = include 'PHPExcelDataJshk.php';

        $optionIndex = array_search('옵션정보', array_keys($filterMerged));
        $amountIndex = array_search('수량', array_keys($filterMerged));
        $week = array('일', '월', '화', '수', '목', '금', '토');
        $rows = array();
        $sort = array();

        // 날짜는 발송일 -1
        $date = date('Ymd', strtotime('-1 days', strtotime($date)));

        foreach ($body as $items) {
            if (empty($items)) continue;
            foreach ($items['chan'] as $cookName) {
                $amount = $items['quantity'];

                // 레시피에 없다면 오류!
                if (empty($jshkData[$cookName])) {
                    exit('레시피에 해당 반찬이 등록되어 있지 않습니다.('.$cookName.')');
                }

                foreach ($jshkData as $cookNamePart => $ingredients) {
                    if ($cookName == $cookNamePart) {
                        $row = array();
                        $ingredientsSum = array();
                        $contents = array();

                        // 제품명 (중량)
                        $itemName = $cookName.' ('.$ingredients['_total'][0].$ingredients['_total'][1].')';

                        $row[] = $itemName;

                        foreach ($ingredients as $ingredientName => $ingredientData) {
                            if (substr($ingredientName, 0, 1) == '_') continue;

                            // 소스인 경우 소스재료로 넣기
                            if (!empty($jshkData[$ingredientName])) {
                                foreach ($jshkData[$ingredientName] as $sourceName => $sourceData) {
                                    if (!empty($jshkData[$sourceName])) {
                                        foreach ($jshkData[$sourceName] as $sourceName2 => $sourceData2) {
                                            // 재료명(원산지)
                                            if (in_array($sourceName2, array('물'))) continue;
                                            $ingredientsText = $sourceName2;
                                            if (!empty($sourceData2[3])) $ingredientsText .= '('.$sourceData2[3].')';
                                            if (!in_array($ingredientsText, $ingredientsSum)) {
                                                $ingredientsSum[] = $ingredientsText;
                                            }
                                        }
                                    } else {
                                        // 재료명(원산지)
                                        if (in_array($sourceName, array('물'))) continue;
                                        $ingredientsText = $sourceName;
                                        if (!empty($sourceData[3])) $ingredientsText .= '('.$sourceData[3].')';
                                        if (!in_array($ingredientsText, $ingredientsSum)) {
                                            $ingredientsSum[] = $ingredientsText;
                                        }
                                    }
                                }
                            // 일반재료
                            } else {
                                // 재료명(원산지)
                                if (in_array($ingredientName, array('물'))) continue;
                                $ingredientsText = $ingredientName;
                                if (!empty($ingredientData[3])) $ingredientsText .= '('.$ingredientData[3].')';
                                if (!in_array($ingredientsText, $ingredientsSum)) {
                                    $ingredientsSum[] = $ingredientsText;
                                }
                            }
                        }

                        // 재료
                        $row[] = '사용재료 : '.implode(', ', $ingredientsSum);

                        // 내용
                        $nextTime = strtotime('+'.$ingredients['_expired'][0].' '.$ingredients['_expired'][1], strtotime($date));
                        $contents[] = '만든날짜 : '.date('Y년 m월 d일', strtotime($date)).' ('.$week[date("w", strtotime($date))].')';
                        $contents[] = '유통기한 : '.date('Y년 m월 d일', $nextTime).' ('.$week[date("w", $nextTime)].')';
                        $contents[]  = '보관방법 : 냉장보관';
                        $contents[] = '판매업체 : 정성한끼';
                        $row[] = implode(chr(10), $contents);

                        // 안내
                        if (mb_substr($cookName, -1) == '국') {
                            $row[] = '냄비나 용기에 담아 데워서 드시기 바랍니다.';
                        } else {
                            $row[] = '반찬용기 그대로 전자레인지에 30초만 데우면 훨씬 맛있답니다.';
                        }

                        // 신고
                        $row[] = '부정/불량식품 신고는 국번없이 1399';

                        // echo '<pre>';
                        // print_r(mb_substr($cookName, -1));
                        // echo '</pre>';
                        // exit();
                        $sort[] = $itemName;
                        $rows[] = $row;

                        // 1개 이상인 경우 반복
                        if (!empty($amount) && $amount > 1) {
                            for ($i = 1; $i < $amount; $i++) {
                                $rows[] = $row;
                                $sort[] = $itemName;
                            }
                        }

                        break;
                    }
                }
            }
        }

        // 재료별로 정렬
        array_multisort($sort, SORT_ASC, $rows);

        $data = array();
        $data[] = array('제품명', '재료', '내용', '안내1', '안내2');

        foreach ($rows as $value) {
            $data[] = $value;
        }

        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // exit();

        $bgColor = 'FFD8D8D8';
        $lastChar = PHPExcel_Cell::stringFromColumnIndex((count($data[0]) - 1));
        $cntRow = sizeof($data);

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
        $widths = array(30, 100, 50, 30);
        foreach ($widths as $i => $w) {
            $excel->setActiveSheetIndex(0)->getColumnDimension($this->columnChar($i))->setWidth($w);
        }

        // 데이터 적용
        $excel->getActiveSheet()->fromArray($data, NULL, 'A1');

        // \r 줄내림 처리
        $excel->getActiveSheet()->getStyle("C2:C{$cntRow}")->getAlignment()->setWrapText(true);

        // 양식 설정
        ob_end_clean();
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="정성한끼_스티커_'.date('Ymd').'.xlsx"');
        header('Cache-Control: max-age=0');

        // 다운로드
        $writer->save('php://output');
        die();
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

    /**
     * 정성한끼 전용 옵션
     */
    public function getShortOptionJshk($option) {
        if (empty($option)) return '';

        if (strpos($option, '/') !== false) $option = explode('/', $option)[1];
        if (strpos($option, ':') !== false) $option = explode(':', $option)[1];
        $option = trim($option);

        if ($option == '오늘의국') {
            // array('일', '월', '화', '수', '목', '금', '토');
            if (in_array(date("w"), array(0, 1, 6))) $option = '소고기미역국';
            else if (in_array(date("w"), array(2, 4))) $option = '근대된장국';
            else if (in_array(date("w"), array(3, 5))) $option = '황태국';
        } else if (strpos($option, '매일반찬5종세트') !== false) {
            $option = '매일반찬5종세트';
        }

        return $option;
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
                    $newStock2 = $this->setStockUpdateAdd($newStock2, str_replace('코세척기+분말100포', '코세척기', $title), $amount);
                    $newStock2 = $this->setStockUpdateAdd($newStock2, str_replace('코세척기+분말100포', '분말100포', $title), $amount);
                }
            } else if (strpos($title, '귀지압패치') !== false) {
                $newStock2 = $this->setStockUpdateAdd($newStock2, $title, ($amount * 2));
                $newStock2 = $this->setStockUpdateAdd($newStock2, '귀혈자리지도', $amount);
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
