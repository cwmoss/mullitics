<?php

class report {

    public sqlite $db;

    public function __construct(sqlite $db) {
        $this->db = $db;
    }

    public function recent() {
        $res = $this->db->select(
            'select * from hits where Timestamp > :twentyfourhours order by Timestamp desc',
            ['twentyfourhours' => time() - (24 * 60 * 60)]
        );
        return iterator_to_array($res);
    }
}
