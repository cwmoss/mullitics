<?php

class hit {
    public request $req;
    public appender $appender;
    public string $salt;

    public static $maxpathlen = 200;

    public function __construct(
        request $req,
        appender $appender,
        string $salt
    ) {
        $this->req = $req;
        $this->salt = $salt;
        $this->appender = $appender;
        $this->append();
    }

    public function append() {
        $ip = $this->req->server['REMOTE_ADDR'];
        $country = $this->get_country();
        $ts = time();
        $path = substr($this->get_path(), 0, self::$maxpathlen);
        $ref = substr($this->get_ref(), 0, self::$maxpathlen);
        $session = md5(
            $ip .
                date('Ymd', $ts) .
                $this->req->headers['user-agent'] ?? '' .
                $this->salt
        );
        dbg("HIT", $ip, $country, $path, $ref);
        $this->appender->write(
            [
                'Timestamp' => $ts,
                'Session' => $session,
                'URI' => $path,
                'Ref' => $ref,
                'Country' => $country,
                'Device' => $this->get_device(),
                'width' => $this->req->get['d'] ?? null,
                'geoip_lookup' => false
            ]
        );
    }

    /*
    Accept-Language: de,en-US;q=0.7,en;q=0.3
    */
    public function get_country() {
        $lang = Locale::acceptFromHttp($this->req->headers['accept-language'] ?? '');
        if ($lang) {
            $parts = explode('-', $lang);
            if (isset($parts[1])) {
                return strtoupper($parts[1]);
            }
            return strtoupper($parts[0]);
        }
    }

    public function get_device() {
        $mobile_ua = explode(' ', 'mobile iphone ipad android');
        $ua = strtolower($this->req->headers['user-agent'] ?? '');
        foreach ($mobile_ua as $search) {
            if (strpos($ua, $search) !== false) return 'mobile';
        }
        return 'desktop';
    }

    /*
    headers.referer ?
    */
    public function get_path() {
        $trackurl = $this->req->get['u'] ?? '';
        $url = parse_url($trackurl);
        return $url['path'] ?? '';
    }

    public function get_ref() {
        return $this->req->get['r'] ?? '';
    }
}
