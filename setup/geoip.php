<?php

namespace mullitics;

ini_set('memory_limit', '1G');

$ip = 'GeoLite2-Country-Blocks-IPv4.csv';
$countries = 'GeoLite2-Country-Locations-en.csv';

require_once(__DIR__ . '/../src/sqlite.php');
require_once(__DIR__ . '/../src/helper.php');

$zipf = $argv[1];
$zip = new ZipArchive;
$c_name = "";
$ips_name = "";
$cc = [];
$ips = [];
$VAR = __DIR__ . '/../var/';

$res = $zip->open($zipf, ZipArchive::RDONLY);
if ($res !== true) {
    print "open ZIP failed.\n";
}

for ($i = 0; $i < $zip->numFiles; $i++) {
    $stat = $zip->statIndex($i);
    if (basename($stat['name']) == $countries) {
        $c_name = $stat['name'];
    }
    if (basename($stat['name']) == $ip) {
        $ips_name = $stat['name'];
    }
    print_r($stat['name'] . PHP_EOL);
}

print "found " . $c_name . ' - ' . $ips_name . " - ";

$stream = $zip->getStream($c_name);
if (!$stream) {
    print "failed $countries";
} else {
    $cc = parse_csv_stream($stream, ['geoname_id', 'country_iso_code']);
    fclose($stream);
}


$stream = $zip->getStream($ips_name);
if (!$stream) {
    print "failed $ips_name";
} else {
    $ips = parse_csv_stream($stream, ['geoname_id', 'network']);
    fclose($stream);
}

$zip->close();

$cc = array_column($cc, 'country_iso_code', 'geoname_id');
print_r($cc);

$db = new sqlite('_geo-tmp', __DIR__ . '/../', ['schema' => 'geo']);
make_db($db, $ips, $cc);
print "OK" . PHP_EOL;
rename(__DIR__ . '/../_geo-tmp.db', $VAR . '/_geo.db');

function make_db($db, $ips, $cc) {
    $db->db->beginTransaction();
    foreach ($ips as $ip) {
        list($start, $end) = cidr2iplong($ip['network']);
        $c = $cc[$ip['geoname_id']];
        // print "{$start} {$end} {$c}\n";
        $db->insert('geopoint', ['start_ip' => $start, 'end_ip' => $end, 'country_code' => $c]);
    }
    $db->db->commit();
    // $db->db = null;
}
function parse_csv_stream($stream, $cols = []) {
    $header = fgetcsv($stream);
    $header = array_intersect($header, $cols);
    $res = [];
    while (!feof($stream)) {
        $csv = fgetcsv($stream);
        // empty lines?
        if (!$csv) continue;
        $data = [];
        foreach ($header as $idx => $name) {
            $data[$name] = $csv[$idx];
        }
        $res[] = $data;
    }
    return $res;
}
