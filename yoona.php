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
<title>부동산 차트</title>
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
        <div class="row mt-2 mb-2">
            <ul class="list-group list-group-horizontal">
                <a href="#" class="list-menu" data-menu="simri"><li class="list-group-item">1. 심리차트</li></a>
                <a href="#" class="list-menu" data-menu="m1m2"><li class="list-group-item">2. M1/M2차트</li></a>
                <a href="#" class="list-menu" data-menu="jeonmang"><li class="list-group-item">3. 전망차트</li></a>
                <a href="#" class="list-menu" data-menu="choongjeon"><li class="list-group-item">4. 충전차트</li></a>
                <a href="#" class="list-menu" data-menu="miboonyang"><li class="list-group-item">5. 미분양차트</li></a>
                <a href="#" class="list-menu" data-menu="ingoo"><li class="list-group-item">6. 인구수차트</li></a>
                <a href="#" class="list-menu" data-menu="ingooidong"><li class="list-group-item">7. 인구이동차트</li></a>
                <a href="#" class="list-menu" data-menu="age"><li class="list-group-item">8. 평균연령차트</li></a>
                <a href="#" class="list-menu" data-menu="apt"><li class="list-group-item">9. 아파트</li></a>
            </ul>
        </div>

        <div class="row mt-2 mb-2">
            <div class="wrap-chart"></div>
        </div>
    </div>    
</body>

<script>
var menu = null;
var chartColumn = 3;
var chartType = 'LineChart';
var chartDetail = {};

function clickMenu(e) {    
    menu = $(e.currentTarget).data('menu');    
    
    if (!menu) return false;

    $.ajax({
        type: "POST",
        url: '../api/yoona.php',
        dataType : 'json',
        cache: false,
        timeout: 60000,
        data: {
            menu: menu
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);
            clearChart();
            window['render' + menu](result);
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

function clickSigoongoo(e) {    
    extra = $(e.currentTarget).data('code');    
    
    if (!extra) return false;

    $.ajax({
        type: "POST",
        url: '../api/yoona.php',
        dataType : 'json',
        cache: false,
        timeout: 60000,
        data: {
            menu: menu + '_detail',
            extra: extra 
        },
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus);                        
            window['render' + menu + 'Detail'](result);
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

function callApi(data, callback) {
    if (!data) return false;    

    $.ajax({
        type: "POST",
        url: '../api/yoona.php',
        dataType : 'json',
        cache: false,
        timeout: 60000,
        data: data,
        success: function (result, textStatus) {
            console.log(result);
            console.log(textStatus); 
            if (callback && typeof callback == 'function') {
                callback(result);
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

// 1. 심리차트
function rendersimri(data) {    
    // 데이터 준비
    if (data) {
        var chartData = {};
        var chartTitle = {};
        var year = null;
        
        for (var i = 0; i < data.length; i++) {
            if (i == 0) continue;
            if (i == 2) continue;
            if (i == 3) continue;
            
            if (i == 1) {
                for (var j = 0; j < data[i].length; j++) {                                    
                    if (data[i][j]) {
                        chartTitle[j] = data[i][j];
                    }
                }

                continue;
            }
            
            if (!data[i][0]) continue;
            
            // 연도 생성
            var date = data[i][0].split('/');
            
            if (date.length == 3) {
                year = date[0];
            } else {
                data[i][0] = year + '/' + data[i][0];
            }

            for (key in chartTitle) {
                if (!chartData[key]) {
                    chartData[key] = [];
                }
                
                chartData[key].push([data[i][0], parseFloat(data[i][key]), parseFloat(data[i][(parseInt(key) + 1)])]);
            }
        }
    }
        
    // console.log(chartTitle);
    // console.log(chartData);
    // return false;  

    chartColumn = 3;
    chartType = 'LineChart';
    
    for (key in chartTitle) {        
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '매도');
        data.addColumn('number', '매수');
        data.addRows(chartData[key]);

        var options = {
            'title': chartTitle[key],                 
            'legend': 'none', 
            'width': 400, 
            'height': 200
        };

        drawChart(data, options);
    }
}

// 2. m1m2 차트
function renderm1m2(data) {
    // 데이터 준비
    if (data) {
        var chartData = {};
        var m1m2Sum = 0;
        var m1m2Average = 0;
        chartData['m1'] = [];
        chartData['m1m2'] = [];
        
        for (var i = 0; i < data['m1'].length; i++) {
            var m1m2Ratio = parseFloat((data['m1'][i].DT/data['m2'][i].DT).toFixed(2));
            m1m2Sum += m1m2Ratio;
            chartData['m1'].push([data['m1'][i].PRD_DE, parseFloat(data['m1'][i].DT)]);            
            chartData['m1m2'].push([data['m1'][i].PRD_DE, m1m2Ratio]);
        }

        m1m2Average = parseFloat((m1m2Sum / data['m1'].length).toFixed(2));

        for (var i = 0; i < data['m1'].length; i++) {            
            chartData['m1m2'][i].push(m1m2Average);
        }

        console.log(chartData);        

        chartColumn = 1;
        
        chartType = 'ComboChart';

        for (key in chartData) {        
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Date');
            data.addColumn('number', key);
            
            if (key == 'm1m2') {
                data.addColumn('number', key);            
            }

            data.addRows(chartData[key]);

            var options = {
                'title': key,                 
                'legend': 'none', 
                'seriesType': 'bars',
                'width': 1000, 
                'height': 300,                
            };

            if (key == 'm1m2') {                
                options.series = {1: {type: 'line'}};
            }

            drawChart(data, options);
        }
    }
}

// 3. 부동산전망 차트
function renderjeonmang(data) {
    if (!data) return false;
    
    var chartData = {};
    var chartTitle = {};
    var year = null;
   
    // 날짜 맞춤
    data['maemae'].splice(4, 33);    
    
    for (var i = 0; i < data['maemae'].length; i++) {
        if (i == 0) continue;
        if (i == 2) continue;
        if (i == 3) continue;
        
        if (i == 1) {
            for (var j = 0; j < data['maemae'][i].length; j++) {                                    
                if (data['maemae'][i][j]) {
                    chartTitle[j] = data['maemae'][i][j];
                }
            }

            continue;
        }
        
        if (!data['maemae'][i][0]) continue;
        
        // 연도 생성        
        
        if (typeof data['maemae'][i][0] == 'string' && data['maemae'][i][0].indexOf('.') !== -1) {
            year = data['maemae'][i][0].split('.')[0]                        
            year = year.substring(1);         
            data['maemae'][i][0] = data['maemae'][i][0].substring(1);
        } else {
            data['maemae'][i][0] = year + '.' + data['maemae'][i][0];
        }

        for (key in chartTitle) {
            if (!chartData[key]) {
                chartData[key] = [];
            }
            
            var maemae = parseFloat(data['maemae'][i][(parseInt(key) + 1)]) - parseFloat(data['maemae'][i][(parseInt(key) + 3)]);
            var jeonse = parseFloat(data['jeonse'][i][(parseInt(key) + 1)]) - parseFloat(data['jeonse'][i][(parseInt(key) + 3)]);

            maemae = parseFloat(maemae.toFixed(2));
            jeonse = parseFloat(jeonse.toFixed(2));
            
            chartData[key].push([data['maemae'][i][0], maemae, jeonse]);
        }
    }
    
    // console.log(chartTitle);
    // console.log(chartData);
    // return false;  

    chartColumn = 2;
    chartType = 'ColumnChart';

    for (key in chartTitle) {        
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '매매');
        data.addColumn('number', '전세');
        data.addRows(chartData[key]);

        var options = {
            'title': chartTitle[key],                 
            'legend': 'none', 
            'width': 600, 
            'height': 300
        };

        drawChart(data, options);   
    } 
}

// 4. 충전차트
function renderchoongjeon(data) {       

    if (!data) return false;
    
    var chartData = {};
    var chartTitle = {};
    var year = null;
    
    for (var i = 0; i < data['jeonse'].length; i++) {
        if (i == 0) continue;
        if (i == 2) continue;            
        
        if (i == 1) {
            for (var j = 1; j < data['jeonse'][i].length; j++) {                                    
                if (data['jeonse'][i][j]) {
                    chartTitle[j] = data['jeonse'][i][j];
                }
            }

            continue;
        }
        
        if (!data['jeonse'][i][0]) continue;
        
        // 연도 생성
        var date = data['jeonse'][i][0].split('/');
        
        if (date.length == 3) {
            year = date[0];
        } else {
            data['jeonse'][i][0] = year + '/' + data['jeonse'][i][0];
        }

        for (key in chartTitle) {
            if (!chartData[key]) {
                chartData[key] = [];
            }

            var jeonse = parseFloat(data['jeonse'][i][key]);
            var maemae = parseFloat(data['maemae'][i][key]);
            var jeonse = parseFloat(jeonse.toFixed(2));
            var maemae = parseFloat(maemae.toFixed(2));
            var choongjeon = parseFloat((jeonse - maemae).toFixed(2));
            
            chartData[key].push([data['jeonse'][i][0], jeonse, maemae, choongjeon]);
        }
    }    
        
    // console.log(chartTitle);
    // console.log(chartData);
    // return false;  

    chartColumn = 3;
    chartType = 'LineChart';
    
    for (key in chartTitle) {        
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '전세');
        data.addColumn('number', '매매');
        data.addColumn('number', '충전');
        data.addRows(chartData[key]);

        var options = {
            'title': chartTitle[key],                 
            'legend': 'none', 
            'width': 400, 
            'height': 200,
            'series': {                
                2: { 
                    'targetAxisIndex': 1
                }
            },
        };

        drawChart(data, options);        
    }
}

// 5. 미분양차트
function rendermiboonyang(data) {   
    if (!data) return false;    
    
    var chartData = [];
    var index = 0;
    var min = 0;
    var max = 0;

    for (var i = 0; i < data.length; i++) {    
        if (!chartData[index]) chartData[index] = [];

        data[i]['DT'] = parseInt(data[i]['DT'])
        data[i]['C1_NM'] = data[i]['C1_NM'] + ' ' + data[i]['C2_NM'];
        
        // 추가 데이터
        if (!chartDetail[data[i]['C1_NM']]) {
            chartDetail[data[i]['C1_NM']] = data[i]['C1'] + '||' + data[i]['C2'];            
        }
        
        chartData[index].push([data[i]['C1_NM'], data[i]['DT']]);

        if (min > data[i]['DT']) {
            min = data[i]['DT'];
        }

        if (max < data[i]['DT']) {
            max = data[i]['DT'];
        }
        
        if (i > 0 && i % 20 == 0) index++;
    }          
    
    // console.log(chartData);    

    chartColumn = 1;
    
    chartType = 'ColumnChart';

    for (var i = 0; i < chartData.length; i++) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', '시군구');
        data.addColumn('number', '미분양');        
        data.addRows(chartData[i]);

        var options = {
            'title': '미분양차트',                 
            'legend': 'none',             
            'width': 1000, 
            'height': 300,
            vAxis: {                
                viewWindow: {
                    max: max,
                    min: min
                }
            }                                    
        };

        drawChart(data, options);        
    }   
    // if (!data) return false;
    
    // var chartData = {};
    
    // for (var i = 0; i < data.length; i++) {
    //     for (var j = 0; j < data[i].length; j++) {
    //         if (!chartData[data[i][j]['C1_NM']]) {
    //             chartData[data[i][j]['C1_NM']] = [];
    //         }

    //         chartData[data[i][j]['C1_NM']].push([data[i][j]['PRD_DE'], parseInt(data[i][j]['DT'])]);
    //     }
    // }       

    // chartColumn = 1;
    
    // chartType = 'ColumnChart';

    // for (key in chartData) {        
    //     var data = new google.visualization.DataTable();
    //     data.addColumn('string', 'Date');
    //     data.addColumn('number', key);       
    //     data.addRows(chartData[key]);

    //     var options = {
    //         'title': key,                 
    //         'legend': 'none',             
    //         'width': 1000, 
    //         'height': 300,                
    //     };

    //     drawChart(data, options);
    // }    
}

// 5-1. 미분양 지역상세
function rendermiboonyangDetail(data) {      
    if (!data) return false;        
    
    var chartData = {};
    
    for (var i = 0; i < data.length; i++) {        
        data[i]['C1_NM'] = data[i]['C1_NM'] + ' ' + data[i]['C2_NM'];

        if (!chartData[data[i]['C1_NM']]) {
            chartData[data[i]['C1_NM']] = [];
        }

        chartData[data[i]['C1_NM']].push([data[i]['PRD_DE'], parseInt(data[i]['DT'])]);        
    }       

    chartColumn = 1;
    
    chartType = 'ColumnChart';

    for (key in chartData) {        
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '미분양');       
        data.addRows(chartData[key]);

        var options = {
            'title': key,                 
            'legend': 'none',             
            'width': 1500, 
            'height': 300,                
        };

        drawChart(data, options);
    }        
}

// 6. 인구수 (최근 1개월 내)
function renderingoo(data) {      
    if (!data) return false;        
    
    var chartData = [];
    var index = 0;
    var min = 0;
    var max = 0;

    for (var i = 1; i < data.length; i++) {    
        if (!chartData[index]) chartData[index] = [];

        data[i]['DT'] = parseInt(data[i]['DT'])
        
        // 추가 데이터
        if (!chartDetail[data[i]['C1_NM']]) {
            chartDetail[data[i]['C1_NM']] = data[i]['C1'];
        }
        
        chartData[index].push([data[i]['C1_NM'], data[i]['DT']]);

        if (min > data[i]['DT']) {
            min = data[i]['DT'];
        }

        if (max < data[i]['DT']) {
            max = data[i]['DT'];
        }
        
        if (i > 0 && i % 20 == 0) index++;
    }          
    
    // console.log(chartData);    

    chartColumn = 1;
    
    chartType = 'ColumnChart';

    for (var i = 0; i < chartData.length; i++) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', '시군구');
        data.addColumn('number', '인구수');        
        data.addRows(chartData[i]);

        var options = {
            'title': '인구수',                 
            'legend': 'none',             
            'width': 1000, 
            'height': 300,
            vAxis: {                
                viewWindow: {
                    max: max,
                    min: min
                }
            }                                    
        };

        drawChart(data, options);        
    }
}

// 6-1. 인구수차트 지역상세
function renderingooDetail(data) {      
    if (!data) return false;
    
    var chartData = {};
    
    for (var i = 0; i < data['ingoo'].length; i++) {
        if (!chartData[data['ingoo'][i]['C1_NM']]) {
            chartData[data['ingoo'][i]['C1_NM']] = [];
        }

        chartData[data['ingoo'][i]['C1_NM']].push([data['ingoo'][i]['PRD_DE'], parseInt(data['ingoo'][i]['DT']), parseInt(data['sedae'][i]['DT'])]);        
    }       

    chartColumn = 1;
    
    chartType = 'LineChart';

    for (key in chartData) {        
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', '인구수');
        data.addColumn('number', '세대수');
        data.addRows(chartData[key]);

        var options = {
            'title': key,                 
            'legend': 'none',             
            'width': 1000, 
            'height': 300, 
            'series': {                
                1: { 
                    'targetAxisIndex': 1
                }
            },               
        };

        drawChart(data, options);
    }      
}

// 7. 인구이동차트(최근3개월)
function renderingooidong(data) {      
    if (!data) return false;    
    
    var chartData = [];
    var index = 0;
    var min = 0;
    var max = 0;

    for (var i = 0; i < data.length; i++) {    
        if (!chartData[index]) chartData[index] = [];

        data[i]['DT'] = parseInt(data[i]['DT'])
        
        // 추가 데이터
        if (!chartDetail[data[i]['C1_NM']]) {
            chartDetail[data[i]['C1_NM']] = data[i]['C1'];
        }
        
        chartData[index].push([data[i]['C1_NM'], data[i]['DT']]);

        if (min > data[i]['DT']) {
            min = data[i]['DT'];
        }

        if (max < data[i]['DT']) {
            max = data[i]['DT'];
        }
        
        if (i > 0 && i % 20 == 0) index++;
    }          
    
    // console.log(chartData);    

    chartColumn = 1;
    
    chartType = 'ColumnChart';

    for (var i = 0; i < chartData.length; i++) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', '시군구');
        data.addColumn('number', '순이동');        
        data.addRows(chartData[i]);

        var options = {
            'title': '인구이동차트(최근3개월)',                 
            'legend': 'none',             
            'width': 1000, 
            'height': 300,
            vAxis: {                
                viewWindow: {
                    max: max,
                    min: min
                }
            }                                    
        };

        drawChart(data, options);        
    }
}

// 7-1. 인구이동차트 지역상세
function renderingooidongDetail(data) {      
    if (!data) return false;      
    
    var chartData = {};
    
    for (var i = 0; i < data.length; i++) {        
        if (!chartData[data[i]['C1_NM']]) {
            chartData[data[i]['C1_NM']] = [];
        }

        chartData[data[i]['C1_NM']].push([data[i]['PRD_DE'], parseInt(data[i]['DT'])]);        
    }       

    chartColumn = 1;
    
    chartType = 'ColumnChart';

    for (key in chartData) {        
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', key);       
        data.addRows(chartData[key]);

        var options = {
            'title': key,                 
            'legend': 'none',             
            'width': 1500, 
            'height': 300,                
        };

        drawChart(data, options);
    }        
}

// 8. 평균연령
function renderage(data) {          
    if (!data) return false;    
    
    var chartData = {};    
    var min = 0;
    var max = 0;
    var date = '';

    for (var i = 0; i < data.length; i++) {    
        if (i == 0) continue;
        if (i == 1) {
            date = data[i][1];
            continue;
        }
        if (i == 2) continue;
        
        var codes = data[i][0].match(/\((.*)\)/);
        var name = '';
        var parent = 0;
        
        data[i][0] = data[i][0].substring(0, codes['index']);
        data[i][0] = data[i][0].split(' ').map(function(item) {
            if (item) {
                // 부모
                if (name) {
                    parent = name;
                }

                name = item.trim();
                return name;
            }
        });
        
        // console.log(codes[1]);
        // console.log(name);
        // return false;

        data[i][3] = parseFloat(data[i][3])
        
        // // 추가 데이터
        // if (!chartDetail[data[i]['C1_NM']]) {
        //     chartDetail[data[i]['C1_NM']] = data[i]['C1'];
        // }


        if (!chartData[parent]) chartData[parent] = [];
        chartData[parent].push([name, data[i][3]]);

        if (min > data[i][3]) {
            min = data[i][3];
        }

        if (max < data[i][3]) {
            max = data[i][3];
        }
    }          
    
    // console.log(chartData);    

    chartColumn = 1;
    
    chartType = 'ColumnChart';   

    for (key in chartData) {   
        // 오름차순 정렬
        chartData[key].sort(function (a, b) {
            if (a[1] > b[1]) {
                return 1;
            }
            if (a[1] < b[1]) {
                return -1;
            }
            
            return 0;
        });        

        var data = new google.visualization.DataTable();
        data.addColumn('string', '읍면동');
        data.addColumn('number', '평균연령');        
        data.addRows(chartData[key]);
        
        if (key == '0') title = '';        
        else title = key        

        var options = {
            'title': title + ' 평균연령 (' + date + ')',                 
            'legend': 'none',             
            'width': 1000, 
            'height': 300,
            vAxis: {                
                viewWindow: {
                    max: max,
                    min: min
                }
            }                                    
        };

        drawChart(data, options);        
    } 
}

// 9. 아파트
function renderapt(data) {          
    if (!data) return false;       
    
    var html = '<select class="form-control" id="apt-sigoongoo">';
    html    += '<option value="">- 시군구 -</option>';

    for (var i = 0; i < data.length; i++) {   
        html += '<option value="' + data[i]['code_sigoongoo'] + '">' + data[i]['sigoongoo'] + '</option>';
        html += '<li class="breadcrumb-item"><a href="#" class="sigoongoo" data-code="' + data[i]['C1'] + '">' + data[i]['C1_NM'] + '</a></li>';
    } 

    html += '</select>';        

    $('.wrap-chart').html(html);    
}

// 9-1. 아파트 랭킹 렌더링
function renderaptSiRank(data) {          
    if (!data) return false;           
    
    var chartData = [];
    
    for (var i = 0; i < data.length; i++) {                
        var aptName = data[i]['name_apt'] + ' (' + data[i]['upmyeondong'] + ', ' + data[i]['pyeong'] + '평)';
        
        // 추가 데이터
        if (!chartDetail[aptName]) {
            chartDetail[aptName] = data[i]['id'];
        }

        chartData.push([aptName, parseInt(data[i]['price'])]);        
    }       

    chartColumn = 1;
    
    chartType = 'ColumnChart';
    
    var data = new google.visualization.DataTable();
    data.addColumn('string', '아파트');
    data.addColumn('number', '평당가격');       
    data.addRows(chartData);

    var options = {
        'title': '평당가격 순위',                 
        'legend': 'none',             
        'width': 1500, 
        'height': 300,                
    };

    drawChart(data, options);
}

// 9-2. 아파트 매매/전세, 가치 렌더링
function renderaptDetail(data) {          
    if (!data) return false;           

    
}

// breadcrumb sample
// function renderage(data) {          
//     if (!data) return false;     
    
//     var html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';

//     for (var i = 0; i < data.length; i++) {    
         
//         html += '<li class="breadcrumb-item"><a href="#" class="sigoongoo" data-code="' + data[i]['C1'] + '">' + data[i]['C1_NM'] + '</a></li>';
//     } 

//     html += '</ol></nav>';        

//     $('.wrap-chart').html(html);    
// }
    
function drawChart(data, options) {
    if (!data) return false;
    if (!options) return false;    
    
    insertChart();

    var chart = new google.visualization[chartType]($('.chart-empty')[0]);
    
    chart.draw(data, options); 

    // 차트 클릭 
    google.visualization.events.addListener(chart, 'select', function() {
        var selection = chart.getSelection();

        var message = '';
        
        for (var i = 0; i < selection.length; i++) {
            var item = selection[i]; 
            var extra = chartDetail[data.getValue(item.row, 0)];

            console.log(data.getValue(item.row, 0))
            console.log(extra);        
            
            if (extra && window['render' + menu + 'Detail']) {

                $.ajax({
                    type: "POST",
                    url: '../api/yoona.php',
                    dataType : 'json',
                    cache: false,
                    timeout: 60000,
                    data: {
                        menu: menu + '_detail',
                        extra: extra 
                    },
                    success: function (result, textStatus) {
                        console.log(result);
                        console.log(textStatus);                        
                        window['render' + menu + 'Detail'](result);
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

            // console.log(data.getValue(item.row, 0));
            // console.log(data.getValue(0, item.column));
            // console.log(data.getValue(item.row, item.column));
        }        
    });

    $('.chart-empty').eq(0).removeClass('chart-empty');
}

function insertChart() {
    if ($('.chart-empty').length > 0) return false;

    var html = '';
    
    html += '<div class="row mt-2 mb-2">';    
    
    for (var i = 0; i < chartColumn; i++) {        
        html += '<div class="col-sm"><div class="chart chart-empty"></div></div>';        
    }
    
    html += '</div> ';    

    $('.wrap-chart').append(html);
}

function clearChart() {
    $('.wrap-chart').html('');
}

$(document).ready(function() {
    $(document).on('click', '.list-menu', clickMenu);
    $(document).on('click', '.sigoongoo', clickSigoongoo);
    $(document).on('change', '#apt-sigoongoo', function (){
        var code = $(this).val();
        if (!code) return false;        
        callApi({menu: 'apt_rank', extra: code}, renderaptSiRank);
    });
    // $('.list-menu').on('click', clickMenu);    
    

    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart']});

    // // Set a callback to run when the Google Visualization API is loaded.
    // google.charts.setOnLoadCallback(drawChart);
});
</script>

</html>