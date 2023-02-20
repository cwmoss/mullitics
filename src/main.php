<?php
/*

new Image().src='/n.gif?u=' + 
    encodeURI(location.href) + 
    '&r=' + encodeURI(document.referrer) + 
    '&d=' + screen.width;

https://simonhearne.com/2022/caching-header-best-practices/

Cache-Control: max-age=604800, stale-while-revalidate=86400
ETag: "<file-hash-generated-by-server>"

*/
require_once(__DIR__ . '/helper.php');

spl_autoload_register(function ($name) {
    require_once(__DIR__ . "/{$name}.php");
});

$VAR = __DIR__ . '/../var/';
$DESK = __DIR__ . '/../resources/';
$name = '20sec';

$req = new request($_SERVER, $_GET, lc_headers());
if ($req->method('GET')) {
    if (isset($req->get['__desk'])) {
        include($DESK . 'index.html');
    } elseif (isset($req->get['__script'])) {
        $url = get_self_url($req);
        $js = file_get_contents($DESK . 'ping.js');

        $seconds = 60 * 60 * 2;
        // header("Cache-Control: max-age=$seconds, stale-while-revalidate=$seconds");
        header("Cache-Control: max-age=$seconds");
        // header("Etag: " . md5($js));

        $resp = new response('js', text_for($js, ['self_url' => $url]));
        $resp->send();
    } elseif (isset($req->get['__query'])) {
        $db = new sqlite($name, $VAR, ['readonly' => true]);

        $report = new report($db);

        $resp = new response('json');
        $resp->send_data($report->recent());
    } else {
        dbg("+++ hit", $req);

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
