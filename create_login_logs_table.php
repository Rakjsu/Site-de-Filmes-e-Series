<?php
/**
 * Script para criar a tabela de logs de login no banco de dados
 * 
 * Este script é utilizado para criar a tabela que armazenará os registros
 * de tentativas de login no sistema.
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
    
    echo "Conectado ao banco de dados com sucesso.<br>";
    
    // Verificar se a tabela já existe
    $checkSql = "SHOW TABLES LIKE 'login_logs'";
    $stmt = $pdo->query($checkSql);
    
    if ($stmt->rowCount() > 0) {
        echo "A tabela login_logs já existe.<br>";
    } else {
        // Criar a tabela login_logs
        $createSql = "CREATE TABLE login_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            ip_address VARCHAR(45) NOT NULL,
            success TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createSql);
        echo "Tabela login_logs criada com sucesso.<br>";
    }
    
    // Verificar se a coluna remember_token existe na tabela users
    $checkColumnSql = "SHOW COLUMNS FROM users LIKE 'remember_token'";
    $stmt = $pdo->query($checkColumnSql);
    
    if ($stmt->rowCount() > 0) {
        echo "A coluna remember_token já existe na tabela users.<br>";
    } else {
        // Adicionar a coluna remember_token e token_expiry à tabela users
        $alterSql = "ALTER TABLE users 
                     ADD COLUMN remember_token VARCHAR(64) DEFAULT NULL,
                     ADD COLUMN token_expiry DATETIME DEFAULT NULL";
        
        $pdo->exec($alterSql);
        echo "Colunas remember_token e token_expiry adicionadas à tabela users.<br>";
    }
    
    echo "<br>Processo concluído com sucesso!";
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?> 