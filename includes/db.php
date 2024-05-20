<?php

ini_set('mysql.connect_timeout', '0');
ini_set('skip_name_resolve', 'ON');

date_default_timezone_set('Europe/Bucharest');

// Create connection
$mysqli = new mysqli(
    $_ENV['MYSQL_HOST'],
    $_ENV['MYSQL_USERNAME'],
    $_ENV['MYSQL_PASSWORD'],
    $_ENV['MYSQL_DBNAME']
);
$mysqli->set_charset("utf8");

$_ENV['mysqli'] = $mysqli;

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
