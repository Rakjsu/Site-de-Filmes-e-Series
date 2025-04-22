<?php
/**
 * Template do Cabeçalho Principal para o Painel Administrativo
 * Versão: 1.0.0
 */

// Dados do usuário (substituir por dados reais do banco de dados)
if (!isset($adminName)) {
    $adminName = "Administrador";
}
if (!isset($adminAvatar)) {
    $adminAvatar = "../assets/img/avatars/admin.jpg";
}

// Simular notificações (substituir por dados reais do banco de dados)
$notificacoes = [
    [
        'tipo' => 'user-plus',
        'mensagem' => 'Novo usuário registrado',
        'tempo' => 'Há 1 hora',
        'lida' => false
    ],
    [
        'tipo' => 'exclamation-circle',
        'mensagem' => 'Alerta de sistema',
        'tempo' => 'Há 2 horas',
        'lida' => false
    ],
    [
        'tipo' => 'upload',
        'mensagem' => 'Novo vídeo enviado',
        'tempo' => 'Há 1 dia',
        'lida' => true
    ]
];
$naoLidas = array_reduce($notificacoes, function($total, $item) {
    return $total + (!$item['lida'] ? 1 : 0);
}, 0);
?>

<!-- Cabeçalho Principal -->
<header class="main-header">
    <div class="header-search">
        <form action="search.php" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Pesquisar...">
            <button type="submit">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    <div class="header-actions">
        <div class="notification-dropdown">
            <button class="notification-btn">
                <i class="fas fa-bell"></i>
                <?php if ($naoLidas > 0): ?>
                <span class="badge"><?php echo $naoLidas; ?></span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu">
                <div class="dropdown-header">
                    <h6>Notificações</h6>
                    <a href="#" class="mark-all">Marcar todas como lidas</a>
                </div>
                <div class="dropdown-content">
                    <?php foreach ($notificacoes as $notificacao): ?>
                    <a href="#" class="notification-item <?php echo !$notificacao['lida'] ? 'unread' : ''; ?>">
                        <div class="notification-icon">
                            <i class="fas fa-<?php echo $notificacao['tipo']; ?>"></i>
                        </div>
                        <div class="notification-text">
                            <p><?php echo $notificacao['mensagem']; ?></p>
                            <span><?php echo $notificacao['tempo']; ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div class="dropdown-footer">
                    <a href="notificacoes.php">Ver todas</a>
                </div>
            </div>
        </div>
        <div class="user-dropdown">
            <button class="user-btn">
                <img src="<?php echo $adminAvatar; ?>" alt="Admin Avatar">
                <span><?php echo $adminName; ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu">
                <a href="perfil.php" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>Meu Perfil</span>
                </a>
                <a href="configuracoes.php" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="../logout.php" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </div>
        </div>
        <button id="theme-toggle-header" class="theme-toggle-btn" title="Alternar tema">
            <i class="fas fa-moon"></i>
            <i class="fas fa-sun"></i>
        </button>
        <button class="mobile-search-btn">
            <i class="fas fa-search"></i>
        </button>
    </div>
</header>

<!-- Breadcrumb e Alertas -->
<div class="page-header">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <?php if (isset($breadcrumbs)): ?>
        <ol class="breadcrumb-list">
            <li class="breadcrumb-item">
                <a href="index.php">
                    <i class="fas fa-home"></i>
                </a>
            </li>
            <?php foreach ($breadcrumbs as $label => $link): ?>
            <?php if ($link): ?>
            <li class="breadcrumb-item">
                <a href="<?php echo $link; ?>"><?php echo $label; ?></a>
            </li>
            <?php else: ?>
            <li class="breadcrumb-item active"><?php echo $label; ?></li>
            <?php endif; ?>
            <?php endforeach; ?>
        </ol>
        <?php endif; ?>
    </div>

    <!-- Alerta de sistema -->
    <?php if (isset($systemMessage)): ?>
    <div class="alert alert-<?php echo $systemMessage['type']; ?>" role="alert">
        <?php echo $systemMessage['message']; ?>
        <?php if (!empty($systemMessage['details'])): ?>
        <p class="alert-details"><?php echo $systemMessage['details']; ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div> 