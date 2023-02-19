<?php
// https://httpd.apache.org/docs/2.2/logs.html

foreach (file($argv[1]) as $line) {
    $data = str_getcsv($line, " ");
    $data = fix_date($data);
    $data = format_time($data, 3);
    print_r($data);
}

function format_time($data, $i) {
    $format = "[d/M/Y:H:i:s O]";
    $date = DateTime::createFromFormat($format, $data[$i]);
    $data[$i + 1] = $date->format('Y-m-d H:i:s');
    return $data;
}

function fix_date($data) {
    foreach ($data as $i => $v) {
        if ($v[0] == '[') {
            $data[$i] .= ' ' . $data[$i + 1];
        }
    }
    return $data;
}
