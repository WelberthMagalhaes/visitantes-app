<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

function cadastrarVisitanteBackend($nome, $telefone)
{
    $db = db();
    $nomeNorm = normaliza($nome);

    // buscar por nome normalizado
    $stmt = $db->prepare("SELECT * FROM visitantes");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        if (normaliza($r['nome']) === $nomeNorm) {
            // atualiza visitas e ultima_visita
            $upd = $db->prepare("UPDATE visitantes SET visitas = visitas + 1, ultima_visita = :data WHERE id = :id");
            $upd->execute([':data' => date('Y-m-d'), ':id' => $r['id']]);

            return ['tipo' => 'retorno', 'id' => $r['id'], 'nome' => $r['nome']];
        }
    }

    // novo
    $ins = $db->prepare("INSERT INTO visitantes (nome, telefone, visitas, ultima_visita, criado_em) VALUES (:n, :t, 1, :hoje, :hoje)");
    $ins->execute([':n' => $nome, ':t' => $telefone, ':hoje' => date('Y-m-d')]);

    return ['tipo' => 'novo', 'id' => $db->lastInsertId(), 'nome' => $nome];
}

function listarVisitantesPorData(string $data)
{
    $db = db();
    $stmt = $db->prepare("SELECT id, nome FROM visitantes WHERE ultima_visita = :d ORDER BY id DESC");
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
