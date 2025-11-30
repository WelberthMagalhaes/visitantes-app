-- Schema PostgreSQL para sistema de visitantes
CREATE TABLE IF NOT EXISTS visitantes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    visitas INTEGER DEFAULT 1,
    ultima_visita DATE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS visitas (
    id SERIAL PRIMARY KEY,
    visitante_id INTEGER NOT NULL REFERENCES visitantes(id) ON DELETE CASCADE,
    data_visita DATE NOT NULL DEFAULT CURRENT_DATE,
    acompanhantes INTEGER DEFAULT 0,
    observacao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para performance
CREATE INDEX IF NOT EXISTS idx_visitantes_ultima_visita ON visitantes(ultima_visita);

CREATE INDEX IF NOT EXISTS idx_visitantes_nome ON visitantes(nome);

CREATE INDEX IF NOT EXISTS idx_visitas_data ON visitas(data_visita);

CREATE INDEX IF NOT EXISTS idx_visitas_visitante ON visitas(visitante_id);