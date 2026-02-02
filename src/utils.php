<?php

function normaliza(string $str): string
{
    $str = mb_strtolower($str, 'UTF-8');

    if (class_exists('Normalizer')) {
        $norm = normalizer_normalize($str, Normalizer::FORM_D);
        if ($norm !== false) {
            $str = $norm;
        }
    }

    // remove marcas de acento
    $str = preg_replace('/[\x{0300}-\x{036f}]/u', '', $str);
    // remove caracteres não alfanuméricos exceto espaços
    $str = preg_replace('/[^a-z0-9 ]+/u', '', $str);
    $str = preg_replace('/\s+/', ' ', $str);
    return trim($str);
}

function require_api_key()
{
    // lê API_KEY do ambiente
    $expected = getenv('API_KEY') ?: null;

    // Primeiro tenta header
    $provided = null;
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        $provided = $headers['X-API-KEY'] ?? $headers['x-api-key'] ?? $provided;
    }

    // se não veio no header, permite query param (menos seguro) — útil para Holyrics que não envia headers
    if (!$provided && isset($_GET['api_key'])) {
        $provided = $_GET['api_key'];
    }

    if (!$expected || !$provided || !hash_equals($expected, $provided)) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'unauthorized']);
        exit;
    }
}
