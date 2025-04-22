<?php
/**
 * Componente de Player de Vídeo
 * 
 * Componente reutilizável para player de vídeo que pode ser usado
 * tanto na página de filmes quanto na página de séries.
 * 
 * @param array $videoData Dados do vídeo a ser reproduzido
 * @param string $type Tipo de conteúdo ('movie' ou 'episode')
 * @param array $options Opções adicionais (opcional)
 */

// Verificar se os parâmetros necessários foram fornecidos
if (!isset($videoData) || !isset($type)) {
    echo '<div class="error-message">Erro: Dados de vídeo insuficientes</div>';
    return;
}

// Definir valores padrão com base no tipo
$title = '';
$description = '';
$poster = '';
$videoUrl = '';
$duration = 0;
$year = '';
$categories = [];
$isLoggedIn = isset($_SESSION['user_data']);
$isAdmin = isset($_SESSION['user_data']['is_admin']) && $_SESSION['user_data']['is_admin'];

// Extrair dados com base no tipo de conteúdo
if ($type === 'movie') {
    $title = $videoData['title'] ?? '';
    $description = $videoData['description'] ?? '';
    $poster = $videoData['poster'] ?? '';
    $videoUrl = $videoData['video_url'] ?? '';
    $duration = $videoData['duration'] ?? 0;
    $year = $videoData['year'] ?? '';
    $categories = $videoData['categories'] ?? [];
    $contentId = $videoData['id'] ?? 0;
} elseif ($type === 'episode') {
    $seriesTitle = $videoData['series_title'] ?? '';
    $seasonNumber = $videoData['season_number'] ?? '';
    $episodeNumber = $videoData['episode_number'] ?? '';
    $title = $videoData['title'] ?? '';
    $description = $videoData['description'] ?? '';
    $poster = $videoData['thumbnail'] ?? '';
    $videoUrl = $videoData['video_url'] ?? '';
    $duration = $videoData['duration'] ?? 0;
    $contentId = $videoData['id'] ?? 0;
}

// Extrair opções adicionais
$showRecommended = $options['show_recommended'] ?? true;
$recommendedItems = $options['recommended_items'] ?? [];
$showNavigation = $options['show_navigation'] ?? ($type === 'episode');
$prevItem = $options['prev_item'] ?? null;
$nextItem = $options['next_item'] ?? null;
$useAdvancedFeatures = $options['advanced_features'] ?? true;

// Carregar estilos e scripts necessários para o player avançado
if ($useAdvancedFeatures) {
    // Adicionar estilos e scripts ao cabeçalho da página
    $pageAdditionalCSS[] = 'css/advanced-player.css';
    $pageFooterScripts[] = '<script src="js/advanced-player.js"></script>';
}
?>

<!-- Player de Vídeo Integrado -->
<section class="player-section">
    <div class="video-player-container">
        <video id="content-player" class="video-js vjs-big-play-centered" 
               controls preload="auto" poster="<?php echo $poster; ?>" 
               data-setup='{"fluid": true}'
               data-content-id="<?php echo $contentId; ?>"
               data-content-type="<?php echo $type; ?>"
               <?php if ($isAdmin): ?>data-user-role="admin"<?php endif; ?>>
            <source src="<?php echo $videoUrl; ?>" type="application/x-mpegURL">
            <p class="vjs-no-js">
                Para assistir este vídeo, por favor habilite JavaScript e considere atualizar para um navegador que
                <a href="https://videojs.com/html5-video-support/" target="_blank">suporte vídeo HTML5</a>
            </p>
        </video>
    </div>
    
    <div class="player-details-container">
        <div class="container">
            <div class="content-details">
                <div class="titles">
                    <?php if ($type === 'episode'): ?>
                        <h1 class="series-title"><?php echo htmlspecialchars($seriesTitle); ?></h1>
                        <h2 class="episode-title">
                            <span class="season-info">T<?php echo $seasonNumber; ?>:E<?php echo $episodeNumber; ?></span>
                            <?php echo htmlspecialchars($title); ?>
                        </h2>
                    <?php else: ?>
                        <h1 class="movie-title"><?php echo htmlspecialchars($title); ?></h1>
                    <?php endif; ?>
                </div>
                
                <div class="content-meta">
                    <?php if ($type === 'movie'): ?>
                        <span class="year"><?php echo $year; ?></span>
                        <span class="duration"><?php echo $duration; ?> min</span>
                        <?php if (!empty($categories)): ?>
                        <div class="categories">
                            <?php foreach ($categories as $category): ?>
                            <span class="category"><?php echo htmlspecialchars($category); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="duration"><?php echo $duration; ?> min</span>
                    <?php endif; ?>
                </div>
                
                <div class="content-actions">
                    <?php if ($isLoggedIn): ?>
                    <button class="btn-mark-watched" data-type="<?php echo $type; ?>" 
                            data-content-id="<?php echo $contentId; ?>">
                        <i class="fas fa-check-circle"></i> Marcar como visto
                    </button>
                    <button class="btn-favorite" data-type="<?php echo $type; ?>" 
                             data-content-id="<?php echo $contentId; ?>">
                        <i class="far fa-heart"></i> Favoritar
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($showNavigation): ?>
                    <div class="navigation-controls">
                        <?php if ($prevItem): ?>
                        <a href="<?php echo $prevItem['url']; ?>" class="btn-prev-item">
                            <i class="fas fa-step-backward"></i> <?php echo $type === 'episode' ? 'Episódio anterior' : 'Filme anterior'; ?>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($nextItem): ?>
                        <a href="<?php echo $nextItem['url']; ?>" class="btn-next-item" id="next-item-btn" 
                           data-title="<?php echo htmlspecialchars($nextItem['title']); ?>">
                            <?php echo $type === 'episode' ? 'Próximo episódio' : 'Próximo filme'; ?> <i class="fas fa-step-forward"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="content-description">
                    <p><?php echo htmlspecialchars($description); ?></p>
                </div>
                
                <?php if ($isAdmin): ?>
                <div class="admin-controls">
                    <h4>Controles de Administrador</h4>
                    <div class="admin-buttons">
                        <button class="btn-edit-content" data-type="<?php echo $type; ?>" data-id="<?php echo $contentId; ?>">
                            <i class="fas fa-edit"></i> Editar Conteúdo
                        </button>
                        <button class="btn-manage-markers" data-type="<?php echo $type; ?>" data-id="<?php echo $contentId; ?>">
                            <i class="fas fa-bookmark"></i> Gerenciar Marcadores
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($showRecommended && !empty($recommendedItems)): ?>
            <div class="recommended-content">
                <h3><?php echo $type === 'episode' ? 'Mais episódios' : 'Recomendados para você'; ?></h3>
                <div class="<?php echo $type === 'episode' ? 'episodes-scroll' : 'recommended-grid'; ?>">
                    <?php foreach ($recommendedItems as $item): ?>
                    <a href="<?php echo $item['url']; ?>" class="<?php echo $type === 'episode' ? 'episode-thumb' : 'recommended-item'; ?> <?php echo ($item['active'] ?? false) ? 'active' : ''; ?>">
                        <div class="thumb-img">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php if (($item['active'] ?? false) && $type === 'episode'): ?>
                            <div class="current-badge">Assistindo</div>
                            <?php else: ?>
                            <i class="fas fa-play-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="thumb-info">
                            <?php if ($type === 'episode'): ?>
                            <span class="episode-number">E<?php echo $item['number']; ?></span>
                            <?php endif; ?>
                            <h4 class="thumb-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                            <div class="thumb-meta">
                                <?php if (isset($item['year'])): ?>
                                <span><?php echo $item['year']; ?></span>
                                <?php endif; ?>
                                <?php if (isset($item['duration'])): ?>
                                <span><?php echo $item['duration']; ?> min</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($isLoggedIn && $useAdvancedFeatures): ?>
            <!-- Keyboard Shortcuts Help -->
            <div class="keyboard-shortcuts">
                <div class="shortcuts-header">
                    <h3>Atalhos de Teclado</h3>
                    <button class="toggle-shortcuts"><i class="fas fa-chevron-down"></i></button>
                </div>
                <div class="shortcuts-content" style="display: none;">
                    <div class="shortcuts-grid">
                        <div class="shortcut-item">
                            <span class="key-combo">Espaço</span>
                            <span class="key-action">Reproduzir/Pausar</span>
                        </div>
                        <div class="shortcut-item">
                            <span class="key-combo">←</span>
                            <span class="key-action">Retroceder 10s</span>
                        </div>
                        <div class="shortcut-item">
                            <span class="key-combo">→</span>
                            <span class="key-action">Avançar 10s</span>
                        </div>
                        <div class="shortcut-item">
                            <span class="key-combo">Shift + ←</span>
                            <span class="key-action">Retroceder 30s</span>
                        </div>
                        <div class="shortcut-item">
                            <span class="key-combo">Shift + →</span>
                            <span class="key-action">Avançar 30s</span>
                        </div>
                        <div class="shortcut-item">
                            <span class="key-combo">F</span>
                            <span class="key-action">Tela Cheia</span>
                        </div>
                        <div class="shortcut-item">
                            <span class="key-combo">M</span>
                            <span class="key-action">Mudo</span>
                        </div>
                        <div class="shortcut-item">
                            <span class="key-combo">P</span>
                            <span class="key-action">Picture-in-Picture</span>
                        </div>
                        <div class="shortcut-item">
                            <span class="key-combo">0-9</span>
                            <span class="key-action">Saltar para %</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const toggleBtn = document.querySelector('.toggle-shortcuts');
                    const shortcutsContent = document.querySelector('.shortcuts-content');
                    
                    if (toggleBtn && shortcutsContent) {
                        toggleBtn.addEventListener('click', function() {
                            const isHidden = shortcutsContent.style.display === 'none';
                            shortcutsContent.style.display = isHidden ? 'block' : 'none';
                            toggleBtn.innerHTML = isHidden ? 
                                '<i class="fas fa-chevron-up"></i>' : 
                                '<i class="fas fa-chevron-down"></i>';
                        });
                    }
                });
            </script>
            <?php endif; ?>
        </div>
    </div>
</section> 