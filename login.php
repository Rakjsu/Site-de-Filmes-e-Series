<?php
/**
 * Página de Login
 * 
 * Este arquivo contém o formulário de login e processamento
 * das credenciais para autenticação de usuários.
 * 
 * @version 1.0.0
 */

// Iniciar sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivo de autenticação
require_once 'auth.php';

// Verificar se o usuário já está autenticado
if (isAuthenticated()) {
    // Redirecionar para a página inicial
    header('Location: index.php');
    exit;
}

// Verificar se há um cookie "lembrar-me"
checkRememberMe();

// Variáveis para o formulário
$username = '';
$error = '';

// Processar o formulário se enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
    
    // Validar campos obrigatórios
    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos';
    } else {
        // Tentar fazer login
        $result = loginUser($username, $password, $remember);
        
        // Log para debug
        error_log("Tentativa de login: username=$username, resultado=" . $result['status']);
        
        if ($result['status'] === 'success') {
            // Redirecionar após login bem-sucedido
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

// Verificar se há uma mensagem de erro na URL
if (isset($_GET['error']) && $_GET['error'] === 'auth_required') {
    $error = 'Você precisa fazer login para acessar esta página';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Player</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: #1f1f23;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .login-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #ddd;
            font-size: 28px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #aaa;
            font-size: 16px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #333;
            background-color: #2a2a2e;
            color: #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #7b68ee;
            outline: none;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            color: #aaa;
            font-size: 14px;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .btn-login {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #7b68ee;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-login:hover {
            background-color: #6a5acd;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #aaa;
        }
        
        .register-link a {
            color: #7b68ee;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Entrar</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Você saiu com sucesso.
            </div>
        <?php endif; ?>
        
        <form id="loginForm" method="post" action="">
            <div class="form-group">
                <label for="username">Nome de usuário ou Email</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Lembrar de mim</label>
            </div>
            
            <button type="submit" class="btn-login" id="loginBtn">Entrar</button>
            
            <div class="register-link">
                Não tem conta? <a href="register.php">Registrar-se</a>
            </div>
        </form>
        
        <div id="loginError" style="display: none;">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <span id="errorMessage"></span>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Versão AJAX do formulário de login
            $('#loginForm').submit(function(e) {
                // Impedir o envio tradicional do formulário
                e.preventDefault();
                
                // Ocultar mensagens de erro anteriores
                $('#loginError').hide();
                
                const username = $('#username').val().trim();
                const password = $('#password').val();
                const remember = $('#remember').is(':checked') ? 'on' : 'off';
                
                // Validar campos
                if (!username) {
                    $('#errorMessage').text('Por favor, informe seu nome de usuário ou email');
                    $('#loginError').show();
                    return;
                }
                
                if (!password) {
                    $('#errorMessage').text('Por favor, informe sua senha');
                    $('#loginError').show();
                    return;
                }
                
                // Desabilitar botão durante o processamento
                $('#loginBtn').prop('disabled', true).text('Entrando...');
                
                // Enviar solicitação de login
                $.ajax({
                    url: 'auth.php',
                    type: 'POST',
                    contentType: 'application/x-www-form-urlencoded',
                    data: {
                        action: 'login',
                        username: username,
                        password: password,
                        remember: remember
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Login bem-sucedido, redirecionar
                            window.location.href = 'index.php';
                        } else {
                            // Exibir mensagem de erro
                            $('#errorMessage').text(response.message);
                            $('#loginError').show();
                            $('#loginBtn').prop('disabled', false).text('Entrar');
                        }
                    },
                    error: function(xhr, status, error) {
                        // Erro na solicitação AJAX
                        $('#errorMessage').text('Erro ao processar sua solicitação. Tente novamente.');
                        $('#loginError').show();
                        $('#loginBtn').prop('disabled', false).text('Entrar');
                        console.error('Erro AJAX:', xhr.responseText);
                    }
                });
            });
        });
    </script>
</body>
</html> 