<?php

namespace mullitics;
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

\spl_autoload_register(function ($name) {
    $path = explode('\\', $name);
    if ($path[0] == __NAMESPACE__) {
        require_once(__DIR__ . "/{$path[1]}.php");
    }
});

$base = __DIR__ . '/../';

$name = 'default';

$req = new request($_SERVER, $_GET, lc_headers());



if ($req->method('GET')) {
    $mu = new application($name, ['var' => $base . 'var/', 'desk' => $base . 'resources/']);

    if (isset($req->get['__desk'])) {
        $mu->handle_report();
    } elseif (isset($req->get['__script'])) {
        $mu->handle_script($req);
    } elseif (isset($req->get['__query'])) {
        $mu->handle_query($req);
    } else {
        // dbg("+++ hit", $req);

        $mu->handle_hit($req);
    }
} else {
    header('HTTP/1.1 404 Not Found');
    print "not found";
}
