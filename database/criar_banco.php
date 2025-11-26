<?php
require __DIR__ . '/../src/db.php';

$db = db();

$db->exec(
    <<<'SQL'
CREATE TABLE IF NOT EXISTS familias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_familia TEXT NOT NULL,
    criado_em TEXT
);

CREATE TABLE IF NOT EXISTS visitantes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    telefone TEXT,
    parentesco TEXT,
    familia_id INTEGER,
    visitas INTEGER DEFAULT 1,
    ultima_visita TEXT,
    criado_em TEXT,
    FOREIGN KEY (familia_id) REFERENCES familias(id)
);
SQL
);

echo "Banco criado com sucesso\n";
