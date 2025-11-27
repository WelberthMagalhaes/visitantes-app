<?php

function db()
{
    static $db = null;

    if ($db === null) {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: 5432;
        $dbname = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');

        if (!$dbname || !$user) {
            throw new Exception('Variáveis de banco não configuradas. Configure DB_HOST, DB_NAME, DB_USER, DB_PASS no .env');
        }

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
        $db = new PDO($dsn, $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    return $db;
}
