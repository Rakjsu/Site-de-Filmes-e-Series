<?php
/**
 * Página de Logout
 * 
 * Este arquivo processa o logout do usuário, encerrando sua sessão
 * e removendo cookies de autenticação antes de redirecionar para a página inicial.
 * 
 * @version 1.0.0
 */

// Iniciar sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivo de autenticação
require_once 'auth.php';

// Processar o logout
$result = logoutUser();

// Verificar se é uma requisição AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    // Retornar resposta em JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
} else {
    // Redirecionar para a página inicial com uma mensagem
    header('Location: index.php?logout=success');
    exit;
}
?> 