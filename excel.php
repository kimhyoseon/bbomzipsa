<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!empty($_POST)) {    
    include_once('./class/PHPExcelDownload.php');
    $excel = new PHPExcelDownload();

    if ($_POST['type'] == 'korspo') {                
        $excel->korspo($_FILES['input']);
    } else if ($_POST['type'] == 'hanjin') {                
        $excel->hanjin($_FILES['input']);
    } else if ($_POST['type'] == 'sendall') {                
        $excel->sendall($_FILES['input'], $_FILES['output']);
    }    
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>엑셀변환</title>
</head>
<style>
.row{margin:10px 0}
</style>
<body>
    <div id="wrap">
        <!-- header -->
        <div id="header">
        </div>
        <!-- //header -->

        <!-- container -->
        <div id="container">
            <!-- content -->
            <div id="content">
            <form action="" method="POST" accept-charset="UTF-8" enctype="multipart/form-data">
                <div class="row">
                    <select id="type" name="type">
                        <option value="korspo">코리아스포츠</option>
                        <option value="hanjin">한진택배</option>
                        <option value="sendall">엑셀일괄발송</option>
                    </select>
                </div>
                <div class="row">
                    <label for="input">입력&nbsp;</label><input type="file" id="input" name="input" />
                    <label for="output">출력&nbsp;</label><input type="file" id="output" name="output" />
                </div>
                <div class="row">
                    <button type="submit">변환</button>
                </div>
            </form>
            </div>
            <!-- //content -->
        </div>
        <!-- //container -->

        <!-- footer -->
        <div id="footer">
        </div>
        <!-- //footer -->
    </div>
</body>
</html>