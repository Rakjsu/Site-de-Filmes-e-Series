<?php
/**
 * Widget: Novos Usuários na Semana
 * Componente reutilizável
 * Versão: 1.2.0
 * Uso: include 'admin/components/widgets/widget-novos-usuarios.php';
 * Espera array $novosUsuarios
 */
if (!isset($novosUsuarios) || !is_array($novosUsuarios)) {
    $novosUsuarios = [];
}
?>
<div class="dashboard-widget widget-novos-usuarios animate-fadeInUp" data-widget-id="novos-usuarios" style="animation-delay:0.05s;">
    <div class="dashboard-card-header">
        <i class="fas fa-user-plus"></i> Novos Usuários na Semana
    </div>
    <div class="dashboard-card-body">
        <div style="height:180px;">
            <canvas id="widgetChart"></canvas>
        </div>
    </div>
</div> 