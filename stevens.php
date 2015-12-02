<?php
date_default_timezone_set('America/New_York');
$timeStampBase = strtotime(date('Y-m-d 00:00', strtotime('-3 hours')));
$lateNightService = 0;
$year = date("Y");   	
$today = date("D M j G:i:s T Y");   	

function getDateStr($color='gray'){
    global $timeStampBase; 

    $green_day = '4178880,7:40,8:00,8:20,8:40,9:00,9:20,9:40,10:00,10:20,10:40,11:00,11:20,16:30,16:50,17:10,17:30,17:50,18:10,18:30,18:50,19:30,19:50,20:10,20:30,20:50,21:10,21:30,21:50,22:30,22:50,23:10,23:30.
        4178868,+19+0.
        4178866,+11+0';


    $red_day = '4178880,7:15,7:30,7:45,8:00,8:15,8:30,8:45,9:00,9:15,9:30,9:45,10:00,10:15,10:30,10:45,11:00,11:15,11:45,12:45,13:45,14:15,15:15,15:45,16:10,16:35,17:00,17:15,17:30,17:45,18:00,18:15,18:30,18:45,19:30,19:45,20:00,20:15,20:30,20:45,21:00,21:15,21:30,22:00,23:00,23:30,24:00.
        4176264,+7+0.
        4111538,+13+0';

    $gray_day = '4178880,7:15,7:30,7:40,7:55,8:05,8:20,8:30,8:45,8:55,9:10,9:20,9:35,9:45,10:00,10:10,10:25,10:35,10:50,11:00,11:40,12:05,12:30,12:55,13:20,13:45,14:10,15:00,15:25,15:50,16:15,16:40,17:05,17:15,17:30,17:40,17:55,18:05,18:20,18:30,18:45,18:55,19:10,19:20,19:45,20:00,20:10,20:25,20:35,20:50,21:00,21:15,21:25,21:40,22:05,22:30,22:55,24:00,24:30,25:05,25:40,26:10.
       4141102,+7+18';//7->normal time span  18->late night time span
    $day6 = '4178880,7:30,8:05,8:40,9:15,9:50,10:25,11:00,11:35,12:10,13:20,13:55,14:30,15:05,15:40,16:15,16:50,17:25,18:00,18:35,19:10,19:45,20:20,21:30,22:05,22:40,23:15,23:50,24:25,25:00,25:35,26:10.
       4141102,+17+0.';
    $day0 = '4178880,12:00,12:35,13:10,13:45,14:20,14:55,15:30,16:40,17:15,17:50,18:25,19:00,19:35,20:10,20:45,21:20,22:30,23:05,23:40,24:15,24:50,25:25,26:00.
       4141102,+15+0.';
    $dayOfWeek = date('w', $timeStampBase);
    $dataStr = $color.'_day'; 
    if( $dayOfWeek == 0 || $dayOfWeek == 6){
        $dataStr = 'day'. $dayOfWeek;
    }
    $dataStr = $$dataStr;
    return $dataStr;
}

function loadDataStr($dataStr){
    global $timeStampBase;
    global $lateNightService;
    $stops = explode('.', $dataStr);
    $returnArr = array();
    foreach($stops as &$stop){
        $stop = trim($stop);
        if(empty($stop)){
            unset($stop);
            continue;
        }
        $parts = explode(',', $stop);
        $stopName = $parts[0]; 
        unset($parts[0]);
        $nextDay = 0;
        foreach($parts as $departTime){
            if(empty($departTime)) continue;
            if(strpos($departTime, '+') !== false){
                $returnArr[$stopName] = $returnArr['Babbio Center'];
                $spans = array();
                foreach(explode('+', $departTime) as $timeSpan){
                    if(empty($timeSpan)) continue;
                    $spans[] = $timeSpan;
                }
                $timeSpan = $normalSpan = $spans[0] * 60;
                if(count($spans) > 1)
                    $lateNightSpan = $spans[1] * 60;
                if(time() >= strtotime('00:15') && time() <= strtotime('02:34')){
                    $lateNightService = 1;
                    $timeSpan =  $lateNightSpan;
                }
                array_walk( $returnArr[$stopName], function(&$value, $key, $timeSpan){
                    //echo $value. '='.$value .'+'.$timeSpan."\n"; 
                    $value = $value + $timeSpan; 
                    //echo $value."\n";
                }, $timeSpan) ;
                break;
            }
            $hour_min = explode(':', $departTime);
            if($hour_min[0] > 12 && $nextDay == 0){
                $nextDay = 1;    
            }
            $hour = $hour_min[0];
            $min = $hour_min[1];
            $timeDiff =  $hour* 3600  + $min*60 + $timeStampBase - time();
            $returnArr[$stopName][] = $timeDiff;
        }
    }
    return $returnArr;
}

function secondsToReadableTime($seconds){
    $suffix = '';
    if($seconds < 0 ){
        $suffix = 'ago';
    }
    $seconds = abs($seconds);
    $hour = intval($seconds/3600);
    $min = intval(($seconds-3600*$hour)/60);
    $sec = $seconds-3600*$hour - 60*$min;
    $readable = '';
    if(!empty($hour)){
        $readable .= "$hour hours "; 
    }
    if(!empty($min)){
        $readable .= "$min minutes "; 
    }
    if(!empty($sec)){
        $readable .= "$sec seconds "; 
    }
    $readable .= $suffix;
    return $readable;
}


function secondsToArray($seconds){
    $seconds = abs($seconds);
    $hour = intval($seconds/3600);
    $min = intval(($seconds-3600*$hour)/60);
    $sec = $seconds-3600*$hour - 60*$min;
    return $seconds;
}

function getFinalData($color = 'gray', $json = false){
    $schedules = loadDataStr(getDateStr($color));
    $finalDisplay = array();
    foreach($schedules as $stopName => $departTimes){
        $count = 3;
        foreach($departTimes as $departTime){
            if($departTime > 0){
                $count--;
                if($count >= 0){
                    if($json == true){
                        $finalDisplay[$stopName][] = secondsToArray($departTime);
                    }else{
                        $finalDisplay[$stopName][] = secondsToReadableTime($departTime);
                    }
                }
            }
        }
    }
    return $finalDisplay;
}

$json = false;
if(isset($_GET['json'])){
    $json = true;
}
$finalDisplay = getFinalData('gray', $json);
$finalDisplay2 = getFinalData('red', $json);
$finalDisplay3 = getFinalData('green', $json);

if(isset($_GET['json'])){
    header('Content-type: application/json');
    echo json_encode(array('gray'=>$finalDisplay, 'red'=>$finalDisplay2, 'green'=>$finalDisplay3));
    exit;
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="http://runxiflute.com/logo_sss.jpg">
	<title>The Schedule</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <style type="text/css">
.h1{
}

.h2{
   font-weight:normal;
}

.font_normal{
   font-weight:normal;
}

.gray{
color:gray;
}

.red{
color:red;
}

.green{
color:green;
}
html {
    -webkit-text-size-adjust: none; /* Prevent font scaling in landscape */
}

.yo{
    text-decoration: none;
    color: lightskyblue;
}
    </style>
</head>
<body>
<!-- 哎哟,你在看源代码了哎.. 来加个微信 sunnyding602-->
<div id='container'>
<h1 style="font-size:60px;">加载中...</h1>
<h1 style="font-size:60px;">LOADING...</h1>
<h1 style="font-size:60px;">荷積み...</h1>
<h1 style="font-size:60px;">加載中...</h1>
<h1 style="font-size:60px;">짐싣기...</h1>
</div>

    <!--<h2>8 min (Rider: 3 min): 按照时刻表来说8分钟后有一趟车, 但素Rider认为3分钟校车会抵达(FYI:建议你相信时刻表的)</h1>-->
    <h2 class="font_normal">Now: <?=$today?></h2>
    <h2 class="font_normal">Suggestions?  Interested? Offer help?<br/> Wechat: sunnyding602</h2>
    <h2 >Copyright <?=$year?>, <a class="yo" href="http://m.ximalaya.com/2472682/sound/">计算机界大龄竹笛少年</a></h2>
</body>
<script type="text/javascript">
lateNightService = <?=$lateNightService?>;
        $.ajax({
            url: "stops.php",
        }).done(function(data) {
            $('#container').html(data);
            autoResize();
        });
var autoResize = function(){
    var width = $( window ).width();
    var fontSizeh1 = width/15;
    var fontSizeh2 = width/20;
    var fontSizeh3 = width/50;
    $('h1').css('font-size', fontSizeh1+'px');
    $('h2').css('font-size', fontSizeh2+'px');
    $('h3').css('font-size', fontSizeh3+'px');
}
    window.setInterval(function(){
        $.ajax({
            url: "stops.php",
        }).done(function(data) {
            $('#container').html(data);
            autoResize();
        });
    }, 20000);
</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-69075222-1', 'auto');
  ga('send', 'pageview');

</script>
</html>
