<?php
/**
 * Página de Filmes - Site de Filmes e Series
 * 
 * Exibe o catálogo de filmes disponíveis na plataforma
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

// Em uma aplicação real, buscar dados do banco de dados
// Simulação de filmes populares
$filmes_populares = [
    [
        'id' => 'film1',
        'titulo' => 'A Jornada Épica',
        'imagem' => 'media/thumbnails/episode1.jpg',
        'ano' => '2023',
        'classificacao' => '14',
        'generos' => ['Ação', 'Aventura']
    ],
    [
        'id' => 'film2',
        'titulo' => 'Mistério nas Sombras',
        'imagem' => 'media/thumbnails/episode2.jpg',
        'ano' => '2022',
        'classificacao' => '16',
        'generos' => ['Suspense', 'Terror']
    ],
    [
        'id' => 'film3',
        'titulo' => 'Amor em Paris',
        'imagem' => 'media/thumbnails/episode3.jpg',
        'ano' => '2021',
        'classificacao' => '12',
        'generos' => ['Romance', 'Comédia']
    ],
    [
        'id' => 'film4',
        'titulo' => 'Batalha Final',
        'imagem' => 'media/thumbnails/episode4.jpg',
        'ano' => '2023',
        'classificacao' => '18',
        'generos' => ['Ação', 'Ficção Científica']
    ],
    [
        'id' => 'film5',
        'titulo' => 'Legado de Heróis',
        'imagem' => 'media/thumbnails/episode1.jpg',
        'ano' => '2022',
        'classificacao' => '14',
        'generos' => ['Aventura', 'Fantasia']
    ],
    [
        'id' => 'film6',
        'titulo' => 'Redenção',
        'imagem' => 'media/thumbnails/episode2.jpg',
        'ano' => '2023',
        'classificacao' => '16',
        'generos' => ['Drama', 'Ação']
    ]
];

// Simulação de filmes recentes
$filmes_recentes = [
    [
        'id' => 'newfilm1',
        'titulo' => 'Horizontes Perdidos',
        'imagem' => 'media/thumbnails/episode3.jpg',
        'ano' => '2023',
        'classificacao' => '14',
        'generos' => ['Aventura', 'Ficção Científica']
    ],
    [
        'id' => 'newfilm2',
        'titulo' => 'Conspiração Global',
        'imagem' => 'media/thumbnails/episode4.jpg',
        'ano' => '2023',
        'classificacao' => '16',
        'generos' => ['Ação', 'Suspense']
    ],
    [
        'id' => 'newfilm3',
        'titulo' => 'Vidas Cruzadas',
        'imagem' => 'media/thumbnails/episode1.jpg',
        'ano' => '2023',
        'classificacao' => '12',
        'generos' => ['Drama', 'Romance']
    ],
    [
        'id' => 'newfilm4',
        'titulo' => 'Labirinto de Memórias',
        'imagem' => 'media/thumbnails/episode2.jpg',
        'ano' => '2023',
        'classificacao' => '14',
        'generos' => ['Suspense', 'Mistério']
    ]
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmes - Site de Filmes e Series</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="css/home.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <div class="logo-container">
                <a href="index.php" class="logo">
                    <span class="logo-text">Site de Filmes e Series</span>
                </a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="filmes.php" class="active">Filmes</a></li>
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
                    <input type="text" placeholder="Pesquisar filmes...">
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
    
    <div class="page-header">
        <div class="container">
            <h1>Filmes</h1>
            <div class="filter-options">
                <select id="genre-filter">
                    <option value="">Todos os Gêneros</option>
                    <option value="acao">Ação</option>
                    <option value="aventura">Aventura</option>
                    <option value="comedia">Comédia</option>
                    <option value="drama">Drama</option>
                    <option value="ficcao">Ficção Científica</option>
                    <option value="romance">Romance</option>
                    <option value="terror">Terror</option>
                </select>
                <select id="year-filter">
                    <option value="">Todos os Anos</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="2021">2021</option>
                    <option value="2020">2020</option>
                </select>
                <select id="sort-by">
                    <option value="recent">Mais Recentes</option>
                    <option value="name">Nome A-Z</option>
                    <option value="rating">Melhor Avaliação</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Filmes Populares -->
    <section class="content-section popular">
        <div class="section-container">
            <div class="section-header">
                <h2>Filmes Populares</h2>
                <a href="populares.php?tipo=filmes" class="view-all">Ver todos <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="content-grid">
                <?php foreach ($filmes_populares as $filme): ?>
                <div class="content-card">
                    <a href="player.php?video=<?php echo $filme['id']; ?>" class="card-thumbnail">
                        <img src="<?php echo $filme['imagem']; ?>" alt="<?php echo htmlspecialchars($filme['titulo']); ?>">
                        <div class="play-overlay">
                            <i class="fas fa-play"></i>
                        </div>
                    </a>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($filme['titulo']); ?></h3>
                        <div class="meta-info">
                            <span class="year"><?php echo htmlspecialchars($filme['ano']); ?></span>
                            <span class="rating"><?php echo htmlspecialchars($filme['classificacao']); ?>+</span>
                        </div>
                        <div class="genre-tags">
                            <?php foreach ($filme['generos'] as $genero): ?>
                            <span class="genre"><?php echo htmlspecialchars($genero); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Filmes Recentes -->
    <section class="content-section recent">
        <div class="section-container">
            <div class="section-header">
                <h2>Adicionados Recentemente</h2>
                <a href="recentes.php?tipo=filmes" class="view-all">Ver todos <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="content-grid">
                <?php foreach ($filmes_recentes as $filme): ?>
                <div class="content-card">
                    <a href="player.php?video=<?php echo $filme['id']; ?>" class="card-thumbnail">
                        <img src="<?php echo $filme['imagem']; ?>" alt="<?php echo htmlspecialchars($filme['titulo']); ?>">
                        <div class="play-overlay">
                            <i class="fas fa-play"></i>
                        </div>
                        <span class="new-tag">NOVO</span>
                    </a>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($filme['titulo']); ?></h3>
                        <div class="meta-info">
                            <span class="year"><?php echo htmlspecialchars($filme['ano']); ?></span>
                            <span class="rating"><?php echo htmlspecialchars($filme['classificacao']); ?>+</span>
                        </div>
                        <div class="genre-tags">
                            <?php foreach ($filme['generos'] as $genero): ?>
                            <span class="genre"><?php echo htmlspecialchars($genero); ?></span>
                            <?php endforeach; ?>
                        </div>
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
                <span class="logo-text">Site de Filmes e Series</span>
                <p>Sua plataforma de streaming personalizada</p>
            </div>
            
            <div class="footer-links">
                <div class="footer-col">
                    <h4>Navegar</h4>
                    <ul>
                        <li><a href="index.php">Início</a></li>
                        <li><a href="filmes.php">Filmes</a></li>
                        <li><a href="series.php">Séries</a></li>
                        <li><a href="generos.php">Gêneros</a></li>
                        <li><a href="novidades.php">Novidades</a></li>
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
            <p>&copy; <?php echo date('Y'); ?> Site de Filmes e Series. Todos os direitos reservados.</p>
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
            
            // Filtragem de filmes
            const genreFilter = document.getElementById('genre-filter');
            const yearFilter = document.getElementById('year-filter');
            const sortBy = document.getElementById('sort-by');
            
            // Implemente a lógica de filtro aqui
            if (genreFilter && yearFilter && sortBy) {
                const filters = [genreFilter, yearFilter, sortBy];
                
                filters.forEach(filter => {
                    filter.addEventListener('change', function() {
                        // Em um sistema real, isso enviaria uma solicitação AJAX
                        // para obter os resultados filtrados
                        console.log('Filtros atualizados:', {
                            genre: genreFilter.value,
                            year: yearFilter.value,
                            sort: sortBy.value
                        });
                    });
                });
            }
        });
    </script>
</body>
</html> 