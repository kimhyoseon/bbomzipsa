<?php
// DB 정보 조합
$accountDb = parse_ini_file("./config/db.ini");
require_once './class/pdo.php';
$db = new Db($accountDb['DB_HOST'], $accountDb['DB_NAME'], $accountDb['DB_USER'], $accountDb['DB_PASSWORD']);

$todayDate = date('Ymd', strtotime('+ 1 days'));

$listDb = $db->query("SELECT * FROM smartstore_order_jshk WHERE date >= ? ORDER BY date ASC", array($todayDate));

$menus = ['1인세트(3개)', '2인세트(6개)', '1.5인세트(5개)', '패밀리세트(8개)'];
// echo '<pre>';
// print_r($orderData1);
// print_r($listDb);
// echo '</pre>';
// exit();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>정성한끼 주문등록</title>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript" src="/js/jsoneditor.min.js"></script>
</head>
<style>
html,
body {
  height: 100%;
}

input::placeholder {
  color: #ddd !important;
  font-style: italic;
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

.table.print {
  table-layout: fixed;
  border-collapse: collapse;
  border-style: hidden;
  border-top: 1px solid #dee2e6;
  background-color: #fff;
}

.table.print th {
  text-align: center;
  font-size:2.5rem;
}

.table.print td {
  border: 1px solid #dee2e6;
}

.table.print td p {
  margin-bottom: 0.1rem;
  text-align: center;
  font-size:2.2rem;
}

.table.print td .h6 {
  display: block;
  margin-bottom: 0.5rem;
  font-size:2.2rem;
}

</style>

<body>
    <div class="container-fluid">
      <h3 class="text-center m-2">정성한끼 주문등록</h3>
      <div class="row m-0 mt-3 mb-2">
        <form class="needs-validation w-100 mb-10" novalidate>
          <div class="form-row mb-2">
            <div class="col-md-2"><input class="form-control" type="text" name="search" placeholder="검색" onkeydown="return event.key != 'Enter';"/></div>
          </div>
        </form>

        <form class="needs-validation w-100 input-form" novalidate>
          <div class="form-row mb-2">
            <div class="col-md-2"><input class="form-control" type="text" name="date" placeholder="수령일 (,구분)" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1">
              <select class="form-control" name="menu">
                <option value="">- 세트메뉴 -</option>
                <?php foreach ($menus as $value) { ?>
                <option value="<?=$value?>"><?=$value?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-1"><input class="form-control" type="text" name="item_order_no" placeholder="상품주문번호" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input class="form-control" type="text" name="name" placeholder="수취인명" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input class="form-control" type="text" name="quantity" placeholder="수량" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input class="form-control" type="text" name="tel1" placeholder="수취인연락처1" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input class="form-control" type="text" name="tel2" placeholder="수취인연락처2" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input class="form-control" type="text" name="address" placeholder="배송지" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input class="form-control" type="text" name="message" placeholder="배송메세지" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input class="form-control" type="text" name="deposit" placeholder="입금정보" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1">
              <button class="btn btn-primary mr-1 btn-add">저장</button>
            </div>
          </div>
        </form>
      </div>

      <div class="border-top my-3"></div>

      <div class="wrap-chart">
      <?php
      if (!empty($listDb)) {
        foreach ($listDb as $value) {
          $id = $value['id'];
          $date = $value['date'];
          $item_order_no = $value['item_order_no'];
          $name = $value['name'];
          $quantity = $value['quantity'];
          $tel1 = $value['tel1'];
          $tel2 = $value['tel2'];
          $address = $value['address'];
          $message = $value['message'];
          $meun = $value['menu'];
      ?>
        <form class="needs-validation w-100" novalidate>
          <input type="hidden" name="id" value="<?=$id?>">
          <div class="form-row mb-2">
            <div class="col-md-1"><input value="<?=$date?>" class="form-control" type="text" name="date" placeholder="수령일 (,구분)" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1">
              <select class="form-control" name="menu">
                <?php foreach ($menus as $value) { ?>
                <?php $selected = ($value == $meun) ? 'selected="selected"' : ''; ?>
                <option value="<?=$value?>" <?=$selected?>><?=$value?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-1"><input value="<?=$item_order_no?>" class="form-control" type="text" name="item_order_no" placeholder="상품주문번호" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input value="<?=$name?>" class="form-control" type="text" name="name" placeholder="수취인명" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input value="<?=$quantity?>" class="form-control" type="text" name="quantity" placeholder="수량" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input value="<?=$tel1?>" class="form-control" type="text" name="tel1" placeholder="수취인연락처1" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input value="<?=$tel2?>" class="form-control" type="text" name="tel2" placeholder="수취인연락처2" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-3"><input value="<?=$address?>" class="form-control" type="text" name="address" placeholder="배송지" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1"><input value="<?=$message?>" class="form-control" type="text" name="message" placeholder="배송메세지" onkeydown="return event.key != 'Enter';"/></div>
            <div class="col-md-1">
              <button class="btn btn-primary mr-1 btn-edit">수정</button>
              <button class="btn btn-danger mr-1 btn-delete">삭제</button>
            </div>
          </div>
        </form>
      <?php
        }
      }
      ?>
      </div>
    </div>
</body>

<script>
  var searchOffset = 0;

  $(document).ready(function() {
    $(document).on('click', '.btn-add', addOrder);
    $(document).on('click', '.btn-edit', editOrder);
    $(document).on('click', '.btn-delete', delOrder);
    $(document).on('keydown', 'input[name=search]', evSearch);
  });

  function evSearch(e) {
    if (e.keyCode == 13) {
      getSearch();
    } else {
      searchOffset = 0;
    }
  }

  function resetInput() {
    $('.input-form input').val("");
    $('.input-form select').val("");
  }

  function getSearch() {
    var keyword = $("input[name=search]").val();

    if (!keyword) return false;

    $.ajax({
        type: 'POST',
        url: '../api/hanki.php',
        dataType : 'json',
        timeout: 5000,
        data: {
            menu: 'search',
            keyword: keyword,
            offset: searchOffset
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);
            searchOffset++;

            if (result == false) {
              alert('결과를 찾을 수 없습니다.');
              return false;
            }

            resetInput();

            for(name in result){
              if (!result[name]) continue;

              if ($('.input-form input[name="' + name + '"]').length > 0) {
                $('.input-form input[name="' + name + '"]').val(result[name]);
              } else if ($('.input-form select[name="' + name + '"]').length > 0) {
                $('.input-form select[name="' + name + '"]').val(result[name]);
              }

              console.log(result[name]);
            };
        },
        error: function(result, textStatus, jqXHR) {
            console.log(result);
            console.log(textStatus);
            console.log(jqXHR);
        },
        complete: function() {
        }
    });
  }

  function getData(e) {
    if (!e) return false;
    var data = {}
    var form = $(e.currentTarget).closest('form');

    form.find("input").each(function(){
      var name = $(this).attr("name");
      var val = $(this).val();
      console.log(name);
      console.log(val);

      if (val) {
        data[name] = val.trim();
      }
    });

    // 메뉴
    data["menu"] = form.find("select[name=menu]").val();

    return data;
  }

  function addOrder(e) {
    e.preventDefault();

    if (confirm("저장하시겠습니까?")) {
        var data = getData(e);

        console.log(data);

        // 필수값 체크
        if (!data["date"]) return false;
        if (!data["menu"]) return false;

        $.ajax({
            type: 'POST',
            url: '../api/hanki.php',
            dataType : 'json',
            timeout: 5000,
            data: {
                menu: 'addorder',
                data: data
            },
            success: function (result, textStatus) {
                console.log(result);
                console.log(textStatus);

                if (result['result'] == true) {
                    location.reload();
                }
            },
            error: function(result, textStatus, jqXHR) {
                console.log(result);
                console.log(textStatus);
                console.log(jqXHR);
            },
            complete: function() {
            }
        });
    }
  }

  function editOrder(e) {
    e.preventDefault();

    if (confirm("수정하시겠습니까?")) {
        var data = getData(e);

        console.log(data);

        // 필수값 체크
        if (!data["date"]) return false;
        if (!data["menu"]) return false;
        if (!data["id"]) return false;

        $.ajax({
            type: 'POST',
            url: '../api/hanki.php',
            dataType : 'json',
            timeout: 5000,
            data: {
                menu: 'editorder',
                data: data
            },
            success: function (result, textStatus) {
                console.log(result);
                console.log(textStatus);

                if (result['result'] == true) {
                    location.reload();
                }
            },
            error: function(result, textStatus, jqXHR) {
                console.log(result);
                console.log(textStatus);
                console.log(jqXHR);
            },
            complete: function() {
            }
        });
    }
  }

  function delOrder(e) {
    e.preventDefault();

    if (confirm("삭제하시겠습니까?")) {
        var data = getData(e);
        console.log(data);

        if (!data["id"]) return false;

        $.ajax({
            type: 'POST',
            url: '../api/hanki.php',
            dataType : 'json',
            timeout: 5000,
            data: {
                menu: 'delorder',
                data: data
            },
            success: function (result, textStatus) {
                console.log(result);
                console.log(textStatus);

                if (result['result'] == true) {
                    location.reload();
                }
            },
            error: function(result, textStatus, jqXHR) {
                console.log(result);
                console.log(textStatus);
                console.log(jqXHR);
            },
            complete: function() {
            }
        });
    }
  }

  /*var dataNew = null;
  var orderDetail = {};
  var editorJson = null;

  function clickMenu(e) {
    var menu = $(e.currentTarget).data('menu');
    var year = $('select[name=year]').val();
    var month = $('select[name=month]').val();

    if (!menu) return false;

    $.ajax({
        type: "POST",
        url: '../api/hanki.php',
        dataType : 'json',
        cache: false,
        timeout: 10000,
        data: {
            menu: menu,
            year: year,
            month: month
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);
            window['render' + menu](result);
            dataNew = result;
        },
        error: function(result, textStatus, jqXHR) {
            console.log(result);
            console.log(textStatus);
            console.log(jqXHR);
        },
        complete: function() {
        }
    });

    return false;
  }

  function renderorder(data) {
    if (!data) return false;

    var html = '';
    html += '<table class="table">';
    html += '<thead><tr><th scope="col">조리일</th><th scope="col">주문내역</th><th scope="col"></th></tr></thead>';
    html += '<tbody>';

    for (var i = 0; i < data.length; i++) {
      var contents = [];
      contents.push(data[i]['contents'][0]);
      contents.push(data[i]['contents'][3]);
      contents.push(data[i]['contents'][13]);

      html += '<tr data-id="' + data[i]['id'] + '">';
      html += '<td><input type="text" value="' + data[i]['date'] + '" class="form-control-sm form-control inp-date"/></td>';
      html += '<td>' + contents.join(' / ') + '</td>';
      html += '<td><button class="btn btn-secondary btn-sm mr-1 btn-edit-order">수정</button><button class="btn btn-primary btn-sm mr-1 btn-edit-order-detail" data-toggle="modal" data-target="#exampleModal">주문수정</button></td>';
      html += '</tr>';

      orderDetail[data[i]['id']] = data[i]['contents'];
    }

    html += '</tbody>';
    html += '</table>';

    $('.wrap-chart').html(html);
  }

  function renderview(data) {
    renderCalendar(data);
  }

  function rendernew(data) {
    renderCalendar(data);
    var html = '';
    html += '<div class="mt-5">';
    html += '<button type="button" class="save-new btn btn-primary btn-lg btn-block">저장</button>';
    html += '</div>';

    $('.wrap-chart').append(html);
  }

  function renderCalendar(data) {
    if (!data) return false;

    var html = '';
    html += '<table class="table print">';
    html += '<thead><tr><th scope="col">화</th><th scope="col">수</th><th scope="col">목</th><th scope="col">금</th><th scope="col"><span class="text-primary">토</span></th></tr></thead>';
    html += '<tbody>';

    var keys = Object.keys(data);

    while (keys.length > 0) {
      html += '<tr>';

      for (var i = 0; i < 7; i++) {
        var date = keys[0];

        if (!date) {
          keys.shift();

          if (i == 0 || i == 1) {
            continue;
          }

          html += '<td></td>';
          continue;
        }

        var dash = date.replace(/(\d{4})(\d{2})(\d{2})/g, '$1-$2-$3');
        var dayOfWeek = new Date(dash).getDay();

        if (dayOfWeek != i) {
          if (i == 0 || i == 1) {
            continue;
          }

          html += '<td></td>';
        } else {
          // 일, 월 제외
          if (i == 0 || i == 1) {
            keys.shift();
            continue;
          }

          // 메뉴
          var menuHtml = '';

          if (data[date] && data[date] != 0) {
            for (var j = 0; j < data[date].length; j++) {
              var txt = data[date][j];

              if (j > 5) {
                txt = '<b>' + txt + '</b>';
              }

              if (j < 3) {
                txt = '<span class="text-secondary">' + txt + '</span>';
              }

              menuHtml += '<p>' + txt + '</p>';

              // if (j == 2) {
              //   menuHtml += '<div style="border-top:3px dashed #4F9EC4"></div>';
              // } else if (i == 4 && j == 5) {
              //   menuHtml += '<div style="border-top:3px dashed #FF756D"></div>';
              // }
            }
          }

          var month = date.substr(4, 2).replace(/^0+/, '');
          var day = date.substr(6, 2).replace(/^0+/, '');

          day = month + '/' + day;

          if (i == 0) {
            day = '<span class="text-danger h6">' + day + '</span>';
          } else if (i == 6) {
            day = '<span class="text-primary h6">' + day + '</span>';
          } else {
            day = '<span class="h6">' + day + '</span>';
          }

          html += '<td>';
          html += day;
          html += '<div>';
          html += menuHtml;
          html += '<div>';
          html += '</td>';
          keys.shift();
        }
      }

      html += '</tr>';
    }

    html += '</tbody>';
    html += '</table>';

    $('.wrap-chart').html(html);
  }

  function saveNew() {
    if (!dataNew) return false;

    $.ajax({
        type: "POST",
        url: '../api/hanki.php',
        dataType : 'json',
        cache: false,
        timeout: 10000,
        data: {
            menu: 'saveNew',
            data: dataNew
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);

            if (result['result'] == true) {
              alert('저장했습니다.');
            } else {
              var month = $('select[name=month]').val();
              if (confirm('이미 생성된 정보가 있습니다.\n다시 생성하시겠습니다? (데이터 삭제주의)')) {
                if (confirm(month + '월 데이터 리셋이 맞습니까? 다시 한번 확인하세요.')) {
                  $.ajax({
                    type: "POST",
                    url: '../api/hanki.php',
                    dataType : 'json',
                    cache: false,
                    timeout: 10000,
                    data: {
                        menu: 'saveNewOver',
                        data: dataNew
                    },
                    success: function (result, textStatus) {
                        console.log(result);
                        console.log(textStatus);

                        if (result['result'] == true) {
                          alert('저장했습니다.');
                        } else {
                          alert('저장에 실패했습니다.');
                        }
                    },
                    error: function(result, textStatus, jqXHR) {
                        console.log(result);
                        console.log(textStatus);
                        console.log(jqXHR);
                    },
                    complete: function() {
                    }
                  });
                }
              }
            }
        },
        error: function(result, textStatus, jqXHR) {
            console.log(result);
            console.log(textStatus);
            console.log(jqXHR);
        },
        complete: function() {
        }
    });
  }

  function optionExcel() {
    window.open('hanki.php?excel=1', '_blank');
  }

  function editOrder(e) {
    if (confirm("수정하시겠습니까?")) {
        var row = $(e.currentTarget).closest('tr').eq(0);

        var data = {
            id: $(row).data('id'),
            date: $(row).find('.inp-date').val(),
        };

        // console.log(data);

        $.ajax({
            type: 'POST',
            url: '../api/hanki.php',
            dataType : 'json',
            timeout: 5000,
            data: {
                menu: 'editorder',
                data: data
            },
            success: function (result, textStatus) {
                console.log(result);
                console.log(textStatus);

                if (result['result'] == true) {
                    location.reload();
                }
            },
            error: function(result, textStatus, jqXHR) {
                console.log(result);
                console.log(textStatus);
                console.log(jqXHR);
            },
            complete: function() {
            }
        });
    }
  }

  function editOrderDetail(e) {
    if (editorJson) editorJson.destroy();

    var row = $(e.currentTarget).closest('tr').eq(0);

    var data = {
        id: $(row).data('id'),
        date: $(row).find('.inp-date').val(),
    };

    var jsonData = JSON.parse(JSON.stringify(orderDetail[data['id']]));
    var jsonDataConvert = {};

    for (const key in jsonData) {
      if (jsonData.hasOwnProperty(key)) {
        jsonDataConvert[key] = {default: jsonData[key]};
      }
    }

    $('#edit-orderdetail-id').val(data['id']);
    $('#edit-orderdetail-date').val(data['date']);

    editorJson = new JSONEditor($(".modal-body").get(0), {
      theme: 'bootstrap3',
      schema: {
        type: "object",
        title: data['id'],
        properties: jsonDataConvert
      }
    });
  }

  function saveEditOrderDetail() {
    var data = {
        id: $('#edit-orderdetail-id').val(),
        date: $('#edit-orderdetail-date').val(),
        contents: editorJson.getValue()
    };

    $.ajax({
        type: 'POST',
        url: '../api/hanki.php',
        dataType : 'json',
        timeout: 5000,
        data: {
            menu: 'editorder',
            data: data
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);

            if (result['result'] == true) {
                location.reload();
            }
        },
        error: function(result, textStatus, jqXHR) {
            console.log(result);
            console.log(textStatus);
            console.log(jqXHR);
        },
        complete: function() {
        }
    });
  }

  $(document).ready(function() {
    $(document).on('click', '.list-menu', clickMenu);
    $(document).on('click', '.save-new', saveNew);
    $(document).on('click', '.option-excel', optionExcel);
    $(document).on('click', '.btn-edit-order', editOrder);
    $(document).on('click', '.btn-edit-order-detail', editOrderDetail);
    $(document).on('click', '.btn-save-edit-orderdetail', saveEditOrderDetail);
  });*/
</script>

</html>