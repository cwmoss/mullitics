<?php

namespace mullitics;

class response {
    public string $type;
    public $data;

    public static array $mime = [
        'png' => 'image/png',
        'js' => 'text/javascript',
        'json' => 'application/json'
    ];

    public function __construct(string $type, string $data = null) {
        $this->type = $type;
        $this->data = $data;
    }

    public function send() {
        header("Content-Type: " . self::mime($this->type));
        print $this->data;
    }

    public function send_data($data) {
        header("Content-Type: " . self::mime($this->type));
        print json_encode_nice($data);
    }
    public static function mime($type) {
        return self::$mime[$type];
    }
}
