<?php
date_default_timezone_set('UTC');
require './vendor/autoload.php';
require './db.php';
$app = new \Slim\Slim();

$app->get('/friday/:zip', function ($zip) use ($app) {
    $app->response->headers->set('Content-Type', 'text/xml');
    try {
        $location = findZip($zip);
        if(!$location) throw new Exception("Couldn't find!");
        $sunset = findNextFridaySunset($zip, $location);
        $location[] = $sunset['sunset']->format('c');
        $data =  array_combine(['city','state', 'lat', 'long', 'offset' , 'timezone', 'sunset'], $location);
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="/simple.xsl" ?>
<xml/>');
        foreach ($data as $key => $val) { $node = $xml->addChild($key, $val); }
        echo $xml->asXML();
    } catch(Exception $e) {
        echo '<xml error="404"/>';
        $app->response()->status(404);
        return;
    }

});
$app->get('/sunset/:zip(/:date)', $sunset = function ($zip, $date = null) use ($app) {
    $app->response->headers->set('Content-Type', 'text/xml');
    try {
        $location = findZip($zip);
        if(!$location) throw new Exception("Couldn't find!");
    } catch(Exception $e) {
        echo '<xml error="404"/>';
        $app->response()->status(404);
        return;
    }
    try {
        list($lat, $long, $tz) = [$location[2], $location[3], $location[5]];
        $ts = ($date) ? strtotime(join("/", str_split($date, 2))) : $_SERVER['REQUEST_TIME'];
        $sun_info = date_sun_info($ts, $lat, $long);
        $xml = new SimpleXMLElement('<xml/>');
        $data =  array_combine(['city','state', 'lat', 'long', 'offset' , 'timezone'], $location);
        foreach ($data as $key => $val) { $node = $xml->addChild($key, $val); }
        // Convert Unix timestamps returned from date_sum_info to dates
        foreach ($sun_info as $key => $val) {
            $dt = new DateTime("@$val", new DateTimeZone('UTC'));
            $dt->setTimeZone(new DateTimeZone($tz));
            $node = $xml->addChild($key, $dt->format('c'));
        }
        echo $xml->asXML();
        return $sun_info['sunset'];
    }
    catch(Exception $e) {
        echo '<xml error="500"/>';
        $app->response()->status(500);
    }
});

$app->get('/json/sunset(/:zip)', function ($zip = 94306, $date=NULL)  use ($app) {
    $app->response->headers->set('Content-Type', 'application/json');
    try {
        $location = findZip($zip);
        if(!$location) throw new Exception("Couldn't find!");
    } catch(Exception $e) {
        $app->response()->status(404);
        return;
    }
    try {
        list($lat, $long, $tz) = [$location[2], $location[3], $location[5]];
        $ts = ($date) ? strtotime(join("/", str_split($date, 2))) : $_SERVER['REQUEST_TIME'];
        $sun_info = date_sun_info($ts, $lat, $long);
        $xml = new SimpleXMLElement('<xml/>');
        $data =  array_combine(['city','state', 'lat', 'long', 'offset' , 'timezone'], $location);
        list($data['lat'], $data['long']) = [floatval($data['lat']), floatval($data['long'])];
        foreach ($data as $key => $val) { $node = $xml->addChild($key, $val); }
        // Convert Unix timestamps returned from date_sum_info to dates
        foreach ($sun_info as $key => $val) {
            $dt = new DateTime("@$val", new DateTimeZone('UTC'));
            $dt->setTimeZone(new DateTimeZone($tz));
            $node = $xml->addChild($key, $dt->format('c'));
            $data[$key] = $dt->format('h:i:s A');
        }
        echo json_encode($data);
    }
    catch(Exception $e) {
        $app->response()->status(500);
        print_r($e);
    }
});
$app->run();