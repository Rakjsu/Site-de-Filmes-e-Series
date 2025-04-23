<?php
/**
 * Widget: Mais Vistos: Filmes & Séries
 * Componente reutilizável
 * Versão: 1.2.0
 * Uso: include 'admin/components/widgets/widget-mais-vistos.php';
 * Espera array $maisVistos (ex: [['titulo' => '', 'tipo' => '', 'views' => 0]])
 */
if (!isset($maisVistos) || !is_array($maisVistos)) {
    $maisVistos = [];
}
?>
<div class="dashboard-widget widget-mais-vistos animate-fadeInUp" data-widget-id="mais-vistos" style="animation-delay:0.25s;">
    <div class="dashboard-card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-chart-bar"></i> Mais Vistos: Filmes & Séries</span>
        <select id="filtro-top" class="form-select form-select-sm" style="width:auto;min-width:140px;">
            <option value="7dias">Últimos 7 dias</option>
            <option value="mes">Este mês</option>
        </select>
    </div>
    <div class="dashboard-card-body">
        <div style="height:220px;">
            <canvas id="topChart"></canvas>
        </div>
    </div>
</div> 