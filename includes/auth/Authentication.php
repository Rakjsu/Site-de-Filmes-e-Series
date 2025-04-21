<?php
/**
 * Sistema de Autenticação
 * 
 * Este arquivo contém funções para gerenciar autenticação, registro e sessões de usuários.
 * 
 * @version 1.0.0
 */

// Incluir conexão com o banco de dados
require_once __DIR__ . '/../core/Database.php';

/**
 * Testa a conexão com o banco de dados
 * 
 * @return bool Verdadeiro se a conexão foi bem-sucedida
 */
function testDatabaseConnection() {
    try {
        $connection = getConnection();
        error_log("Teste de conexão com o banco de dados: Sucesso");
        return true;
    } catch (Exception $e) {
        error_log("Teste de conexão com o banco de dados: Falha - " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica se o usuário está autenticado
 * 
 * @return bool Verdadeiro se o usuário estiver autenticado
 */
function isAuthenticated() {
    // Iniciar a sessão se ainda não estiver ativa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar se existe ID de usuário na sessão
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;
    }
    
    // Verificar cookies de "lembrar-me"
    if (checkRememberMe()) {
        return true;
    }
    
    return false;
}

/**
 * Autentica um usuário
 * 
 * @param string $username Nome de usuário ou email
 * @param string $password Senha do usuário
 * @param bool $remember Ativar funcionalidade "lembrar-me"
 * @return array Dados do usuário e status da autenticação
 */
function loginUser($username, $password, $remember = false) {
    // Iniciar a sessão se ainda não estiver ativa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Validar campos obrigatórios
    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => 'Todos os campos são obrigatórios.'];
    }
    
    try {
        // Obter dados do usuário do banco de dados
        $userData = getUserDataFromDB($username);
        
        // Verificar se o usuário existe
        if (!$userData) {
            error_log("Login falhou: Usuário não encontrado - $username");
            return ['success' => false, 'message' => 'Credenciais inválidas.'];
        }
        
        // Verificar se a senha está correta
        if (!password_verify($password, $userData['password'])) {
            error_log("Login falhou: Senha incorreta para o usuário - $username");
            return ['success' => false, 'message' => 'Credenciais inválidas.'];
        }
        
        // Verificar se a conta está ativa
        if ($userData['status'] !== 'active') {
            error_log("Login falhou: Conta inativa para o usuário - $username");
            return ['success' => false, 'message' => 'Esta conta está desativada.'];
        }
        
        // Atualizar último login
        updateLastLogin($userData['id']);
        
        // Configurar variáveis de sessão
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['role'] = $userData['role']; 
        $_SESSION['last_activity'] = time();
        
        // Configurar cookies "lembrar-me" se solicitado
        if ($remember) {
            $token = generateRememberToken();
            setRememberCookie($userData['id'], $token);
        }
        
        error_log("Login bem-sucedido: $username");
        
        // Remover senha antes de retornar
        unset($userData['password']);
        
        return [
            'success' => true, 
            'message' => 'Login realizado com sucesso.', 
            'user' => $userData
        ];
        
    } catch (Exception $e) {
        error_log("Erro durante login: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro ao processar o login. Tente novamente.'];
    }
}

/**
 * Obtém os dados do usuário do banco de dados
 * 
 * @param string $username Nome de usuário ou email
 * @return array|null Dados do usuário ou null se não encontrado
 */
function getUserDataFromDB($username) {
    try {
        // Consulta SQL para encontrar o usuário pelo nome de usuário ou email
        $sql = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
        $user = dbQuerySingle($sql, [$username, $username]);
        
        return $user;
    } catch (Exception $e) {
        error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
        return null;
    }
}

/**
 * Atualiza o timestamp de último login do usuário
 * 
 * @param int $userId ID do usuário
 * @return bool Sucesso da operação
 */
function updateLastLogin($userId) {
    try {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        dbExecute($sql, [$userId]);
        return true;
    } catch (Exception $e) {
        error_log("Erro ao atualizar último login: " . $e->getMessage());
        return false;
    }
}

/**
 * Gera um token aleatório para "lembrar-me"
 * 
 * @param int $length Tamanho do token
 * @return string Token gerado
 */
function generateRememberToken($length = 64) {
    try {
        $token = bin2hex(random_bytes($length / 2));
        return $token;
    } catch (Exception $e) {
        error_log("Erro ao gerar token de autenticação: " . $e->getMessage());
        // Fallback para método menos seguro
        return md5(uniqid(mt_rand(), true));
    }
}

/**
 * Define cookies para "lembrar-me"
 * 
 * @param int $userId ID do usuário
 * @param string $token Token de autenticação
 * @return bool Sucesso da operação
 */
function setRememberCookie($userId, $token) {
    try {
        // Definir data de expiração (30 dias)
        $expiry = time() + (30 * 24 * 60 * 60);
        
        // Armazenar token hash no banco de dados
        $tokenHash = password_hash($token, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO user_tokens (user_id, token, expires_at, created_at) 
                VALUES (?, ?, FROM_UNIXTIME(?), NOW()) 
                ON DUPLICATE KEY UPDATE token = ?, expires_at = FROM_UNIXTIME(?)";
                
        dbExecute($sql, [$userId, $tokenHash, $expiry, $tokenHash, $expiry]);
        
        // Definir cookies
        setcookie('remember_user', $userId, $expiry, '/', '', false, true);
        setcookie('remember_token', $token, $expiry, '/', '', false, true);
        
        return true;
    } catch (Exception $e) {
        error_log("Erro ao definir cookie de autenticação: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica cookies "lembrar-me" para autenticação automática
 * 
 * @return bool Verdadeiro se o usuário foi autenticado pelos cookies
 */
function checkRememberMe() {
    // Verificar se os cookies existem
    if (!isset($_COOKIE['remember_user']) || !isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    try {
        $userId = $_COOKIE['remember_user'];
        $token = $_COOKIE['remember_token'];
        
        // Obter token armazenado
        $sql = "SELECT * FROM user_tokens 
                WHERE user_id = ? 
                AND expires_at > NOW() 
                ORDER BY created_at DESC LIMIT 1";
                
        $tokenData = dbQuerySingle($sql, [$userId]);
        
        if (!$tokenData) {
            deleteCookies();
            return false;
        }
        
        // Verificar se o token corresponde
        if (!password_verify($token, $tokenData['token'])) {
            deleteCookies();
            return false;
        }
        
        // Obter dados do usuário
        $sql = "SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1";
        $userData = dbQuerySingle($sql, [$userId]);
        
        if (!$userData) {
            deleteCookies();
            return false;
        }
        
        // Iniciar a sessão se ainda não estiver ativa
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Autenticar o usuário
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['last_activity'] = time();
        
        // Atualizar último login
        updateLastLogin($userData['id']);
        
        // Renovar token (opcional)
        $newToken = generateRememberToken();
        setRememberCookie($userData['id'], $newToken);
        
        return true;
    } catch (Exception $e) {
        error_log("Erro ao verificar autenticação por cookie: " . $e->getMessage());
        return false;
    }
}

/**
 * Registra um novo usuário
 * 
 * @param string $username Nome de usuário
 * @param string $email Email do usuário
 * @param string $password Senha do usuário
 * @return array Resultado do registro
 */
function registerUser($username, $email, $password) {
    // Validar campos
    if (empty($username) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Todos os campos são obrigatórios.'];
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email inválido.'];
    }
    
    // Validar tamanho do nome de usuário
    if (strlen($username) < 3 || strlen($username) > 30) {
        return ['success' => false, 'message' => 'O nome de usuário deve ter entre 3 e 30 caracteres.'];
    }
    
    // Validar formato do nome de usuário (apenas letras, números e underscore)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['success' => false, 'message' => 'O nome de usuário deve conter apenas letras, números e underscore.'];
    }
    
    // Validar força da senha
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'A senha deve ter pelo menos 8 caracteres.'];
    }
    
    try {
        // Verificar se o nome de usuário já existe
        $sql = "SELECT id FROM users WHERE username = ? LIMIT 1";
        $existingUser = dbQuerySingle($sql, [$username]);
        
        if ($existingUser) {
            return ['success' => false, 'message' => 'Este nome de usuário já está em uso.'];
        }
        
        // Verificar se o email já existe
        $sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $existingEmail = dbQuerySingle($sql, [$email]);
        
        if ($existingEmail) {
            return ['success' => false, 'message' => 'Este email já está cadastrado.'];
        }
        
        // Hash da senha
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Inserir novo usuário
        $sql = "INSERT INTO users (username, email, password, role, status, created_at) 
                VALUES (?, ?, ?, 'user', 'active', NOW())";
        
        $userId = dbInsert($sql, [$username, $email, $passwordHash]);
        
        if (!$userId) {
            throw new Exception("Falha ao inserir novo usuário.");
        }
        
        error_log("Novo usuário registrado: $username (ID: $userId)");
        
        return [
            'success' => true,
            'message' => 'Cadastro realizado com sucesso!',
            'user_id' => $userId
        ];
        
    } catch (Exception $e) {
        error_log("Erro ao registrar usuário: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro ao processar o cadastro. Tente novamente.'];
    }
}

/**
 * Encerra a sessão do usuário
 * 
 * @return bool Sucesso da operação
 */
function logoutUser() {
    // Iniciar a sessão se ainda não estiver ativa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Remover cookies "lembrar-me"
    deleteCookies();
    
    // Registrar logout em log
    if (isset($_SESSION['username'])) {
        error_log("Logout de usuário: " . $_SESSION['username']);
    }
    
    // Limpar e destruir a sessão
    $_SESSION = [];
    
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
    
    session_destroy();
    
    return true;
}

/**
 * Remove cookies de autenticação
 * 
 * @return void
 */
function deleteCookies() {
    if (isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
        $userId = $_COOKIE['remember_user'];
        
        // Remover token do banco de dados
        try {
            $sql = "DELETE FROM user_tokens WHERE user_id = ?";
            dbExecute($sql, [$userId]);
        } catch (Exception $e) {
            error_log("Erro ao remover token do banco: " . $e->getMessage());
        }
        
        // Remover cookies
        setcookie('remember_user', '', time() - 3600, '/', '', false, true);
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
}

/**
 * Verifica se o usuário atual é um administrador
 * 
 * @return bool Verdadeiro se o usuário for administrador
 */
function isAdmin() {
    // Iniciar a sessão se ainda não estiver ativa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar se o usuário está autenticado e é um administrador
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Requer que o usuário seja um administrador para acessar o recurso
 * 
 * @param string $redirect URL para redirecionamento se não for administrador
 * @return bool Verdadeiro se o usuário for administrador
 */
function requireAdmin($redirect = 'index.php') {
    if (!isAuthenticated() || !isAdmin()) {
        // Redirecionar se não for admin
        header("Location: $redirect");
        exit;
    }
    
    return true;
}

/**
 * Requer que o usuário esteja autenticado para acessar o recurso
 * 
 * @param string $redirect URL para redirecionamento se não estiver autenticado
 * @return bool Verdadeiro se o usuário estiver autenticado
 */
function requireAuth($redirect = 'login.php') {
    if (!isAuthenticated()) {
        // Redirecionar se não estiver autenticado
        header("Location: $redirect");
        exit;
    }
    
    return true;
}

// Processamento de requisições AJAX para login e logout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Iniciar a sessão se ainda não estiver ativa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar se é uma requisição AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // Processar login
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
        
        $result = loginUser($username, $password, $remember);
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } else {
            if ($result['success']) {
                header('Location: index.php');
            } else {
                $_SESSION['login_error'] = $result['message'];
                header('Location: login.php');
            }
            exit;
        }
    }
    
    // Processar logout
    if (isset($_POST['action']) && $_POST['action'] === 'logout') {
        logoutUser();
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso.']);
            exit;
        } else {
            header('Location: login.php');
            exit;
        }
    }
    
    // Processar registro
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        $result = registerUser($username, $email, $password);
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } else {
            if ($result['success']) {
                $_SESSION['register_success'] = $result['message'];
                header('Location: login.php');
            } else {
                $_SESSION['register_error'] = $result['message'];
                header('Location: register.php');
            }
            exit;
        }
    }
}
?> 