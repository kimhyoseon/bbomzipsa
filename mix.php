<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>키워드조합</title>
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

</style>
<body>    
    <form action="" method="POST" accept-charset="UTF-8" enctype="multipart/form-data" class="form-signin">        
        <h1 class="h3 mb-3 font-weight-normal">키워드 조합기</h1>

        <div class="form-group">            
            <div class="wrap-direct">
                <textarea class="form-control inp-direct" name="input-direct" rows="3"></textarea>                
            </div>
        </div>        
        
        <div class="form-group">
            <button class="btn btn-lg btn-primary btn-block" type="button">조합하기</button>        
        </div>

        <h1 class="h3 mb-3 font-weight-normal">결과 : <span class="total">0</span>개</h1>

        <div class="form-group">                 
            <div class="wrap-result">            
                <label>결과 (콤마)</label>       
                <textarea class="form-control inp-result" name="input-result" rows="3"></textarea>                
            </div>
        </div>

        <div class="form-group">                 
            <div class="wrap-result">            
                <label>결과 (한줄)</label>   
                <textarea class="form-control inp-result-line" name="input-result-line" rows="3"></textarea>                
            </div>
        </div>
    </form>    
</body>
<script>
$(".btn-block").click(function () {
    var keywords = $(".inp-direct").val().trim();    
    var keywordsMixed = [];
    var keywordEnd = ['추천', '효과'];

    if (!keywords) return false;

    keywords = keywords.split(" ");
    keywords = keywords.filter(Boolean);
    keywords = keywords.filter(function(item, pos) {
        return keywords.indexOf(item) == pos;
    });    

    for (i = 0; i < keywordEnd.length; i++) {
      if (keywords.indexOf(keywordEnd[i]) !== -1) continue;            
      keywords.push(keywordEnd[i]);
    }    

    for (i = 0; i < keywords.length; i++) {
      if (keywordEnd.indexOf(keywords[i]) !== -1) continue;
      
      for (j = 0; j < keywords.length; j++) {
          if (keywords[i] == keywords[j]) continue;
          if (keywords[i].indexOf(keywords[j]) !== -1) continue;
          if (keywords[j].indexOf(keywords[i]) !== -1) continue;            
          keywordsMixed.push(keywords[i] + keywords[j]);
      }
    }    

    var total = keywordsMixed.length;
    var has = false;

    for (i = 0; i < total; i++) {      
      has = false;
      
      for (j = 0; j < keywordEnd.length; j++) {        
        if (keywordsMixed[i].indexOf(keywordEnd[j]) !== -1) has = true;        
      }

      if (has == true) continue;

      for (j = 0; j < keywordEnd.length; j++) {                
        keywordsMixed.push(keywordsMixed[i] + keywordEnd[j]);
      }      
    }   

    // console.log(keywordsMixed);
    
    $(".total").text(keywordsMixed.length.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    $(".inp-result").val(keywordsMixed.join(","));
    $(".inp-result-line").val(keywordsMixed.join("\n"));
});
</script>
</html>