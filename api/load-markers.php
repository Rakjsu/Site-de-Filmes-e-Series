<?php
/**
 * load-markers.php
 * 
 * API para carregar marcadores de cena para um conteúdo específico.
 * Aceita requisições GET com os seguintes parâmetros:
 * - contentId: ID do conteúdo (filme ou episódio)
 * - contentType: Tipo do conteúdo ('movie' ou 'episode')
 * 
 * Retorna um objeto JSON com os marcadores do conteúdo.
 */

// Definir cabeçalho para resposta JSON
header('Content-Type: application/json');

// Verificar parâmetros obrigatórios
if (empty($_GET['contentId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de conteúdo não fornecido.'
    ]);
    exit;
}

if (empty($_GET['contentType']) || !in_array($_GET['contentType'], ['movie', 'episode'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Tipo de conteúdo inválido. Use "movie" ou "episode".'
    ]);
    exit;
}

// Limpar e validar contentId para evitar injeção de caminho
$contentId = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['contentId']);
$contentType = $_GET['contentType'];

// Diretório para armazenar os marcadores
$markersDir = __DIR__ . '/../data/markers/' . $contentType;

// Caminho do arquivo para carregar
$filePath = $markersDir . '/' . $contentId . '.json';

// Verificar se o arquivo existe
if (!file_exists($filePath)) {
    echo json_encode([
        'success' => true,
        'message' => 'Nenhum marcador encontrado para este conteúdo.',
        'data' => [
            'markers' => [],
            'contentId' => $contentId,
            'contentType' => $contentType
        ]
    ]);
    exit;
}

// Ler o arquivo
$jsonData = file_get_contents($filePath);
$data = json_decode($jsonData, true);

// Verificar se o JSON é válido
if ($data === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao ler dados de marcadores (JSON inválido).'
    ]);
    exit;
}

// Validar a estrutura do arquivo
if (!isset($data['markers']) || !is_array($data['markers'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Formato de arquivo de marcadores inválido.'
    ]);
    exit;
}

// Retornar os dados de marcadores
echo json_encode([
    'success' => true,
    'message' => 'Marcadores carregados com sucesso.',
    'data' => [
        'markers' => $data['markers'],
        'contentId' => $contentId,
        'contentType' => $contentType,
        'updatedAt' => $data['updatedAt'] ?? date('Y-m-d H:i:s')
    ]
]); 