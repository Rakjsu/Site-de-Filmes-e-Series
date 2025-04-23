<?php
// admin/ajax/save_dashboard_layout.php
session_start();
header('Content-Type: application/json');

// Apenas admins podem acessar
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

require_once '../../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Salvar novo layout
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['rows']) || !is_array($input['rows'])) {
        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
        exit;
    }
    $layout = json_encode(['rows' => $input['rows']]);
    $stmt = $pdo->prepare('UPDATE admin_dashboard_layout SET layout = ? WHERE id = 1');
    $ok = $stmt->execute([$layout]);
    echo json_encode(['success' => $ok]);
    exit;
}

if ($method === 'GET') {
    // Buscar layout salvo
    $stmt = $pdo->query('SELECT layout FROM admin_dashboard_layout WHERE id = 1');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo $row['layout'];
    } else {
        echo json_encode(['rows' => [['novos-usuarios','visualizacoes'],['mais-vistos','avisos']]]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Método não permitido']); 