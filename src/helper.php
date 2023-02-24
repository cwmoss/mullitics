<?php

namespace mullitics;

function json_encode_nice($data) {
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function dbg($txt, ...$vars) {
    // im servermodus wird der zeitstempel automatisch gesetzt
    // $log = [date('Y-m-d H:i:s')];
    $log = [];
    if (!is_string($txt)) {
        array_unshift($vars, $txt);
    } else {
        $log[] = $txt;
    }
    $log[] = join(' ', array_map(__NAMESPACE__ . '\\json_encode_nice', $vars));
    error_log(join(' ', $log));
}

function lc_headers() {
    return array_change_key_case(getRequestHeaders());
}

function getRequestHeaders() {
    $headers = array();

    // If getallheaders() is available, use that
    if (function_exists('getallheaders')) {
        $headers = getallheaders();

        // getallheaders() can return false if something went wrong
        if ($headers !== false) {
            return $headers;
        }
    }

    // Method getallheaders() not available or went wrong: manually extract 'm
    foreach ($_SERVER as $name => $value) {
        if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
            $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }

    return $headers;
}

/*
http://proger.i-forge.net/%D0%9A%D0%BE%D0%BC%D0%BF%D1%8C%D1%8E%D1%82%D0%B5%D1%80/[20121112]%20The%20smallest%20transparent%20pixel.html
*/
function pixel() {
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=');
}

function url_origin($s, $use_forwarded_host = false) {
    $ssl      = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
    $sp       = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
    $port     = $s['SERVER_PORT'];
    $port     = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
    $host     = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host     = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function full_url($s, $use_forwarded_host = false) {
    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

// https://stackoverflow.com/questions/6768793/get-the-full-url-in-php
function get_self_url($req) {
    return url_origin($req->server) .
        parse_url($req->server['REQUEST_URI'], PHP_URL_PATH);
}

function text_for($muster, $vars = array()) {
    $repl = array();
    foreach ($vars as $k => $v) {
        $repl['{' . strtolower($k) . '}'] = $v;
    }
    $txt = $muster;
    $txt = str_replace(array_keys($repl), $repl, $txt);
    return $txt;
}

function get_data_url($image, $mime = '') {
    if (!$mime) $mime = mime_content_type($image);
    return 'data: ' . $mime .
        ';base64,' . base64_encode(file_get_contents($image));
}

function cidr2iplong($cidr) {
    $ip_arr = explode('/', $cidr);
    $start = ip2long($ip_arr[0]);
    $nm = $ip_arr[1];
    $num = pow(2, 32 - $nm);
    $end = $start + $num - 1;
    return [sprintf("%u", $start), sprintf("%u", $end)];
}

function get_salt(sqlite $db) {
    $salt = $db->select_first_cell(
        'SELECT salt from salt where salted_at >= :today',
        ['today' => date('Y-m-d') . ' 00:00:00']
    );
    if (!$salt) {
        $salt = bin2hex(random_bytes(32));
        $db->query(
            'insert or replace into salt (salt) values(:newsalt)',
            ['newsalt' => $salt]
        );
    }
    return $salt;
}
