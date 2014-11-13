<?php

function findZip($zip) {
    if(strlen($zip) != 5) {
        return null;
    }
    list($first3, $last2)= [substr($zip, 0, 3), substr($zip, 3,2)];
    $file = './db/' . $first3;
    if(!file_exists($file)) {
        return null;
    }
    require_once $file;
    $value = null;
    try {
        $value = $db[$last2];
    }
    catch(Exception $e) {} 
    return $value;
}

function sunset($lat, $long, $tz, $ts = 0) {
    $sun_info = date_sun_info($ts, $lat, $long);

    foreach ($sun_info as $key => $val) {
        $dt = new DateTime("@$val", new DateTimeZone('UTC'));
        $dt->setTimeZone(new DateTimeZone($tz));
        $sun_info[$key] = $dt;
    }
    return $sun_info;
}


function findNextFridaySunset($zip, $location = []) {
    $friday = null;
    $location = $location?: findZip($zip);
    $data =  array_combine(['city','name', 'lat', 'long', 'offset' , 'timezone'], $location);
    $date = new DateTime("now", new DateTimeZone($data['timezone']));

    if($date->format('D') == 'Fri') {
        $sun_info = sunset($data['lat'], $data['long'], $data['timezone'], $date->getTimestamp());
        // Sunset hasn't happened
        if($date < $sun_info['sunset']) {
            return $sun_info;
        }
        // If it has, bump the day
        $date->add(new DateInterval("P1D"));
    }
    $friday = strtotime("next friday", $date->getTimestamp());
    $date = new DateTime("@$friday", new DateTimeZone($data['timezone']));
    $sun_info = sunset($data['lat'], $data['long'], $data['timezone'], $date->getTimestamp());
    $sunset = $sun_info['sunset'];
    return $sun_info;
}