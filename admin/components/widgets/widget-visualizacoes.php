<?php
/**
 * Widget: Visualizações
 * Componente reutilizável
 * Versão: 1.1.0
 * Uso: include 'admin/components/widgets/widget-visualizacoes.php';
 * Espera array $visualizacoes (ex: ['total' => 0, 'grafico' => []])
 */
if (!isset($visualizacoes) || !is_array($visualizacoes)) {
    $visualizacoes = ['total' => 0, 'grafico' => []];
}
?>
<div class="dashboard-widget widget-visualizacoes animate-fadeInUp" data-widget-id="visualizacoes" style="animation-delay:0.15s;">
    <div class="dashboard-card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-eye"></i> Visualizações</span>
        <select id="filtro-views" class="form-select form-select-sm" style="width:auto;min-width:140px;">
            <option value="7dias">Últimos 7 dias</option>
            <option value="mes">Este mês</option>
        </select>
    </div>
    <div class="dashboard-card-body">
        <div class="kpi-value mb-2">
            <?php echo (int)$visualizacoes['total']; ?> <span class="text-muted" style="font-size:0.95rem;">no período</span>
        </div>
        <div style="height:180px;">
            <canvas id="viewsChart"></canvas>
        </div>
    </div>
</div>
<script>
if (window.Chart && document.getElementById('viewsChart')) {
    new Chart(document.getElementById('viewsChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($visualizacoes['grafico'], 'label')); ?>,
            datasets: [{
                label: 'Visualizações',
                data: <?php echo json_encode(array_column($visualizacoes['grafico'], 'valor')); ?>,
                backgroundColor: 'rgba(37,99,235,0.12)',
                borderColor: '#2563eb',
                borderWidth: 2,
                pointRadius: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } },
            animation: { duration: 900, easing: 'easeOutQuart' }
        }
    });
}
</script> 