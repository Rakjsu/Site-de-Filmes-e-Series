<?php
/**
 * Página de Filmes - Site de Filmes e Series
 * 
 * Exibe listagem de filmes e player para assistir filme específico.
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

// Verificar se estamos em modo de visualização de filme específico ou listagem
$viewMode = 'list'; // Padrão: listar filmes
$movieId = null;

// Verificar parâmetros na URL
if (isset($_GET['id'])) {
    $movieId = intval($_GET['id']);
    $viewMode = 'player';
}

// Dados de exemplo (em produção, estes dados viriam do banco de dados)
$movies = [
    [
        'id' => 1,
        'title' => 'Aventura Épica',
        'description' => 'Uma jornada épica através de mundos fantásticos, onde um jovem descobre seu verdadeiro destino.',
        'poster' => 'media/thumbnails/episode1.jpg',
        'backdrop' => 'media/thumbnails/backdrop1.jpg',
        'year' => 2023,
        'duration' => 125,
        'rating' => 4.5,
        'categories' => ['Ação', 'Aventura', 'Fantasia'],
        'video_url' => 'media/videos/episode1.m3u8'
    ],
    [
        'id' => 2,
        'title' => 'Mistérios do Oceano',
        'description' => 'Uma equipe de cientistas desvenda os segredos nas profundezas do oceano, encontrando criaturas nunca antes vistas.',
        'poster' => 'media/thumbnails/episode2.jpg',
        'backdrop' => 'media/thumbnails/backdrop2.jpg',
        'year' => 2022,
        'duration' => 110,
        'rating' => 4.2,
        'categories' => ['Documentário', 'Ciência', 'Natureza'],
        'video_url' => 'media/videos/episode2.m3u8'
    ],
    [
        'id' => 3,
        'title' => 'Além das Estrelas',
        'description' => 'Uma aventura espacial que explora os confins do universo e os limites da humanidade.',
        'poster' => 'media/thumbnails/episode3.jpg',
        'backdrop' => 'media/thumbnails/backdrop3.jpg',
        'year' => 2021,
        'duration' => 140,
        'rating' => 4.8,
        'categories' => ['Ficção Científica', 'Drama', 'Aventura'],
        'video_url' => 'media/videos/episode3.m3u8'
    ],
    [
        'id' => 4,
        'title' => 'O Último Confronto',
        'description' => 'Em um mundo pós-apocalíptico, um guerreiro solitário enfrenta seu último desafio para trazer paz à sua terra.',
        'poster' => 'media/thumbnails/episode4.jpg',
        'backdrop' => 'media/thumbnails/backdrop4.jpg',
        'year' => 2023,
        'duration' => 135,
        'rating' => 4.3,
        'categories' => ['Ação', 'Ficção Científica', 'Drama'],
        'video_url' => 'media/videos/episode4.m3u8'
    ],
    [
        'id' => 5,
        'title' => 'Sonhos de Liberdade',
        'description' => 'A história emocionante de um homem que busca a liberdade após ser injustamente preso por um crime que não cometeu.',
        'poster' => 'media/thumbnails/episode1.jpg',
        'backdrop' => 'media/thumbnails/backdrop1.jpg',
        'year' => 2022,
        'duration' => 150,
        'rating' => 4.9,
        'categories' => ['Drama', 'Crime', 'História'],
        'video_url' => 'media/videos/episode1.m3u8'
    ],
    [
        'id' => 6,
        'title' => 'Risadas Infinitas',
        'description' => 'Uma comédia hilariante que acompanha as desventuras de um grupo de amigos em uma viagem de férias desastrosa.',
        'poster' => 'media/thumbnails/episode2.jpg',
        'backdrop' => 'media/thumbnails/backdrop2.jpg',
        'year' => 2023,
        'duration' => 95,
        'rating' => 4.0,
        'categories' => ['Comédia', 'Aventura'],
        'video_url' => 'media/videos/episode2.m3u8'
    ]
];

// Buscar filme específico se estiver em modo de player
$currentMovie = null;
if ($movieId) {
    foreach ($movies as $m) {
        if ($m['id'] == $movieId) {
            $currentMovie = $m;
            break;
        }
    }
}

// Filmes sugeridos para exibir após o atual (quando em modo player)
$suggestedMovies = [];
if ($viewMode == 'player' && $currentMovie) {
    // Pegar filmes da mesma categoria
    $matchingCategories = $currentMovie['categories'];
    
    foreach ($movies as $movie) {
        if ($movie['id'] != $currentMovie['id']) {
            // Verificar se há categorias em comum
            $hasCommonCategory = false;
            foreach ($movie['categories'] as $category) {
                if (in_array($category, $matchingCategories)) {
                    $hasCommonCategory = true;
                    break;
                }
            }
            
            if ($hasCommonCategory) {
                $suggestedMovies[] = $movie;
                if (count($suggestedMovies) >= 3) {
                    break; // Limitar a 3 sugestões
                }
            }
        }
    }
    
    // Se não tiver sugestões suficientes, adicionar filmes aleatórios
    if (count($suggestedMovies) < 3) {
        $needed = 3 - count($suggestedMovies);
        $currentIds = [$currentMovie['id']];
        foreach ($suggestedMovies as $s) {
            $currentIds[] = $s['id'];
        }
        
        foreach ($movies as $movie) {
            if (!in_array($movie['id'], $currentIds)) {
                $suggestedMovies[] = $movie;
                if (count($suggestedMovies) >= 3) {
                    break;
                }
            }
        }
    }
}

// Configurar variáveis para o template
$pageTitle = 'Filmes - Site de Filmes e Series';
if ($viewMode == 'player' && $currentMovie) {
    $pageTitle = $currentMovie['title'] . ' - Site de Filmes e Series';
}

$bodyClass = 'filmes-page';
if ($viewMode == 'player') {
    $bodyClass .= ' player-page';
}

$additionalCSS = ['css/home.css'];
if ($viewMode == 'player') {
    $additionalCSS[] = 'css/player.css';
}

$headerScripts = '';
$footerScripts = '';

if ($viewMode == 'list') {
    $footerScripts .= '<script src="js/carrossel.js"></script>';
}

if ($viewMode == 'player') {
    $footerScripts .= '<script src="js/player.js"></script>';
    if ($isLoggedIn) {
        $footerScripts .= '<script src="js/user-preferences.js"></script>';
    }
}

// Incluir o cabeçalho
include 'includes/templates/header.php';
?>

<?php if ($viewMode == 'list'): ?>
<!-- Listagem de Filmes -->
<section class="filmes-listing-section">
    <div class="container">
        <h1 class="page-title">Filmes</h1>
        
        <div class="filter-bar">
            <div class="filter-group">
                <label for="genre-filter">Gênero:</label>
                <select id="genre-filter" class="filter-select">
                    <option value="all">Todos</option>
                    <option value="action">Ação</option>
                    <option value="adventure">Aventura</option>
                    <option value="comedy">Comédia</option>
                    <option value="drama">Drama</option>
                    <option value="scifi">Ficção Científica</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="year-filter">Ano:</label>
                <select id="year-filter" class="filter-select">
                    <option value="all">Todos</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="2021">2021</option>
                    <option value="2020">2020</option>
                    <option value="older">Anterior</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="sort-filter">Ordenar por:</label>
                <select id="sort-filter" class="filter-select">
                    <option value="popular">Popularidade</option>
                    <option value="recent">Mais Recentes</option>
                    <option value="rating">Avaliação</option>
                    <option value="az">A-Z</option>
                </select>
            </div>
        </div>
        
        <div class="filmes-grid">
            <?php foreach ($movies as $movie): ?>
            <a href="filmes.php?id=<?php echo $movie['id']; ?>" class="filme-card">
                <div class="filme-poster">
                    <img src="<?php echo $movie['poster']; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                    <div class="filme-info-overlay">
                        <span class="filme-duration"><?php echo $movie['duration']; ?> min</span>
                        <span class="filme-year"><?php echo $movie['year']; ?></span>
                        <div class="filme-rating">
                            <i class="fas fa-star"></i>
                            <span><?php echo $movie['rating']; ?></span>
                        </div>
                    </div>
                </div>
                <h3 class="filme-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                <div class="filme-categories">
                    <?php echo htmlspecialchars(implode(', ', $movie['categories'])); ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <div class="pagination">
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <span>...</span>
            <a href="#">10</a>
            <a href="#" class="next">Próxima <i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
</section>

<?php elseif ($viewMode == 'player' && $currentMovie): ?>
<!-- Player de Filme -->
<div class="player-wrapper">
    <?php 
    // Configurar dados para o player
    $videoData = [
        'id' => $currentMovie['id'],
        'title' => $currentMovie['title'],
        'description' => $currentMovie['description'],
        'poster' => $currentMovie['poster'],
        'video_url' => $currentMovie['video_url'],
        'duration' => $currentMovie['duration'],
        'year' => $currentMovie['year'],
        'categories' => $currentMovie['categories']
    ];
    
    // Configurar opções adicionais
    $options = [
        'show_recommended' => true,
        'recommended_items' => array_map(function($movie) {
            return [
                'title' => $movie['title'],
                'image' => $movie['poster'],
                'url' => "filmes.php?id={$movie['id']}",
                'year' => $movie['year'],
                'duration' => $movie['duration']
            ];
        }, $suggestedMovies),
        'advanced_features' => true // Habilitar recursos avançados
    ];
    
    // Incluir o componente do player
    $type = 'movie';
    include 'includes/components/player-component.php'; 
    ?>
</div>

<?php else: ?>
<!-- Erro - Filme não encontrado -->
<section class="error-section">
    <div class="container">
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <h1>Filme não encontrado</h1>
            <p>O filme que você está procurando não foi encontrado ou não está disponível.</p>
            <a href="filmes.php" class="btn-primary">Ver todos os filmes</a>
        </div>
    </div>
</section>

<?php endif; ?>

<?php
// Incluir o rodapé
include 'includes/templates/footer.php';
?> 