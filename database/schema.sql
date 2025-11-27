-- Schema PostgreSQL para sistema de visitantes

CREATE TABLE IF NOT EXISTS visitantes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    visitas INTEGER DEFAULT 1,
    ultima_visita DATE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para performance
CREATE INDEX IF NOT EXISTS idx_visitantes_ultima_visita ON visitantes(ultima_visita);
CREATE INDEX IF NOT EXISTS idx_visitantes_nome ON visitantes(nome);
