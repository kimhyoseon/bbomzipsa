<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!empty($_POST)) {
    include_once('./class/PHPExcelDownload.php');
    $excel = new PHPExcelDownload();

    if ($_POST['type'] == 'korspo') {
        $excel->korspo($_FILES['input']);
    } else if ($_POST['type'] == 'korspo_price') {
        $excel->korspoPrice($_FILES['input']);
    } else if ($_POST['type'] == 'hanjin') {
        $excel->hanjin($_FILES['input']);
    } else if ($_POST['type'] == 'cj') {
        $excel->cj($_FILES['input']);
    } else if ($_POST['type'] == 'cjok') {
        $excel->cj($_FILES['input'], true);
    } else if ($_POST['type'] == 'sendall') {
        if (!empty($_POST['input-direct'])) {
            $excel->sendall($excel->convertDirectDataToInputExcelData($_POST['input-direct']), $_FILES['output'], 'kospo');
        } else {
            $excel->sendall($_FILES['input'], $_FILES['output'], null);
        }
    } else if ($_POST['type'] == 'sendall_lotte') {
        $excel->sendallLotte($_FILES['input'], $_FILES['output'], null);
    } else if ($_POST['type'] == 'hanki') {
        $excel->jshkCj($_FILES['input']);
    } else if ($_POST['type'] == 'jshk_basket') {
        $excel->jshkBasket($_FILES['input']);
    } else if ($_POST['type'] == 'jshk_sticker') {
        $excel->jshkSticker($_FILES['input']);
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>엑셀변환</title>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<style>
html,
body {
  height: 100%;
}

body {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-align: center;
  align-items: center;
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #f5f5f5;
}

.form-signin {
  width: 100%;
  max-width: 330px;
  padding: 15px;
  margin: auto;
}
.form-signin .checkbox {
  font-weight: 400;
}
.form-signin .form-control {
  position: relative;
  box-sizing: border-box;
  height: auto;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

#btn-hanki,
.wrap-direct,
.inp-direct,
.wrap-output {
  display: none;
}
</style>
<body>
    <form action="" method="POST" accept-charset="UTF-8" enctype="multipart/form-data" class="form-signin">
        <h1 class="h3 mb-3 font-weight-normal">엑셀변환기</h1>

        <div class="form-group">
            <select id="type" name="type" class="form-control">
                <option value="cj">CJ택배</option>
                <option value="cjok">CJ택배(중복)</option>
                <option value="sendall">엑셀일괄발송</option>
                <option value="korspo">코리아스포츠</option>
                <option value="korspo_price">코리아스포츠(계산서)</option>
                <option value="hanki">정성한끼</option>
                <option value="jshk_basket">정성한끼 조리표</option>
                <option value="jshk_sticker">정성한끼 스티커</option>
                <option value="sendall_lotte">엑셀일괄발송(롯데)</option>
                <!-- <option value="wmp">위메프</option>                                 -->
                <!-- <option value="wmp_sendall">위메프일괄발송</option>                                 -->
            </select>
        </div>

        <div class="form-group">
            <div class="wrap-input">
                <label for="input">입력파일</label><input type="file" id="input" name="input" class="form-control-file"/>
            </div>

            <div class="wrap-direct">
                <textarea class="form-control inp-direct" name="input-direct" rows="3"></textarea>
                <div class="checkbox mb-3">
                    <label>
                        <input type="checkbox" class="input-direct" value="remember-me"> 직접입력
                    </label>
                </div>
            </div>

            <div class="wrap-output">
                <label for="output">출력파일</label><input type="file" id="output" name="output" class="form-control-file"/>
            </div>
        </div>

        <div class="form-group">
            <button id="btn-submit" class="btn btn-lg btn-primary btn-block" type="submit">변환하기</button>
            <button id="btn-hanki" class="btn btn-lg btn-primary btn-block" type="button">다운받기</button>
        </div>
    </form>
</body>
<script>
$('select[name=type]').change(function(){
    if ($(this).val() == 'sendall' || $(this).val() == 'sendall_lotte') {
        $('.wrap-output, .wrap-direct').show();
    } else {
        $('.wrap-output, .wrap-direct').hide();
    }

    // if ($(this).val() == 'hanki') {
    //     $('#btn-submit').hide();
    //     $('#btn-hanki').show();
    // } else {
    //     $('#btn-submit').show();
    //     $('#btn-hanki').hide();
    // }
});

$('.input-direct').change(function(){
    if ($(this).is(':checked')) {
        $('.inp-direct').show();
        $('.wrap-input').hide();
    } else {
        $('.inp-direct').hide();
        $('.wrap-input').show();
    }
});

// $('#btn-hanki').click(function(){
//     $('#type').val('hanki');
//     $('.form-signin').attr('target', '_blank').submit();

//     // setTimeout(() => {
//         // $('#type').val('jshk_basket');
//         // $('.form-signin').attr('target', '_blank').submit();
//         // $('#type').val('jshk_sticker');
//         // $('.form-signin').attr('target', '_blank').submit();
//     // }, 1000);
// });
</script>
</html>