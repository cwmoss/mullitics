<?php

class response {
    public string $type;
    public string $data;

    public static array $mime = [
        'png' => 'image/png',
        'js' => 'text/javascript'
    ];

    public function __construct(string $type, string $data) {
        $this->type = $type;
        $this->data = $data;
    }

    public function send() {
        header("Content-Type: " . self::mime($this->type));
        print $this->data;
    }

    public static function mime($type) {
        return self::$mime[$type];
    }
}
