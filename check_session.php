<?php
/**
 * Verificação de Sessão
 * 
 * Este arquivo verifica se o usuário possui uma sessão válida
 * ou se um token de autenticação válido está presente nos cookies.
 * 
 * @version 1.0.0
 */

// Iniciar sessão
session_start();

// Definir cabeçalhos para evitar cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json; charset=utf-8');

// Incluir arquivo de configuração do banco de dados
require_once 'db_config.php';

// Definir constantes
define('SESSION_TIMEOUT', 1800); // 30 minutos em segundos
define('RESPONSE_SUCCESS', 'success');
define('RESPONSE_ERROR', 'error');

// Variável para armazenar resposta
$response = [
    'status' => RESPONSE_ERROR,
    'message' => 'Sessão inválida',
    'authenticated' => false,
    'user' => null
];

// Verificar se a sessão está ativa e válida
if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
    // Verificar timeout da sessão
    $inactiveTime = time() - $_SESSION['last_activity'];
    
    if ($inactiveTime < SESSION_TIMEOUT) {
        // Sessão válida - Atualizar timestamp de última atividade
        $_SESSION['last_activity'] = time();
        
        // Buscar dados do usuário (apenas os necessários)
        $user = dbQuerySingle(
            "SELECT user_id, username, email, role FROM users WHERE user_id = ?",
            [$_SESSION['user_id']]
        );
        
        if ($user) {
            $response = [
                'status' => RESPONSE_SUCCESS,
                'message' => 'Sessão válida',
                'authenticated' => true,
                'user' => [
                    'id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
        }
    } else {
        // Sessão expirou - Limpar dados
        session_unset();
        session_destroy();
        $response['message'] = 'Sessão expirou por inatividade';
    }
} 
// Verificar token nos cookies se sessão não for válida
elseif (isset($_COOKIE['auth_token'])) {
    try {
        $token = trim($_COOKIE['auth_token']);
        
        // Verificar token na base de dados
        $tokenData = dbQuerySingle(
            "SELECT u.user_id, u.username, u.email, u.role, t.expiry 
             FROM user_tokens t 
             JOIN users u ON t.user_id = u.user_id 
             WHERE t.token = ? AND t.is_active = 1",
            [$token]
        );
        
        if ($tokenData && strtotime($tokenData['expiry']) > time()) {
            // Token válido - Iniciar nova sessão
            $_SESSION['user_id'] = $tokenData['user_id'];
            $_SESSION['last_activity'] = time();
            
            $response = [
                'status' => RESPONSE_SUCCESS,
                'message' => 'Autenticado via token',
                'authenticated' => true,
                'user' => [
                    'id' => $tokenData['user_id'],
                    'username' => $tokenData['username'],
                    'email' => $tokenData['email'],
                    'role' => $tokenData['role']
                ]
            ];
        } else if ($tokenData) {
            // Token expirado - Desativar no banco
            dbExecute(
                "UPDATE user_tokens SET is_active = 0 WHERE token = ?",
                [$token]
            );
            
            // Remover cookie
            setcookie('auth_token', '', time() - 3600, '/', '', true, true);
            $response['message'] = 'Token de autenticação expirado';
        } else {
            // Token inválido - Remover cookie
            setcookie('auth_token', '', time() - 3600, '/', '', true, true);
            $response['message'] = 'Token de autenticação inválido';
        }
    } catch (PDOException $e) {
        error_log('Erro na verificação de token: ' . $e->getMessage());
        $response['message'] = 'Erro ao verificar autenticação';
    }
}

// Retornar resposta
echo json_encode($response);
exit;
?> 