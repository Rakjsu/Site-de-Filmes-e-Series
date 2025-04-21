<?php
/**
 * Sistema de Gerenciamento de Usuários
 * 
 * Este arquivo contém funções para gerenciar usuários no sistema.
 * 
 * @version 1.0.0
 */

// Incluir dependências
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Database.php';

/**
 * Verifica se um usuário está autenticado
 * 
 * @return bool Verdadeiro se o usuário estiver autenticado
 */
function isAuthenticated() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtém o ID do usuário atual
 * 
 * @return int|null ID do usuário ou null se não estiver autenticado
 */
function getCurrentUserId() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return $_SESSION['user_id'];
}

/**
 * Obtém dados do usuário atual
 * 
 * @param array $fields Campos específicos para retornar (opcional)
 * @return array|null Dados do usuário ou null se não estiver autenticado
 */
function getCurrentUser($fields = []) {
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return null;
    }
    
    return getUserById($userId, $fields);
}

/**
 * Obtém dados de um usuário pelo ID
 * 
 * @param int $userId ID do usuário
 * @param array $fields Campos específicos para retornar (opcional)
 * @return array|null Dados do usuário ou null se não encontrado
 */
function getUserById($userId, $fields = []) {
    if (empty($userId)) {
        return null;
    }
    
    $selectFields = '*';
    
    if (!empty($fields)) {
        // Remover campos sensíveis que não devem ser incluídos
        $safeFields = array_diff($fields, ['password']);
        $selectFields = implode(', ', $safeFields);
    }
    
    $sql = "SELECT $selectFields FROM users WHERE id = ?";
    
    return dbQuerySingle($sql, [$userId]);
}

/**
 * Obtém dados de um usuário pelo nome de usuário
 * 
 * @param string $username Nome de usuário
 * @return array|null Dados do usuário ou null se não encontrado
 */
function getUserByUsername($username) {
    if (empty($username)) {
        return null;
    }
    
    $sql = "SELECT * FROM users WHERE username = ?";
    
    return dbQuerySingle($sql, [$username]);
}

/**
 * Obtém dados de um usuário pelo e-mail
 * 
 * @param string $email E-mail do usuário
 * @return array|null Dados do usuário ou null se não encontrado
 */
function getUserByEmail($email) {
    if (empty($email)) {
        return null;
    }
    
    $sql = "SELECT * FROM users WHERE email = ?";
    
    return dbQuerySingle($sql, [$email]);
}

/**
 * Verifica se um nome de usuário já existe
 * 
 * @param string $username Nome de usuário
 * @return bool Verdadeiro se o nome de usuário existir
 */
function usernameExists($username) {
    return getUserByUsername($username) !== null;
}

/**
 * Verifica se um e-mail já existe
 * 
 * @param string $email E-mail
 * @return bool Verdadeiro se o e-mail existir
 */
function emailExists($email) {
    return getUserByEmail($email) !== null;
}

/**
 * Autentica um usuário com nome de usuário/e-mail e senha
 * 
 * @param string $usernameOrEmail Nome de usuário ou e-mail
 * @param string $password Senha
 * @param bool $remember Lembrar usuário
 * @return array|bool Dados do usuário se autenticado ou false se falhar
 */
function loginUser($usernameOrEmail, $password, $remember = false) {
    // Sanitizar entrada
    $usernameOrEmail = sanitizeInput($usernameOrEmail);
    
    // Verificar se é um e-mail ou nome de usuário
    $isEmail = strpos($usernameOrEmail, '@') !== false;
    
    // Buscar usuário
    $user = $isEmail 
        ? getUserByEmail($usernameOrEmail) 
        : getUserByUsername($usernameOrEmail);
    
    // Verificar se o usuário existe
    if (!$user) {
        return false;
    }
    
    // Verificar se a senha está correta
    if (!verifyPassword($password, $user['password'])) {
        // Registrar tentativa de login falha para rate limiting
        rateLimiter('login_' . $usernameOrEmail, 5, 300);
        return false;
    }
    
    // Resetar rate limiter após login bem-sucedido
    resetRateLimiter('login_' . $usernameOrEmail);
    
    // Iniciar sessão se ainda não estiver ativa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Atualizar última data de login
    updateLastLogin($user['id']);
    
    // Armazenar dados do usuário na sessão
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    
    // Gerar e armazenar token para proteção CSRF
    $_SESSION['csrf_token'] = generateCSRFToken();
    
    // Configurar cookie "lembrar-me" se solicitado
    if ($remember) {
        // Gerar token seguro
        $token = generateSecureToken(64);
        $expiry = time() + (30 * 24 * 60 * 60); // 30 dias
        
        // Salvar token no banco de dados
        saveRememberToken($user['id'], $token, $expiry);
        
        // Definir cookie
        setcookie('remember_token', $token, $expiry, '/', '', true, true);
        setcookie('remember_user', $user['id'], $expiry, '/', '', true, true);
    }
    
    // Remover senha dos dados retornados
    unset($user['password']);
    
    return $user;
}

/**
 * Finaliza a sessão do usuário atual
 * 
 * @param bool $destroyRememberMe Remover cookie "lembrar-me"
 * @return bool Verdadeiro se bem-sucedido
 */
function logoutUser($destroyRememberMe = true) {
    // Iniciar sessão se ainda não estiver ativa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Remover token "lembrar-me" do banco de dados se existir
    if ($destroyRememberMe && isset($_COOKIE['remember_token']) && isset($_COOKIE['remember_user'])) {
        deleteRememberToken($_COOKIE['remember_user'], $_COOKIE['remember_token']);
    }
    
    // Remover cookies
    if ($destroyRememberMe) {
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        setcookie('remember_user', '', time() - 3600, '/', '', true, true);
    }
    
    // Destruir sessão
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 3600,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    session_destroy();
    
    return true;
}

/**
 * Registra um novo usuário
 * 
 * @param string $username Nome de usuário
 * @param string $email E-mail
 * @param string $password Senha
 * @param array $additionalData Dados adicionais
 * @return int|bool ID do usuário criado ou false se falhar
 */
function registerUser($username, $email, $password, $additionalData = []) {
    // Validar dados
    if (!validateUsername($username)) {
        return false;
    }
    
    if (!validateEmail($email)) {
        return false;
    }
    
    if (!validatePasswordStrength($password)) {
        return false;
    }
    
    // Verificar se o nome de usuário ou e-mail já existem
    if (usernameExists($username)) {
        return false;
    }
    
    if (emailExists($email)) {
        return false;
    }
    
    // Gerar hash seguro da senha
    $passwordHash = secureHash($password);
    
    // Preparar dados para inserção
    $userData = [
        'username' => sanitizeInput($username),
        'email' => sanitizeInput($email),
        'password' => $passwordHash,
        'role' => $additionalData['role'] ?? 'user',
        'created_at' => date('Y-m-d H:i:s'),
        'status' => $additionalData['status'] ?? 'active'
    ];
    
    // Mesclar dados adicionais, se fornecidos
    if (!empty($additionalData)) {
        foreach ($additionalData as $key => $value) {
            if ($key !== 'password' && $key !== 'username' && $key !== 'email') {
                $userData[$key] = sanitizeInput($value);
            }
        }
    }
    
    // Inserir dados no banco
    $userId = dbInsert('users', $userData);
    
    if (!$userId) {
        return false;
    }
    
    return $userId;
}

/**
 * Atualiza os dados de um usuário
 * 
 * @param int $userId ID do usuário
 * @param array $data Dados a serem atualizados
 * @return bool Verdadeiro se bem-sucedido
 */
function updateUser($userId, $data) {
    if (empty($userId) || empty($data)) {
        return false;
    }
    
    // Verificar se o usuário existe
    $user = getUserById($userId);
    if (!$user) {
        return false;
    }
    
    // Sanitizar e validar dados
    $updateData = [];
    
    foreach ($data as $key => $value) {
        // Ignorar ID e campos restritos
        if (in_array($key, ['id', 'created_at'])) {
            continue;
        }
        
        // Tratar cada campo especificamente
        switch ($key) {
            case 'username':
                if (!validateUsername($value)) {
                    return false;
                }
                // Verificar se o novo nome de usuário já existe para outro usuário
                $existingUser = getUserByUsername($value);
                if ($existingUser && $existingUser['id'] != $userId) {
                    return false;
                }
                $updateData[$key] = sanitizeInput($value);
                break;
                
            case 'email':
                if (!validateEmail($value)) {
                    return false;
                }
                // Verificar se o novo e-mail já existe para outro usuário
                $existingUser = getUserByEmail($value);
                if ($existingUser && $existingUser['id'] != $userId) {
                    return false;
                }
                $updateData[$key] = sanitizeInput($value);
                break;
                
            case 'password':
                if (!validatePasswordStrength($value)) {
                    return false;
                }
                $updateData[$key] = secureHash($value);
                break;
                
            default:
                $updateData[$key] = sanitizeInput($value);
        }
    }
    
    // Adicionar data de atualização
    $updateData['updated_at'] = date('Y-m-d H:i:s');
    
    // Atualizar no banco
    return dbUpdate('users', $updateData, 'id = ?', [$userId]);
}

/**
 * Altera a senha de um usuário
 * 
 * @param int $userId ID do usuário
 * @param string $currentPassword Senha atual
 * @param string $newPassword Nova senha
 * @return bool Verdadeiro se bem-sucedido
 */
function changePassword($userId, $currentPassword, $newPassword) {
    // Verificar se o usuário existe
    $user = getUserById($userId);
    if (!$user) {
        return false;
    }
    
    // Verificar senha atual
    if (!verifyPassword($currentPassword, $user['password'])) {
        return false;
    }
    
    // Validar força da nova senha
    if (!validatePasswordStrength($newPassword)) {
        return false;
    }
    
    // Gerar hash da nova senha
    $passwordHash = secureHash($newPassword);
    
    // Atualizar senha
    return dbUpdate('users', [
        'password' => $passwordHash,
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$userId]);
}

/**
 * Atualiza a última data de login do usuário
 * 
 * @param int $userId ID do usuário
 * @return bool Verdadeiro se bem-sucedido
 */
function updateLastLogin($userId) {
    return dbUpdate('users', [
        'last_login' => date('Y-m-d H:i:s')
    ], 'id = ?', [$userId]);
}

/**
 * Gera um token para "lembrar-me" e o salva no banco de dados
 * 
 * @param int $userId ID do usuário
 * @param string $token Token a ser salvo
 * @param int $expiry Data de expiração (timestamp)
 * @return bool Verdadeiro se bem-sucedido
 */
function saveRememberToken($userId, $token, $expiry) {
    // Remover tokens antigos para este usuário
    dbDelete('user_tokens', 'user_id = ? AND type = ?', [$userId, 'remember']);
    
    // Inserir novo token
    return dbInsert('user_tokens', [
        'user_id' => $userId,
        'token' => $token,
        'type' => 'remember',
        'expires_at' => date('Y-m-d H:i:s', $expiry),
        'created_at' => date('Y-m-d H:i:s')
    ]) !== false;
}

/**
 * Remove um token "lembrar-me" do banco de dados
 * 
 * @param int $userId ID do usuário
 * @param string $token Token a ser removido
 * @return bool Verdadeiro se bem-sucedido
 */
function deleteRememberToken($userId, $token) {
    return dbDelete('user_tokens', 'user_id = ? AND token = ? AND type = ?', [
        $userId, $token, 'remember'
    ]);
}

/**
 * Verifica se um token "lembrar-me" é válido
 * 
 * @param int $userId ID do usuário
 * @param string $token Token a ser verificado
 * @return bool Verdadeiro se o token for válido
 */
function isValidRememberToken($userId, $token) {
    $sql = "SELECT * FROM user_tokens 
            WHERE user_id = ? AND token = ? AND type = ? AND expires_at > ?";
    
    $tokenData = dbQuerySingle($sql, [
        $userId, $token, 'remember', date('Y-m-d H:i:s')
    ]);
    
    return $tokenData !== null;
}

/**
 * Verifica cookies "lembrar-me" e loga o usuário automaticamente
 * 
 * @return bool Verdadeiro se o login automático for bem-sucedido
 */
function checkRememberMe() {
    // Verificar se o usuário já está autenticado
    if (isAuthenticated()) {
        return true;
    }
    
    // Verificar se os cookies necessários existem
    if (!isset($_COOKIE['remember_token']) || !isset($_COOKIE['remember_user'])) {
        return false;
    }
    
    $token = $_COOKIE['remember_token'];
    $userId = $_COOKIE['remember_user'];
    
    // Validar token
    if (!isValidRememberToken($userId, $token)) {
        // Remover cookies inválidos
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        setcookie('remember_user', '', time() - 3600, '/', '', true, true);
        return false;
    }
    
    // Obter dados do usuário
    $user = getUserById($userId);
    
    if (!$user) {
        return false;
    }
    
    // Iniciar sessão se ainda não estiver ativa
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Armazenar dados do usuário na sessão
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    
    // Gerar e armazenar token CSRF
    $_SESSION['csrf_token'] = generateCSRFToken();
    
    // Atualizar última data de login
    updateLastLogin($user['id']);
    
    return true;
}

/**
 * Verifica se o usuário atual é um administrador
 * 
 * @return bool Verdadeiro se o usuário for administrador
 */
function isAdmin() {
    if (!isAuthenticated()) {
        return false;
    }
    
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Requer que o usuário seja um administrador
 * 
 * @param string $redirectUrl URL para redirecionamento se não for admin
 * @return bool Verdadeiro se o usuário for um administrador
 */
function requireAdmin($redirectUrl = '') {
    if (isAdmin()) {
        return true;
    }
    
    if (!empty($redirectUrl)) {
        header("Location: $redirectUrl");
        exit;
    }
    
    return false;
}

/**
 * Requer que o usuário esteja autenticado
 * 
 * @param string $redirectUrl URL para redirecionamento se não autenticado
 * @return bool Verdadeiro se o usuário estiver autenticado
 */
function requireAuth($redirectUrl = '') {
    if (isAuthenticated()) {
        return true;
    }
    
    if (!empty($redirectUrl)) {
        header("Location: $redirectUrl");
        exit;
    }
    
    return false;
}

/**
 * Lista os usuários do sistema
 * 
 * @param array $filters Filtros para a consulta
 * @param int $limit Limite de resultados
 * @param int $offset Deslocamento para paginação
 * @return array Lista de usuários
 */
function listUsers($filters = [], $limit = 20, $offset = 0) {
    $whereConditions = [];
    $params = [];
    
    // Aplicar filtros
    if (!empty($filters)) {
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                switch ($field) {
                    case 'role':
                        $whereConditions[] = "role = ?";
                        $params[] = $value;
                        break;
                    case 'status':
                        $whereConditions[] = "status = ?";
                        $params[] = $value;
                        break;
                    case 'search':
                        $whereConditions[] = "(username LIKE ? OR email LIKE ? OR name LIKE ?)";
                        $searchTerm = "%" . $value . "%";
                        $params[] = $searchTerm;
                        $params[] = $searchTerm;
                        $params[] = $searchTerm;
                        break;
                }
            }
        }
    }
    
    $where = '';
    if (!empty($whereConditions)) {
        $where = "WHERE " . implode(' AND ', $whereConditions);
    }
    
    $sql = "SELECT id, username, email, role, status, created_at, last_login 
            FROM users 
            $where 
            ORDER BY id DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    return dbQueryAll($sql, $params);
}

/**
 * Conta o número total de usuários com filtros aplicados
 * 
 * @param array $filters Filtros para a consulta
 * @return int Número de usuários
 */
function countUsers($filters = []) {
    $whereConditions = [];
    $params = [];
    
    // Aplicar filtros
    if (!empty($filters)) {
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                switch ($field) {
                    case 'role':
                        $whereConditions[] = "role = ?";
                        $params[] = $value;
                        break;
                    case 'status':
                        $whereConditions[] = "status = ?";
                        $params[] = $value;
                        break;
                    case 'search':
                        $whereConditions[] = "(username LIKE ? OR email LIKE ? OR name LIKE ?)";
                        $searchTerm = "%" . $value . "%";
                        $params[] = $searchTerm;
                        $params[] = $searchTerm;
                        $params[] = $searchTerm;
                        break;
                }
            }
        }
    }
    
    $where = '';
    if (!empty($whereConditions)) {
        $where = "WHERE " . implode(' AND ', $whereConditions);
    }
    
    $sql = "SELECT COUNT(*) as count FROM users $where";
    
    $result = dbQuerySingle($sql, $params);
    
    return isset($result['count']) ? (int)$result['count'] : 0;
}

/**
 * Desativa um usuário
 * 
 * @param int $userId ID do usuário
 * @return bool Verdadeiro se bem-sucedido
 */
function deactivateUser($userId) {
    return dbUpdate('users', [
        'status' => 'inactive',
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$userId]);
}

/**
 * Ativa um usuário
 * 
 * @param int $userId ID do usuário
 * @return bool Verdadeiro se bem-sucedido
 */
function activateUser($userId) {
    return dbUpdate('users', [
        'status' => 'active',
        'updated_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$userId]);
}
?> 