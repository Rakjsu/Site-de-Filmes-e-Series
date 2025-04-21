<?php
/**
 * Página Inicial - Player
 * 
 * Página principal do sistema de streaming de vídeos
 * com funcionalidades de reprodução e navegação.
 * 
 * @version 1.0.0
 */

// Iniciar sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivo de autenticação
require_once 'auth.php';

// Verificar se o usuário está autenticado
$isLoggedIn = isAuthenticated();
$userName = $isLoggedIn && isset($_SESSION['user_data']['username']) ? $_SESSION['user_data']['username'] : '';
$isAdmin = $isLoggedIn && isset($_SESSION['user_data']['is_admin']) ? $_SESSION['user_data']['is_admin'] : false;

// Categorias populares (em produção viriam do banco de dados)
$categorias = [
    ['id' => 1, 'nome' => 'Filmes', 'icone' => 'fa-film'],
    ['id' => 2, 'nome' => 'Séries', 'icone' => 'fa-tv'],
    ['id' => 3, 'nome' => 'Documentários', 'icone' => 'fa-book'],
    ['id' => 4, 'nome' => 'Esportes', 'icone' => 'fa-futbol'],
    ['id' => 5, 'nome' => 'Música', 'icone' => 'fa-music'],
    ['id' => 6, 'nome' => 'Infantil', 'icone' => 'fa-child']
];

// Conteúdo destacado (em produção viria do banco de dados)
$conteudoDestaque = [
    'id' => 'destaque1',
    'titulo' => 'O Caminho do Herói',
    'descricao' => 'Uma jornada épica através de mundos fantásticos, onde um jovem descobre seu verdadeiro destino.',
    'imagem' => 'media/thumbnails/destaque.jpg',
    'video_url' => 'media/videos/episode1.m3u8',
    'tipo' => 'Série',
    'ano' => '2023',
    'classificacao' => '14',
    'temporadas' => 2
];

// Conteúdos populares (em produção viriam do banco de dados)
$conteudosPopulares = [
    [
        'id' => 'pop1', 
        'titulo' => 'O Início', 
        'imagem' => 'media/thumbnails/episode1.jpg',
        'tipo' => 'Episódio 1'
    ],
    [
        'id' => 'pop2', 
        'titulo' => 'O Despertar', 
        'imagem' => 'media/thumbnails/episode2.jpg',
        'tipo' => 'Episódio 2'
    ],
    [
        'id' => 'pop3', 
        'titulo' => 'A Jornada', 
        'imagem' => 'media/thumbnails/episode3.jpg',
        'tipo' => 'Episódio 3'
    ],
    [
        'id' => 'pop4', 
        'titulo' => 'Alianças', 
        'imagem' => 'media/thumbnails/episode4.jpg',
        'tipo' => 'Episódio 4'
    ],
    [
        'id' => 'pop5', 
        'titulo' => 'A Revelação', 
        'imagem' => 'media/thumbnails/episode1.jpg',
        'tipo' => 'Episódio 5'
    ],
    [
        'id' => 'pop6', 
        'titulo' => 'O Confronto', 
        'imagem' => 'media/thumbnails/episode2.jpg',
        'tipo' => 'Episódio 6'
    ]
];

// Conteúdos recentemente adicionados (em produção viriam do banco de dados)
$conteudosRecentes = [
    [
        'id' => 'rec1', 
        'titulo' => 'Novo Horizonte', 
        'imagem' => 'media/thumbnails/episode3.jpg',
        'tipo' => 'Filme'
    ],
    [
        'id' => 'rec2', 
        'titulo' => 'Além das Estrelas', 
        'imagem' => 'media/thumbnails/episode4.jpg',
        'tipo' => 'Série'
    ],
    [
        'id' => 'rec3', 
        'titulo' => 'Mistérios do Oceano', 
        'imagem' => 'media/thumbnails/episode1.jpg',
        'tipo' => 'Documentário'
    ],
    [
        'id' => 'rec4', 
        'titulo' => 'Final Decisivo', 
        'imagem' => 'media/thumbnails/episode2.jpg',
        'tipo' => 'Esporte'
    ],
    [
        'id' => 'rec5', 
        'titulo' => 'A Grande Sinfonia', 
        'imagem' => 'media/thumbnails/episode3.jpg',
        'tipo' => 'Música'
    ],
    [
        'id' => 'rec6', 
        'titulo' => 'Aventuras Mágicas', 
        'imagem' => 'media/thumbnails/episode4.jpg',
        'tipo' => 'Infantil'
    ]
];

// Continuar assistindo (para usuários logados)
$continuarAssistindo = [];
if ($isLoggedIn) {
    $continuarAssistindo = [
        [
            'id' => 'cont1', 
            'titulo' => 'O Início', 
            'imagem' => 'media/thumbnails/episode1.jpg',
            'progresso' => 75,
            'tempo_restante' => '15 min'
        ],
        [
            'id' => 'cont2', 
            'titulo' => 'Além das Estrelas', 
            'imagem' => 'media/thumbnails/episode4.jpg',
            'progresso' => 45,
            'tempo_restante' => '32 min'
        ],
        [
            'id' => 'cont3', 
            'titulo' => 'Mistérios do Oceano', 
            'imagem' => 'media/thumbnails/episode1.jpg',
            'progresso' => 60,
            'tempo_restante' => '22 min'
        ],
        [
            'id' => 'cont4', 
            'titulo' => 'A Revelação', 
            'imagem' => 'media/thumbnails/episode1.jpg',
            'progresso' => 30,
            'tempo_restante' => '42 min'
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlayerJS - Streaming de Vídeos</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="css/home.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="home-page">
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <div class="logo-container">
                <a href="index.php" class="logo">
                    <img src="assets/images/logo.png" alt="PlayerJS Logo">
                </a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="active">Início</a></li>
                    <li><a href="filmes.php">Filmes</a></li>
                    <li><a href="series.php">Séries</a></li>
                    <li><a href="generos.php">Gêneros</a></li>
                    <li><a href="novidades.php">Novidades</a></li>
                    <?php if ($isLoggedIn && $isAdmin): ?>
                    <li><a href="admin/index.php">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="header-right">
                <div class="search-box">
                    <input type="text" placeholder="Pesquisar...">
                    <button><i class="fas fa-search"></i></button>
                </div>
                
                <?php if ($isLoggedIn): ?>
                <div class="user-menu">
                    <button class="user-btn">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($userName); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown">
                        <ul>
                            <li><a href="perfil.php"><i class="fas fa-user"></i> Meu Perfil</a></li>
                            <li><a href="favoritos.php"><i class="fas fa-heart"></i> Favoritos</a></li>
                            <li><a href="historico.php"><i class="fas fa-history"></i> Histórico</a></li>
                            <li><a href="configuracoes.php"><i class="fas fa-cog"></i> Configurações</a></li>
                            <?php if (isAdmin()): ?>
                            <li><a href="admin/index.php"><i class="fas fa-user-shield"></i> Painel Admin</a></li>
                            <?php endif; ?>
                            <li class="separator"></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                        </ul>
                    </div>
                </div>
                <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="login-btn">Entrar</a>
                    <a href="register.php" class="register-btn">Cadastrar</a>
                </div>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    
    <!-- Hero Banner -->
    <section class="hero-banner" style="background-image: url('<?php echo $conteudoDestaque['imagem']; ?>');">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-info">
                <span class="content-type"><?php echo htmlspecialchars($conteudoDestaque['tipo']); ?></span>
                <h1><?php echo htmlspecialchars($conteudoDestaque['titulo']); ?></h1>
                <div class="content-meta">
                    <span class="year"><?php echo htmlspecialchars($conteudoDestaque['ano']); ?></span>
                    <span class="rating">
                        <i class="fas fa-star"></i>
                        <?php echo htmlspecialchars($conteudoDestaque['classificacao']); ?>+
                    </span>
                    <span class="seasons">
                        <i class="fas fa-tv"></i>
                        <?php echo htmlspecialchars($conteudoDestaque['temporadas']); ?> Temporadas
                    </span>
                </div>
                <p class="description"><?php echo htmlspecialchars($conteudoDestaque['descricao']); ?></p>
                <div class="hero-buttons">
                    <a href="player.php?video=<?php echo $conteudoDestaque['id']; ?>" class="watch-btn">
                        <i class="fas fa-play"></i> Assistir
                    </a>
                    <a href="detalhes.php?id=<?php echo $conteudoDestaque['id']; ?>" class="info-btn">
                        <i class="fas fa-info-circle"></i> Mais Informações
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Categorias -->
    <section class="categories">
        <div class="section-container">
            <div class="categories-grid">
                <?php foreach ($categorias as $categoria): ?>
                <a href="categoria.php?id=<?php echo $categoria['id']; ?>" class="category-card">
                    <div class="category-icon">
                        <i class="fas <?php echo $categoria['icone']; ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($categoria['nome']); ?></h3>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Conteúdo Popular -->
    <section class="content-section popular">
        <div class="section-container">
            <div class="section-header">
                <h2>Populares</h2>
                <a href="populares.php" class="view-all">Ver todos <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="content-slider">
                <?php foreach ($conteudosPopulares as $conteudo): ?>
                <div class="content-card">
                    <a href="player.php?video=<?php echo $conteudo['id']; ?>" class="card-thumbnail">
                        <img src="<?php echo $conteudo['imagem']; ?>" alt="<?php echo htmlspecialchars($conteudo['titulo']); ?>">
                        <div class="play-overlay">
                            <i class="fas fa-play"></i>
                        </div>
                    </a>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($conteudo['titulo']); ?></h3>
                        <span class="content-type"><?php echo htmlspecialchars($conteudo['tipo']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <?php if (!empty($continuarAssistindo)): ?>
    <!-- Continuar Assistindo -->
    <section class="content-section continue-watching">
        <div class="section-container">
            <div class="section-header">
                <h2>Continuar Assistindo</h2>
                <a href="historico.php" class="view-all">Ver histórico <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="content-slider">
                <?php foreach ($continuarAssistindo as $conteudo): ?>
                <div class="content-card">
                    <a href="player.php?video=<?php echo $conteudo['id']; ?>" class="card-thumbnail">
                        <img src="<?php echo $conteudo['imagem']; ?>" alt="<?php echo htmlspecialchars($conteudo['titulo']); ?>">
                        <div class="play-overlay">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo $conteudo['progresso']; ?>%;"></div>
                        </div>
                        <span class="time-remaining"><?php echo $conteudo['tempo_restante']; ?> restantes</span>
                    </a>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($conteudo['titulo']); ?></h3>
                        <div class="resume-button">
                            <i class="fas fa-redo"></i> Continuar
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Adicionados Recentemente -->
    <section class="content-section recently-added">
        <div class="section-container">
            <div class="section-header">
                <h2>Adicionados Recentemente</h2>
                <a href="recentes.php" class="view-all">Ver todos <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="content-slider">
                <?php foreach ($conteudosRecentes as $conteudo): ?>
                <div class="content-card">
                    <a href="player.php?video=<?php echo $conteudo['id']; ?>" class="card-thumbnail">
                        <img src="<?php echo $conteudo['imagem']; ?>" alt="<?php echo htmlspecialchars($conteudo['titulo']); ?>">
                        <div class="play-overlay">
                            <i class="fas fa-play"></i>
                        </div>
                        <span class="new-tag">NOVO</span>
                    </a>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($conteudo['titulo']); ?></h3>
                        <span class="content-type"><?php echo htmlspecialchars($conteudo['tipo']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-logo">
                <img src="assets/images/logo.png" alt="PlayerJS Logo">
                <p>Sua plataforma de streaming personalizada</p>
            </div>
            
            <div class="footer-links">
                <div class="footer-col">
                    <h4>Navegar</h4>
                    <ul>
                        <li><a href="index.php">Início</a></li>
                        <li><a href="categorias.php">Categorias</a></li>
                        <li><a href="novidades.php">Novidades</a></li>
                        <li><a href="player.php">Player</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Ajuda</h4>
                    <ul>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="contato.php">Contato</a></li>
                        <li><a href="termos.php">Termos de Uso</a></li>
                        <li><a href="privacidade.php">Privacidade</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Conta</h4>
                    <ul>
                        <?php if ($isLoggedIn): ?>
                        <li><a href="perfil.php">Meu Perfil</a></li>
                        <li><a href="favoritos.php">Favoritos</a></li>
                        <li><a href="historico.php">Histórico</a></li>
                        <li><a href="logout.php">Sair</a></li>
                        <?php else: ?>
                        <li><a href="login.php">Entrar</a></li>
                        <li><a href="register.php">Cadastrar</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <div class="footer-social">
                <h4>Siga-nos</h4>
                <div class="social-icons">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> PlayerJS. Todos os direitos reservados.</p>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Menu móvel
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const mainNav = document.querySelector('.main-nav');
            
            if (mobileMenuBtn && mainNav) {
                mobileMenuBtn.addEventListener('click', function() {
                    this.classList.toggle('active');
                    mainNav.classList.toggle('active');
                });
            }
            
            // Menu do usuário
            const userBtn = document.querySelector('.user-btn');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userBtn && userDropdown) {
                userBtn.addEventListener('click', function() {
                    userDropdown.classList.toggle('active');
                });
                
                // Fechar dropdown quando clicar fora
                document.addEventListener('click', function(event) {
                    if (!userBtn.contains(event.target) && !userDropdown.contains(event.target)) {
                        userDropdown.classList.remove('active');
                    }
                });
            }
        });
    </script>
</body>
</html> 