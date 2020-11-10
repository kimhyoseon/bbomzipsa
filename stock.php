<?php
ini_set("memory_limit" , -1);
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>재고관리</title>
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
  background-color: #f5f5f5;
}

.chart {
    text-align: center;
}
.chart > div {
    display: inline-block;
}
</style>

<body>
    <div class="container-fluid">
        <h3>재고관리</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" width="100">ID</th>
                    <th scope="col" width="100">분류</th>
                    <th scope="col" width="100">상품명</th>
                    <th scope="col" width="100">옵션</th>
                    <th scope="col" width="100">재고수량</th>
                    <th scope="col" width="100">입고기간</th>
                    <th scope="col" width="100">일평균판매량</th>
                    <th scope="col" width="100">남은기간</th>
                    <th scope="col" width="100"></th>
                </tr>
            </thead>
            <tbody id="list-item">
                <tr>
                    <td></td>
                    <td><input type="text" class="form-control-sm form-control inp-category"/></td>
                    <td><input type="text" class="form-control-sm form-control inp-title"/></td>
                    <td><input type="text" class="form-control-sm form-control inp-opt"/></td>
                    <td><input type="text" class="form-control-sm form-control inp-amount"/></td>
                    <td><input type="text" class="form-control-sm form-control inp-period"/></td>
                    <td></td>
                    <td></td>
                    <td><button class="btn btn-dark btn-sm btn-add">추가</button></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">히스토리</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <input type="hidden" id="edit-orderdetail-id" />
          <input type="hidden" id="edit-orderdetail-date" />
          <div class="modal-body"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-save-edit-orderdetail">Save changes</button>
          </div>
        </div>
      </div>
    </div>
</body>

<script>
function init() {
    $.ajax({
        type: 'POST',
        url: '../api/stock.php',
        dataType : 'json',
        timeout: 5000,
        data: {
            menu: 'list'
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);

            if (result) {
                var html = '';
                for (let index = 0; index < result.length; index++) {
                    const element = result[index];
                    var color = 'green';

                    if (element.remain == null) color = 'gray'
                    else if (element.remain < 5) color = 'red';
                    else if (element.remain < 15) color = 'orange';

                    html += '<tr data-id="' + element.id + '">';
                    html += '<td>' + element.id + '</td>';
                    html += '<td><input type="text" value="' + element.category + '" class="form-control-sm form-control inp-category"/></td>';
                    html += '<td><input type="text" value="' + element.title + '" class="form-control-sm form-control inp-title"/></td>';
                    html += '<td><input type="text" value="' + element.opt + '" class="form-control-sm form-control inp-opt"/></td>';
                    html += '<td><input type="text" value="' + element.amount + '" class="form-control-sm form-control inp-amount"/></td>';
                    html += '<td><input type="text" value="' + element.period + '" class="form-control-sm form-control inp-period"/></td>';
                    html += '<td>' + element.sale_cnt + '</td>';
                    html += '<td><font color="' + color + '">' + element.remain + '</font></td>';
                    html += '<td><button class="btn btn-info btn-sm mr-1 btn-history" data-toggle="modal" data-target="#exampleModal">히스토리</button><button class="btn btn-secondary btn-sm mr-1 btn-edit">수정</button><button class="btn btn-danger btn-sm btn-del">삭제</button></td>';
                    html += '</tr>';
                }

                $('#list-item').prepend(html);
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

    return false;
}

function editAmount(e) {
    if (confirm("수정하시겠습니까?")) {
        var row = $(e.currentTarget).closest('tr').eq(0);

        var data = {
            id: $(row).data('id'),
            category: $(row).find('.inp-category').val(),
            title: $(row).find('.inp-title').val(),
            opt: $(row).find('.inp-opt').val(),
            amount: $(row).find('.inp-amount').val(),
            period: $(row).find('.inp-period').val(),
        };

        $.ajax({
            type: 'POST',
            url: '../api/stock.php',
            dataType : 'json',
            timeout: 5000,
            data: {
                menu: 'edit',
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

function addAmount(e) {
    if (confirm("추가하시겠습니까?")) {
        var row = $(e.currentTarget).closest('tr').eq(0);

        var data = {
            category: $(row).find('.inp-category').val(),
            title: $(row).find('.inp-title').val(),
            opt: $(row).find('.inp-opt').val(),
            amount: $(row).find('.inp-amount').val(),
            period: $(row).find('.inp-period').val(),
        };

        $.ajax({
            type: 'POST',
            url: '../api/stock.php',
            dataType : 'json',
            timeout: 5000,
            data: {
                menu: 'add',
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

function delAmount(e) {
    if (confirm("삭제하시겠습니까?")) {
        var row = $(e.currentTarget).closest('tr').eq(0);

        var data = {
            id: $(row).data('id'),
        };

        $.ajax({
            type: 'POST',
            url: '../api/stock.php',
            dataType : 'json',
            timeout: 5000,
            data: {
                menu: 'history',
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

function showHistory(e) {
    var row = $(e.currentTarget).closest('tr').eq(0);

    var data = {
        id: $(row).data('id'),
    };

    $.ajax({
        type: 'POST',
        url: '../api/stock.php',
        dataType : 'json',
        timeout: 5000,
        data: {
            menu: 'history',
            data: data
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);

            var html = '';
            html += '<ul class="list-group">';
            for (let index = 0; index < result.length; index++) {
                html += '<li class="list-group-item">' + result[index]['regDate'] + ' / ' + result[index]['amount'] + '개 추가</li>';
            }
            html += '</ul>';

            $(".modal-body").html(html);
        },
        error: function(result, textStatus, jqXHR) {
            console.log(result);
            console.log(textStatus);
            console.log(jqXHR);

            $(".modal-body").html("<p>내역이 없습니다.</p>");
        },
        complete: function() {
        }
    });
}

$(document).ready(function() {
    init();

    $(document).on('click', '.btn-edit', editAmount);
    $(document).on('click', '.btn-add', addAmount);
    $(document).on('click', '.btn-del', delAmount);
    $(document).on('click', '.btn-history', showHistory);
});
</script>

</html>