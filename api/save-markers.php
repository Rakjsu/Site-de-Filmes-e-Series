<?php
/**
 * API para salvar marcadores de cena para um conteúdo específico.
 * Aceita requisições POST com os seguintes parâmetros:
 * - contentId: ID do conteúdo (filme ou episódio)
 * - contentType: Tipo do conteúdo ('movie' ou 'episode')
 * - markers: Array de marcadores com 'time' e 'title'
 * 
 * Retorna um objeto JSON com o status da operação.
 */

// Definir cabeçalho para resposta JSON
header('Content-Type: application/json');

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use POST.'
    ]);
    exit;
}

// Obter o corpo da requisição
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Verificar se o JSON é válido
if ($data === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Dados JSON inválidos.'
    ]);
    exit;
}

// Validar parâmetros obrigatórios
if (empty($data['contentId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de conteúdo não fornecido.'
    ]);
    exit;
}

if (empty($data['contentType']) || !in_array($data['contentType'], ['movie', 'episode'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Tipo de conteúdo inválido. Use "movie" ou "episode".'
    ]);
    exit;
}

if (!isset($data['markers']) || !is_array($data['markers'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Array de marcadores não fornecido ou inválido.'
    ]);
    exit;
}

// Limpar e validar contentId para evitar injeção de caminho
$contentId = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['contentId']);
$contentType = $data['contentType'];

// Verificar permissões de administrador
// Em um ambiente real, deve-se verificar a autenticação e autorização do usuário
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Permissão negada. Apenas administradores podem salvar marcadores.'
    ]);
    exit;
}

// Diretório para armazenar os marcadores
$markersDir = __DIR__ . '/../data/markers/' . $contentType;

// Criar diretório se não existir
if (!is_dir($markersDir)) {
    if (!mkdir($markersDir, 0755, true)) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao criar diretório para marcadores.'
        ]);
        exit;
    }
}

// Caminho do arquivo para salvar
$filePath = $markersDir . '/' . $contentId . '.json';

// Validar marcadores
$validMarkers = [];
foreach ($data['markers'] as $marker) {
    // Verificar campos obrigatórios
    if (!isset($marker['time']) || !isset($marker['title'])) {
        continue;
    }
    
    // Validar tempo
    $time = floatval($marker['time']);
    if ($time < 0) {
        continue;
    }
    
    // Validar título
    $title = trim($marker['title']);
    if (empty($title)) {
        $title = 'Marcador em ' . gmdate('i:s', $time);
    }
    
    // Adicionar marcador válido
    $validMarkers[] = [
        'time' => $time,
        'title' => $title
    ];
}

// Ordenar marcadores por tempo
usort($validMarkers, function($a, $b) {
    return $a['time'] <=> $b['time'];
});

// Preparar dados para salvar
$saveData = [
    'markers' => $validMarkers,
    'updatedAt' => date('Y-m-d H:i:s')
];

// Salvar arquivo
if (file_put_contents($filePath, json_encode($saveData, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Marcadores salvos com sucesso.',
        'data' => [
            'count' => count($validMarkers),
            'contentId' => $contentId,
            'contentType' => $contentType
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar marcadores no arquivo.'
    ]);
} 