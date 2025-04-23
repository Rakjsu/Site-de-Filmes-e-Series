<?php
/**
 * Template da Sidebar para o Painel Administrativo
 * Versão: 1.0.0
 */

// Determinar a página atual
$currentPage = basename($_SERVER['PHP_SELF']);

// Função para verificar a página ativa
function isActive($page) {
    global $currentPage;
    return ($currentPage == $page) ? 'active' : '';
}

// Dados do usuário (substituir por dados reais do banco de dados)
$adminName = "Administrador";
$adminRole = "Super Admin";
$adminAvatar = "../assets/img/avatars/admin.jpg";
?>

<!-- Sidebar -->
<aside class="admin-sidebar">
    <!-- Logo e Toggle -->
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-logo">
            <img src="../assets/img/logo-admin.png" alt="Logo Admin" class="logo-lg">
            <img src="../assets/img/logo-icon.png" alt="Logo Icon" class="logo-sm">
        </a>
        <button id="sidebar-toggle" class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Perfil do Usuário -->
    <div class="sidebar-profile">
        <div class="profile-avatar">
            <img src="<?php echo $adminAvatar; ?>" alt="Admin Avatar">
        </div>
        <div class="profile-info">
            <h5><?php echo $adminName; ?></h5>
            <p><?php echo $adminRole; ?></p>
        </div>
    </div>

    <!-- Menu de Navegação -->
    <nav class="sidebar-nav">
        <div class="nav-section">
            <h6 class="nav-section-title">CONTEÚDO</h6>
            <ul class="nav-list">
                <li>
                    <a href="filmes.php" class="nav-link <?php echo isActive('filmes.php'); ?>">
                        <i class="fas fa-film"></i>
                        <span>Filmes</span>
                    </a>
                </li>
                <li>
                    <a href="series.php" class="nav-link <?php echo isActive('series.php'); ?>">
                        <i class="fas fa-tv"></i>
                        <span>Séries</span>
                    </a>
                </li>
                <li>
                    <a href="generos.php" class="nav-link <?php echo isActive('generos.php'); ?>">
                        <i class="fas fa-tags"></i>
                        <span>Gênero</span>
                    </a>
                </li>
                <li>
                    <a href="atores.php" class="nav-link <?php echo isActive('atores.php'); ?>">
                        <i class="fas fa-user-friends"></i>
                        <span>Atores</span>
                    </a>
                </li>
                <li>
                    <a href="diretores.php" class="nav-link <?php echo isActive('diretores.php'); ?>">
                        <i class="fas fa-video"></i>
                        <span>Diretores</span>
                    </a>
                </li>
                <li>
                    <a href="escritores.php" class="nav-link <?php echo isActive('escritores.php'); ?>">
                        <i class="fas fa-pen-nib"></i>
                        <span>Escritores</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="nav-section">
            <h6 class="nav-section-title">ADMINISTRAÇÃO</h6>
            <ul class="nav-list">
                <li>
                    <a href="usuarios.php" class="nav-link <?php echo isActive('usuarios.php'); ?>">
                        <i class="fas fa-user"></i>
                        <span>Usuário</span>
                    </a>
                </li>
                <li>
                    <a href="estatisticas.php" class="nav-link <?php echo isActive('estatisticas.php'); ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>Estatísticas</span>
                    </a>
                </li>
                <li>
                    <a href="logs.php" class="nav-link <?php echo isActive('logs.php'); ?>">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Logs</span>
                    </a>
                </li>
                <li>
                    <a href="configuracoes.php" class="nav-link <?php echo isActive('configuracoes.php'); ?>">
                        <i class="fas fa-cogs"></i>
                        <span>Configurações</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="theme-toggle">
            <button id="theme-toggle-btn" class="toggle-btn">
                <i class="fas fa-moon"></i>
                <i class="fas fa-sun"></i>
                <span>Alternar Tema</span>
            </button>
        </div>
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Sair</span>
        </a>
    </div>
</aside> 