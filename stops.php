<?php
//file_get_contents('http://feeds.transloc.com/3/stops.json?include_routes=true&agencies=307&_=1445517963435');
//file_get_contents('http://feeds.transloc.com/3/routes.json?agencies=307&_=1445517963431');
$stops = file_get_contents('stops');
$routes = file_get_contents('routes');
$stops = json_decode($stops, true);
$stops = $stops['stops'];
$routes = json_decode($routes, true);
$routes = $routes['routes'];

$myRoutes = array();
$myArrival = array();
foreach($routes as $value){
    $id = $value['id'];
    $name  = $value['long_name'];
    $myRoutes[$id] = $name; 
    $myRoutes[$name] = $id; 
    $color = '';
    if(strpos(strtolower($name), 'gray') !== false){
        $color = 'gray';
    }

    if(strpos(strtolower($name), 'red') !== false){
        $color = 'red';
    }

    if(strpos(strtolower($name), 'green')!== false){
        $color = 'green';
    }

    if(strpos(strtolower($name), 'blue')!== false){
        $color = 'blue';
    }
    $myArrival[$color] = getArrivals($id);
}
$myStops = array();
foreach($stops as $value){
    $id = $value['id'];
    $name  = $value['name'];
    $myStops[$id] = $name;
    //$myStops[$name] = $id;
}
//print_r($myStops);
//print_r($myRoutes);


function getArrivals($route){
    $xmlstring = file_get_contents('http://stevens.transloc.com/m/feeds/arrivals/route/'.$route);
    $xml = simplexml_load_string($xmlstring);
    $json = json_encode($xml);
    $array = json_decode($json,TRUE);
    $myArrivals = array();
    foreach($array['stop'] as $value){
        $id = $value['@attributes']['id'];
        $arravals = $value['@attributes']['arrivals'];
        $myArrivals[$id] =  $arravals;
    }
    return $myArrivals;
}

date_default_timezone_set('America/New_York');
$timeStampBase = strtotime(date('Y-m-d 00:00', strtotime('-3 hours')));
$lateNightService = 0;
$year = date("Y");   	
$today = date("D M j G:i:s T Y");   	
$dayOfWeek = '';

function getDateStr($color='gray'){
    global $timeStampBase; 
    global $dayOfWeek;

    $green_day = '4178880,7:40,8:00,8:20,8:40,9:00,9:20,9:40,10:00,10:20,10:40,11:00,11:20,16:30,16:50,17:10,17:30,17:50,18:10,18:30,18:50,19:30,19:50,20:10,20:30,20:50,21:10,21:30,21:50,22:30,22:50,23:10,23:30.
        4178868,+9+0.
        4178866,+11+0';


    $red_day = '4178880,7:15,7:30,7:45,8:00,8:15,8:30,8:45,9:00,9:15,9:30,9:45,10:00,10:15,10:30,10:45,11:00,11:15,11:45,12:45,13:45,14:15,15:15,15:45,16:10,16:35,17:00,17:15,17:30,17:45,18:00,18:15,18:30,18:45,19:30,19:45,20:00,20:15,20:30,20:45,21:00,21:15,21:30,22:00,23:00,23:30,24:00.
        4176264,+7+0.
        4111538,+13+0.';

    $gray_day = '4178880,7:15,7:30,7:40,7:55,8:05,8:20,8:30,8:45,8:55,9:10,9:20,9:35,9:45,10:00,10:10,10:25,10:35,10:50,11:00,11:40,12:05,12:30,12:55,13:20,13:45,14:10,15:00,15:25,15:50,16:15,16:40,17:05,17:15,17:30,17:40,17:55,18:05,18:20,18:30,18:45,18:55,19:10,19:20,19:45,20:00,20:10,20:25,20:35,20:50,21:00,21:15,21:25,21:40,22:05,22:30,22:55,24:00,24:30,25:05,25:40,26:10.
       4141102,+7+18.';//7->normal time span  18->late night time span
    $day6 = '4178880,7:30,8:05,8:40,9:15,9:50,10:25,11:00,11:35,12:10,13:20,13:55,14:30,15:05,15:40,16:15,16:50,17:25,18:00,18:35,19:10,19:45,20:20,21:30,22:05,22:40,23:15,23:50,24:25,25:00,25:35,26:10.
       4176264,+6+0.
       4132102,+11+0.
       4111538,+14+0.
       4141102,+17+0.
       4132090,+23+0.
       ';
    $day0 = '4178880,12:00,12:35,13:10,13:45,14:20,14:55,15:30,16:40,17:15,17:50,18:25,19:00,19:35,20:10,20:45,21:20,22:30,23:05,23:40,24:15,24:50,25:25,26:00.
       4176264,+7+0.
       4132102,+10+0.
       4111538,+12+0.
       4141102,+15+0.
       4132090,+22+0.';
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
                $returnArr[$stopName] = isset($returnArr[4178880]) ? $returnArr[4178880] : array() ;
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
        $readable .= "$hour h "; 
    }
    if(!empty($min)){
        $readable .= "$min min "; 
    }
    if(!empty($sec)){
        $readable .= "$sec sec "; 
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
    global $myStops;
    global $myArrival;
    $arrival = $myArrival[$color];
    $schedules = loadDataStr(getDateStr($color));
    $finalDisplay = array();
    foreach($schedules as $stopName => $departTimes){
        $stopId = $stopName;
        $stopName = $myStops[$stopName];
        $count = 3;
        $arrival[$stopId] = str_replace( 'min', '', $arrival[$stopId]);
        $arrivals = explode('&', $arrival[$stopId]);
        if(!empty($arrivals)){
            foreach($arrivals as &$a){
                $a = trim($a);
                if(is_numeric($a)){
                    $a = $a. ' min';
                }else{
                    $a = '';
                }
            }
        }

        foreach($departTimes as $departTime){
            if($departTime > 0){
                $count--;
                if($count >= 0){
                    if($json == true){
                        $finalDisplay[$stopName][] = secondsToArray($departTime);
                    }else{
                        $rider = array_shift($arrivals);
                        $finalDisplay[$stopName][] = secondsToReadableTime($departTime) . "(Rider: ".(empty($rider) ? 'N/A' : $rider).")";
                    }
                }
            }
        }
    }
    return $finalDisplay;
}



    $finalDisplay2 = getFinalData('red', $json);
if( $dayOfWeek == 0 || $dayOfWeek == 6){
    if($dayOfWeek == "6")  $serviceStr='SAT';
    if($dayOfWeek === "0")  $serviceStr='SUN';
    $finalDisplay = array();
    $finalDisplay3 = array();
}else{
    $finalDisplay = getFinalData('gray', $json);
    //$finalDisplay2 = getFinalData('red', $json);
    $finalDisplay3 = getFinalData('green', $json);
}
/*
    [4111538] => Madison St. & 8th St.
    [4111546] => Hudson St. & Newark St.
    [4132090] => Howe Center
    [4132098] => 11th St. & Park Ave.
    [4132102] => Madison St. & 4th St.
    [4141098] => River St. & 4th St.
    [4141102] => 12th St. & Grand St.
    [4151394] => Jackson St. betw. 6th St. & 7th St.
    [4151398] => Madison St. & 11th St.
    [4162754] => Marshall St. & 2nd St.
    [4162762] => 11th St. & Hudson St.
    [4176262] => Madison St. & 7th St.
    [4176264] => 1st St & Harrison St.
    [4178866] => Jackson St. @ Elevator
    [4178868] => 8th St. & Monroe St.
    [4178880] => Babbio Center
    [4178882] => Newark
    [4178884] => The PATH Stop
    [4179242] => 11th St. & Jefferson St.

    [4004698] => Red Line Residential
    [4004706] => Blue Line Hoboken Terminal / PATH
    [4007212] => Green Line Light Rail / Elevator
    [4007214] => Gray Line North Loop 
*/
?>

<div id='gray'>
<?php
foreach($finalDisplay as $stopName => $departTimes){
?>
    <h1 class="h1 <?=$lateNightService==1?'NightServ':'gray'?>"><?=$stopName?>[<?=$lateNightService==1?'NightServ':'GRAY'?>]</h1>
<?php
    foreach($departTimes as $departTime){
?>
    <h2 class="h2 <?=$lateNightService==1?'NightServ':'gray'?>"><?=$departTime?></h2>
<?php
    }
}
?>
</div>
<div id='red'>
<?php
foreach($finalDisplay2 as $stopName => $departTimes){
?>
    <h1 class="h1 red"><?=$stopName?>[<?=!empty($serviceStr)?$serviceStr:'RED'?>]</h1>
<?php
    foreach($departTimes as $departTime){
?>
    <h2 class="h2 red"><?=$departTime?></h2>
<?php
    }
}
?>
</div>
<div id='green'>
<?php
foreach($finalDisplay3 as $stopName => $departTimes){
?>
    <h1 class="h1 green"><?=$stopName?>[GREEN]</h1>
<?php
    foreach($departTimes as $departTime){
?>
    <h2 class="h2 green"><?=$departTime?></h2>
<?php
    }
}
?>
</div>
