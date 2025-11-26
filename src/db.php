<?php

function db()
{
    static $db = null;

    if ($db === null) {
        $path = __DIR__ . '/../database/visitantes.sqlite';

        // garante que a pasta existe
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $db = new PDO('sqlite:' . $path);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // pragmas para performance
        $db->exec('PRAGMA journal_mode = WAL;');
        $db->exec('PRAGMA foreign_keys = ON;');
    }

    return $db;
}
