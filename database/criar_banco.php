<?php
require __DIR__ . '/../src/db.php';

$db = db();

$db->exec(
    <<<'SQL'
CREATE TABLE IF NOT EXISTS visitantes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    telefone TEXT,
    visitas INTEGER DEFAULT 1,
    ultima_visita TEXT,
    criado_em TEXT
);
SQL
);

echo "Banco criado com sucesso\n";
