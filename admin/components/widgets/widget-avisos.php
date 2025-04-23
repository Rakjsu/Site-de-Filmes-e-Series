<?php
/**
 * Widget: Avisos do Sistema
 * Componente reutilizável
 * Versão: 1.1.0
 * Uso: include 'admin/components/widgets/widget-avisos.php';
 * Espera array $avisos (ex: [['mensagem' => '', 'tipo' => 'info', 'data' => '']])
 */
if (!isset($avisos) || !is_array($avisos)) {
    $avisos = [];
}
?>
<div class="dashboard-widget widget-avisos animate-fadeInUp" data-widget-id="avisos" style="animation-delay:0.35s;">
    <div class="dashboard-card-header">
        <i class="fas fa-exclamation-circle"></i> Avisos do Sistema
    </div>
    <div class="dashboard-card-body">
        <?php if (empty($avisos)): ?>
            <p class="text-muted">Nenhum aviso no momento.</p>
        <?php else: ?>
            <ul class="activity-list-modern">
                <?php foreach ($avisos as $aviso): ?>
                <li class="activity-item-modern">
                    <div class="activity-info-modern">
                        <span class="activity-action text-<?php echo htmlspecialchars($aviso['tipo']); ?>">
                            <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($aviso['mensagem']); ?>
                        </span>
                        <span class="activity-time text-muted"><?php echo htmlspecialchars($aviso['data']); ?></span>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div> 