<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

function cadastrarVisitanteBackend($nome, $telefone)
{
    $db = db();
    $nomeNorm = normaliza($nome);
    $hoje = date('Y-m-d');

    // buscar por nome normalizado
    $stmt = $db->prepare("SELECT * FROM visitantes");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        if (normaliza($r['nome']) === $nomeNorm) {
            // se já visitou hoje, não permite novo cadastro
            if ($r['ultima_visita'] === $hoje) {
                return ['tipo' => 'ja_cadastrado_hoje', 'id' => $r['id'], 'nome' => $r['nome']];
            }

            // atualiza visitas e ultima_visita
            $upd = $db->prepare("UPDATE visitantes SET visitas = visitas + 1, ultima_visita = :data WHERE id = :id");
            $upd->execute([':data' => $hoje, ':id' => $r['id']]);

            return ['tipo' => 'retorno', 'id' => $r['id'], 'nome' => $r['nome']];
        }
    }

    // novo
    $ins = $db->prepare("INSERT INTO visitantes (nome, telefone, visitas, ultima_visita, criado_em) VALUES (:n, :t, 1, :hoje, CURRENT_TIMESTAMP)");
    $ins->execute([':n' => $nome, ':t' => $telefone, ':hoje' => $hoje]);

    return ['tipo' => 'novo', 'id' => $db->lastInsertId(), 'nome' => $nome];
}

function listarVisitantesPorData(string $data)
{
    $db = db();
    $stmt = $db->prepare("SELECT id, nome, visitas FROM visitantes WHERE ultima_visita = :d ORDER BY id DESC");
    $stmt->execute([':d' => $data]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarTodosVisitantes()
{
    $db = db();
    $stmt = $db->prepare("SELECT id, nome, telefone, visitas, ultima_visita FROM visitantes ORDER BY nome ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sincronizarVisitantes(array $visitantesLocais)
{
    $db = db();
    $hoje = date('Y-m-d');
    $resultado = ['atualizados' => 0, 'inseridos' => 0, 'ignorados' => 0];

    foreach ($visitantesLocais as $vLocal) {
        $nome = $vLocal['nome'] ?? '';
        $telefone = $vLocal['telefone'] ?? '';
        $visitasLocal = $vLocal['visitas'] ?? 1;

        if (empty($nome)) continue;

        $nomeNorm = normaliza($nome);

        // Busca visitante no banco
        $stmt = $db->prepare("SELECT * FROM visitantes");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $encontrado = null;
        foreach ($rows as $r) {
            if (normaliza($r['nome']) === $nomeNorm) {
                $encontrado = $r;
                break;
            }
        }

        if ($encontrado) {
            // Se já visitou hoje, ignora
            if ($encontrado['ultima_visita'] === $hoje) {
                $resultado['ignorados']++;
                continue;
            }

            // Atualiza se visitas local > servidor
            if ($visitasLocal > $encontrado['visitas']) {
                $upd = $db->prepare("UPDATE visitantes SET visitas = :visitas, ultima_visita = :data, telefone = :tel WHERE id = :id");
                $upd->execute([
                    ':visitas' => $visitasLocal,
                    ':data' => $hoje,
                    ':tel' => $telefone ?: $encontrado['telefone'],
                    ':id' => $encontrado['id']
                ]);
                $resultado['atualizados']++;
            } else {
                // Apenas atualiza ultima_visita
                $upd = $db->prepare("UPDATE visitantes SET visitas = visitas + 1, ultima_visita = :data WHERE id = :id");
                $upd->execute([':data' => $hoje, ':id' => $encontrado['id']]);
                $resultado['atualizados']++;
            }
        } else {
            // Insere novo
            $ins = $db->prepare("INSERT INTO visitantes (nome, telefone, visitas, ultima_visita, criado_em) VALUES (:n, :t, :v, :hoje, CURRENT_TIMESTAMP)");
            $ins->execute([':n' => $nome, ':t' => $telefone, ':v' => $visitasLocal, ':hoje' => $hoje]);
            $resultado['inseridos']++;
        }
    }

    return $resultado;
}
