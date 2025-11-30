<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

function cadastrarVisitanteBackend($nome, $telefone, $acompanhantes = 0, $observacao = null)
{
    $db = db();
    $nomeNorm = normaliza($nome);
    $hoje = date('Y-m-d');

    // buscar por nome normalizado
    $stmt = $db->prepare("SELECT * FROM visitantes");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $visitanteId = null;
    $tipo = 'novo';

    foreach ($rows as $r) {
        if (normaliza($r['nome']) === $nomeNorm) {
            // Verifica se já tem visita registrada hoje
            $checkVisita = $db->prepare("SELECT 1 FROM visitas WHERE visitante_id = :id AND data_visita = :hoje");
            $checkVisita->execute([':id' => $r['id'], ':hoje' => $hoje]);

            if ($checkVisita->fetch()) {
                return ['tipo' => 'ja_cadastrado_hoje', 'id' => $r['id'], 'nome' => $r['nome']];
            }

            // Atualiza visitante existente
            $upd = $db->prepare("UPDATE visitantes SET visitas = visitas + 1, ultima_visita = :data WHERE id = :id");
            $upd->execute([':data' => $hoje, ':id' => $r['id']]);

            $visitanteId = $r['id'];
            $tipo = 'retorno';
            break;
        }
    }

    // Se não encontrou, cria novo visitante
    if (!$visitanteId) {
        $ins = $db->prepare("INSERT INTO visitantes (nome, telefone, visitas, ultima_visita, criado_em) VALUES (:n, :t, 1, :hoje, CURRENT_TIMESTAMP)");
        $ins->execute([':n' => $nome, ':t' => $telefone, ':hoje' => $hoje]);
        $visitanteId = $db->lastInsertId();
    }

    // SEMPRE cria registro na tabela visitas
    $insVisita = $db->prepare("INSERT INTO visitas (visitante_id, data_visita, acompanhantes, observacao) VALUES (:vid, :data, :acomp, :obs)");
    $insVisita->execute([
        ':vid' => $visitanteId,
        ':data' => $hoje,
        ':acomp' => $acompanhantes,
        ':obs' => $observacao
    ]);

    return ['tipo' => $tipo, 'id' => $visitanteId, 'visita_id' => $db->lastInsertId(), 'nome' => $nome];
}

function atualizarObservacaoVisita($visitaId, $acompanhantes, $observacao)
{
    $db = db();
    $upd = $db->prepare("UPDATE visitas SET acompanhantes = :acomp, observacao = :obs WHERE id = :id");
    $upd->execute([':acomp' => $acompanhantes, ':obs' => $observacao, ':id' => $visitaId]);
    return ['status' => 'ok'];
}

function atualizarNomeVisitante($visitanteId, $nome)
{
    $db = db();
    $upd = $db->prepare("UPDATE visitantes SET nome = :nome WHERE id = :id");
    $upd->execute([':nome' => $nome, ':id' => $visitanteId]);
    return ['status' => 'ok'];
}

function listarVisitantesPorData(string $data)
{
    $db = db();
    $stmt = $db->prepare("
        SELECT 
            v.id,
            v.nome,
            vt.visitante_id,
            vt.acompanhantes,
            vt.observacao,
            (SELECT COUNT(*) FROM visitas WHERE visitante_id = v.id) as total_visitas
        FROM visitas vt
        JOIN visitantes v ON v.id = vt.visitante_id
        WHERE vt.data_visita = :d
        ORDER BY vt.id DESC
    ");
    $stmt->execute([':d' => $data]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listarTodosVisitantes()
{
    $db = db();
    $stmt = $db->prepare("SELECT id, nome, telefone, visitas, DATE(ultima_visita) as ultima_visita FROM visitantes ORDER BY nome ASC");
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
            // Verifica se já tem visita hoje
            $checkVisita = $db->prepare("SELECT 1 FROM visitas WHERE visitante_id = :id AND data_visita = :hoje");
            $checkVisita->execute([':id' => $encontrado['id'], ':hoje' => $hoje]);

            if ($checkVisita->fetch()) {
                $resultado['ignorados']++;
                continue;
            }

            // Atualiza visitante
            $upd = $db->prepare("UPDATE visitantes SET visitas = visitas + 1, ultima_visita = :data WHERE id = :id");
            $upd->execute([':data' => $hoje, ':id' => $encontrado['id']]);

            // Cria registro de visita
            $insVisita = $db->prepare("INSERT INTO visitas (visitante_id, data_visita, acompanhantes, observacao) VALUES (:vid, :data, 0, NULL)");
            $insVisita->execute([':vid' => $encontrado['id'], ':data' => $hoje]);

            $resultado['atualizados']++;
        } else {
            // Insere novo visitante
            $ins = $db->prepare("INSERT INTO visitantes (nome, telefone, visitas, ultima_visita, criado_em) VALUES (:n, :t, 1, :hoje, CURRENT_TIMESTAMP)");
            $ins->execute([':n' => $nome, ':t' => $telefone, ':hoje' => $hoje]);
            $novoId = $db->lastInsertId();

            // Cria registro de visita
            $insVisita = $db->prepare("INSERT INTO visitas (visitante_id, data_visita, acompanhantes, observacao) VALUES (:vid, :data, 0, NULL)");
            $insVisita->execute([':vid' => $novoId, ':data' => $hoje]);

            $resultado['inseridos']++;
        }
    }

    return $resultado;
}
