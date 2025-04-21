<?php
/**
 * Sistema de Gerenciamento de Sessões
 * 
 * Este arquivo contém funções para gerenciar sessões de usuários de forma segura.
 * 
 * @version 1.0.0
 */

/**
 * Inicia uma sessão segura
 * 
 * @param bool $regenerate_id Se deve regenerar o ID da sessão
 * @return bool Sucesso da operação
 */
function startSecureSession($regenerate_id = true) {
    // Definir configurações de cookie seguro
    $cookie_params = session_get_cookie_params();
    $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    
    session_set_cookie_params([
        'lifetime' => $cookie_params['lifetime'],
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // Iniciar a sessão se ainda não estiver ativa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerar ID da sessão para evitar fixação de sessão
    if ($regenerate_id) {
        session_regenerate_id(true);
    }
    
    // Verificar se é necessário renovar a sessão
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        // Sessão expirada após 30 minutos de inatividade
        sessionDestroy();
        return false;
    }
    
    // Atualizar timestamp de última atividade
    $_SESSION['last_activity'] = time();
    
    // Verificar se o IP do usuário mudou durante a sessão
    if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        // Possível sequestro de sessão, destruir sessão
        error_log("Possível sequestro de sessão detectado: IP mudou de {$_SESSION['user_ip']} para {$_SERVER['REMOTE_ADDR']}");
        sessionDestroy();
        return false;
    }
    
    // Registrar IP do usuário na primeira visita
    if (!isset($_SESSION['user_ip'])) {
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
    }
    
    // Registrar User-Agent na primeira visita
    if (!isset($_SESSION['user_agent'])) {
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
    
    return true;
}

/**
 * Define um valor na sessão
 * 
 * @param string $key Chave para armazenar o valor
 * @param mixed $value Valor para armazenar
 * @return void
 */
function setSessionValue($key, $value) {
    // Garantir que a sessão esteja ativa
    if (session_status() == PHP_SESSION_NONE) {
        startSecureSession(false);
    }
    
    $_SESSION[$key] = $value;
}

/**
 * Recupera um valor da sessão
 * 
 * @param string $key Chave do valor a ser recuperado
 * @param mixed $default Valor padrão para retornar se a chave não existir
 * @return mixed Valor da sessão ou padrão
 */
function getSessionValue($key, $default = null) {
    // Garantir que a sessão esteja ativa
    if (session_status() == PHP_SESSION_NONE) {
        startSecureSession(false);
    }
    
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Remove um valor da sessão
 * 
 * @param string $key Chave do valor a ser removido
 * @return void
 */
function unsetSessionValue($key) {
    // Garantir que a sessão esteja ativa
    if (session_status() == PHP_SESSION_NONE) {
        startSecureSession(false);
    }
    
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * Verifica se existe um valor na sessão
 * 
 * @param string $key Chave a verificar
 * @return bool Verdadeiro se a chave existir
 */
function hasSessionValue($key) {
    // Garantir que a sessão esteja ativa
    if (session_status() == PHP_SESSION_NONE) {
        startSecureSession(false);
    }
    
    return isset($_SESSION[$key]);
}

/**
 * Define uma mensagem flash (mensagem temporária que será exibida apenas uma vez)
 * 
 * @param string $type Tipo da mensagem (success, error, warning, info)
 * @param string $message Conteúdo da mensagem
 * @return void
 */
function setFlashMessage($type, $message) {
    // Garantir que a sessão esteja ativa
    if (session_status() == PHP_SESSION_NONE) {
        startSecureSession(false);
    }
    
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Recupera todas as mensagens flash e as remove da sessão
 * 
 * @return array Mensagens flash ou array vazio
 */
function getFlashMessages() {
    // Garantir que a sessão esteja ativa
    if (session_status() == PHP_SESSION_NONE) {
        startSecureSession(false);
    }
    
    $messages = isset($_SESSION['flash_messages']) ? $_SESSION['flash_messages'] : [];
    
    // Limpar mensagens após recuperá-las
    unset($_SESSION['flash_messages']);
    
    return $messages;
}

/**
 * Verifica se existem mensagens flash
 * 
 * @return bool Verdadeiro se existirem mensagens flash
 */
function hasFlashMessages() {
    // Garantir que a sessão esteja ativa
    if (session_status() == PHP_SESSION_NONE) {
        startSecureSession(false);
    }
    
    return isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages']);
}

/**
 * Destrói completamente a sessão atual
 * 
 * @return void
 */
function sessionDestroy() {
    // Limpar variáveis de sessão
    $_SESSION = [];
    
    // Destruir o cookie da sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destruir a sessão
    session_destroy();
}
?> 