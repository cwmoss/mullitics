<?php

namespace mullitics;

class application {

    public string $name;
    public array $opts;

    public function __construct(string $name, array $opts) {
        $this->name = $name;
        $this->opts = $opts;
    }

    function handle_hit($req) {
        $this->get_hit($req);
        $resp = new response('png', pixel());
        $resp->send();
    }

    function handle_report() {
        $report = $this->get_report();
        include($this->opts['desk'] . 'index.html');
    }

    function handle_script($req) {
        $url = get_self_url($req);
        $js = file_get_contents($this->opts['desk'] . 'ping.js');

        $seconds = 60 * 60 * 2;
        // header("Cache-Control: max-age=$seconds, stale-while-revalidate=$seconds");
        header("Cache-Control: max-age=$seconds");
        // header("Etag: " . md5($js));

        $resp = new response('js', text_for($js, ['self_url' => $url]));
        $resp->send();
    }

    function handle_query($req) {
        $report = $this->get_report();

        $resp = new response('json');
        $resp->send_data($report->recent());
    }

    function get_hit(request $req) {
        $db = $this->get_hit_db();
        $salt = get_salt($db);

        $geo = null;
        if (file_exists($this->opts['var'] . '_geo.db')) {
            $geodb = $this->get_geo_db();
            $geo = new geo($geodb);
        }
        return new hit($req, new appender($db), $salt, $geo);
    }

    function get_report() {
        return new report($this->get_report_db());
    }

    function get_hit_db() {
        return $this->get_db($this->name, ['wal' => true]);
    }

    function get_report_db() {
        return $this->get_db($this->name, ['readonly' => true]);
    }

    function get_geo_db() {
        return $this->get_db('_geo', ['readonly' => true, 'schema' => 'geo']);
    }

    function get_db($name, $opts) {
        return new sqlite($name, $this->opts['var'], $opts);
    }
}
