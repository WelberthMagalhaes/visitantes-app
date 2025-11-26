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
    $ins = $db->prepare("INSERT INTO visitantes (nome, telefone, visitas, ultima_visita, criado_em) VALUES (:n, :t, 1, :hoje, :hoje)");
    $ins->execute([':n' => $nome, ':t' => $telefone, ':hoje' => $hoje]);

    return ['tipo' => 'novo', 'id' => $db->lastInsertId(), 'nome' => $nome];
}

function cadastrarFamiliaBackend($nomeFamilia, $membros)
{
    $db = db();
    $hoje = date('Y-m-d');
    
    // Verificar se algum membro já visitou hoje
    foreach ($membros as $membro) {
        $nomeNorm = normaliza($membro['nome']);
        $stmt = $db->prepare("SELECT * FROM visitantes WHERE ultima_visita = :hoje");
        $stmt->execute([':hoje' => $hoje]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as $r) {
            if (normaliza($r['nome']) === $nomeNorm) {
                return ['tipo' => 'ja_cadastrado_hoje', 'nome' => $r['nome']];
            }
        }
    }
    
    // Criar família
    $insFamilia = $db->prepare("INSERT INTO familias (nome_familia, criado_em) VALUES (:nome, :hoje)");
    $insFamilia->execute([':nome' => $nomeFamilia, ':hoje' => $hoje]);
    $familiaId = $db->lastInsertId();
    
    $membrosIds = [];
    
    foreach ($membros as $membro) {
        $nome = $membro['nome'];
        $telefone = $membro['telefone'] ?? '';
        $parentesco = $membro['parentesco'] ?? '';
        
        // Verificar se já existe (retorno)
        $nomeNorm = normaliza($nome);
        $stmt = $db->prepare("SELECT * FROM visitantes");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $encontrado = false;
        foreach ($rows as $r) {
            if (normaliza($r['nome']) === $nomeNorm) {
                // Atualizar visitante existente
                $upd = $db->prepare("UPDATE visitantes SET visitas = visitas + 1, ultima_visita = :data, familia_id = :fid, parentesco = :parentesco WHERE id = :id");
                $upd->execute([':data' => $hoje, ':fid' => $familiaId, ':parentesco' => $parentesco, ':id' => $r['id']]);
                $membrosIds[] = $r['id'];
                $encontrado = true;
                break;
            }
        }
        
        if (!$encontrado) {
            // Novo visitante
            $ins = $db->prepare("INSERT INTO visitantes (nome, telefone, parentesco, familia_id, visitas, ultima_visita, criado_em) VALUES (:n, :t, :p, :fid, 1, :hoje, :hoje)");
            $ins->execute([':n' => $nome, ':t' => $telefone, ':p' => $parentesco, ':fid' => $familiaId, ':hoje' => $hoje]);
            $membrosIds[] = $db->lastInsertId();
        }
    }
    
    return ['tipo' => 'familia_cadastrada', 'familia_id' => $familiaId, 'membros_ids' => $membrosIds];
}

function listarVisitantesPorData(string $data)
{
    $db = db();
    $stmt = $db->prepare("
        SELECT v.id, v.nome, v.parentesco, f.nome_familia, v.familia_id
        FROM visitantes v 
        LEFT JOIN familias f ON v.familia_id = f.id 
        WHERE v.ultima_visita = :d 
        ORDER BY v.familia_id DESC, v.id DESC
    ");
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
