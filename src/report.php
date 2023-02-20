<?php
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
}
