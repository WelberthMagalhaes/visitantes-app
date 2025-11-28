<?php
require __DIR__ . '/../src/visitantes.php';
require __DIR__ . '/../src/auth.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// responder JSON por padrão
header('Content-Type: application/json; charset=utf-8');

// === ROTAS API (com API_KEY) - Para Holyrics e integrações externas ===

// GET /api/visitantes?data=YYYY-MM-DD -> lista visitantes por data (exige API key)
if ($method === 'GET' && $uri === '/api/visitantes') {
    require_api_key();

    if (empty($_GET['data'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Parâmetro `data` é obrigatório (formato: YYYY-MM-DD)']);
        exit;
    }

    $data = $_GET['data'];
    $lista = listarVisitantesPorData($data);
    echo json_encode($lista);
    exit;
}

// === ROTAS INTERNAS (com autenticação por sessão) ===

// POST /interno/login -> autentica sessão
if ($method === 'POST' && $uri === '/interno/login') {
    $dados = json_decode(file_get_contents('php://input'), true) ?? [];
    $senha = $dados['senha'] ?? null;



    if (verificarSessaoInterna($senha)) {
        echo json_encode(['status' => 'ok', 'message' => 'Autenticado']);
    } else {
        http_response_code(401);
        echo json_encode(['erro' => 'Senha incorreta']);
    }
    exit;
}

// POST /interno/visitantes -> cadastrar (exige autenticação)
if ($method === 'POST' && $uri === '/interno/visitantes') {
    exigirAutenticacaoInterna();

    $dados = json_decode(file_get_contents('php://input'), true) ?? [];

    if (empty($dados['nome'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Nome é obrigatório']);
        exit;
    }

    $telefone = $dados['telefone'] ?? '';
    $res = cadastrarVisitanteBackend($dados['nome'], $telefone);

    echo json_encode(['status' => 'ok', 'res' => $res]);
    exit;
}

// GET /interno/visitantes/all -> lista completa (exige autenticação)
if ($method === 'GET' && $uri === '/interno/visitantes/all') {
    exigirAutenticacaoInterna();
    echo json_encode(listarTodosVisitantes());
    exit;
}

// GET /interno/visitantes/hoje -> lista visitantes de hoje (exige autenticação)
if ($method === 'GET' && $uri === '/interno/visitantes/hoje') {
    exigirAutenticacaoInterna();

    $hoje = date('Y-m-d');
    $visitantes = listarVisitantesPorData($hoje);

    echo json_encode(['individuais' => $visitantes]);
    exit;
}

// POST /interno/sync -> sincroniza dados locais com servidor (exige autenticação)
if ($method === 'POST' && $uri === '/interno/sync') {
    exigirAutenticacaoInterna();

    $dados = json_decode(file_get_contents('php://input'), true) ?? [];
    $visitantesLocais = $dados['visitantes'] ?? [];

    $resultado = sincronizarVisitantes($visitantesLocais);

    echo json_encode(['status' => 'ok', 'resultado' => $resultado]);
    exit;
}

// GET / -> redireciona para cadastrar.html
if ($method === 'GET' && $uri === '/') {
    header('Location: /cadastrar.html');
    exit;
}

http_response_code(404);
echo json_encode(['erro' => 'Rota não encontrada']);
