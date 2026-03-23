-- Migração: normalizar telefone para apenas dígitos e tratar duplicidades
-- Banco: PostgreSQL
-- 1) Normaliza telefone removendo tudo que não é dígito (espaços, parênteses, hífens, etc.)
-- 2) Converte vazio para NULL
-- 3) Em caso de duplicidade, mantém o mais recente e zera os demais (NULL)

BEGIN;

-- Opcional: visualizar possíveis duplicidades antes
-- SELECT regexp_replace(telefone, E'\\D', '', 'g') AS telefone_norm, COUNT(*)
-- FROM visitantes
-- WHERE telefone IS NOT NULL
-- GROUP BY 1
-- HAVING COUNT(*) > 1;

-- Normaliza telefone para só dígitos e converte vazio em NULL
UPDATE visitantes
SET telefone = NULLIF(regexp_replace(telefone, E'\\D', '', 'g'), '')
WHERE telefone IS NOT NULL;

-- Resolve duplicidades: mantém o registro mais recente (ultima_visita) e NULL nos demais
WITH ranked AS (
  SELECT id,
         telefone,
         ROW_NUMBER() OVER (
           PARTITION BY telefone
           ORDER BY ultima_visita DESC NULLS LAST, id ASC
         ) AS rn
  FROM visitantes
  WHERE telefone IS NOT NULL
)
UPDATE visitantes v
SET telefone = NULL
FROM ranked r
WHERE v.id = r.id
  AND r.rn > 1;

COMMIT;

-- Depois da migração, garanta o índice UNIQUE parcial:
-- CREATE UNIQUE INDEX IF NOT EXISTS uq_visitantes_telefone
--     ON visitantes(telefone)
--     WHERE telefone IS NOT NULL;
