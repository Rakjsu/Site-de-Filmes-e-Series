<?php
/**
 * Sistema de Segurança
 * 
 * Este arquivo contém funções para gerenciar segurança no sistema.
 * 
 * @version 1.0.0
 */

/**
 * Sanitiza entrada de usuário para evitar XSS
 * 
 * @param string $input Entrada a ser sanitizada
 * @return string Entrada sanitizada
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
        return $input;
    }
    
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Gera um hash seguro para senha
 * 
 * @param string $password Senha
 * @return string Hash da senha
 */
function secureHash($password) {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
}

/**
 * Verifica se uma senha corresponde ao hash
 * 
 * @param string $password Senha
 * @param string $hash Hash armazenado
 * @return bool Verdadeiro se a senha corresponder ao hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Gera um token CSRF para proteção contra ataques CSRF
 * 
 * @return string Token CSRF
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = generateSecureToken(32);
    $_SESSION['csrf_token'] = $token;
    
    return $token;
}

/**
 * Valida token CSRF
 * 
 * @param string $token Token a ser validado
 * @return bool Verdadeiro se o token for válido
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Gera um token seguro
 * 
 * @param int $length Comprimento do token
 * @return string Token seguro
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Implementa um rate limiter baseado em sessão
 * 
 * @param string $key Chave única para o limitador
 * @param int $maxAttempts Número máximo de tentativas
 * @param int $timeWindow Janela de tempo em segundos
 * @return bool Verdadeiro se o limite for excedido
 */
function rateLimiter($key, $maxAttempts = 5, $timeWindow = 300) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $now = time();
    
    // Inicializar array de tentativas se ainda não existir
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    // Inicializar contador para esta chave se ainda não existir
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [
            'attempts' => 0,
            'last_attempt' => $now,
            'locked_until' => 0
        ];
    }
    
    $limiter = &$_SESSION['rate_limits'][$key];
    
    // Verificar se está bloqueado
    if ($limiter['locked_until'] > $now) {
        return true; // Limite excedido, ainda bloqueado
    }
    
    // Verificar se o tempo expirou desde a última tentativa
    if (($now - $limiter['last_attempt']) > $timeWindow) {
        // Redefinir contador após o tempo limite
        $limiter['attempts'] = 1;
        $limiter['last_attempt'] = $now;
        return false;
    }
    
    // Incrementar contador de tentativas
    $limiter['attempts']++;
    $limiter['last_attempt'] = $now;
    
    // Verificar se excedeu tentativas máximas
    if ($limiter['attempts'] > $maxAttempts) {
        // Bloquear por um período
        $limiter['locked_until'] = $now + $timeWindow;
        return true; // Limite excedido
    }
    
    return false; // Limite não excedido
}

/**
 * Reinicia o rate limiter para uma chave específica
 * 
 * @param string $key Chave do rate limiter
 * @return void
 */
function resetRateLimiter($key) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['rate_limits'][$key])) {
        unset($_SESSION['rate_limits'][$key]);
    }
}

/**
 * Valida força da senha
 * 
 * @param string $password Senha a ser validada
 * @param int $minLength Comprimento mínimo
 * @return bool Verdadeiro se a senha for forte o suficiente
 */
function validatePasswordStrength($password, $minLength = 8) {
    // Verificar comprimento mínimo
    if (strlen($password) < $minLength) {
        return false;
    }
    
    // Verificar se contém pelo menos uma letra minúscula
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    // Verificar se contém pelo menos uma letra maiúscula
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    // Verificar se contém pelo menos um número
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    // Verificar se contém pelo menos um caractere especial
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        return false;
    }
    
    return true;
}

/**
 * Valida um nome de usuário
 * 
 * @param string $username Nome de usuário
 * @param int $minLength Comprimento mínimo
 * @param int $maxLength Comprimento máximo
 * @return bool Verdadeiro se o nome de usuário for válido
 */
function validateUsername($username, $minLength = 3, $maxLength = 20) {
    $length = strlen($username);
    
    // Verificar comprimento
    if ($length < $minLength || $length > $maxLength) {
        return false;
    }
    
    // Permitir apenas letras, números e underscores
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return false;
    }
    
    return true;
}

/**
 * Valida um endereço de e-mail
 * 
 * @param string $email E-mail
 * @return bool Verdadeiro se o e-mail for válido
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Gera um ID único
 * 
 * @return string ID único
 */
function generateUniqueId() {
    return uniqid('', true);
}

/**
 * Aplica uma camada adicional de segurança ao valor antes de armazenar
 * 
 * @param string $value Valor a ser protegido
 * @param string $key Chave de criptografia
 * @return string Valor protegido
 */
function secureValue($value, $key = null) {
    $key = $key ?? getSecureKey();
    $value = json_encode($value);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
    $encrypted = openssl_encrypt($value, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

/**
 * Recupera um valor protegido pela função secureValue
 * 
 * @param string $securedValue Valor protegido
 * @param string $key Chave de criptografia
 * @return mixed Valor original ou null em caso de falha
 */
function getSecuredValue($securedValue, $key = null) {
    try {
        $key = $key ?? getSecureKey();
        $parts = explode('::', base64_decode($securedValue), 2);
        
        if (count($parts) !== 2) {
            return null;
        }
        
        list($encrypted, $iv) = $parts;
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
        
        if ($decrypted === false) {
            return null;
        }
        
        return json_decode($decrypted, true);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Obtém uma chave segura para criptografia
 * 
 * @return string Chave segura
 */
function getSecureKey() {
    // Em um ambiente real, esta chave deve ser armazenada em variáveis de ambiente
    // ou em um arquivo de configuração seguro e não deve ser incluída diretamente no código
    $secure_key = getenv('SECURE_KEY');
    
    if (!$secure_key) {
        // Fallback para um valor de aplicação se não estiver definido
        $secure_key = 'AppSecureKey123!@#';
    }
    
    return hash('sha256', $secure_key);
}

/**
 * Protege contra ataques de timing ao comparar strings
 * 
 * @param string $known_string String conhecida
 * @param string $user_string String fornecida pelo usuário
 * @return bool Verdadeiro se as strings forem iguais
 */
function secureCompare($known_string, $user_string) {
    return hash_equals($known_string, $user_string);
}

/**
 * Registra uma tentativa de ação suspeita no log
 * 
 * @param string $action Ação que foi tentada
 * @param string $ip Endereço IP do usuário
 * @param string $userAgent User agent do navegador
 * @param array $additionalData Dados adicionais
 * @return void
 */
function logSecurityEvent($action, $ip = null, $userAgent = null, $additionalData = []) {
    $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => sanitizeInput($action),
        'ip' => sanitizeInput($ip),
        'user_agent' => sanitizeInput($userAgent),
        'user_id' => isAuthenticated() ? getCurrentUserId() : null
    ];
    
    if (!empty($additionalData)) {
        foreach ($additionalData as $key => $value) {
            $logData[$key] = sanitizeInput($value);
        }
    }
    
    // Em um ambiente real, você pode salvar no banco de dados
    // ou em um arquivo de log, dependendo da configuração
    
    // Exemplo: salvar no banco de dados
    if (function_exists('dbInsert')) {
        dbInsert('security_logs', $logData);
    }
    
    // Ou: salvar em um arquivo de log
    $logMessage = json_encode($logData);
    error_log("SECURITY_EVENT: " . $logMessage);
}

/**
 * Verifica se o endereço IP atual está na lista negra
 * 
 * @param string $ip IP a ser verificado (opcional, usa IP atual por padrão)
 * @return bool Verdadeiro se o IP estiver na lista negra
 */
function isIPBlacklisted($ip = null) {
    $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Em um ambiente real, verificar em uma tabela do banco de dados
    // ou em um arquivo de configuração
    
    // Exemplo: verificar no banco de dados
    if (function_exists('dbQuerySingle')) {
        $result = dbQuerySingle("SELECT * FROM ip_blacklist WHERE ip = ?", [$ip]);
        return $result !== null;
    }
    
    return false;
}

/**
 * Detecta se a origem da requisição pode ser um bot malicioso
 * 
 * @return bool Verdadeiro se for detectado como bot
 */
function isMaliciousBot() {
    // Verificar se há cabeçalhos comuns
    if (!isset($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER['HTTP_USER_AGENT'])) {
        return true;
    }
    
    // Verificar se a referência existe em requisições não-GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' && 
        (!isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER']))) {
        return true;
    }
    
    // Lista de user agents conhecidos como maliciosos
    $malicious_bots = [
        'wget',
        'curl',
        'python-requests',
        'scrapy',
        'phantomjs',
        'apachebench',
        'java/',
        'zgrab',
        'zgrab/0',
        'bot'
    ];
    
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    
    foreach ($malicious_bots as $bot) {
        if (strpos($user_agent, $bot) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Remove valores de URL, mitigando ataques de injeção de header
 * 
 * @param string $url URL a ser limpa
 * @return string URL limpa
 */
function cleanUrl($url) {
    // Remover caracteres de nova linha
    $url = str_replace(["\r", "\n", "\t"], '', $url);
    
    // Verificar protocolo (aceitar apenas http e https)
    $parts = parse_url($url);
    
    if (!isset($parts['scheme']) || !in_array($parts['scheme'], ['http', 'https'])) {
        // Padrão para http se o protocolo for inválido
        return false;
    }
    
    return $url;
}

/**
 * Define cabeçalhos de segurança para a resposta HTTP
 * 
 * @return void
 */
function setSecurityHeaders() {
    // Proteção contra clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Habilitar proteção XSS no navegador
    header('X-XSS-Protection: 1; mode=block');
    
    // Impedir detecção MIME type pelo navegador
    header('X-Content-Type-Options: nosniff');
    
    // Política de segurança de conteúdo (CSP)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';");
    
    // Política de referência
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Política de recursos de permissão
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    
    // Transporte seguro estrito
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
?> 