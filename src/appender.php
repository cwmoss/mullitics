<?php

class appender {

    public sqlite $db;

    public function __construct(sqlite $db) {
        $this->db = $db;
    }

    public function write($data) {
        $this->db->insert("hits", $data);
    }
}
