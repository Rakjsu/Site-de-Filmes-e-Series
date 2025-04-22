<?php
/**
 * Template Header
 * 
 * Cabeçalho comum para todas as páginas do site.
 * 
 * @param string $pageTitle Título da página
 * @param string $bodyClass Classes CSS para o body
 * @param array $additionalCSS Array com caminhos para CSS adicionais
 * @param string $headerScripts Scripts adicionais no cabeçalho
 */

// Valores padrão
$pageTitle = $pageTitle ?? 'Site de Filmes e Series';
$bodyClass = $bodyClass ?? '';
$additionalCSS = $additionalCSS ?? [];
$headerScripts = $headerScripts ?? '';

// Verificar autenticação do usuário
$isLoggedIn = isset($isLoggedIn) ? $isLoggedIn : (function_exists('isAuthenticated') ? isAuthenticated() : false);
$userName = $isLoggedIn && isset($_SESSION['user_data']['username']) ? $_SESSION['user_data']['username'] : '';
$isAdmin = $isLoggedIn && isset($_SESSION['user_data']['is_admin']) ? $_SESSION['user_data']['is_admin'] : false;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Fontes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS - Animate On Scroll -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    
    <!-- Estilos Base -->
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    
    <!-- Estilos específicos da página -->
    <?php foreach ($additionalCSS as $css): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
    <?php endforeach; ?>
    
    <!-- Scripts do cabeçalho -->
    <?php if (!empty($headerScripts)): ?>
    <?php echo $headerScripts; ?>
    <?php endif; ?>
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
    
    <!-- Styles -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/carrossel.css">
    
    <!-- Scripts -->
    <script src="js/carrossel.js" defer></script>
    <script src="js/main.js" defer></script>
</head>
<body class="<?php echo htmlspecialchars($bodyClass); ?>">
    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-content">
            <div class="preloader-spinner"></div>
        </div>
    </div>
    
    <!-- Cabeçalho -->
    <header class="main-header">
        <div class="header-container">
            <div class="logo-container">
                <a href="index.php" class="logo">
                    <img src="assets/logo.png" alt="Site de Filmes e Series">
                </a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Início</a></li>
                    <li><a href="filmes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'filmes.php' ? 'active' : ''; ?>">Filmes</a></li>
                    <li><a href="series.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'series.php' ? 'active' : ''; ?>">Series</a></li>
                    <li><a href="categorias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categorias.php' ? 'active' : ''; ?>">Categorias</a></li>
                    <li><a href="novidades.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'novidades.php' ? 'active' : ''; ?>">Novidades</a></li>
                    <?php if (isset($isAdmin) && $isAdmin): ?>
                    <li><a href="admin/" class="<?php echo strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? 'active' : ''; ?>">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="header-right">
                <form class="search-box" action="pesquisa.php" method="get">
                    <input type="text" name="q" placeholder="Pesquisar..." aria-label="Pesquisar">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                
                <?php if (isset($isLoggedIn) && $isLoggedIn): ?>
                <div class="user-menu">
                    <button type="button" class="user-btn">
                        <i class="fas fa-user-circle"></i>
                        <span class="username"><?php echo htmlspecialchars($userName); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown">
                        <ul>
                            <li><a href="perfil.php"><i class="fas fa-user"></i> Meu Perfil</a></li>
                            <li><a href="favoritos.php"><i class="fas fa-heart"></i> Favoritos</a></li>
                            <li><a href="historico.php"><i class="fas fa-history"></i> Histórico</a></li>
                            <li class="separator"></li>
                            <li><a href="config.php"><i class="fas fa-cog"></i> Configurações</a></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                        </ul>
                    </div>
                </div>
                <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="login-btn">Entrar</a>
                    <a href="registro.php" class="register-btn">Cadastrar</a>
                </div>
                <?php endif; ?>
                
                <button type="button" class="mobile-menu-btn">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Conteúdo principal -->
    <main>

<style>
/* Estilos do preloader */
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #141414;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
}

.preloader.hidden {
    opacity: 0;
    visibility: hidden;
}

.preloader-spinner {
    width: 50px;
    height: 50px;
    border: 3px solid transparent;
    border-top-color: #e50914;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.preloader-spinner:before {
    content: "";
    position: absolute;
    top: 5px;
    left: 5px;
    right: 5px;
    bottom: 5px;
    border: 3px solid transparent;
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 2s linear infinite;
}

.preloader-spinner:after {
    content: "";
    position: absolute;
    top: 15px;
    left: 15px;
    right: 15px;
    bottom: 15px;
    border: 3px solid transparent;
    border-top-color: #e50914;
    border-radius: 50%;
    animation: spin 1.5s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Estilos adicionais para o header */
.main-header {
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.main-header.scrolled {
    background-color: rgba(20, 20, 20, 0.98);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

body {
    font-family: 'Poppins', sans-serif;
}
</style> 