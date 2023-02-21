<?php

$ips = 'GeoLite2-Country-Blocks-IPv4.csv';
$countries = 'GeoLite2-Country-Locations-en.csv';
$zipf = $argv[1];
$zip = new ZipArchive;
$c_name = "";
$ips_name = "";
$cc = [];

$res = $zip->open($zipf, ZipArchive::RDONLY);
if ($res === true) {
    // echo 'ok';

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        if (basename($stat['name']) == $countries) {
            $c_name = $stat['name'];
        }
        if (basename($stat['name']) == $ips) {
            $ips_name = $stat['name'];
        }
        print_r($stat['name'] . PHP_EOL);
    }
    $stream = $zip->getStream($c_name);
    if (!$stream) {
        print "failed $countries";
    }
    while (!feof($stream)) {
        $csv = fgetcsv($stream, 200);
        print $csv[0] . ' - ' . $csv[4] . PHP_EOL;
        $cc[$csv[0]] = $csv[4];
    }
    fclose($stream);
    // $zip->extractTo('test');
    $zip->close();
} else {
    echo 'Fehler, Code:' . $res;
}

print_r($cc);
