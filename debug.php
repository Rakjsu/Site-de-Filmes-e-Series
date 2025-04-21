<?php
/**
 * Página de Diagnóstico do Sistema
 * 
 * Este arquivo contém ferramentas para diagnosticar problemas com o sistema,
 * incluindo conexão com o banco de dados e autenticação de usuários.
 * 
 * IMPORTANTE: Remova este arquivo em ambiente de produção!
 */

// Habilitar exibição de erros para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir constante para ativar diagnóstico em auth.php
define('DEBUG_MODE', true);

// Incluir os arquivos necessários
require_once 'db_config.php';
require_once 'auth.php';

// Função para executar teste de conexão
function runConnectionTest() {
    echo "<h3>Teste de Conexão com o Banco de Dados</h3>";
    
    try {
        $connection = getConnection();
        echo "<p style='color:green'>✓ Conexão com o banco de dados estabelecida com sucesso!</p>";
        
        // Verificar se a tabela 'users' existe
        $stmt = $connection->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>✓ Tabela 'users' encontrada.</p>";
            
            // Contar usuários
            $stmt = $connection->query("SELECT COUNT(*) as total FROM users");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Total de usuários cadastrados: " . $result['total'] . "</p>";
            
            // Listar usuários (apenas para diagnóstico)
            $stmt = $connection->query("SELECT id, username, email, role FROM users LIMIT 10");
            if ($stmt->rowCount() > 0) {
                echo "<h4>Primeiros 10 usuários:</h4>";
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Usuário</th><th>Email</th><th>Função</th></tr>";
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p style='color:orange'>⚠️ Não há usuários cadastrados.</p>";
            }
        } else {
            echo "<p style='color:red'>✗ Tabela 'users' não encontrada! O banco de dados não está configurado corretamente.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erro na conexão com o banco de dados: " . $e->getMessage() . "</p>";
        echo "<p>Verifique se as configurações em db_config.php estão corretas:</p>";
        echo "<ul>";
        echo "<li>DB_HOST: " . DB_HOST . "</li>";
        echo "<li>DB_NAME: " . DB_NAME . "</li>";
        echo "<li>DB_USER: " . DB_USER . "</li>";
        echo "<li>DB_PASS: (não exibido por segurança)</li>";
        echo "</ul>";
    }
}

// Função para testar login
function testLogin($username, $password) {
    echo "<h3>Teste de Login</h3>";
    
    echo "<p>Tentando login com:</p>";
    echo "<ul>";
    echo "<li>Usuário: " . htmlspecialchars($username) . "</li>";
    echo "<li>Senha: " . str_repeat("*", strlen($password)) . "</li>";
    echo "</ul>";
    
    $result = loginUser($username, $password, false);
    
    if ($result['status'] === 'success') {
        echo "<p style='color:green'>✓ Login bem-sucedido!</p>";
        echo "<p>Usuário: " . htmlspecialchars($result['user']['username']) . "</p>";
        echo "<p>ID: " . htmlspecialchars($result['user']['id']) . "</p>";
        echo "<p>Admin: " . ($result['user']['isAdmin'] ? 'Sim' : 'Não') . "</p>";
    } else {
        echo "<p style='color:red'>✗ Falha no login: " . htmlspecialchars($result['message']) . "</p>";
    }
}

// Verificar se é uma solicitação de teste de login
$testLoginMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_login'])) {
    ob_start();
    testLogin($_POST['username'], $_POST['password']);
    $testLoginMessage = ob_get_clean();
}

// Cabeçalho HTML
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico do Sistema</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        
        h2 {
            color: #444;
            margin-top: 30px;
        }
        
        pre {
            background-color: #f8f8f8;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        
        .error {
            color: #dc3545;
        }
        
        .success {
            color: #28a745;
        }
        
        .warning {
            color: #ffc107;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        th, td {
            text-align: left;
            padding: 8px;
        }
        
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico do Sistema</h1>
        <p><strong>ATENÇÃO:</strong> Esta página contém informações sensíveis. Remova-a do ambiente de produção!</p>
        
        <div class="results">
            <?php runConnectionTest(); ?>
        </div>
        
        <h2>Testar Login</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Nome de usuário ou Email:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="test_login" class="btn">Testar Login</button>
        </form>
        
        <?php if (!empty($testLoginMessage)): ?>
            <div class="test-results">
                <h3>Resultado do Teste</h3>
                <?php echo $testLoginMessage; ?>
            </div>
        <?php endif; ?>
        
        <h2>Informações do PHP</h2>
        <ul>
            <li>Versão do PHP: <?php echo phpversion(); ?></li>
            <li>PDO instalado: <?php echo extension_loaded('pdo') ? 'Sim' : 'Não'; ?></li>
            <li>PDO MySQL instalado: <?php echo extension_loaded('pdo_mysql') ? 'Sim' : 'Não'; ?></li>
            <li>Sessão ativa: <?php echo session_status() === PHP_SESSION_ACTIVE ? 'Sim' : 'Não'; ?></li>
        </ul>
        
        <p><a href="index.php">Voltar para a página inicial</a></p>
    </div>
</body>
</html> 