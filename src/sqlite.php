<?php

namespace mullitics;

use PDO;

// mysql:host=localhost;dbname=something
class sqlite {

    public PDO $db;
    public string $name;

    public function __construct($name, $dir, $opts = []) {
        $file = $dir . $name . '.db';
        $dsn = 'sqlite:' . $file;
        $exists = \file_exists($file);
        $con = [];
        if ($opts['readonly'] ?? false) {
            $con[\PDO::SQLITE_ATTR_OPEN_FLAGS] = \PDO::SQLITE_OPEN_READONLY;
        }
        $this->db = new \PDO(
            $dsn,
            null,
            null,
            $con
        );
        $this->name = $name;

        if (isset($opts['wal']) && $opts['wal'] === true) {
            $this->run("PRAGMA journal_mode = wal;");
        }

        if (!$exists) {
            $this->create_schema($opts['schema'] ?? '');
        }
    }

    public function insert($table, $vars) {
        $col_names = array_keys($vars);
        $placeholder_names = $this->placeholder_names($col_names);
        $query = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            join(', ', $col_names),
            join(', ', $placeholder_names)
        );
        $this->query($query, $vars);
    }

    public function select($q, $vars = []) {
        $res = $this->query($q, $vars);
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }

    public function select_first_row($q, $vars = []) {
        $res = $this->query($q, $vars);
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            return $row;
        }
        return [];
    }

    public function select_first_cell($q, $vars = []) {
        $res = $this->query($q, $vars);
        return $res->fetchColumn();
    }

    public function query($q, $vars) {
        $vars = $this->typed_vars($vars);
        $sth = $this->db->prepare($q);
        $ok = $sth->execute($vars);
        return $sth;
    }

    public function placeholder_names($cols) {
        return array_map(function ($col) {
            return ':' . $col;
        }, $cols);
    }
    public function typed_vars($cols) {
        return array_map(function ($col) {
            if (is_null($col)) return 'NULL';
            if (is_bool($col)) return $col ? 1 : 0;
            return $col;
        }, $cols);
    }
    public function run($query) {
        $this->db->exec($query);
    }

    public function create_schema($name = "") {
        $name = $name ? "schema-{$name}.sql" : "schema.sql";
        $ddl = file_get_contents(__DIR__ . '/' . $name);
        $statements = explode('----', $ddl);
        #print $ddl;
        foreach ($statements as $ddl_s) {
            $ddl_s = trim($ddl_s);
            if (!$ddl_s || $ddl_s[0] == '#') continue;

            $this->run($ddl_s);
        }
        return;
    }
}

/*

 Use these you'll be just fine with high writes on SQLite. 
 Reads are basically free.

PRAGMA journal_mode = wal2;
PRAGMA synchronous = normal;
PRAGMA temp_store = memory;
PRAGMA cache_size = 100000; 

https://blog.pecar.me/sqlite-prod
PRAGMA foreign_keys = ON;
PRAGMA journal_mode=WAL;
PRAGMA synchronous=NORMAL; -- this might roll back a committed transaction following a power loss or system crash, use with caution
PRAGMA mmap_size = 134217728;
PRAGMA journal_size_limit = 27103364;
PRAGMA cache_size=2000;

https://sqlite-utils.datasette.io/en/stable/python-api.html#transforming-a-table

 */