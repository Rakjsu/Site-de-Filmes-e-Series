<?php
/**
 * API para salvar progresso de reprodução
 * 
 * Este arquivo salva o progresso atual da reprodução de um vídeo
 * para que o usuário possa continuar assistindo de onde parou.
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
$currentTime = isset($data['current_time']) ? floatval($data['current_time']) : 0;

// Validar parâmetros obrigatórios
if (empty($contentId) || empty($contentType) || $currentTime < 0) {
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

// Verificar se a tabela existe, criando-a se necessário
if (!dbTableExists('playback_progress')) {
    try {
        $createTableSql = "CREATE TABLE IF NOT EXISTS playback_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            content_id INT NOT NULL,
            content_type ENUM('movie', 'episode') NOT NULL,
            progress_seconds FLOAT NOT NULL,
            duration_seconds FLOAT,
            percent_complete FLOAT GENERATED ALWAYS AS (
                CASE WHEN duration_seconds > 0 
                THEN LEAST(progress_seconds / duration_seconds, 1) 
                ELSE 0 
                END
            ) STORED,
            last_watched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_progress (user_id, content_id, content_type),
            KEY idx_user_content (user_id, content_type)
        )";
        
        dbExecute($createTableSql);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Erro ao criar tabela de progresso',
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Salvar progresso no banco de dados
try {
    // Verificar se já existe registro para este conteúdo
    $sql = "SELECT id FROM playback_progress 
            WHERE user_id = ? AND content_id = ? AND content_type = ?";
    
    $existingRecord = dbQuerySingle($sql, [$userId, $contentId, $contentType]);
    
    // Variável para duração (opcional)
    $durationSeconds = isset($data['duration']) ? floatval($data['duration']) : null;
    
    if ($existingRecord) {
        // Atualizar registro existente
        $sql = "UPDATE playback_progress 
                SET progress_seconds = ?, 
                    duration_seconds = COALESCE(?, duration_seconds)
                WHERE user_id = ? AND content_id = ? AND content_type = ?";
        
        $params = [$currentTime, $durationSeconds, $userId, $contentId, $contentType];
        $result = dbExecute($sql, $params);
        
    } else {
        // Inserir novo registro
        $sql = "INSERT INTO playback_progress 
                (user_id, content_id, content_type, progress_seconds, duration_seconds) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [$userId, $contentId, $contentType, $currentTime, $durationSeconds];
        $result = dbExecute($sql, $params);
    }
    
    // Verificar resultado da operação
    if ($result !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Progresso salvo com sucesso',
            'data' => [
                'user_id' => $userId,
                'content_id' => $contentId,
                'content_type' => $contentType,
                'progress_seconds' => $currentTime
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Não foi possível salvar o progresso'
        ]);
    }
    
} catch (PDOException $e) {
    // Log do erro
    error_log('Erro ao salvar progresso: ' . $e->getMessage());
    
    // Retornar erro
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao salvar progresso',
        'message' => $e->getMessage()
    ]);
} 