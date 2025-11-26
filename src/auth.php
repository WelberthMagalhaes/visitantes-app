<?php

function iniciarSessao()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function verificarSessaoInterna($senhaFornecida = null)
{
    iniciarSessao();

    // Se já está autenticado, ok
    if (isset($_SESSION['auth_interno']) && $_SESSION['auth_interno'] === true) {
        return true;
    }

    // Se não forneceu senha, tenta pegar de POST/GET
    if ($senhaFornecida === null) {
        $senhaFornecida = $_POST['senha'] ?? $_GET['senha'] ?? null;
    }

    // Lê senha do ambiente
    $senhaCorreta = getenv('SENHA_INTERNA') ?: 'recepcao123teste@';

    if ($senhaFornecida && hash_equals($senhaCorreta, $senhaFornecida)) {
        $_SESSION['auth_interno'] = true;
        return true;
    }

    return false;
}

function exigirAutenticacaoInterna()
{
    if (!verificarSessaoInterna()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Acesso negado. Senha necessária.']);
        exit;
    }
}
