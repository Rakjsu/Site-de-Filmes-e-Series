<?php
/**
 * Página de Séries - Site de Filmes e Series
 * 
 * Exibe listagem de séries e detalhes de série específica
 * com funcionalidades de reprodução integrada.
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

// Verificar se estamos em modo de visualização de série específica ou listagem
$viewMode = 'list'; // Padrão: listar séries
$seriesId = null;
$seasonId = null;
$episodeId = null;

// Verificar parâmetros na URL
if (isset($_GET['id'])) {
    $seriesId = intval($_GET['id']);
    $viewMode = 'details';
    
    if (isset($_GET['season'])) {
        $seasonId = intval($_GET['season']);
    }
    
    if (isset($_GET['episode'])) {
        $episodeId = intval($_GET['episode']);
        $viewMode = 'player';
    }
}

// Dados de exemplo (em produção, estes dados viriam do banco de dados)
$series = [
    [
        'id' => 1,
        'title' => 'O Caminho do Herói',
        'description' => 'Uma jornada épica através de mundos fantásticos, onde um jovem descobre seu verdadeiro destino.',
        'poster' => 'media/thumbnails/series1.jpg',
        'backdrop' => 'media/thumbnails/backdrop1.jpg',
        'year' => 2023,
        'seasons' => 2,
        'rating' => 4.5,
        'categories' => ['Ação', 'Aventura', 'Fantasia']
    ],
    [
        'id' => 2,
        'title' => 'Mistérios do Oceano',
        'description' => 'Uma equipe de cientistas desvenda os segredos nas profundezas do oceano, encontrando criaturas nunca antes vistas.',
        'poster' => 'media/thumbnails/series2.jpg',
        'backdrop' => 'media/thumbnails/backdrop2.jpg',
        'year' => 2022,
        'seasons' => 3,
        'rating' => 4.2,
        'categories' => ['Documentário', 'Ciência', 'Natureza']
    ],
    [
        'id' => 3,
        'title' => 'Além das Estrelas',
        'description' => 'Uma aventura espacial que explora os confins do universo e os limites da humanidade.',
        'poster' => 'media/thumbnails/series3.jpg',
        'backdrop' => 'media/thumbnails/backdrop3.jpg',
        'year' => 2021,
        'seasons' => 4,
        'rating' => 4.8,
        'categories' => ['Ficção Científica', 'Drama', 'Aventura']
    ]
];

// Buscar série específica se estiver em modo de detalhes ou player
$currentSeries = null;
if ($seriesId) {
    foreach ($series as $s) {
        if ($s['id'] == $seriesId) {
            $currentSeries = $s;
            break;
        }
    }
}

// Temporadas de exemplo para a série selecionada
$seasons = [];
if ($currentSeries) {
    for ($i = 1; $i <= $currentSeries['seasons']; $i++) {
        $seasons[] = [
            'id' => $i,
            'number' => $i,
            'title' => "Temporada $i",
            'episodes' => 10,
            'year' => $currentSeries['year'] - ($currentSeries['seasons'] - $i)
        ];
    }
}

// Selecionar temporada atual
$currentSeason = null;
if ($seasonId) {
    foreach ($seasons as $s) {
        if ($s['id'] == $seasonId) {
            $currentSeason = $s;
            break;
        }
    }
} elseif (!empty($seasons)) {
    // Selecionar primeira temporada por padrão
    $currentSeason = $seasons[0];
    $seasonId = $currentSeason['id'];
}

// Episódios de exemplo para a temporada selecionada
$episodes = [];
if ($currentSeason) {
    for ($i = 1; $i <= $currentSeason['episodes']; $i++) {
        $episodes[] = [
            'id' => $i,
            'number' => $i,
            'title' => "Episódio $i: " . ($i == 1 ? "Piloto" : "Título do Episódio $i"),
            'description' => "Descrição do episódio $i da temporada {$currentSeason['number']}.",
            'duration' => rand(25, 45),
            'thumbnail' => 'media/thumbnails/episode' . ($i % 4 + 1) . '.jpg',
            'video_url' => 'media/videos/episode' . ($i % 4 + 1) . '.m3u8'
        ];
    }
}

// Selecionar episódio atual
$currentEpisode = null;
if ($episodeId) {
    foreach ($episodes as $e) {
        if ($e['id'] == $episodeId) {
            $currentEpisode = $e;
            break;
        }
    }
} elseif ($viewMode == 'player' && !empty($episodes)) {
    // Selecionar primeiro episódio por padrão
    $currentEpisode = $episodes[0];
    $episodeId = $currentEpisode['id'];
}

// Configurar variáveis para o template
$pageTitle = 'Séries - Site de Filmes e Series';
if ($viewMode == 'details' && $currentSeries) {
    $pageTitle = $currentSeries['title'] . ' - Site de Filmes e Series';
} elseif ($viewMode == 'player' && $currentEpisode) {
    $pageTitle = $currentSeries['title'] . ' - ' . $currentEpisode['title'] . ' - Site de Filmes e Series';
}

$bodyClass = 'series-page';
if ($viewMode == 'details') {
    $bodyClass .= ' series-details-page';
} elseif ($viewMode == 'player') {
    $bodyClass .= ' player-page';
}

$additionalCSS = ['css/home.css'];
if ($viewMode == 'player') {
    $additionalCSS[] = 'css/player.css';
}

$headerScripts = '';
$footerScripts = '';

if ($viewMode == 'list' || $viewMode == 'details') {
    $footerScripts .= '<script src="js/carrossel.js"></script>';
}

if ($viewMode == 'player') {
    $footerScripts .= '<script src="js/player.js"></script>';
    $footerScripts .= '<script src="js/next-episode.js"></script>';
    if ($isLoggedIn) {
        $footerScripts .= '<script src="js/user-preferences.js"></script>';
    }
}

// Incluir o cabeçalho
include 'includes/templates/header.php';
?>

<?php if ($viewMode == 'list'): ?>
<!-- Listagem de Séries -->
<section class="series-listing-section">
    <div class="container">
        <h1 class="page-title">Séries</h1>
        
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
        
        <div class="series-grid">
            <?php foreach ($series as $s): ?>
            <a href="series.php?id=<?php echo $s['id']; ?>" class="series-card">
                <div class="series-poster">
                    <img src="<?php echo $s['poster']; ?>" alt="<?php echo htmlspecialchars($s['title']); ?>">
                    <div class="series-info-overlay">
                        <span class="series-seasons"><?php echo $s['seasons']; ?> Temporadas</span>
                        <span class="series-year"><?php echo $s['year']; ?></span>
                        <div class="series-rating">
                            <i class="fas fa-star"></i>
                            <span><?php echo $s['rating']; ?></span>
                        </div>
                    </div>
                </div>
                <h3 class="series-title"><?php echo htmlspecialchars($s['title']); ?></h3>
                <div class="series-categories">
                    <?php echo htmlspecialchars(implode(', ', $s['categories'])); ?>
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

<?php elseif ($viewMode == 'details' && $currentSeries): ?>
<!-- Detalhes da Série -->
<section class="series-details-section" style="background-image: url('<?php echo $currentSeries['backdrop']; ?>');">
    <div class="details-overlay"></div>
    <div class="container">
        <div class="series-details">
            <div class="series-poster">
                <img src="<?php echo $currentSeries['poster']; ?>" alt="<?php echo htmlspecialchars($currentSeries['title']); ?>">
            </div>
            
            <div class="series-info">
                <h1 class="series-title"><?php echo htmlspecialchars($currentSeries['title']); ?></h1>
                
                <div class="series-meta">
                    <span class="series-year"><?php echo $currentSeries['year']; ?></span>
                    <span class="series-seasons"><?php echo $currentSeries['seasons']; ?> Temporadas</span>
                    <span class="series-rating"><i class="fas fa-star"></i> <?php echo $currentSeries['rating']; ?></span>
                </div>
                
                <div class="series-categories">
                    <?php foreach ($currentSeries['categories'] as $category): ?>
                    <span class="series-category"><?php echo htmlspecialchars($category); ?></span>
                    <?php endforeach; ?>
                </div>
                
                <p class="series-description"><?php echo htmlspecialchars($currentSeries['description']); ?></p>
                
                <div class="series-actions">
                    <?php if (!empty($episodes)): ?>
                    <a href="series.php?id=<?php echo $seriesId; ?>&season=<?php echo $seasonId; ?>&episode=<?php echo $episodes[0]['id']; ?>" class="btn-play">
                        <i class="fas fa-play"></i> Assistir
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($isLoggedIn): ?>
                    <button class="btn-favorite">
                        <i class="far fa-heart"></i> Favoritar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="series-seasons-tabs">
            <div class="seasons-tabs">
                <?php foreach ($seasons as $s): ?>
                <a href="series.php?id=<?php echo $seriesId; ?>&season=<?php echo $s['id']; ?>" class="season-tab <?php echo ($s['id'] == $seasonId) ? 'active' : ''; ?>">
                    Temporada <?php echo $s['number']; ?>
                </a>
                <?php endforeach; ?>
            </div>
            
            <div class="season-episodes">
                <?php foreach ($episodes as $e): ?>
                <a href="series.php?id=<?php echo $seriesId; ?>&season=<?php echo $seasonId; ?>&episode=<?php echo $e['id']; ?>" class="episode-card">
                    <div class="episode-thumbnail">
                        <img src="<?php echo $e['thumbnail']; ?>" alt="<?php echo htmlspecialchars($e['title']); ?>">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    
                    <div class="episode-info">
                        <h3 class="episode-title">
                            <span class="episode-number"><?php echo $e['number']; ?>.</span>
                            <?php echo htmlspecialchars($e['title']); ?>
                        </h3>
                        <div class="episode-meta">
                            <span class="episode-duration"><?php echo $e['duration']; ?> min</span>
                        </div>
                        <p class="episode-description"><?php echo htmlspecialchars($e['description']); ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php elseif ($viewMode == 'player' && $currentEpisode): ?>
<!-- Player de Episódio -->
<div class="player-wrapper">
    <?php 
    // Configurar dados para o player
    $videoData = [
        'id' => $currentEpisode['id'],
        'series_title' => $currentSeries['title'],
        'season_number' => $currentSeason['number'],
        'episode_number' => $currentEpisode['number'],
        'title' => $currentEpisode['title'],
        'description' => $currentEpisode['description'],
        'thumbnail' => $currentEpisode['thumbnail'],
        'video_url' => $currentEpisode['video_url'],
        'duration' => $currentEpisode['duration']
    ];
    
    // Preparar navegação entre episódios
    $prevEpisode = null;
    $nextEpisode = null;
    
    // Encontrar episódio anterior
    if ($currentEpisode['number'] > 1) {
        foreach ($episodes as $e) {
            if ($e['number'] == ($currentEpisode['number'] - 1)) {
                $prevEpisode = [
                    'title' => $e['title'],
                    'url' => "series.php?id={$seriesId}&season={$seasonId}&episode={$e['id']}"
                ];
                break;
            }
        }
    }
    
    // Encontrar próximo episódio
    if ($currentEpisode['number'] < count($episodes)) {
        foreach ($episodes as $e) {
            if ($e['number'] == ($currentEpisode['number'] + 1)) {
                $nextEpisode = [
                    'title' => $e['title'],
                    'url' => "series.php?id={$seriesId}&season={$seasonId}&episode={$e['id']}"
                ];
                break;
            }
        }
    }
    
    // Preparar lista de episódios recomendados (outros episódios da temporada)
    $recommendedItems = [];
    foreach ($episodes as $e) {
        $recommendedItems[] = [
            'title' => $e['title'],
            'image' => $e['thumbnail'],
            'url' => "series.php?id={$seriesId}&season={$seasonId}&episode={$e['id']}",
            'number' => $e['number'],
            'duration' => $e['duration'],
            'active' => ($e['id'] == $currentEpisode['id'])
        ];
    }
    
    // Configurar opções adicionais
    $options = [
        'show_recommended' => true,
        'recommended_items' => $recommendedItems,
        'show_navigation' => true,
        'prev_item' => $prevEpisode,
        'next_item' => $nextEpisode,
        'advanced_features' => true // Habilitar recursos avançados
    ];
    
    // Incluir o componente do player
    $type = 'episode';
    include 'includes/components/player-component.php'; 
    ?>
</div>

<?php elseif ($viewMode == 'details' && $currentSeries): ?>
<!-- Detalhes da Série -->
<section class="series-details-section" style="background-image: url('<?php echo $currentSeries['backdrop']; ?>');">
    <div class="details-overlay"></div>
    <div class="container">
        <div class="series-details">
            <div class="series-poster">
                <img src="<?php echo $currentSeries['poster']; ?>" alt="<?php echo htmlspecialchars($currentSeries['title']); ?>">
            </div>
            
            <div class="series-info">
                <h1 class="series-title"><?php echo htmlspecialchars($currentSeries['title']); ?></h1>
                
                <div class="series-meta">
                    <span class="series-year"><?php echo $currentSeries['year']; ?></span>
                    <span class="series-seasons"><?php echo $currentSeries['seasons']; ?> Temporadas</span>
                    <span class="series-rating"><i class="fas fa-star"></i> <?php echo $currentSeries['rating']; ?></span>
                </div>
                
                <div class="series-categories">
                    <?php foreach ($currentSeries['categories'] as $category): ?>
                    <span class="series-category"><?php echo htmlspecialchars($category); ?></span>
                    <?php endforeach; ?>
                </div>
                
                <p class="series-description"><?php echo htmlspecialchars($currentSeries['description']); ?></p>
                
                <div class="series-actions">
                    <?php if (!empty($episodes)): ?>
                    <a href="series.php?id=<?php echo $seriesId; ?>&season=<?php echo $seasonId; ?>&episode=<?php echo $episodes[0]['id']; ?>" class="btn-play">
                        <i class="fas fa-play"></i> Assistir
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($isLoggedIn): ?>
                    <button class="btn-favorite">
                        <i class="far fa-heart"></i> Favoritar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="series-seasons-tabs">
            <div class="seasons-tabs">
                <?php foreach ($seasons as $s): ?>
                <a href="series.php?id=<?php echo $seriesId; ?>&season=<?php echo $s['id']; ?>" class="season-tab <?php echo ($s['id'] == $seasonId) ? 'active' : ''; ?>">
                    Temporada <?php echo $s['number']; ?>
                </a>
                <?php endforeach; ?>
            </div>
            
            <div class="season-episodes">
                <?php foreach ($episodes as $e): ?>
                <a href="series.php?id=<?php echo $seriesId; ?>&season=<?php echo $seasonId; ?>&episode=<?php echo $e['id']; ?>" class="episode-card">
                    <div class="episode-thumbnail">
                        <img src="<?php echo $e['thumbnail']; ?>" alt="<?php echo htmlspecialchars($e['title']); ?>">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    
                    <div class="episode-info">
                        <h3 class="episode-title">
                            <span class="episode-number"><?php echo $e['number']; ?>.</span>
                            <?php echo htmlspecialchars($e['title']); ?>
                        </h3>
                        <div class="episode-meta">
                            <span class="episode-duration"><?php echo $e['duration']; ?> min</span>
                        </div>
                        <p class="episode-description"><?php echo htmlspecialchars($e['description']); ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php else: ?>
<!-- Erro - Série não encontrada -->
<section class="error-section">
    <div class="container">
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <h1>Série não encontrada</h1>
            <p>A série que você está procurando não foi encontrada ou não está disponível.</p>
            <a href="series.php" class="btn-primary">Ver todas as séries</a>
        </div>
    </div>
</section>

<?php endif; ?>

<?php
// Incluir o rodapé
include 'includes/templates/footer.php';
?> 