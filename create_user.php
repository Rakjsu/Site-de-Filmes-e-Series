<?php
/**
 * Script para criar um novo usuário no banco de dados
 * 
 * Este script cria um usuário com nome, email, senha e função específicos.
 * Utilize apenas uma vez para criar o usuário desejado.
 */

// Incluir configuração do banco de dados
require_once 'db_config.php';

// Dados do usuário a ser criado
$username = 'Rakjsu';
$email = 'rakjsu@exemplo.com';
$password = '05062981';
$role = 'admin'; // 'admin' ou 'user'

// Verificar se o usuário já existe
$checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
$existingUser = dbQuerySingle($checkSql, [$username, $email]);

if ($existingUser) {
    die("Erro: Usuário ou email já existe no sistema.");
}

// Criptografar a senha (usando bcrypt)
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Preparar dados para inserção
$userData = [
    'username' => $username,
    'email' => $email,
    'password' => $hashedPassword,
    'role' => $role
];

try {
    // Inserir o usuário no banco de dados
    $userId = dbInsert('users', $userData);
    
    if ($userId) {
        echo "Usuário criado com sucesso! ID: " . $userId;
        
        // Configurações padrão do usuário (JSON)
        $defaultPreferences = json_encode([
            'autoplay' => true,
            'theme' => 'dark',
            'volume' => 0.8
        ]);
        
        // Atualizar preferências do usuário
        dbUpdate('users', ['preferences' => $defaultPreferences], 'id', $userId);
        
        echo "<br>Preferências padrão configuradas.";
    } else {
        echo "Erro: Não foi possível criar o usuário.";
    }
} catch (Exception $e) {
    echo "Erro ao criar usuário: " . $e->getMessage();
}
?> 