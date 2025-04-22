<?php
/**
 * API para limpar progresso de reprodução
 * 
 * Este arquivo limpa o progresso de reprodução de um vídeo,
 * tipicamente chamado quando o usuário assiste o vídeo até o final.
 * 
 * @return array JSON com status da operação
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
        'success' => false,
        'error' => 'Usuário não autenticado'
    ]);
    exit;
}

// Obter dados da requisição POST (formato JSON)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verificar se os dados foram decodificados corretamente
if ($data === null) {
    echo json_encode([
        'success' => false,
        'error' => 'Formato JSON inválido'
    ]);
    exit;
}

// Extrair e validar parâmetros
$contentId = isset($data['content_id']) ? intval($data['content_id']) : 0;
$contentType = isset($data['content_type']) ? $data['content_type'] : '';

// Validar parâmetros obrigatórios
if (empty($contentId) || empty($contentType)) {
    echo json_encode([
        'success' => false,
        'error' => 'Parâmetros inválidos ou incompletos'
    ]);
    exit;
}

// Validar tipo de conteúdo
if (!in_array($contentType, ['movie', 'episode'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Tipo de conteúdo inválido'
    ]);
    exit;
}

// Obter ID do usuário da sessão
$userId = $_SESSION['user_id'];

// Verificar se a tabela existe
if (!dbTableExists('playback_progress')) {
    echo json_encode([
        'success' => true,
        'message' => 'Tabela de progresso não existe, nada a limpar'
    ]);
    exit;
}

// Verificar se existe uma entrada watch_history para marcar como completo
if (dbTableExists('watch_history')) {
    try {
        // Transformar tipo de conteúdo para o formato da tabela watch_history
        $itemType = $contentType === 'movie' ? 'movie' : 'episode';
        $itemId = $contentId;
        
        // Marcar como assistido na tabela watch_history
        $completeSql = "INSERT INTO watch_history (user_id, item_id, item_type, completed, watched_at) 
                         VALUES (?, ?, ?, 1, NOW())
                         ON DUPLICATE KEY UPDATE 
                         completed = 1,
                         watched_at = NOW()";
        
        dbExecute($completeSql, [$userId, $itemId, $itemType]);
        
    } catch (PDOException $e) {
        // Apenas registrar o erro, mas continuar
        error_log('Erro ao marcar como completo: ' . $e->getMessage());
    }
}

// Limpar progresso no banco de dados
try {
    $sql = "DELETE FROM playback_progress 
            WHERE user_id = ? AND content_id = ? AND content_type = ?";
    
    $result = dbExecute($sql, [$userId, $contentId, $contentType]);
    
    // Verificar resultado da operação
    echo json_encode([
        'success' => true,
        'message' => 'Progresso limpo com sucesso',
        'rows_affected' => $result
    ]);
    
} catch (PDOException $e) {
    // Log do erro
    error_log('Erro ao limpar progresso: ' . $e->getMessage());
    
    // Retornar erro
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao limpar progresso',
        'message' => $e->getMessage()
    ]);
} 