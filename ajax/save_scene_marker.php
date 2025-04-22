<?php
/**
 * API para salvar marcadores de cena
 * 
 * Este arquivo salva um novo marcador de cena para um conteúdo específico.
 * Somente administradores podem criar marcadores.
 * 
 * @return array JSON com status da operação
 */

// Definir cabeçalhos para resposta JSON
header('Content-Type: application/json');

// Incluir a configuração do banco de dados
require_once '../db_config.php';

// Incluir funções de autenticação
require_once '../auth.php';

// Iniciar sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário é administrador
if (!isAdmin()) {
    echo json_encode([
        'success' => false,
        'error' => 'Permissão negada. Somente administradores podem criar marcadores.'
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
$time = isset($data['time']) ? floatval($data['time']) : 0;
$title = isset($data['title']) ? trim($data['title']) : '';

// Validar parâmetros obrigatórios
if (empty($contentId) || empty($contentType) || $time <= 0 || empty($title)) {
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

// Verificar se a tabela existe, criando-a se necessário
if (!dbTableExists('scene_markers')) {
    try {
        $createTableSql = "CREATE TABLE IF NOT EXISTS scene_markers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_id INT NOT NULL,
            content_type ENUM('movie', 'episode') NOT NULL,
            time_seconds FLOAT NOT NULL,
            title VARCHAR(255) NOT NULL,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_content (content_id, content_type)
        )";
        
        dbExecute($createTableSql);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Erro ao criar tabela de marcadores',
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Inserir marcador no banco de dados
try {
    $userId = $_SESSION['user_id'];
    
    $sql = "INSERT INTO scene_markers (content_id, content_type, time_seconds, title, created_by) 
            VALUES (?, ?, ?, ?, ?)";
    
    $result = dbExecute($sql, [$contentId, $contentType, $time, $title, $userId]);
    
    if ($result) {
        // Obter ID do marcador inserido
        $markerId = getConnection()->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Marcador criado com sucesso',
            'marker' => [
                'id' => $markerId,
                'content_id' => $contentId,
                'content_type' => $contentType,
                'time' => $time,
                'title' => $title
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Não foi possível criar o marcador'
        ]);
    }
    
} catch (PDOException $e) {
    // Log do erro
    error_log('Erro ao salvar marcador de cena: ' . $e->getMessage());
    
    // Retornar erro
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao salvar marcador',
        'message' => $e->getMessage()
    ]);
} 