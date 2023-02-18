<?php
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
    $log[] = join(' ', array_map('json_encode_nice', $vars));
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
