<?php
/**
 * API para obter marcadores de cena
 * 
 * Parâmetros GET:
 * - contentId: ID do conteúdo (filme ou episódio)
 * - contentType: Tipo de conteúdo ('movie' ou 'episode')
 * 
 * Responde com os marcadores armazenados em data/markers/
 */

// Configurar cabeçalhos para resposta JSON
header('Content-Type: application/json');

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido. Use GET.'
    ]);
    exit;
}

// Obter parâmetros da requisição
$contentId = isset($_GET['contentId']) ? $_GET['contentId'] : '';
$contentType = isset($_GET['contentType']) ? $_GET['contentType'] : '';

// Verificar se os parâmetros foram fornecidos
if (empty($contentId) || empty($contentType)) {
    echo json_encode([
        'success' => false,
        'message' => 'Parâmetros incompletos. Forneça contentId e contentType.'
    ]);
    exit;
}

// Validar tipo de conteúdo
if ($contentType !== 'movie' && $contentType !== 'episode') {
    echo json_encode([
        'success' => false,
        'message' => 'Tipo de conteúdo inválido. Use "movie" ou "episode".'
    ]);
    exit;
}

// Sanitizar contentId para evitar injeção de caminho
$contentId = preg_replace('/[^a-zA-Z0-9_-]/', '', $contentId);

// Definir caminho para o arquivo de marcadores
$markersDir = '../data/markers';
$filename = "{$markersDir}/{$contentType}_{$contentId}.json";

// Verificar se o arquivo existe
if (!file_exists($filename)) {
    echo json_encode([
        'success' => true,
        'message' => 'Nenhum marcador encontrado para este conteúdo.',
        'data' => [
            'contentId' => $contentId,
            'contentType' => $contentType,
            'markers' => []
        ]
    ]);
    exit;
}

// Ler o arquivo
$jsonContent = file_get_contents($filename);
$data = json_decode($jsonContent, true);

// Verificar se o JSON é válido
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao ler arquivo de marcadores. Formato JSON inválido.'
    ]);
    exit;
}

// Verificar se os dados têm o formato esperado
if (!isset($data['markers']) || !is_array($data['markers'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Formato de dados inválido no arquivo de marcadores.'
    ]);
    exit;
}

// Retornar os marcadores
$response = [
    'success' => true,
    'message' => 'Marcadores obtidos com sucesso.',
    'data' => [
        'contentId' => $contentId,
        'contentType' => $contentType,
        'markers' => $data['markers'],
        'count' => count($data['markers']),
        'updatedAt' => isset($data['updatedAt']) ? $data['updatedAt'] : null
    ],
    // Compatibilidade: chave markers na raiz
    'markers' => $data['markers']
];
echo json_encode($response); 