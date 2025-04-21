<?php
/**
 * Script para verificar os usuários cadastrados no banco de dados
 */

// Configurações do banco de dados
$host = 'localhost';
$port = '3306';
$user = 'Rakjsu';
$password = '05062981';
$dbname = 'player';
$charset = 'utf8mb4';

// Conectar ao banco de dados
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Conectado ao banco de dados com sucesso.\n\n";
    
    // Consultar usuários cadastrados
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "Usuários cadastrados:\n";
        echo "----------------------------------------------------\n";
        echo "ID\t| Username\t| Email\t\t\t| Função\t| Data de Criação\n";
        echo "----------------------------------------------------\n";
        
        foreach ($users as $user) {
            printf(
                "%d\t| %s\t| %s\t| %s\t| %s\n",
                $user['id'],
                $user['username'],
                $user['email'],
                $user['role'],
                $user['created_at']
            );
        }
        
        echo "----------------------------------------------------\n";
        echo "\nTotal de usuários: " . count($users) . "\n";
    } else {
        echo "Não há usuários cadastrados no banco de dados.\n";
    }
    
    // Verificar se o usuário Rakjsu foi criado com sucesso
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['Rakjsu']);
    $rakjsuUser = $stmt->fetch();
    
    if ($rakjsuUser) {
        echo "\nO usuário Rakjsu foi criado com sucesso e está disponível no sistema.\n";
        echo "Você pode fazer login com as seguintes credenciais:\n";
        echo "- Username: Rakjsu\n";
        echo "- Senha: 05062981\n";
        echo "- Função: admin\n";
    } else {
        echo "\nAtençao: O usuário Rakjsu não foi encontrado no sistema.\n";
    }
    
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage() . "\n";
}
?> 