<?php

// mysql:host=localhost;dbname=something
class sqlite {

    public PDO $db;
    public string $name;

    public function __construct($name, $dir, $opts = []) {
        $file = $dir . $name . '.db';
        $dsn = 'sqlite:' . $file;
        $exists = \file_exists($file);
        dbg("+++ exists?", $exists);
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
        $this->run("PRAGMA journal_mode = wal;");
        if (!$exists) {
            $this->create_schema();
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

    public function select($q, $vars) {
        $res = $this->query($q, $vars);
        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            yield $row;
        }
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

    public function create_schema() {
        $ddl = file_get_contents(__DIR__ . '/schema.sql');
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
