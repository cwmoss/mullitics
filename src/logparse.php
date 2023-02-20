<?php
// https://httpd.apache.org/docs/2.2/logs.html

// $format = '%h %l %u %t \"%r\" %>s %b';
// 131.153.142.170 - - [19/Feb/2023:19:35:43 +0100] "GET / HTTP/1.1" 200 8694 "-" "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:95.0) Gecko/20100101 Firefox/95.0"
$format = 'ip - - time request statuscode size - ua';
$mapper = make_mapper($format, "[d/M/Y:H:i:s O]");

foreach (file($argv[1]) as $line) {
    $data = str_getcsv($line, ' ');
    // $data = map_data($data);
    // $data = format_time($data, 3);
    $data = $mapper($data);
    print_r($data);
}


function make_mapper($format, $timeformat) {
    $names = explode(' ', $format);
    $names = array_flip(array_filter($names, function ($e) {
        return $e != '-';
    }));
    // print_r($names);

    return function ($d) use ($names, $timeformat) {
        $res = [];
        // print_r($d);
        $offset = 0;
        foreach ($names as $key => $i) {
            $val = $d[$i + $offset];
            if ($key == 'time') {
                if ($timeformat[0] == '[' && substr($val, -1) != ']') {
                    $val .= ' ' . $d[$i + $offset + 1];
                    $offset++;
                }
                $date = DateTime::createFromFormat($timeformat, $val);
                $res[$key] = $date->format('Y-m-d H:i:s');
            } elseif ($key == 'request') {
                $parts = explode(' ', $val);
                $res['request_method'] = $parts[0] == '-' ? '' : $parts[0];
                $res['request_path'] = $parts[1] ?? '';
            } else {
                $res[$key] = $val == '-' ? '' : $val;
            }
        }
        return $res;
    };
}
