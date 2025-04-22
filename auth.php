<?php
/**
 * Sistema de Autenticação
 * 
 * Este arquivo contém funções para gerenciar a autenticação de usuários,
 * incluindo login, verificação de autenticação e logout.
 * 
 * @version 1.0.1
 */

// Incluir a configuração do banco de dados
require_once 'db_config.php';

// Definir modo de debug (remover em produção)
define('DEBUG_MODE', true);

// Iniciar sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Função para testar a conexão com o banco de dados
 * Pode ser chamada para verificar se a conexão está funcionando
 */
function testDatabaseConnection() {
    try {
        $connection = getConnection();
        $stmt = $connection->query("SELECT 1");
        
        if ($stmt) {
            error_log("Conexão com o banco de dados: OK");
            return true;
        } else {
            error_log("Erro na conexão com o banco de dados: Não foi possível executar a consulta de teste");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Erro na conexão com o banco de dados: " . $e->getMessage());
        return false;
    }
}

// Testar conexão com o banco para diagnóstico quando DEBUG_MODE estiver ativo
if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    testDatabaseConnection();
}

/**
 * Verifica se o usuário está autenticado
 * 
 * @return bool Retorna true se o usuário estiver autenticado, false caso contrário
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Autentica um usuário com base no nome de usuário/email e senha
 * 
 * @param string $username Nome de usuário ou email
 * @param string $password Senha do usuário
 * @param bool $remember Flag para ativar o recurso "lembrar-me"
 * @return array Status da operação ('success' ou 'error') e mensagem
 */
function loginUser($username, $password, $remember = false) {
    // Log para debug
    error_log("Tentando login para usuário: $username");
    
    // Buscar dados do usuário do banco de dados
    $userData = getUserDataFromDB($username);
    
    // Verificar se o usuário existe
    if (!$userData) {
        error_log("Usuário não encontrado: $username");
        return [
            'status' => 'error',
            'message' => 'Usuário não encontrado'
        ];
    }
    
    error_log("Usuário encontrado, verificando senha");
    
    // Verificar a senha
    if (!password_verify($password, $userData['password'])) {
        error_log("Senha incorreta para usuário: $username");
        return [
            'status' => 'error',
            'message' => 'Senha incorreta'
        ];
    }
    
    error_log("Login bem-sucedido para usuário: $username (ID: {$userData['id']})");
    
    // Iniciar sessão do usuário
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['user_data'] = [
        'id' => $userData['id'],
        'username' => $userData['username'],
        'email' => $userData['email'],
        'is_admin' => $userData['is_admin'],
        'last_login' => time()
    ];
    // Salvar user_role apenas se for admin
    if ($userData['is_admin']) {
        $_SESSION['user_role'] = 'admin';
    } else {
        unset($_SESSION['user_role']);
    }
    
    // Atualizar o último login
    updateLastLogin($userData['id']);
    
    // Configurar "lembrar de mim" se solicitado
    if ($remember) {
        $token = generateRememberToken();
        setRememberCookie($userData['id'], $token);
    }
    
    error_log(print_r($userData, true));
    
    error_log('Sessão após login: ' . print_r($_SESSION, true));
    
    return [
        'status' => 'success',
        'message' => 'Login realizado com sucesso',
        'user' => [
            'id' => $userData['id'],
            'username' => $userData['username'],
            'isAdmin' => $userData['is_admin']
        ]
    ];
}

/**
 * Recupera dados do usuário do banco de dados
 * 
 * @param string $username Nome de usuário ou email
 * @return array|null Dados do usuário ou null se não encontrado
 */
function getUserDataFromDB($username) {
    try {
        // Buscar usuário pelo nome de usuário ou email
        $sql = "SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?";
        $user = dbQuerySingle($sql, [$username, $username]);
        
        if ($user) {
            // Converter role para flag is_admin
            $user['is_admin'] = ($user['role'] === 'admin');
            
            // Adicionar last_login (pode ser null)
            if (!isset($user['last_login'])) {
                $user['last_login'] = null;
            }
            
            return $user;
        }
        
        return null;
    } catch (PDOException $e) {
        error_log('Erro ao buscar usuário: ' . $e->getMessage());
        return null;
    }
}

/**
 * Atualiza a hora do último login do usuário
 * 
 * @param int $userId ID do usuário
 * @return bool Sucesso da operação
 */
function updateLastLogin($userId) {
    try {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $result = dbExecute($sql, [$userId]);
        return $result > 0;
    } catch (PDOException $e) {
        error_log('Erro ao atualizar último login: ' . $e->getMessage());
        return false;
    }
}

/**
 * Gera um token aleatório para o recurso "lembrar-me"
 * 
 * @return string Token aleatório
 */
function generateRememberToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Define o cookie "lembrar-me" para login automático
 * 
 * @param int $userId ID do usuário
 * @param string $token Token de autenticação
 * @return void
 */
function setRememberCookie($userId, $token) {
    // Cookie válido por 30 dias
    $expiry = time() + (30 * 24 * 60 * 60);
    
    // Configurar cookie seguro
    setcookie('remember_token', $token, [
        'expires' => $expiry,
        'path' => '/',
        'secure' => true,    // Ativar em produção com HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    setcookie('remember_user', $userId, [
        'expires' => $expiry,
        'path' => '/',
        'secure' => true,    // Ativar em produção com HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

/**
 * Verifica se o usuário tem um cookie "lembrar-me" válido e realiza login automático
 * 
 * @return bool Sucesso da operação
 */
function checkRememberMe() {
    if (isAuthenticated()) {
        return false; // Usuário já está autenticado
    }
    
    if (!isset($_COOKIE['remember_token']) || !isset($_COOKIE['remember_user'])) {
        return false; // Cookies não encontrados
    }
    
    $token = $_COOKIE['remember_token'];
    $userId = $_COOKIE['remember_user'];
    
    // Em um sistema real, verificaria o token no banco de dados
    // Para esta simulação, assumimos que o token é válido
    
    // Obter dados do usuário
    $userData = getUserById($userId);
    
    if (!$userData) {
        // Cookie inválido, remover
        deleteCookies();
        return false;
    }
    
    // Login automático
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['user_data'] = [
        'id' => $userData['id'],
        'username' => $userData['username'],
        'email' => $userData['email'],
        'is_admin' => $userData['is_admin'],
        'last_login' => time()
    ];
    
    // Atualizar o cookie com um novo token
    $newToken = generateRememberToken();
    setRememberCookie($userId, $newToken);
    
    return true;
}

/**
 * Obtém dados do usuário por ID
 * 
 * @param int $userId ID do usuário
 * @return array|null Dados do usuário ou null se não encontrado
 */
function getUserById($userId) {
    try {
        $sql = "SELECT id, username, email, role FROM users WHERE id = ?";
        $user = dbQuerySingle($sql, [$userId]);
        
        if ($user) {
            // Converter role para flag is_admin
            $user['is_admin'] = ($user['role'] === 'admin');
            return $user;
        }
        
        return null;
    } catch (PDOException $e) {
        error_log('Erro ao buscar usuário por ID: ' . $e->getMessage());
        return null;
    }
}

/**
 * Registra um novo usuário no sistema
 * 
 * @param string $username Nome de usuário
 * @param string $email Email do usuário
 * @param string $password Senha do usuário
 * @return array Array com status (success/error) e mensagem
 */
function registerUser($username, $email, $password) {
    // Validar campos obrigatórios
    if (empty($username) || empty($email) || empty($password)) {
        return [
            'status' => 'error',
            'message' => 'Todos os campos são obrigatórios'
        ];
    }
    
    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'status' => 'error',
            'message' => 'Formato de email inválido'
        ];
    }
    
    // Validar senha (mínimo 8 caracteres)
    if (strlen($password) < 8) {
        return [
            'status' => 'error',
            'message' => 'A senha deve ter no mínimo 8 caracteres'
        ];
    }
    
    // Verificar se o nome de usuário já existe
    $existingUser = getUserByUsername($username);
    if ($existingUser) {
        return [
            'status' => 'error',
            'message' => 'Este nome de usuário já está em uso'
        ];
    }
    
    // Verificar se o email já existe
    $existingEmail = getUserByEmail($email);
    if ($existingEmail) {
        return [
            'status' => 'error',
            'message' => 'Este email já está em uso'
        ];
    }
    
    // Simular hash da senha
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        // Inserir usuário no banco de dados
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $userId = dbInsert($sql, [$username, $email, $hashedPassword, 'user']);
        
        if (!$userId) {
            return [
                'status' => 'error',
                'message' => 'Erro ao registrar usuário. Tente novamente.'
            ];
        }
        
        // Iniciar sessão para o novo usuário
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_data'] = [
            'id' => $userId,
            'username' => $username,
            'email' => $email,
            'is_admin' => false,
            'last_login' => time()
        ];
        
        return [
            'status' => 'success',
            'message' => 'Registro realizado com sucesso',
            'user' => [
                'id' => $userId,
                'username' => $username,
                'isAdmin' => false
            ]
        ];
    } catch (PDOException $e) {
        error_log('Erro ao registrar usuário: ' . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'Erro ao registrar usuário. Tente novamente mais tarde.'
        ];
    }
}

/**
 * Verifica se um nome de usuário já existe
 * 
 * @param string $username Nome de usuário a verificar
 * @return mixed Dados do usuário se existir, false caso contrário
 */
function getUserByUsername($username) {
    try {
        $sql = "SELECT * FROM users WHERE username = ?";
        return dbQuerySingle($sql, [$username]);
    } catch (PDOException $e) {
        error_log('Erro ao verificar username: ' . $e->getMessage());
        return false;
    }
}

/**
 * Verifica se um email já existe
 * 
 * @param string $email Email a verificar
 * @return mixed Dados do usuário se existir, false caso contrário
 */
function getUserByEmail($email) {
    try {
        $sql = "SELECT * FROM users WHERE email = ?";
        return dbQuerySingle($sql, [$email]);
    } catch (PDOException $e) {
        error_log('Erro ao verificar email: ' . $e->getMessage());
        return false;
    }
}

/**
 * Encerra a sessão do usuário e remove os cookies
 * 
 * @return array Status da operação
 */
function logoutUser() {
    // Remover dados da sessão
    unset($_SESSION['user_id']);
    unset($_SESSION['user_data']);
    
    // Destruir a sessão
    session_destroy();
    
    // Remover cookies
    deleteCookies();
    
    return [
        'status' => 'success',
        'message' => 'Logout realizado com sucesso'
    ];
}

/**
 * Remove os cookies de autenticação
 * 
 * @return void
 */
function deleteCookies() {
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    
    if (isset($_COOKIE['remember_user'])) {
        setcookie('remember_user', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
}

/**
 * Verifica se o usuário atual é um administrador
 * 
 * @return bool true se for admin, false caso contrário
 */
function isAdmin() {
    if (!isAuthenticated()) {
        return false;
    }
    
    return isset($_SESSION['user_data']['is_admin']) && $_SESSION['user_data']['is_admin'] === true;
}

/**
 * Requer que o usuário seja administrador para acessar um recurso
 * 
 * @param bool $redirect Se true, redireciona para a página inicial. Caso contrário, retorna booleano.
 * @return bool|void True se for admin, false ou redirecionamento caso contrário
 */
function requireAdmin($redirect = true) {
    if (!isAdmin()) {
        if ($redirect) {
            // Redirecionar para a página inicial com mensagem de erro
            header('Location: index.php?error=unauthorized');
            exit;
        }
        return false;
    }
    
    return true;
}

/**
 * Requer que o usuário esteja autenticado para acessar um recurso
 * 
 * @param bool $redirect Se true, redireciona para a página de login. Caso contrário, retorna booleano.
 * @return bool|void True se estiver autenticado, false ou redirecionamento caso contrário
 */
function requireAuth($redirect = true) {
    if (!isAuthenticated()) {
        if ($redirect) {
            // Redirecionar para a página de login
            header('Location: login.php?error=auth_required');
            exit;
        }
        return false;
    }
    
    return true;
}

// Processar solicitações AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || 
    isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false ||
    isset($_POST['action'])) {
    // Resposta será sempre em JSON
    header('Content-Type: application/json');
    
    // Verificar se os dados estão sendo enviados como JSON
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    if (strpos($contentType, 'application/json') !== false) {
        // Ler o conteúdo JSON da entrada
        $jsonData = file_get_contents('php://input');
        if (!empty($jsonData)) {
            $postData = json_decode($jsonData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $_POST = $postData;
            }
        }
    }
    
    // Verificar qual ação está sendo requisitada
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Ação: Login
        if ($action == 'login') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $remember = isset($_POST['remember']) && $_POST['remember'] == 'on';
            
            $result = loginUser($username, $password, $remember);
            
            echo json_encode($result);
            exit;
        }
        
        // Ação: Registro
        if ($action == 'register') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $termsAccepted = isset($_POST['terms']) && $_POST['terms'] == 'on';
            
            // Log para debug
            error_log("Dados recebidos: username=$username, email=$email, password=".substr($password, 0, 3)."..., terms=".($termsAccepted ? 'aceito' : 'não aceito'));
            
            // Verificar se os termos foram aceitos
            if (!$termsAccepted) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Você precisa aceitar os Termos de Uso.'
                ]);
                exit;
            }
            
            $result = registerUser($username, $email, $password);
            
            echo json_encode($result);
            exit;
        }
        
        // Ação: Logout
        if ($action == 'logout') {
            logoutUser();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Logout realizado com sucesso.'
            ]);
            exit;
        }
    }
    
    // Se chegou aqui, é porque a ação solicitada não foi reconhecida
    echo json_encode([
        'status' => 'error',
        'message' => 'Ação não reconhecida'
    ]);
    exit;
}
?> 