<?php
ini_set("memory_limit" , -1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$orderFile1 = $_SERVER['DOCUMENT_ROOT'].'/../crawler/log/jshk_order_data_new.json';
// $orderFile2 = $_SERVER['DOCUMENT_ROOT'].'/../crawler/log/jshk_order_data_old.json';
$chanFile = $_SERVER['DOCUMENT_ROOT'].'/data/dailychan.json';

// if (!file_exists($orderFile2)) exit('주문정보를 찾을 수 없습니다.');
if (!file_exists($chanFile)) exit('식단정보를 찾을 수 없습니다.');

$dailyChan = json_decode(file_get_contents($chanFile), true);
$orderData1 = json_decode(file_get_contents($orderFile1), true);
// $orderData2 = json_decode(file_get_contents($orderFile2), true);

// DB 정보 조합
$accountDb = parse_ini_file("./config/db.ini");
require_once './class/pdo.php';
$db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

$orderData2 = $db->query("SELECT * FROM smartstore_order_hanki_wait;");

$orderData = [];

if (!empty($orderData1)) {
  foreach ($orderData1 as $option => $amount) {
    if (empty($orderData[$option])) {
      $orderData[$option] = $amount;
    } else {
      $orderData[$option] = $orderData[$option] + $amount;
    }
  }
}
if (!empty($orderData2)) {
  foreach ($orderData2 as $option => $amount) {
    if (empty($orderData[$amount['opt']])) {
      $orderData[$amount['opt']] = $amount['amount'];
    } else {
      $orderData[$amount['opt']] = $orderData[$amount['opt']] + $amount['amount'];
    }
  }
}
$modTime = filemtime($orderFile1);
$modTime = date('n월 j일 H시', $modTime);

// echo '<pre>';
// print_r($orderData2);
// print_r($orderData);
// echo '</pre>';
// exit();

$week = array('일', '월', '화', '수', '목', '금', '토');
$setWeek = array('', '베스트반찬세트(월요일조리후발송)', '국민반찬세트(화요일조리후발송)', '아들반찬세트(수요일조리후발송)', '엄마반찬세트(목요일조리후발송)', '아빠반찬세트(금요일조리후발송)', '');
$orderInfo = $totalDelevery = $total = $orderData1 = $orderData2 = array();

foreach ($orderData as $option => $amount) {
  $optionFull = $option;
  $options = explode('/', $option);
  $date = trim(explode(':', $options[0])[1]);
  if (strpos($date, '반찬세트') === false) {
    $date = trim(preg_replace("/\([^)]+\)/", "", $date));
  }
  $set = trim(explode(':', $options[1])[1]);

  if (empty($orderData1[$date])) $orderData1[$date] = array();

  $orderData1[$date][] = [$set, $amount];
}

if (date('H') < 8) {
  $todayDate = date('Ymd');
} else {
  $todayDate = date('Ymd', strtotime('+ 1 days'));
}

$listDb = $db->query("SELECT * FROM smartstore_order_hanki WHERE date >= ? ORDER BY date ASC", array($todayDate));
// $listDb = $db->query("SELECT * FROM smartstore_order_hanki WHERE date < ? ORDER BY date ASC", array(date('Ymd')));

// echo '<pre>';
// print_r($orderData1);
// print_r($listDb);
// echo '</pre>';
// exit();

if (!empty($listDb)) {
  foreach ($listDb as $value) {
    $contents = unserialize($value['contents']);
    $option = $contents['13'];
    $amount = $contents['6'];
    $options = explode('/', $option);
    $date = trim(explode(':', $options[0])[1]);
    if (strpos($date, '반찬세트') === false) {
      $date = trim(preg_replace("/\([^)]+\)/", "", $date));
    }
    $set = trim(explode(':', $options[1])[1]);

    // echo '<pre>';
    // print_r($contents);
    // echo '</pre>';
    // exit();

    $isInsert = false;

    // id값이 없다면 그냥 추가
    if (empty($value['id'])) {
      if (!empty($orderData1[$date])) {
        foreach ($orderData1[$date] as $k => $v) {
          if ($set == $v[0]) {
            $orderData1[$date][$k][1] = $v[1] + $amount;
            $isInsert = true;
          }
        }
      }

      // 못찾은 경우에는 새로 삽입
      if ($isInsert == false) {
        $orderData1[$date][] = [$set, $amount];
      }

      if (empty($orderInfo[$date]['DB주문 추가'])) {
        $orderInfo[$date]['DB주문 추가'] = 1;
      } else {
        $orderInfo[$date]['DB주문 추가']++;
      }
    // , 구분으로 반찬을 변경한 경우
    } else if (strpos($set, ',') !== false) {
      if (!empty($orderData1[$date])) {
        foreach ($orderData1[$date] as $k => $v) {
          if (substr($set, 0, 4) == substr($v[0], 0, 4)) {
            $orderData1[$date][$k][1] = $v[1] - $amount;

            // if ($orderData1[$date][$k][1] < 1) {
            //   unset($orderData1[$date][$k]);
            // }
          }
        }
      }

      // 변경메뉴는 새로 삽입
      $orderData1[$date][] = [$set, $amount];

      if (empty($orderInfo[$date]['DB주문 변경'])) {
        $orderInfo[$date]['DB주문 변경'] = 1;
      } else {
        $orderInfo[$date]['DB주문 변경']++;
      }
    // 그 밖에는 관리자사이트에서 정보가 들어가기 때문에 PASS
    } else {
      if (empty($orderInfo[$date]['DB주문 별도주문추가'])) {
        $orderInfo[$date]['DB주문 별도주문추가'] = 1;
      } else {
        $orderInfo[$date]['DB주문 별도주문추가']++;
      }
    }
  }
}

for ($i = 0; $i < 14; $i++) {
  $tomo = $i + 1;
  $todaystr = "+ {$i} days";
  $tomostr = "+ {$tomo} days";

  $tomo = date('Ymd', strtotime($tomostr));
  $tomoKor = date('n월j일', strtotime($tomostr));

  $today = date('Ymd', strtotime($todaystr));
  $todayKor = date('n월j일', strtotime($todaystr));
  $todayWeek = $week[date("w", strtotime($todaystr))];
  $todaySet = $setWeek[date("w", strtotime($todaystr))];
  $todayFull = $todayKor."({$todayWeek})";

  // echo '<pre>';
  // print_r($today);
  // print_r($todayKor);
  // print_r($tomo);
  // print_r($tomoKor);
  // print_r($todayWeek);
  // echo '</pre>';
  // exit();

  // 기본찬 0개로 깔기
  if (!empty($dailyChan[$tomo])) {
    if (empty($orderData2[$todayFull])) $orderData2[$todayFull] = array();

    // 정기배송
    foreach ($dailyChan[$tomo] as $value) {
      if (empty($orderData2[$todayFull][$value])) $orderData2[$todayFull][$value] = 0;
    }

    // // 세트반찬
    // if (strpos($todaySet, '베스트반찬세트') !== false) {
    //     $option = array('매콤진미채무침', '계란말이', '매콤어묵볶음', '감자베이컨볶음', '메추리알조림', '더덕무침');
    // } else if (strpos($todaySet, '국민반찬세트') !== false) {
    //     $option = array('간장멸치견과류볶음', '건파래무침', '두부매콤조림', '연근조림', '간장미역줄기볶음', '무말랭이무침');
    // } else if (strpos($todaySet, '아들반찬세트') !== false) {
    //     $option = array('매콤어묵볶음', '계란말이', '메추리알조림', '매콤건새우볶음', '더덕무침', '소세지야채볶음');
    // } else if (strpos($todaySet, '엄마반찬세트') !== false) {
    //     $option = array('간장미역줄기볶음', '콩나물무침', '깻잎무침', '새우젓애호박볶음', '아삭이된장무침', '무나물');
    // } else if (strpos($todaySet, '아빠반찬세트') !== false) {
    //     $option = array('계란말이', '매콤멸치볶음', '두부조림', '검은콩조림', '건파래무침', '무말랭이무침');
    // }

    // foreach ($option as $v) {
    //   if (empty($orderData2[$todayFull][$v])) $orderData2[$todayFull][$v] = 0;
    // }

    if (empty($total[$todayFull])) $total[$todayFull] = 0;
  }

  // 정기배송 (수령일(내일) 기준으로 검색 후 오늘에 삽입)
  if (!empty($orderData1[$tomoKor]) && !empty($dailyChan[$tomo])) {
    // 정보 상세
    if (!empty($orderInfo[$tomoKor])) {
      $orderInfo[$todayFull] = $orderInfo[$tomoKor];
    }

    if (empty($orderData2[$todayFull])) $orderData2[$todayFull] = array();

    // 주문서를 돌면서
    foreach ($orderData1[$tomoKor] as $value) {
      $menuAmount = 0;

      $option = $value[0];
      $amount = $value[1];
      $menuIndex = false;

      if (strpos($option, ',') !== false) {
        // 괄호안 index를 배열로 추출
        $menuAmount = 8;
        preg_match('#\((.*?)\)#', $option, $match);
        $menuIndex = explode(',', $match[1]);
        // echo '<pre>';
        // print_r($menuIndex);
        // echo '</pre>';
        // exit();
      }
      else if (strpos($option, '1인') !== false) $menuAmount = 3;
      else if (strpos($option, '1.5인') !== false) $menuAmount = 3;
      else if (strpos($option, '2인') !== false) $menuAmount = 6;
      else if (strpos($option, '패밀리') !== false) $menuAmount = 8;

      if ($menuAmount == 0) exit($option.'올바르지 않은 옵션입니다.');

      for ($j = 0; $j < $menuAmount; $j++) {
          // if (empty($dailyChan[$tomo][$j])) exit($tomoKor.' '.$j.'번째 메뉴를 찾을 수 없습니다.');
          if (empty($dailyChan[$tomo][$j])) continue;

          if (!empty($menuIndex)) {
            if (in_array($j, $menuIndex) == false) continue;
          }

          if (empty($orderData2[$todayFull][$dailyChan[$tomo][$j]])) $orderData2[$todayFull][$dailyChan[$tomo][$j]] = 0;

          $orderData2[$todayFull][$dailyChan[$tomo][$j]] += $amount;

          if (empty($total[$todayFull])) $total[$todayFull] = 0;
          $total[$todayFull] += $amount;
      }

      // 1.5인 메인+국 처리
      if (strpos($option, '1.5인') !== false) {
        for ($j = 6; $j < 8; $j++) {
          if (empty($dailyChan[$tomo][$j])) continue;
          if (!empty($menuIndex)) {
            if (in_array($j, $menuIndex) == false) continue;
          }
          if (empty($orderData2[$todayFull][$dailyChan[$tomo][$j]])) $orderData2[$todayFull][$dailyChan[$tomo][$j]] = 0;
          $orderData2[$todayFull][$dailyChan[$tomo][$j]] += $amount;
          if (empty($total[$todayFull])) $total[$todayFull] = 0;
          $total[$todayFull] += $amount;
        }
      }

      if (empty($totalDelevery[$todayFull])) $totalDelevery[$todayFull] = 0;
      $totalDelevery[$todayFull] += $amount;
    }
  }

  // 조회 당일이고 현재시간이 조리마감 이후라면 세트반찬은 노출X
  if ($i == 0 && date('H') > 8) {
    $todaySet = '';
  }

  // 세트반찬
  if (!empty($orderData1[$todaySet])) {
    if (empty($orderData2[$todayFull])) $orderData2[$todayFull] = array();

    // 주문서를 돌면서
    foreach ($orderData1[$todaySet] as $value) {
      $set = $value[0];
      $amount = $value[1];

      if (strpos($todaySet, '베스트반찬세트') !== false) {
          if (strpos($set, '2인세트') !== false) $option = array('매콤진미채무침', '계란말이', '매콤어묵볶음', '감자베이컨볶음', '메추리알조림', '더덕무침');
          else if (strpos($set, '1인세트A') !== false) $option = array('매콤진미채무침', '계란말이', '매콤어묵볶음');
          else if (strpos($set, '1인세트B') !== false) $option = array('감자베이컨볶음', '메추리알조림', '더덕무침');
      } else if (strpos($todaySet, '국민반찬세트') !== false) {
          if (strpos($set, '2인세트') !== false) $option = array('간장멸치견과류볶음', '건파래무침', '두부매콤조림', '연근조림', '간장미역줄기볶음', '무말랭이무침');
          else if (strpos($set, '1인세트A') !== false) $option = array('간장멸치견과류볶음', '건파래무침', '두부매콤조림');
          else if (strpos($set, '1인세트B') !== false) $option = array('연근조림', '간장미역줄기볶음', '무말랭이무침');
      } else if (strpos($todaySet, '아들반찬세트') !== false) {
          if (strpos($set, '2인세트') !== false) $option = array('매콤어묵볶음', '계란말이', '메추리알조림', '매콤건새우볶음', '더덕무침', '소세지야채볶음');
          else if (strpos($set, '1인세트A') !== false) $option = array('매콤어묵볶음', '계란말이', '메추리알조림');
          else if (strpos($set, '1인세트B') !== false) $option = array('매콤건새우볶음', '더덕무침', '소세지야채볶음');
      } else if (strpos($todaySet, '엄마반찬세트') !== false) {
          if (strpos($set, '2인세트') !== false) $option = array('간장미역줄기볶음', '콩나물무침', '깻잎무침', '새우젓애호박볶음', '아삭이된장무침', '무나물');
          else if (strpos($set, '1인세트A') !== false) $option = array('간장미역줄기볶음', '콩나물무침', '깻잎무침');
          else if (strpos($set, '1인세트B') !== false) $option = array('새우젓애호박볶음', '아삭이된장무침', '무나물');
      } else if (strpos($todaySet, '아빠반찬세트') !== false) {
          if (strpos($set, '2인세트') !== false) $option = array('계란말이', '매콤멸치볶음', '두부조림', '검은콩조림', '건파래무침', '무말랭이무침');
          else if (strpos($set, '1인세트A') !== false) $option = array('계란말이', '매콤멸치볶음', '두부조림');
          else if (strpos($set, '1인세트B') !== false) $option = array('검은콩조림', '건파래무침', '무말랭이무침');
      }

      foreach ($option as $v) {
        if (empty($orderData2[$todayFull][$v])) $orderData2[$todayFull][$v] = 0;

        $orderData2[$todayFull][$v] += $amount;

        if (empty($total[$todayFull])) $total[$todayFull] = 0;
        $total[$todayFull] += $amount;
      }

      if (empty($totalDelevery[$todayFull])) $totalDelevery[$todayFull] = 0;
      $totalDelevery[$todayFull] += $amount;
    }

    unset($orderData1[$todaySet]);
  }

  // 정렬
  if (!empty($orderData2[$todayFull])) {
    $sort = array_values($orderData2[$todayFull]);
    array_multisort($sort, SORT_DESC, $orderData2[$todayFull]);

    if ($total[$todayFull] == 0) {
      unset($orderData2[$todayFull]);
    }
  }

  // 14일치 노출
  if (sizeOf($orderData2) > 14) break;
}

// echo '<pre>';
// print_r($orderInfo);
// print_r($totalDelevery);
// echo '</pre>';
// exit();

// echo '<pre>';
// print_r($orderData2);
// echo '</pre>';
// exit();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>정성한끼 주문표</title>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<style>
html,
body {
  height: 100%;
}

body {
  background-color: #fff;
}

.chart {
    text-align: center;
}
.chart > div {
    display: inline-block;
}

.wrap-chart,
.table {
  width: 100%;
}

h2 {
  width: 100%;
  text-align: center;
  color: green;
  margin-top: .5em;
}

.text-secondary {
  font-size: .8em;
  font-weight: normal;
}

.text-info {
  width: 100%;
  margin-bottom: .5em;
}

.table.print {
  table-layout: fixed;
  border-collapse: collapse;
  border-style: hidden;
  border-bottom: 1px solid #dee2e6;
  background-color: #fff;
  margin-bottom: 2em;
}

.table.print th {
  text-align: center;
  font-size:1.5rem;
  border-bottom: 1px solid #dee2e6;
}

.table.print td {
  border: 1px solid #dee2e6;
}

.table.print td p {
  margin-bottom: 0.1rem;
  text-align: center;
  font-size:1.5rem;
}

</style>

<body>
    <div class="container-fluid">
      <div class="container-fluid">
          <div class="row m-0 mt-3 mb-2">
              <h2>정성한끼 주문표</h2>
              <div class="text-info text-center">(<?=$modTime?> 업데이트)</div>
              <div class="wrap-chart">
                <?php foreach ($orderData2 as $key => $value) { ?>
                <table class="table print">
                <!-- // print_r($orderInfo);
                // print_r($totalDelevery); -->
                  <thead><tr><th scope="col">
                    <div><?=$key?> (<?=$total[$key]?>개 / <?=$totalDelevery[$key]?>명)</div>
                    <?php if (!empty($orderInfo[$key])) { ?>
                      <?php foreach ($orderInfo[$key] as $k => $v) { ?>
                        <div class="text-secondary"><?=$k?> <?=$v?></div>
                      <?php } ?>
                    <?php } ?>
                  </th></thead>
                  <tbody>
                    <td>
                    <?php foreach ($value as $k => $v) { ?>
                      <p><?=$k?> <?=$v?></p>
                    <?php } ?>
                    </td>
                  </tbody>
                </table>
                <?php } ?>
              </div>
          </div>
      </div>
    </div>
</body>

<script>
</script>

</html>