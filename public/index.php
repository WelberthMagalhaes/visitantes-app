<?php
require __DIR__ . '/../src/visitantes.php';
require __DIR__ . '/../src/utils.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// responder JSON por padrão
header('Content-Type: application/json; charset=utf-8');

// POST /api/visitantes -> cadastrar (exige API key)
if ($method === 'POST' && $uri === '/api/visitantes') {
    require_api_key();

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

// GET /api/visitantes?data=YYYY-MM-DD -> lista de nomes para Holyrics (exige API key)
if ($method === 'GET' && $uri === '/api/visitantes') {
    require_api_key();

    $data = $_GET['data'] ?? date('Y-m-d');
    $lista = listarVisitantesPorData($data);
    echo json_encode($lista);
    exit;
}

// GET /api/visitantes/all -> lista completa (uso administrativo) (exige API key)
if ($method === 'GET' && $uri === '/api/visitantes/all') {
    require_api_key();
    echo json_encode(listarTodosVisitantes());
    exit;
}

http_response_code(404);
echo json_encode(['erro' => 'Rota não encontrada']);
