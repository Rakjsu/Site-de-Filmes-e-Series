<?php
/**
 * Página de Registro de Usuários
 * 
 * Este arquivo contém o formulário de registro de novos usuários
 * e o processamento do envio dos dados para autenticação.
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

// Definir variáveis para erros
$errors = [];
$formData = [
    'username' => '',
    'email' => ''
];

// Processar o formulário se enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar dados
    if (!isset($_POST['username']) || empty($_POST['username'])) {
        $errors[] = 'Nome de usuário é obrigatório';
    } else {
        $formData['username'] = $_POST['username'];
    }
    
    if (!isset($_POST['email']) || empty($_POST['email'])) {
        $errors[] = 'Email é obrigatório';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    } else {
        $formData['email'] = $_POST['email'];
    }
    
    if (!isset($_POST['password']) || empty($_POST['password'])) {
        $errors[] = 'Senha é obrigatória';
    } elseif (strlen($_POST['password']) < 6) {
        $errors[] = 'Senha deve ter pelo menos 6 caracteres';
    }
    
    if (!isset($_POST['confirm_password']) || $_POST['password'] !== $_POST['confirm_password']) {
        $errors[] = 'As senhas não coincidem';
    }
    
    if (!isset($_POST['terms']) || $_POST['terms'] !== 'on') {
        $errors[] = 'Você deve concordar com os termos de uso';
    }
    
    // Se não houver erros, processar o registro
    if (empty($errors)) {
        $result = registerUser(
            $_POST['username'],
            $_POST['email'],
            $_POST['password']
        );
        
        if ($result['status'] === 'success') {
            // Redirecionar para a página inicial após registro bem-sucedido
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Verificar se há requisição AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    header('Content-Type: application/json');
    
    // Validar os dados
    $ajaxErrors = [];
    
    if (!isset($_POST['username']) || empty($_POST['username'])) {
        $ajaxErrors[] = 'Nome de usuário é obrigatório';
    }
    
    if (!isset($_POST['email']) || empty($_POST['email'])) {
        $ajaxErrors[] = 'Email é obrigatório';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $ajaxErrors[] = 'Email inválido';
    }
    
    if (!isset($_POST['password']) || empty($_POST['password'])) {
        $ajaxErrors[] = 'Senha é obrigatória';
    } elseif (strlen($_POST['password']) < 6) {
        $ajaxErrors[] = 'Senha deve ter pelo menos 6 caracteres';
    }
    
    if (!isset($_POST['confirm_password']) || $_POST['password'] !== $_POST['confirm_password']) {
        $ajaxErrors[] = 'As senhas não coincidem';
    }
    
    if (!isset($_POST['terms']) || $_POST['terms'] !== 'on') {
        $ajaxErrors[] = 'Você deve concordar com os termos de uso';
    }
    
    // Se houver erros, retornar erro
    if (!empty($ajaxErrors)) {
        echo json_encode([
            'status' => 'error',
            'message' => implode('<br>', $ajaxErrors)
        ]);
        exit;
    }
    
    // Processar o registro
    $result = registerUser(
        $_POST['username'],
        $_POST['email'],
        $_POST['password']
    );
    
    echo json_encode($result);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuário</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Adicionar Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #1f1f23;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .register-container h1 {
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
        
        .form-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .form-checkbox input {
            margin-right: 10px;
        }
        
        .form-checkbox label {
            color: #aaa;
            font-size: 14px;
        }
        
        .form-checkbox a {
            color: #7b68ee;
            text-decoration: none;
        }
        
        .form-checkbox a:hover {
            text-decoration: underline;
        }
        
        .btn-register {
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
        
        .btn-register:hover {
            background-color: #6a5acd;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #aaa;
        }
        
        .login-link a {
            color: #7b68ee;
            text-decoration: none;
        }
        
        .login-link a:hover {
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
        
        .form-error {
            color: #ff6b6b;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Criar Conta</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form id="registerForm" method="post" action="">
            <div class="form-group">
                <label for="username">Nome de usuário</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($formData['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="form-error password-error"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar Senha</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <div class="form-error password-match-error"></div>
            </div>
            
            <div class="form-checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">Eu concordo com os <a href="terms.php" target="_blank">Termos de Uso</a></label>
            </div>
            
            <button type="submit" class="btn-register" id="registerBtn">Criar Conta</button>
            
            <div class="login-link">
                Já tem conta? <a href="login.php">Entrar</a>
            </div>
        </form>
        
        <div id="registrationSuccess" style="display: none;">
            <div class="alert alert-success">
                <h4><i class="fas fa-check-circle"></i> Bem-vindo, <span id="registeredUsername"></span>!</h4>
                <p><span id="registrationMessage"></span></p>
                <p>Você será redirecionado para a página principal em alguns segundos...</p>
            </div>
        </div>
        
        <div id="registerError" style="display: none;">
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <span id="errorMessage"></span>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordError = document.querySelector('.password-error');
            const passwordMatchError = document.querySelector('.password-match-error');
            
            // Validar senha em tempo real
            passwordInput.addEventListener('input', function() {
                if (this.value.length < 6) {
                    passwordError.textContent = 'A senha deve ter pelo menos 6 caracteres';
                } else {
                    passwordError.textContent = '';
                }
            });
            
            // Verificar se as senhas coincidem em tempo real
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value !== passwordInput.value) {
                    passwordMatchError.textContent = 'As senhas não coincidem';
                } else {
                    passwordMatchError.textContent = '';
                }
            });
            
            // Processar formulário via AJAX
            form.addEventListener('submit', function(e) {
                // Verificar confirmação de senha
                if (passwordInput.value !== confirmPasswordInput.value) {
                    e.preventDefault();
                    passwordMatchError.textContent = 'As senhas não coincidem';
                    return;
                }
                
                // Verificar comprimento da senha
                if (passwordInput.value.length < 6) {
                    e.preventDefault();
                    passwordError.textContent = 'A senha deve ter pelo menos 6 caracteres';
                    return;
                }
            });
        });
    </script>
    
    <!-- Incluir jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Exibir qualquer mensagem de erro que possa estar escondida
            if ($('#registerError').text().trim() !== '') {
                $('#registerError').show();
            }
            
            // Função para validar o email
            function validateEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
            
            $('#registerBtn').click(function(e) {
                e.preventDefault();
                
                // Limpar mensagens de erro anteriores
                $('#registerError').hide();
                
                const username = $('#username').val().trim();
                const email = $('#email').val().trim();
                const password = $('#password').val();
                const confirm_password = $('#confirm_password').val();
                const terms = $('#terms').is(':checked') ? 'on' : 'off';
                
                // Verificações de validação
                if (!username) {
                    $('#registerError').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Nome de usuário é obrigatório</div>').show();
                    return;
                }
                
                if (!email) {
                    $('#registerError').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Email é obrigatório</div>').show();
                    return;
                }
                
                if (!validateEmail(email)) {
                    $('#registerError').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Formato de email inválido</div>').show();
                    return;
                }
                
                if (!password) {
                    $('#registerError').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Senha é obrigatória</div>').show();
                    return;
                }
                
                // Verificar se as senhas coincidem
                if (password !== confirm_password) {
                    $('#registerError').html('<div class="alert alert-danger">' +
                        '<i class="fas fa-exclamation-circle"></i> As senhas não coincidem</div>').show();
                    return;
                }
                
                // Verificar comprimento da senha
                if (password.length < 8) {
                    $('#registerError').html('<div class="alert alert-danger">' +
                        '<i class="fas fa-exclamation-circle"></i> A senha deve ter no mínimo 8 caracteres</div>').show();
                    return;
                }
                
                // Verificar termos
                if (terms !== 'on') {
                    $('#registerError').html('<div class="alert alert-danger">' +
                        '<i class="fas fa-exclamation-circle"></i> Você precisa aceitar os Termos de Uso</div>').show();
                    return;
                }
                
                $.ajax({
                    url: 'auth.php',
                    type: 'POST',
                    contentType: 'application/x-www-form-urlencoded',
                    data: {
                        action: 'register',
                        username: username,
                        email: email,
                        password: password,
                        confirm_password: confirm_password,
                        terms: terms
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('#registerBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');
                        $('#registerError').hide();
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Registro bem-sucedido
                            $('#registerForm').hide();
                            $('#registrationSuccess').html('<div class="alert alert-success">' + 
                                '<h4><i class="fas fa-check-circle"></i> Bem-vindo, ' + response.user.username + '!</h4>' +
                                '<p>' + response.message + '</p>' +
                                '<p>Você será redirecionado para a página principal em alguns segundos...</p>' +
                                '</div>').show();
                            
                            // Redirecionar para a página principal após 2 segundos
                            setTimeout(function() {
                                window.location.href = 'index.php';
                            }, 2000);
                        } else {
                            // Erro no registro
                            $('#registerError').html('<div class="alert alert-danger">' +
                                '<i class="fas fa-exclamation-circle"></i> ' + response.message + '</div>').show();
                            $('#registerBtn').prop('disabled', false).html('Criar Conta');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Erro AJAX:", xhr.responseText);
                        let errorMsg = 'Erro na conexão com o servidor. Tente novamente.';
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response && response.message) {
                                errorMsg = response.message;
                            }
                        } catch (e) {
                            // Se não conseguir analisar a resposta, usar a mensagem padrão
                        }
                        
                        $('#registerError').html('<div class="alert alert-danger">' +
                            '<i class="fas fa-exclamation-triangle"></i> ' + errorMsg + '</div>').show();
                        $('#registerBtn').prop('disabled', false).html('Criar Conta');
                    }
                });
            });
        });
    </script>
</body>
</html> 