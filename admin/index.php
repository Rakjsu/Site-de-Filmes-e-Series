<?php
/**
 * Painel de Administração - Dashboard Principal
 * Versão: 3.3.1 (debug)
 */

// Iniciar sessão
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Redirecionar para a página de login
    header('Location: ../login.php?redirect=admin');
    exit;
}

// Definir título da página e breadcrumbs
$pageTitle = 'Dashboard';
$breadcrumbs = [
    'Dashboard' => null,
];

// Estatísticas simuladas (substitua por queries reais futuramente)
$totalUsuarios = 5876;
$totalFilmes = 320;
$totalSeries = 120;
$totalGeneros = 18;
$visualizacoesHoje = 2450;

// Últimos usuários registrados
$ultimosUsuarios = [
    ['nome' => 'João Silva', 'email' => 'joao.silva@example.com', 'avatar' => '../assets/img/avatars/user1.jpg', 'data_registro' => '2023-10-15'],
    ['nome' => 'Maria Oliveira', 'email' => 'maria.oliveira@example.com', 'avatar' => '../assets/img/avatars/user2.jpg', 'data_registro' => '2023-10-18'],
    ['nome' => 'Carlos Santos', 'email' => 'carlos.santos@example.com', 'avatar' => '../assets/img/avatars/user3.jpg', 'data_registro' => '2023-10-20'],
];

// Atividades recentes
$atividadesRecentes = [
    ['usuario' => 'João Silva', 'acao' => 'adicionou um filme', 'conteudo' => 'Matrix', 'timestamp' => '2023-10-30 15:45'],
    ['usuario' => 'Maria Oliveira', 'acao' => 'cadastrou um usuário', 'conteudo' => 'Pedro Costa', 'timestamp' => '2023-10-30 14:30'],
    ['usuario' => 'Carlos Santos', 'acao' => 'editou uma série', 'conteudo' => 'Breaking Bad', 'timestamp' => '2023-10-30 13:15'],
];

// Exemplo de badge de notificação (ex: novos usuários)
$novosUsuariosHoje = 4;

// Dados simulados para os gráficos
$visualizacoesSemana = [1200, 1900, 2300, 1800, 2500, 3100, 2700];
$labelsSemana = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
$maisVistos = [
    ['titulo' => 'Interestelar', 'visualizacoes' => 15420],
    ['titulo' => 'Vingadores: Ultimato', 'visualizacoes' => 12850],
    ['titulo' => 'Breaking Bad', 'visualizacoes' => 11200],
    ['titulo' => 'Stranger Things', 'visualizacoes' => 9800],
    ['titulo' => 'O Poderoso Chefão', 'visualizacoes' => 9500],
    ['titulo' => 'Game of Thrones', 'visualizacoes' => 8700],
    ['titulo' => 'Pulp Fiction', 'visualizacoes' => 7890],
];

// Script para os gráficos
$inlineScript = <<<'SCRIPT'
let viewsChart, topChart;
function logDebug(msg, data) { try { console.log('[DASHBOARD DEBUG]', msg, data || ''); } catch(e){} }
document.addEventListener('DOMContentLoaded', function() {
    // --- Gráfico de Novos Usuários na Semana (mantém simulado) ---
    const usersCanvas = document.getElementById('widgetChart');
    logDebug('widgetChart canvas', usersCanvas);
    if (usersCanvas) {
        const ctxUsers = usersCanvas.getContext('2d');
        new Chart(ctxUsers, {
            type: 'line',
            data: {
                labels: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Novos Usuários',
                    data: [2, 4, 3, 5, 6, 4, 7],
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.08)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    } else {
        logDebug('widgetChart não encontrado!');
    }

    // --- Gráfico de Visualizações da Semana/Mês ---
    function renderViewsChart(labels, data) {
        logDebug('renderViewsChart', {labels, data});
        if (viewsChart) viewsChart.destroy();
        const canvas = document.getElementById('viewsChart');
        logDebug('viewsChart canvas', canvas);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        viewsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Visualizações',
                    data: data,
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124,58,237,0.08)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
                scales: { y: { beginAtZero: true } },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    function fetchViewsChart(periodo) {
        logDebug('fetchViewsChart', periodo);
        fetch('../api/grafico-visualizacoes.php?periodo=' + periodo)
            .then(r => { logDebug('fetchViewsChart response', r); return r.json(); })
            .then(json => { logDebug('fetchViewsChart json', json); renderViewsChart(json.labels, json.data); })
            .catch(e => logDebug('fetchViewsChart erro', e));
    }
    // Inicialização padrão
    fetchViewsChart('7dias');
    document.getElementById('filtro-views').addEventListener('change', function() {
        fetchViewsChart(this.value);
    });

    // --- Gráfico de Mais Vistos ---
    function renderTopChart(labels, data) {
        logDebug('renderTopChart', {labels, data});
        if (topChart) topChart.destroy();
        const canvas = document.getElementById('topChart');
        logDebug('topChart canvas', canvas);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        topChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Visualizações',
                    data: data,
                    backgroundColor: [
                        '#2563eb', '#7c3aed', '#22c55e', '#f59e42', '#06b6d4', '#64748b', '#18181b'
                    ],
                    borderRadius: 8,
                    maxBarThickness: 32
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
                scales: { x: { beginAtZero: true } },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
    function fetchTopChart(periodo) {
        logDebug('fetchTopChart', periodo);
        fetch('../api/grafico-mais-vistos.php?periodo=' + periodo)
            .then(r => { logDebug('fetchTopChart response', r); return r.json(); })
            .then(json => { logDebug('fetchTopChart json', json); renderTopChart(json.labels, json.data); })
            .catch(e => logDebug('fetchTopChart erro', e));
    }
    // Inicialização padrão
    fetchTopChart('7dias');
    document.getElementById('filtro-top').addEventListener('change', function() {
        fetchTopChart(this.value);
    });
});
SCRIPT;

// CSS específico para esta página
$pageCss = ['css/admin-dashboard.css'];

// Scripts específicos para esta página
$pageScripts = [];

// Incluir template de cabeçalho
include_once 'templates/header.php';

error_log('PHPSESSID: ' . session_id());
?>

<body>
    <div class="admin-wrapper">
        <!-- Incluir sidebar -->
        <?php include_once 'templates/sidebar.php'; ?>
        
        <!-- Conteúdo Principal -->
        <main class="admin-main">
            <!-- Incluir cabeçalho principal -->
            <?php include_once 'templates/main-header.php'; ?>
            
            <!-- Conteúdo da página -->
            <div class="main-content dashboard-modern">
                <!-- Boas-vindas e ações rápidas -->
                <section class="dashboard-welcome-modern">
                    <div class="welcome-text">
                        <h1>Bem-vindo, Admin!</h1>
                        <p class="subtitle">Gerencie sua plataforma de forma rápida, visual e eficiente.</p>
                    </div>
                    <div class="quick-actions-grid">
                        <a href="filmes.php" class="quick-action-card gradient-blue">
                            <i class="fas fa-film"></i>
                            <span>Filmes</span>
                        </a>
                        <a href="series.php" class="quick-action-card gradient-purple">
                            <i class="fas fa-tv"></i>
                            <span>Séries</span>
                        </a>
                        <a href="usuarios.php" class="quick-action-card gradient-green" tabindex="0">
                            <i class="fas fa-user"></i>
                            <span>Usuários</span>
                            <?php if ($novosUsuariosHoje > 0): ?>
                            <span class="badge-notification" title="Novos usuários hoje"><?php echo $novosUsuariosHoje; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="generos.php" class="quick-action-card gradient-orange">
                            <i class="fas fa-tags"></i>
                            <span>Gêneros</span>
                        </a>
                        <a href="estatisticas.php" class="quick-action-card gradient-cyan">
                            <i class="fas fa-chart-bar"></i>
                            <span>Estatísticas</span>
                        </a>
                        <a href="logs.php" class="quick-action-card gradient-gray">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Logs</span>
                        </a>
                        <a href="configuracoes.php" class="quick-action-card gradient-dark">
                            <i class="fas fa-cogs"></i>
                            <span>Configurações</span>
                        </a>
                    </div>
                </section>

                <!-- KPIs em cards grandes -->
                <section class="kpi-cards-grid">
                    <div class="kpi-card kpi-users">
                        <div class="kpi-icon"><i class="fas fa-users"></i></div>
                        <div class="kpi-info">
                            <span class="kpi-label">Usuários</span>
                            <span class="kpi-value"><?php echo number_format($totalUsuarios, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    <div class="kpi-card kpi-movies">
                        <div class="kpi-icon"><i class="fas fa-film"></i></div>
                        <div class="kpi-info">
                            <span class="kpi-label">Filmes</span>
                            <span class="kpi-value"><?php echo number_format($totalFilmes, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    <div class="kpi-card kpi-series">
                        <div class="kpi-icon"><i class="fas fa-tv"></i></div>
                        <div class="kpi-info">
                            <span class="kpi-label">Séries</span>
                            <span class="kpi-value"><?php echo number_format($totalSeries, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    <div class="kpi-card kpi-genres">
                        <div class="kpi-icon"><i class="fas fa-tags"></i></div>
                        <div class="kpi-info">
                            <span class="kpi-label">Gêneros</span>
                            <span class="kpi-value"><?php echo number_format($totalGeneros, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    <div class="kpi-card kpi-views">
                        <div class="kpi-icon"><i class="fas fa-eye"></i></div>
                        <div class="kpi-info">
                            <span class="kpi-label">Visualizações Hoje</span>
                            <span class="kpi-value"><?php echo number_format($visualizacoesHoje, 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </section>

                <!-- Exemplo de widgets customizáveis -->
                <div class="dashboard-widgets-row">
                    <div class="dashboard-widget" style="min-width:320px;max-width:600px;">
                        <h4 style="margin-bottom:1rem;font-weight:600;color:var(--color-primary);">Novos Usuários na Semana</h4>
                        <div style="height:180px;">
                            <canvas id="widgetChart"></canvas>
                        </div>
                    </div>
                    <div class="dashboard-widget" style="min-width:320px;max-width:600px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <h4 style="margin-bottom:1rem;font-weight:600;color:var(--color-secondary);">Visualizações</h4>
                            <select id="filtro-views" class="form-select form-select-sm" style="width:auto;min-width:140px;">
                                <option value="7dias">Últimos 7 dias</option>
                                <option value="mes">Este mês</option>
                            </select>
                        </div>
                        <div style="height:180px;">
                            <canvas id="viewsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="dashboard-widgets-row">
                    <div class="dashboard-widget" style="min-width:320px;max-width:600px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <h4 style="margin-bottom:1rem;font-weight:600;color:#18181b;">Mais Vistos: Filmes & Séries</h4>
                            <select id="filtro-top" class="form-select form-select-sm" style="width:auto;min-width:140px;">
                                <option value="7dias">Últimos 7 dias</option>
                                <option value="mes">Este mês</option>
                            </select>
                        </div>
                        <div style="height:220px;">
                            <canvas id="topChart"></canvas>
                        </div>
                    </div>
                    <div class="dashboard-widget" style="min-width:320px;max-width:600px;">
                        <h4 style="margin-bottom:1rem;font-weight:600;color:var(--color-secondary);">Avisos do Sistema</h4>
                        <ul style="margin:0;padding-left:1.2rem;">
                            <li>Backup agendado para hoje às 23h</li>
                            <li>Nova versão disponível para atualização</li>
                            <li>2 usuários aguardando aprovação</li>
                        </ul>
                    </div>
                </div>

                <!-- Grid responsivo para listas -->
                <div class="dashboard-flex-cols">
                    <!-- Atividades Recentes -->
                    <section class="dashboard-card dashboard-activities">
                        <div class="dashboard-card-header">
                            <h3>Atividades Recentes</h3>
                        </div>
                        <div class="dashboard-card-body activity-list-modern">
                            <?php foreach ($atividadesRecentes as $atividade): ?>
                            <div class="activity-item-modern">
                                <div class="activity-avatar-modern gradient-blue"></div>
                                <div class="activity-info-modern">
                                    <span class="activity-user"><strong><?php echo htmlspecialchars($atividade['usuario']); ?></strong></span>
                                    <span class="activity-action"><?php echo $atividade['acao']; ?></span>
                                    <span class="activity-content"><strong><?php echo htmlspecialchars($atividade['conteudo']); ?></strong></span>
                                    <span class="activity-time"><?php echo $atividade['timestamp']; ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <!-- Últimos Usuários -->
                    <section class="dashboard-card dashboard-users">
                        <div class="dashboard-card-header">
                            <h3>Últimos Usuários</h3>
                        </div>
                        <div class="dashboard-card-body user-list-modern">
                            <?php foreach ($ultimosUsuarios as $usuario): ?>
                            <div class="user-item-modern">
                                <div class="user-avatar-modern">
                                    <img src="<?php echo $usuario['avatar']; ?>" alt="<?php echo htmlspecialchars($usuario['nome']); ?>">
                                </div>
                                <div class="user-info-modern">
                                    <span class="user-name"><strong><?php echo htmlspecialchars($usuario['nome']); ?></strong></span>
                                    <span class="user-email"><?php echo htmlspecialchars($usuario['email']); ?></span>
                                    <span class="user-date">Registrado em: <?php echo date('d/m/Y', strtotime($usuario['data_registro'])); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuário <strong id="deleteUserName"></strong>?</p>
                    <p class="text-danger">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Excluir</button>
                </div>
            </div>
        </div>
    </div>

<?php
// Incluir template de rodapé
include_once 'templates/footer.php';
?> 