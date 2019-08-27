<?php
/**
 * 부동산 데이터 다운로드 (매주 토요일)  
 */
ini_set("display_errors", 1);
ini_set('max_execution_time', '0');
ini_set('memory_limit', '-1');

$filePath = "/home/dev/ppomzipsa/data/yoona";

$kbFileUrl = 'https://onland.kbstar.com/quics?asfilecode=534213';

// 주간
$kbFileName = 'WeeklySeries(시계열)_'.date('Ymd', strtotime('monday this week')).'.xlsx';
$tmpfileName = "_kb.xlsx";
$fileName = "kb.xlsx";
$postString = "_DOMAIN_CODE=bbs&_SITE_CODE=20/733&_SECURITY_CODE=Y&_FIXED_CODE=Y&_FILE_NAME={$kbFileName}";
$cmds = array();
$cmds[] = "sudo wget --post-data='{$postString}' {$kbFileUrl} -O {$filePath}/{$tmpfileName}";
$cmds[] = "sudo find {$filePath} -type f -empty -delete";
$cmds[] = "sudo mv {$filePath}/{$tmpfileName} {$filePath}/{$fileName}";
$cmds[] = "sudo rm {$filePath}/kb_*";

$cmd = implode(' && ', $cmds);
print_r($cmd);
//exec($cmd);

// 월간
$kbFileName2 = '★(월간)KB주택가격동향 시계열('.date('Y.m', strtotime('-1 Month')).').xlsx';
$tmpfileName2 = "_kbmonth.xlsx";
$fileName2 = "kbmonth.xlsx";
$postString2 = "_DOMAIN_CODE=bbs&_SITE_CODE=20/741&_SECURITY_CODE=Y&_FIXED_CODE=Y&_FILE_NAME={$kbFileName2}";
$cmds = array();
$cmds[] = "sudo wget --post-data='{$postString2}' {$kbFileUrl} -O {$filePath}/{$tmpfileName2}";
$cmds[] = "sudo find {$filePath} -type f -empty -delete";
$cmds[] = "sudo mv {$filePath}/{$tmpfileName2} {$filePath}/{$fileName2}";
$cmds[] = "sudo rm {$filePath}/kbmonth_*";

$cmd = implode(' && ', $cmds);
print_r($cmd);
// //exec($cmd);

// 행정안전부 평균연령 (월간, 매달 말일에 올라옴)
$ageFileUrl = 'http://27.101.213.4/downloadExcelEtc2.do?searchYearMonth=month&xlsStats=3';
$tmpAgefileName = "_agemonth.xlsx";
$fileNameAge = "agemonth.xlsx";
$year = date('Y');
$month = date('m', strtotime('-1 months'));
$postString3 = "sltOrgType=1&sltOrgLvl1=A&sltOrgLvl2=&sum=sum&gender=gender&searchYearStart={$year}&searchMonthStart={$month}&searchYearEnd={$year}&searchMonthEnd={$month}&sltOrderType=1&sltOrderValue=ASC&category=avgAge&state=3&stateMobile=1";
$cmds = array();
$cmds[] = "sudo wget --post-data='{$postString3}' '{$ageFileUrl}' -O {$filePath}/{$tmpAgefileName}";
$cmds[] = "sudo find {$filePath} -type f -empty -delete";
$cmds[] = "sudo mv {$filePath}/{$tmpAgefileName} {$filePath}/{$fileNameAge}";
$cmds[] = "sudo rm {$filePath}/agemonth_*";

$cmd = implode(' && ', $cmds);
print_r($cmd);
//exec($cmd);



