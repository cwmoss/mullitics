<?php
/*

new Image().src='/n.gif?u=' + 
    encodeURI(location.href) + 
    '&r=' + encodeURI(document.referrer) + 
    '&d=' + screen.width;

*/
require_once(__DIR__ . '/helper.php');

spl_autoload_register(function ($name) {
    require_once(__DIR__ . "/{$name}.php");
});

$VAR = __DIR__ . '/../var/';
$DESK = __DIR__ . '/../resources/';

$req = new request($_SERVER, $_GET, lc_headers());
if ($req->method('GET')) {
    if (isset($req->get['__desk'])) {
        include($DESK . 'index.html');
    } else {
        dbg("+++ hit", $req);

        $name = '20sec';
        $salt = file_get_contents($VAR . 'salt');

        $db = new sqlite($name, $VAR);

        new hit($req, new appender($db), $salt);

        $resp = new response('png', pixel());
        $resp->send();
    }
} else {
    header('HTTP/1.1 404 Not Found');
    print "not found";
}
