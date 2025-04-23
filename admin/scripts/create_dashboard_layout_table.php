<?php
/**
 * Script para criar a tabela admin_dashboard_layout e inserir layout padrão
 * Execute uma vez e depois remova por segurança.
 */
require_once '../../includes/db.php'; // Ajuste o caminho conforme seu projeto

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_dashboard_layout (
        id INT PRIMARY KEY DEFAULT 1,
        layout JSON NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $defaultLayout = json_encode([
        'rows' => [
            ['novos-usuarios', 'visualizacoes'],
            ['mais-vistos', 'avisos']
        ]
    ]);

    $stmt = $pdo->prepare("INSERT INTO admin_dashboard_layout (id, layout) VALUES (1, ?) ON DUPLICATE KEY UPDATE layout=VALUES(layout)");
    $stmt->execute([$defaultLayout]);

    echo "Tabela e layout padrão criados com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao criar tabela: " . $e->getMessage();
} 