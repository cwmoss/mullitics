<?php

namespace mullitics;

use DateTime;

/*

select datetime(Timestamp, 'unixepoch') from hits;
select strftime('%Y', date(Timestamp, 'unixepoch')) from hits;
SELECT strftime("%m-%d-%Y", Timestamp, 'unixepoch') AS date_col from hits;

http://sqlfiddle.com/#!5/453d6/19
https://stackoverflow.com/questions/64319360/sqlite-group-by-every-specific-interval

https://stackoverflow.com/questions/72501530/combine-multiple-selects-for-statistics-generation-into-on-result-set
https://stackoverflow.com/questions/54357620/multiple-ctes-and-group-by
https://www.sqlite.org/windowfunctions.html
https://learnsql.com/blog/two-aggregate-functions-sql/
https://stackoverflow.com/questions/64269596/combine-two-group-by-and-count-conditions-into-one
https://stackoverflow.com/questions/13847666/how-to-use-multiple-rank-in-single-query

select datetime((Timestamp/3600)*3600, 'unixepoch') as tf , count(*) c from hits group by tf;
select strftime("%Y%m%d%H", Timestamp, 'unixepoch') as tf, count(*) c from hits group by tf;
select datetime((Timestamp/3600)*3600, 'unixepoch') as tf , URI, count(URI) c from hits group by tf, URI;
select strftime("%Y%m%d%H", Timestamp, 'unixepoch') as tf , URI, count(URI) c from hits group by tf, URI;

WITH ds AS (
  select strftime("%Y%m%d%H", Timestamp, 'unixepoch') as tf,
  count(*) c,
  URI, Ref, Country
  FROM hits
  GROUP BY tf)
 
SELECT 
    tf, URI,
    count(URI) cc
FROM ds
GROUP BY URI;

{"Start":"2023-02-20T00:00:00+01:00","Interval":3600000000000,"URIs":{"Rows":[{"Name":"/","Values":[]...
{"Start":"2023-02-20T00:00:00+01:00","Interval":3600000000000,"URIs":{"Rows":[{"Name":"/","Values":[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,2,1]}
*/

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

    public function daily() {
        $start = new DateTime();
        $start->setTime(0, 0, 0);


        return [
            'Start' => $start->format("c"), 'Interval' => 3600000000000,
            'URIs' => ['Rows' => $this->dailystats_for('URI', $start)],
            'Refs' => ['Rows' => $this->dailystats_for('Ref', $start)],
            'Sessions' => ['Rows' => $this->dailystats_for('Session', $start)],
            'Countries' => ['Rows' => $this->dailystats_for('Country', $start)],
            'Devices' => ['Rows' => $this->dailystats_for('Device', $start)],
        ];
    }

    public function history() {
        $startvalue = $this->db->select_first_cell('select date(min(Timestamp), "unixepoch") as start from hits');
        if (!$startvalue) {
            $startvalue = date("Y-m-d");
        }

        $start = new DateTime($startvalue);
        $end = new DateTime("-1 days");
        $end->setTime(23, 59, 59);
        $days = $end->diff($start)->format("%a") + 1;

        return [
            'Start' => $start->format("c"), 'Interval' => 86400000000000,
            'URIs' => ['Rows' => $this->frame_for('URI', $start, $days, $end)],
            'Refs' => ['Rows' => $this->frame_for('Ref', $start, $days, $end)],
            'Sessions' => ['Rows' => $this->frame_for('Session', $start, $days, $end)],
            'Countries' => ['Rows' => $this->frame_for('Country', $start, $days, $end)],
            'Devices' => ['Rows' => $this->frame_for('Device', $start, $days, $end)],
        ];
    }

    function frame_for($fieldname, $start, $size, $end) {
        if ($fieldname == 'Session') {
            $q = 'SELECT strftime("%Y-%m-%d", Timestamp, "unixepoch", "localtime") as tf , "sessions" as val, count(distinct ' . $fieldname . ') as c from hits ' .
                'WHERE Timestamp  >= :start AND Timestamp <= :end GROUP BY tf';
        } else {
            $q = 'SELECT strftime("%Y-%m-%d", Timestamp, "unixepoch", "localtime") as tf , ' . $fieldname . ' as val, count(' . $fieldname . ') as c from hits ' .
                'WHERE Timestamp  >= :start AND Timestamp <= :end GROUP BY tf,' . $fieldname;
        }
        $res = $this->db->select($q, ['start' => $start->getTimestamp(), 'end' => $end->getTimestamp()]);
        $stats = [];
        foreach ($res as $hit) {
            if (!isset($stats[$hit['val']])) {
                $stats[$hit['val']] = ['Name' => $hit['val'], 'Values' => array_fill(0, $size, 0)];
            }
            $tf_date = new DateTime($hit['tf']);
            $index = (int) $tf_date->diff($start)->format("%a");
            $stats[$hit['val']]['Values'][$index] = (int) $hit['c'];
        }
        return array_values($stats);
    }

    function dailystats_for($fieldname, $start) {
        if ($fieldname == 'Session') {
            $q = 'SELECT strftime("%H", Timestamp, "unixepoch", "localtime") as tf , "sessions" as val, count(distinct ' . $fieldname . ') as c from hits ' .
                'WHERE Timestamp  >= :start GROUP BY tf';
        } else {
            $q = 'SELECT strftime("%H", Timestamp, "unixepoch", "localtime") as tf , ' . $fieldname . ' as val, count(' . $fieldname . ') as c from hits ' .
                'WHERE Timestamp  >= :start GROUP BY tf,' . $fieldname;
        }
        $res = $this->db->select($q, ['start' => $start->getTimestamp()]);
        $stats = [];
        foreach ($res as $hit) {
            if (!isset($stats[$hit['val']])) {
                $stats[$hit['val']] = ['Name' => $hit['val'], 'Values' => array_fill(0, 24, 0)];
            }
            $stats[$hit['val']]['Values'][(int) $hit['tf']] = (int) $hit['c'];
        }
        return array_values($stats);
    }
}
