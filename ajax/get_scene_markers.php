<?php
/**
 * API para obter marcadores de cena
 * 
 * Este arquivo retorna marcadores de cena para um conteúdo específico
 * para exibição na barra de progresso do player de vídeo.
 * 
 * @param int content_id ID do conteúdo (filme ou episódio)
 * @param string content_type Tipo de conteúdo ('movie' ou 'episode')
 * @return array JSON com marcadores de cena
 */

// Definir cabeçalhos para resposta JSON
header('Content-Type: application/json');

// Incluir a configuração do banco de dados
require_once '../db_config.php';

// Iniciar sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'error' => 'Usuário não autenticado',
        'markers' => []
    ]);
    exit;
}

// Obter parâmetros da requisição
$contentId = isset($_GET['content_id']) ? intval($_GET['content_id']) : 0;
$contentType = isset($_GET['content_type']) ? $_GET['content_type'] : '';

// Validar parâmetros
if (empty($contentId) || empty($contentType) || !in_array($contentType, ['movie', 'episode'])) {
    echo json_encode([
        'error' => 'Parâmetros inválidos',
        'markers' => []
    ]);
    exit;
}

// Obter marcadores do banco de dados
try {
    $sql = "SELECT id, time_seconds AS time, title, created_at 
            FROM scene_markers 
            WHERE content_id = ? AND content_type = ?
            ORDER BY time_seconds ASC";
    
    $markers = dbQuery($sql, [$contentId, $contentType]);
    
    // Processar marcadores
    $processedMarkers = [];
    foreach ($markers as $marker) {
        $processedMarkers[] = [
            'id' => (int)$marker['id'],
            'time' => (float)$marker['time'],
            'title' => $marker['title'],
            'created_at' => $marker['created_at']
        ];
    }
    
    // Retornar resposta
    echo json_encode([
        'success' => true,
        'markers' => $processedMarkers
    ]);
    
} catch (PDOException $e) {
    // Log do erro
    error_log('Erro ao buscar marcadores de cena: ' . $e->getMessage());
    
    // Retornar erro
    echo json_encode([
        'error' => 'Erro ao buscar marcadores',
        'message' => $e->getMessage(),
        'markers' => []
    ]);
} 