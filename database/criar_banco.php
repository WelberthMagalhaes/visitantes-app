<?php
require __DIR__ . '/../src/db.php';

$db = db();

$db->exec(
    <<<'SQL'
CREATE TABLE IF NOT EXISTS visitantes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    visitas INTEGER DEFAULT 1,
    ultima_visita DATE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL
);

echo "Banco criado com sucesso\n";
