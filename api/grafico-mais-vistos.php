<?php
/**
 * API: Gráfico de Mais Vistos
 * Versão: 1.0.0
 */
// api/grafico-mais-vistos.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}
$periodo = $_GET['periodo'] ?? '7dias';
$titulos = [
    'Interestelar',
    'Vingadores: Ultimato',
    'Breaking Bad',
    'Stranger Things',
    'O Poderoso Chefão',
    'Game of Thrones',
    'Pulp Fiction'
];
$data = [];
foreach ($titulos as $titulo) {
    $data[] = rand(5000, 20000);
}
echo json_encode(['labels' => $titulos, 'data' => $data]); 