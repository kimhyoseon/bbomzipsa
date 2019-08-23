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
    
function drawChart(data, options) {
    if (!data) return false;
    if (!options) return false;    
    
    insertChart();

    var chart = new google.visualization[chartType]($('.chart-empty')[0]);
    
    chart.draw(data, options); 

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
    $('.list-menu').on('click', clickMenu);    

    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart']});

    // // Set a callback to run when the Google Visualization API is loaded.
    // google.charts.setOnLoadCallback(drawChart);
});
</script>

</html>