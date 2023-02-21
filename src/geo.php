<?php
// https://www.home.neustar/blog/importing-ultrageopoint-into-sqlite
/*

SELECT * FROM geopoint a 
WHERE a.start_ip = 
(SELECT MAX(start_ip) FROM geopoint b WHERE b.start_ip <= 3758096380) 
AND a.end_ip >= 3758096380;   

*/

class geo {
    public sqlite $db;

    public function __construct(sqlite $db) {
        $this->db = $db;
    }

    public function lookup_country($ip) {
        $ip = sprintf('%u', ip2long($ip));
        // dbg("+++ ip2long", $ip);
        // maybe ipv6
        if (!$ip) return "";

        $q = 'SELECT country_code FROM geopoint a ' .
            'WHERE a.start_ip = ' .
            '    (SELECT MAX(start_ip) FROM geopoint b WHERE b.start_ip <= :iplong) ' .
            'AND a.end_ip >= :iplong2';
        return $this->db->select_first_cell($q, ['iplong' => $ip, 'iplong2' => $ip]);
    }
}
