<?php
/**
 * Script para criar o banco de dados e as tabelas necessárias
 * 
 * Este script executa o conteúdo do arquivo database.sql para criar
 * o banco de dados player e suas tabelas.
 */

// Configurações do banco de dados
$host = 'localhost';
$port = '3306';
$user = 'Rakjsu';
$password = '05062981';
$charset = 'utf8mb4';

// Conectar ao MySQL sem selecionar banco de dados
try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=$charset", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Conectado ao MySQL com sucesso.<br>";
    
    // Ler o arquivo SQL
    $sql = file_get_contents('database.sql');
    
    // Dividir o SQL em comandos individuais
    $commands = explode(';', $sql);
    
    foreach ($commands as $command) {
        $command = trim($command);
        
        if (!empty($command)) {
            try {
                $pdo->exec($command);
                echo "Comando SQL executado com sucesso: " . substr($command, 0, 50) . "...<br>";
            } catch (PDOException $e) {
                echo "Erro ao executar comando SQL: " . $e->getMessage() . "<br>";
                echo "Comando: " . $command . "<br>";
            }
        }
    }
    
    echo "<br>Criação do banco de dados e tabelas concluída.";
    
    // Agora vamos inserir o usuário Rakjsu
    // Conectar ao banco de dados 'player'
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=player;charset=$charset", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Verificar se o usuário já existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute(['Rakjsu', 'rakjsu@exemplo.com']);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo "<br>Usuário Rakjsu já existe no sistema.";
    } else {
        // Criptografar a senha
        $hashedPassword = password_hash('05062981', PASSWORD_BCRYPT);
        
        // Inserir o usuário
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute(['Rakjsu', 'rakjsu@exemplo.com', $hashedPassword, 'admin']);
        
        if ($result) {
            $userId = $pdo->lastInsertId();
            echo "<br>Usuário Rakjsu criado com sucesso! ID: " . $userId;
            
            // Configurações padrão do usuário
            $defaultPreferences = json_encode([
                'autoplay' => true,
                'theme' => 'dark',
                'volume' => 0.8
            ]);
            
            // Atualizar preferências
            $stmt = $pdo->prepare("UPDATE users SET preferences = ? WHERE id = ?");
            $stmt->execute([$defaultPreferences, $userId]);
            
            echo "<br>Preferências padrão configuradas.";
        } else {
            echo "<br>Erro: Não foi possível criar o usuário Rakjsu.";
        }
    }
    
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
}
?> 