<?php
require __DIR__ . '/../src/visitantes.php';
require __DIR__ . '/../src/auth.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// responder JSON por padrão
header('Content-Type: application/json; charset=utf-8');

// === ROTA DE DEBUG (remover em produção) ===
if ($method === 'GET' && $uri === '/debug/env') {
    echo json_encode([
        'SENHA_INTERNA' => getenv('SENHA_INTERNA') ?: 'NÃO DEFINIDA',
        'SENHA_INTERNA_length' => strlen(getenv('SENHA_INTERNA') ?: ''),
        'API_KEY' => getenv('API_KEY') ? 'DEFINIDA (' . strlen(getenv('API_KEY')) . ' chars)' : 'NÃO DEFINIDA',
        'DB_HOST' => getenv('DB_HOST') ?: 'NÃO DEFINIDA',
        'DB_NAME' => getenv('DB_NAME') ?: 'NÃO DEFINIDA',
        'all_env_keys' => array_keys($_ENV)
    ]);
    exit;
}

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
    $acompanhantes = $dados['acompanhantes'] ?? 0;
    $observacao = $dados['observacao'] ?? null;
    $res = cadastrarVisitanteBackend($dados['nome'], $telefone, $acompanhantes, $observacao);

    echo json_encode(['status' => 'ok', 'res' => $res]);
    exit;
}

// PUT /interno/visitantes/{id} -> atualizar nome do visitante
if ($method === 'PUT' && preg_match('#^/interno/visitantes/(\d+)$#', $uri, $matches)) {
    exigirAutenticacaoInterna();

    $visitanteId = $matches[1];
    $dados = json_decode(file_get_contents('php://input'), true) ?? [];

    if (empty($dados['nome'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Nome é obrigatório']);
        exit;
    }

    $res = atualizarNomeVisitante($visitanteId, $dados['nome']);
    echo json_encode($res);
    exit;
}

// PUT /interno/visitas/{id} -> atualizar observação/acompanhantes
if ($method === 'PUT' && preg_match('#^/interno/visitas/(\d+)$#', $uri, $matches)) {
    exigirAutenticacaoInterna();

    $visitaId = $matches[1];
    $dados = json_decode(file_get_contents('php://input'), true) ?? [];

    $acompanhantes = $dados['acompanhantes'] ?? 0;
    $observacao = $dados['observacao'] ?? null;

    $res = atualizarObservacaoVisita($visitaId, $acompanhantes, $observacao);
    echo json_encode($res);
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
