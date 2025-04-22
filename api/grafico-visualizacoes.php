<?php
/**
 * API: Gráfico de Visualizações
 * Versão: 1.0.0
 */
// api/grafico-visualizacoes.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}
$periodo = $_GET['periodo'] ?? '7dias';
if ($periodo === 'mes') {
    $labels = [];
    $data = [];
    $diasNoMes = date('t');
    for ($i = 1; $i <= $diasNoMes; $i++) {
        $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        $data[] = rand(1000, 3500);
    }
} else {
    $labels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
    $data = [rand(1000,2000), rand(1500,2500), rand(1800,3000), rand(1200,2200), rand(2000,3500), rand(2500,4000), rand(1700,3200)];
}
echo json_encode(['labels' => $labels, 'data' => $data]); 