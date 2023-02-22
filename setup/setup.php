<?php
$base = __DIR__ . '/../';
mkdir($base . 'var');

#if (!file_exists($base . 'var/salt')) {
#    file_put_contents($base . 'var/salt', bin2hex(random_bytes(32)));
#}

`chmod -R 0777 {$base}var`;
