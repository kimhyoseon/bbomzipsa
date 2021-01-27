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
    public function jshkDataFilter($files) {
        // if (empty($files)) return false;
        $accountDb = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/config/db.ini');
        require_once $_SERVER['DOCUMENT_ROOT'].'/class/pdo.php';
        $db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

        if (!empty($files)) {
            $body = array();
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

            // $filter = array(
            //     '수량' => '수량',
            //     '상품명' => '상품명',
            //     '옵션정보' => '옵션정보',
            //     '수취인명' => '수취인명',
            // );

            // $filterMerged = $filter;

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
                '상품주문번호' => '상품주문번호',
                '상품별 총 주문금액' => '상품별 총 주문금액',
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

                    foreach ($value as $k => $v) {
                        if (empty($v)) continue;

                        if (in_array($k, $filterArray)) {
                            // 2020-12-24 우편번호 오류
                            if ($filterIndexReverse[$k] == '우편번호') {
                                continue;
                            }

                            $row[array_search($filterIndexReverse[$k], array_keys($filterMerged))] = $v;
                        }
                    }

                    $body[] = $row;
                }
            }

            if (empty($body)) {
                return false;
            }

            // 정기배송 주문 저장 및 오늘일자가 아닌 주문 제거
            $body = $this->setRegularMenu($body, $filterMerged, $db);
        }

        $setMenuMerge = array();

        // 세트메뉴 추출
        if (!empty($body)) {
            foreach ($body as $key => $items) {
                $setMenus = $this->getDailySetMenu($items, $filterMerged);

                if ($setMenus !== false) {
                    $setMenuMerge = array_merge($setMenuMerge, $setMenus);
                    unset($body[$key]);
                }
            }

            if (!empty($body)) {
                $body = array_merge($setMenuMerge, $body);
            } else {
                $body = $setMenuMerge;
            }
        }

        // 정기배송 메뉴 추출
        $regularMenus = $this->getRegularMenu($db, $filterMerged);

        // echo '<pre>';
        // print_r($regularMenus);
        // echo '</pre>';
        // exit();

        if (!empty($body)) {
            $body = array_merge($regularMenus, $body);
        } else {
            $body = $regularMenus;
        }

        $db->CloseConnection();

        // echo '<pre>';
        // print_r($body);
        // echo '</pre>';
        // exit();

        return array($filter, $filterMerged, $body);
    }

    /**
     * 정성한끼 정기배송 메뉴 가져오기
     */
    public function getRegularMenu($db, $filterMerged) {
        $menus = array();
        $optionIndex = array_search('옵션정보', array_keys($filterMerged));

        if (!empty($this->jshkDate)) {
            $date = $this->jshkDate;
        } else {
            $date = date('Ymd'); // 오늘자로 조회
        }

        $list = $db->query("SELECT * FROM smartstore_order_hanki WHERE date=? ", array($date));

        if (!empty($list)) {
            $date = date('Ymd', strtotime('+1 days', strtotime($date))); // 메뉴에서는 수령날짜로 조회

            if (file_exists($_SERVER['DOCUMENT_ROOT'].'/data/dailychan.json')) {
                $dailyChan = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/data/dailychan.json'), true);
            }

            if (empty($dailyChan)) exit('식단파일을 찾을 수 없습니다.(dailychan.json)');
            if (empty($dailyChan[$date])) exit($date.' 날짜의 식단파일을 찾을 수 없습니다.(dailychan.json)');

            // 주문리스트를 돌면서
            foreach ($list as $value) {
                $contents = unserialize($value['contents']);
                $menuIndex = false;

                // 반찬 가지수만큼 넣어줍니다.
                $option = $contents[$optionIndex];
                $options = explode('/', $option);
                $set = trim(explode(':', $options[1])[1]);

                $menuAmount = 0;

                if (strpos($set, ',') !== false) {
                    // 괄호안 index를 배열로 추출
                    $menuAmount = 8;
                    preg_match('#\((.*?)\)#', $set, $match);
                    $menuIndex = explode(',', $match[1]);
                }
                else if (strpos($option, '1인') !== false) $menuAmount = 3;
                else if (strpos($option, '1.5인') !== false) $menuAmount = 3;
                else if (strpos($option, '2인') !== false) $menuAmount = 6;
                else if (strpos($option, '패밀리') !== false) $menuAmount = 8;

                if ($menuAmount == 0) exit($option.'올바르지 않은 옵션입니다.');

                for ($i=0; $i < $menuAmount; $i++) {
                    if (empty($dailyChan[$date][$i])) exit($i.'번째 메뉴를 찾을 수 없습니다.');

                    if (!empty($menuIndex)) {
                        if (in_array($i, $menuIndex) == false) continue;
                    }

                    $contents[$optionIndex] = '/:'.$dailyChan[$date][$i];
                    $menus[] = $contents;
                }

                // 1.5인 메인+국 처리
                if (strpos($option, '1.5인') !== false) {
                    for ($i = 6; $i < 8; $i++) {
                        if (empty($dailyChan[$date][$i])) exit($i.'번째 메뉴를 찾을 수 없습니다.');

                        $contents[$optionIndex] = '/:'.$dailyChan[$date][$i];
                        $menus[] = $contents;
                    }
                }
            }
        }

        // echo '<pre>';
        // print_r($menus);
        // echo '</pre>';
        // exit();

        return $menus;
    }

    /**
     * 정성한끼 정기배송 저장
     */
    public function setRegularMenu($body, $filterMerged, $db) {
        if (empty($body)) return false;

        foreach ($body as $key => $value) {
            $optionIndex = array_search('옵션정보', array_keys($filterMerged));
            $idIndex = array_search('상품주문번호', array_keys($filterMerged));

            // 수령일이 포함된 엑셀인 경우에만 (정기배송)
            if (strpos($value[$optionIndex], '수령일') !== false) {
                $option = trim($value[$optionIndex]);
                $id = trim($value[$idIndex]);

                if (strlen($id) != 16) exit($id.' 올바른 ID가 아닙니다.');

                if (strpos($option, '/') !== false) $option = explode('/', $option)[0];
                if (strpos($option, ':') !== false) $option = explode(':', $option)[1];
                if (strpos($option, '(') !== false) $option = explode('(', $option)[0];
                $option = trim($option); // ex) 3월3일
                $option = str_replace('월', '-', $option);
                $option = str_replace('일', '', $option);
                $option = explode('-', $option);
                $month = $option[0];
                $day = $option[1];
                // $month = 2;
                // $day = 6;
                $timestamp = mktime(0, 0, 0, $month, $day, date('Y'));
                $timestampNow = time();
                $date = date("Ymd", $timestamp);
                $dateNow = date("Ymd", $timestampNow);

                // echo '<pre>';
                // print_r($month);
                // echo '<br/>';
                // print_r($day);
                // echo '<br/>';
                // print_r($timestamp);
                // echo '<br/>';
                // print_r($timestampNow);
                // echo '<br/>';
                // print_r($date);
                // echo '<br/>';
                // print_r($dateNow);
                // echo '</pre>';
                // exit();

                // 아침에 출력해서 조리하니 내일 수령까지는 가능

                // 수령일이 오늘보다 작거나 같다면 연도를 바꿔본다.
                if ($date <= $dateNow) {
                    $timestamp = mktime(0, 0, 0, $month, $day, date('Y', strtotime('+ 1years')));
                    $date = date("Ymd", $timestamp);

                    $datediff = $timestamp - $timestampNow;
                    $diff = round($datediff / (60 * 60 * 24));

                    // 년도를 바꿨을때 현재보다 2달 안이아니라면 날짜 미정 처리
                    if ($diff < 0 || $diff > 60) {
                        $date = '00000000';
                    }
                }

                $row = $db->row("SELECT COUNT(*) AS total FROM smartstore_order_hanki WHERE id=? ", array($id));

                // 상품주문번호로 조회해서 없다면 데이터를 넣어줍니다.
                if (!empty($row) && $row['total'] == 0) {
                    if ($date != '00000000') {
                        // 조리일은 하루 전이므로 -1 days
                        $date = date('Ymd', strtotime('-1 days', strtotime($date)));
                    }

                    $dbData = array(
                        'id' => $id,
                        'date' => $date,
                        'contents' => serialize($value),
                    );

                    $dbName = array_keys($dbData, true);
                    $dbValues = array_map(function ($val) {
                        return ':'.$val;
                    }, $dbName);
                    $dbName = implode(',', $dbName);
                    $dbValues = implode(',', $dbValues);

                    // echo '<pre>';
                    // print_r("INSERT INTO smartstore_order_hanki ({$dbName}) VALUES({$dbValues})");
                    // print_r($dbData);
                    // echo '</pre>';

                    $db->query("INSERT INTO smartstore_order_hanki ({$dbName}) VALUES({$dbValues})", $dbData);

                    unset($body[$key]);
                } else {
                    unset($body[$key]);
                }
            }
        }

        return $body;
    }

    /**
     * 정성한끼 세트메뉴
     */
    public function getDailySetMenu($items, $filterMerged) {
        $amountIndex = array_search('수량', array_keys($filterMerged));
        $optionIndex = array_search('옵션정보', array_keys($filterMerged));

        if (strpos($items[$optionIndex], '매일반찬5종세트') === false && strpos($items[$optionIndex], '반찬세트') === false) return false;

        $setMenus = array();

        if (strpos($items[$optionIndex], '매일반찬5종세트') !== false) {
            // array('일', '월', '화', '수', '목', '금', '토');
            if (in_array(date("w"), array(0, 1, 6))) $option = array('매콤진미채무침', '계란말이', '매콤어묵볶음', '감자베이컨볶음', '메추리알조림');
            else if (in_array(date("w"), array(2))) $option = array('간장멸치견과류볶음', '건파래무침', '두부매콤조림', '연근조림', '간장미역줄기볶음');
            else if (in_array(date("w"), array(3))) $option = array('매콤어묵볶음', '계란말이', '메추리알조림', '매콤건새우볶음', '소세지야채볶음');
            else if (in_array(date("w"), array(4))) $option = array('간장미역줄기볶음', '콩나물무침', '깻잎무침', '새우젓애호박볶음', '아삭이된장무침');
            else if (in_array(date("w"), array(5))) $option = array('계란말이', '매콤멸치볶음', '두부조림', '검은콩조림', '간장가지볶음');
        } else if (strpos($items[$optionIndex], '반찬세트') !== false) {
            if (strpos($items[$optionIndex], '베스트반찬세트') !== false) {
                if (strpos($items[$optionIndex], '2인세트') !== false) $option = array('매콤진미채무침', '계란말이', '매콤어묵볶음', '감자베이컨볶음', '메추리알조림', '더덕무침');
                else if (strpos($items[$optionIndex], '1인세트A') !== false) $option = array('매콤진미채무침', '계란말이', '매콤어묵볶음');
                else if (strpos($items[$optionIndex], '1인세트B') !== false) $option = array('감자베이컨볶음', '메추리알조림', '더덕무침');
            } else if (strpos($items[$optionIndex], '국민반찬세트') !== false) {
                if (strpos($items[$optionIndex], '2인세트') !== false) $option = array('간장멸치견과류볶음', '건파래무침', '두부매콤조림', '연근조림', '간장미역줄기볶음', '무말랭이무침');
                else if (strpos($items[$optionIndex], '1인세트A') !== false) $option = array('간장멸치견과류볶음', '건파래무침', '두부매콤조림');
                else if (strpos($items[$optionIndex], '1인세트B') !== false) $option = array('연근조림', '간장미역줄기볶음', '무말랭이무침');
            } else if (strpos($items[$optionIndex], '아들반찬세트') !== false) {
                if (strpos($items[$optionIndex], '2인세트') !== false) $option = array('매콤어묵볶음', '계란말이', '메추리알조림', '매콤건새우볶음', '더덕무침', '소세지야채볶음');
                else if (strpos($items[$optionIndex], '1인세트A') !== false) $option = array('매콤어묵볶음', '계란말이', '메추리알조림');
                else if (strpos($items[$optionIndex], '1인세트B') !== false) $option = array('매콤건새우볶음', '더덕무침', '소세지야채볶음');
            } else if (strpos($items[$optionIndex], '엄마반찬세트') !== false) {
                if (strpos($items[$optionIndex], '2인세트') !== false) $option = array('간장미역줄기볶음', '콩나물무침', '깻잎무침', '새우젓애호박볶음', '아삭이된장무침', '무나물');
                else if (strpos($items[$optionIndex], '1인세트A') !== false) $option = array('간장미역줄기볶음', '콩나물무침', '깻잎무침');
                else if (strpos($items[$optionIndex], '1인세트B') !== false) $option = array('새우젓애호박볶음', '아삭이된장무침', '무나물');
            } else if (strpos($items[$optionIndex], '아빠반찬세트') !== false) {
                if (strpos($items[$optionIndex], '2인세트') !== false) $option = array('계란말이', '매콤멸치볶음', '두부조림', '검은콩조림', '건파래무침', '무말랭이무침');
                else if (strpos($items[$optionIndex], '1인세트A') !== false) $option = array('계란말이', '매콤멸치볶음', '두부조림');
                else if (strpos($items[$optionIndex], '1인세트B') !== false) $option = array('검은콩조림', '건파래무침', '무말랭이무침');
            }
        }

        foreach ($option as $menu) {
            $newItems = $items;
            $newItems[$optionIndex] = '/:'.$menu;
            $setMenus[] = $newItems;
        }

        // echo '<pre>';
        // print_r($items);
        // print_r($setMenus);
        // echo '</pre>';

        return $setMenus;
    }

    /**
     * 정성한끼 cj택배
     */
    public function jshkCj($files) {
        list($filter, $filterMerged, $body) = $this->jshkDataFilter($files);

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
        $preBody = array();
        $bodyOptimized = array();

        $optionIndex = array_search('옵션정보', array_keys($filterMerged));
        $titleIndex = array_search('상품명', array_keys($filterMerged));
        // $amountIndex = array_search('수량', array_keys($filterMerged));
        // $customerIndex = array_search('수취인명', array_keys($filterMerged));

        foreach ($body as $key => $value) {
            $body[$key][$optionIndex] = $this->getShortOptionJshk($value[$optionIndex]);
            $body[$key][$titleIndex] = $this->getShortName($value[$titleIndex]);
            $body[$key][$titleIndex] = '['.$body[$key][$titleIndex].'] '.$body[$key][$optionIndex];
        }

        // filter 값만 다시 담기
        foreach ($body as $key => $value) {
            $bodyRow = array();

            for ($i = 0; $i < sizeof($filter); $i++) {
                $bodyRow[$i] = '';

                if (!empty($value[$i])) {
                    $bodyRow[$i] = $value[$i];
                }
            }

            $preBody[] = $bodyRow;
        }

        // 각각의 반찬이 아닌 세트로 변경 (2021-01-13)
        $unique = '';
        $count = 0;

        // echo '<pre>';
        // print_r($preBody);
        // echo '</pre>';
        // exit();

        foreach ($preBody as $key => $value) {
            if ($unique != '' && $unique != $value[5] || $key + 1 == sizeof($preBody)) {
                // 마지막 라인인 경우 +1
                if ($key + 1 == sizeof($preBody)) {
                    $count++;
                }

                $title = '';

                // 갯수별 타이틀 생성
                if ($count == 3) {
                    $title = '1인세트(3개)';
                } else if ($count == 5) {
                    $title = '1.5인세트(5개)';
                } else if ($count == 6) {
                    $title = '2인세트(6개)';
                } else if ($count == 8) {
                    $title = '패밀리세트(8개)';
                }

                // 1.5를 2인 위로 올리자
                if ($count == 5) {
                    $count = 7;
                }

                // 상품명을 쌓인 갯수에 대한 메뉴로 변경
                $row[7] = '[신선식품 반찬] '.$title;

                // 쌓였던 갯수를 파악후 바디에 넣어줌
                $bodyOptimized[] = $row;

                $count = 1;
            } else {
                // 주소로 유니크값을 잡는다
                $count++;
                $row = $value;
            }

            $unique = $value[5];
        }

        // 정렬
        $sortName = array();
        $sortCount = array();

        foreach ($bodyOptimized as $key => $value) {
            $sortName[] = $value[0];
        }

        array_multisort($sortName, SORT_DESC, $bodyOptimized);

        foreach ($bodyOptimized as $key => $value) {
            if (strpos($value[7], '1.5인') !== false) {
                $sortCount[] = '[신선식품 반찬] 3.5인세트(5개)';
            } else {
                $sortCount[] = $value[7];
            }
        }

        array_multisort($sortCount, SORT_ASC, $bodyOptimized);

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
        header('Content-Disposition: attachment; filename="롯데택배_운송장출력폼_'.date('Ymd').'.xlsx"');
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
    public function jshkBasket($files) {
        list($filter, $filterMerged, $body) = $this->jshkDataFilter($files);

        echo '<pre>';
        print_r($filterMerged);
        print_r($body);
        echo '</pre>';
        exit();

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

        $optionIndex = array_search('옵션정보', array_keys($filterMerged));
        $amountIndex = array_search('수량', array_keys($filterMerged));
        $customerIndex = array_search('수취인명', array_keys($filterMerged));
        $postIndex = array_search('우편번호', array_keys($filterMerged));
        $addrIndex = array_search('배송지', array_keys($filterMerged));

        foreach ($body as $items) {
            if (empty($items)) continue;
            if (empty($items[$optionIndex])) continue;

            $cookName = $this->getShortOptionJshk($items[$optionIndex]);
            $amount = $items[$amountIndex];
            $addr = $items[$addrIndex];
            $addrs = explode(' ', $addr);
            $customer = $items[$customerIndex].'('.$addrs[1].')';

            // echo '<pre>';
            // print_r($addr);
            // echo '</pre>';
            // exit();

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
    public function jshkSticker($files) {
        list($filter, $filterMerged, $body) = $this->jshkDataFilter($files);

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

        foreach ($body as $items) {
            if (empty($items)) continue;
            if (empty($items[$optionIndex])) continue;

            $cookName = $this->getShortOptionJshk($items[$optionIndex]);
            $amount = $items[$amountIndex];

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

                    if (!empty($this->jshkDate)) {
                        $date = $this->jshkDate;
                    } else {
                        $date = date('Ymd'); // 오늘자로 조회
                    }

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