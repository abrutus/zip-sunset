<?php
$fd = fopen("zip_code_database.csv", "r");
$headers = fgetcsv($fd, 1000);
print_r($headers);
$offset_calc = function($tz, $t) {
    $offset = $tz->getOffset( $t ) / 3600;
    return  ($offset < 0 ? $offset : "+".$offset);
};
$result = $no_tz = [];
$batched = [];
while($row = fgetcsv($fd, 1000)) {
    try {
        $tz = new DateTimeZone($row[7]);
        $t = new DateTime("now", $tz);
    } catch (Exception $e) {
        // Things without a timezone
        $no_tz [] = $row;
        continue;
    }
    $offset = $offset_calc($tz, $t);
    $result [$row[0]] = $datum =  [$row[2], $row[5], $row[9], $row[10], $offset, $row[7]];
    list($i, $j) = [substr($row[0], 0, 3), substr($row[0], 3,2)];
    $batched[$i][$j] = $datum;
}

//file_put_contents("test.php", '<?php $db='. var_export($batched, true) . ";");
foreach($batched as $first3 => $children) {
    file_put_contents("db/" . $first3, '<?php $db= ' . var_export($children, true) . ';');
}