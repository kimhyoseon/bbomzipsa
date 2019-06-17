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
          <label>공통 단어</label>
          <div class="wrap-direct">
              <textarea class="form-control inp-direct" name="input-direct" rows="3"></textarea>                
          </div>
        </div>

        <div class="form-group">
          <label>필수 단어</label>
          <div class="wrap-direct">
              <textarea class="form-control inp-direct-req" name="input-direct-req" rows="3"></textarea>                
          </div>
        </div>

        <div class="form-group">
          <label>꼬리 전용 단어</label>
          <div class="wrap-direct">
              <textarea class="form-control inp-direct-tail" name="input-direct-tail" rows="3"></textarea>                
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
  var keywordsReq = $(".inp-direct-req").val().trim();      
  var keywordsTail = $(".inp-direct-tail").val().trim();      
  var keywordsMixed = [];
  var keywordEnd = ['추천', '효과'];  

  if (!keywords) return false;

  keywords = keywords.replace(/,/g , "");
  keywords = keywords.split(" ");  
  keywords = keywords.filter(function(item, pos) {
      return keywords.indexOf(item) == pos;
  });    

  if (keywordsReq) {
    keywordsReq = keywordsReq.replace(/,/g , "");  
    keywordsReq = keywordsReq.split(" ");  
    keywordsReq = keywordsReq.filter(function(item, pos) {
        return keywordsReq.indexOf(item) == pos;
    });      
    keywords = keywords.concat(keywordsReq);
  } else {
    keywordsReq = null;
  }  

  if (keywordsTail) {
    console.log(keywordsTail);
    keywordsTail = keywordsTail.replace(/,/g , "");  
    keywordsTail = keywordsTail.split(" ");  
    keywordsTail = keywordsTail.filter(function(item, pos) {
        return keywordsTail.indexOf(item) == pos;
    });    
    keywords = keywords.concat(keywordsTail);
  } else {
    keywordsTail = null;
  }  
  
  keywords = keywords.filter(Boolean);

  for (i = 0; i < keywordEnd.length; i++) {
    if (keywords.indexOf(keywordEnd[i]) !== -1) continue;            
    keywords.push(keywordEnd[i]);
  }    

  for (i = 0; i < keywords.length; i++) {
    if (keywordEnd.indexOf(keywords[i]) !== -1) continue;
    if (keywordsTail && keywordsTail.indexOf(keywords[i]) !== -1) continue;    
    
    for (j = 0; j < keywords.length; j++) {      
      if (keywords[i] == keywords[j]) continue;
      if (keywords[i].indexOf(keywords[j]) !== -1) continue;
      if (keywords[j].indexOf(keywords[i]) !== -1) continue;                  
      if (isWordOverlap(keywords[i], keywords[j]) == true) continue;
      if (keywordsReq) {
        if (keywordsReq.indexOf(keywords[i]) == -1 && keywordsReq.indexOf(keywords[j]) == -1 ) continue;
      }

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

// 2단어 이상 겹치는지 여부
function isWordOverlap(word1, word2) {  
  if (!word1) return false;
  if (!word2) return false;
  if (word1.length < 3) return false;
  if (word2.length < 3) return false;    

  for (k = 0; k < word1.length - 1; k++) {    
    if (word2.indexOf(word1.substr(k, 2)) !== -1) return true;
  }

  return false;
}
</script>
</html>