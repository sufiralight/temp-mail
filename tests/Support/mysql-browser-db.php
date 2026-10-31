<?php

use Pdo\Mysql;

$name = $argv[2] ?? '';
if (! preg_match('/\Atemp_mail_browser_[a-f0-9]{16}\z/', $name) || ! getenv('MYSQL_INSTALLATION_TEST_PORT') || ! getenv('MYSQL_INSTALLATION_TEST_CA')) {
    exit(1);
}
$pdo = new PDO('mysql:host=127.0.0.1;port='.(int) getenv('MYSQL_INSTALLATION_TEST_PORT'), 'root', '', [Mysql::ATTR_SSL_CA => getenv('MYSQL_INSTALLATION_TEST_CA'), Mysql::ATTR_SSL_VERIFY_SERVER_CERT => true]);
if ($argv[1] === 'create') {
    $pdo->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci');
} elseif ($argv[1] === 'drop') {
    $pdo->exec('DROP DATABASE `'.$name.'`');
} else {
    exit(1);
}
