<?php

function db()
{
    static $db = null;

    if ($db === null) {
        // Tenta PostgreSQL (produção no Render)
        $dbUrl = getenv('DATABASE_URL');

        if ($dbUrl) {
            // Parse DATABASE_URL do Render
            $db = new PDO($dbUrl);
        } else {
            // Fallback para SQLite (desenvolvimento local)
            $path = __DIR__ . '/../database/visitantes.sqlite';

            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            $db = new PDO('sqlite:' . $path);
        }

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    return $db;
}
