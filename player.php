<?php
/**
 * Player de Vídeo - Site de Filmes e Series
 * 
 * Página do player de vídeo com funcionalidades avançadas
 * de reprodução e navegação entre episódios.
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

// Obter o ID do vídeo da URL, se existir
$videoId = isset($_GET['video']) ? $_GET['video'] : null;
$videoSrc = 'media/videos/episode1.m3u8'; // Valor padrão
$videoPoster = 'media/thumbnails/episode1.jpg'; // Valor padrão

// Se houver um ID de vídeo, obter os dados correspondentes
// Em uma aplicação real, isso viria do banco de dados
if ($videoId) {
    // Simulação de busca por ID
    switch ($videoId) {
        case 'destaque1':
        case 'pop1':
        case 'cont1':
            $videoSrc = 'media/videos/episode1.m3u8';
            $videoPoster = 'media/thumbnails/episode1.jpg';
            $videoTitle = 'Episódio 1: O Início';
            $videoIndex = 0;
            break;
        case 'pop2':
        case 'cont2':
            $videoSrc = 'media/videos/episode2.m3u8';
            $videoPoster = 'media/thumbnails/episode2.jpg';
            $videoTitle = 'Episódio 2: O Despertar';
            $videoIndex = 1;
            break;
        case 'pop3':
        case 'cont3':
            $videoSrc = 'media/videos/episode3.m3u8';
            $videoPoster = 'media/thumbnails/episode3.jpg';
            $videoTitle = 'Episódio 3: A Jornada';
            $videoIndex = 2;
            break;
        case 'pop4':
        case 'cont4':
            $videoSrc = 'media/videos/episode4.m3u8';
            $videoPoster = 'media/thumbnails/episode4.jpg';
            $videoTitle = 'Episódio 4: Alianças';
            $videoIndex = 3;
            break;
        default:
            // Usar valores padrão para vídeos não encontrados
            $videoTitle = 'Episódio 1: O Início';
            $videoIndex = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($videoTitle) ? $videoTitle . ' - ' : ''; ?>Site de Filmes e Series</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/custom.css">
    <link rel="stylesheet" href="css/next-episode.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- HLS.js para streaming adaptativo -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
</head>
<body>
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
                    <li><a href="categorias.php">Categorias</a></li>
                    <li><a href="novidades.php">Novidades</a></li>
                    <li><a href="player.php" class="active">Player</a></li>
                </ul>
            </nav>
            
            <div class="header-right">
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
        </div>
    </header>

    <div class="container">
        <div class="player-wrapper">
            <div class="video-container">
                <video id="main-video" poster="<?php echo $videoPoster; ?>" preload="metadata" data-video-id="<?php echo isset($videoIndex) ? $videoIndex + 1 : 1; ?>"></video>
                
                <!-- Overlay de carregamento -->
                <div class="loading-overlay">
                    <div class="spinner"></div>
                    <p>Carregando vídeo...</p>
                </div>
                
                <!-- Novo componente de próximo episódio -->
                <div class="next-episode-container">
                    <div class="next-episode-box">
                        <button class="autoplay-settings" id="autoplay-settings" title="Configurar reprodução automática">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="next-episode-thumbnail">
                            <img src="media/thumbnails/episode2.jpg" alt="Próximo episódio">
                        </div>
                        <div class="next-episode-info">
                            <h4>Próximo Episódio</h4>
                            <h3>Episódio 2: O Despertar</h3>
                            <div class="next-episode-progress">
                                <div class="next-episode-progress-bar"></div>
                            </div>
                            <div class="autoplay-text">Reprodução automática em <span class="autoplay-countdown">15</span>s</div>
                        </div>
                        <button class="next-episode-button" data-index="1" data-src="media/videos/episode2.m3u8" data-poster="media/thumbnails/episode2.jpg">
                            <i class="fas fa-forward"></i>
                            <span>Assistir</span>
                        </button>
                    </div>
                </div>
                
                <!-- Menu de configurações de autoplay -->
                <div id="autoplay-settings-menu" class="settings-menu">
                    <div class="menu-header">
                        <span>Configurações de Reprodução</span>
                        <button class="close-button" title="Fechar configurações">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <ul>
                        <li data-option="autoplay">
                            <span>Reprodução Automática</span>
                            <label class="toggle-switch">
                                <input type="checkbox" id="autoplay-toggle" checked aria-label="Ativar reprodução automática" title="Ativar ou desativar reprodução automática">
                                <span class="slider round"></span>
                            </label>
                        </li>
                        <li data-option="countdown">
                            <span>Tempo de Contagem</span>
                            <div class="countdown-control">
                                <button class="countdown-btn" data-action="decrease" title="Diminuir tempo">-</button>
                                <span id="countdown-value">15</span>
                                <button class="countdown-btn" data-action="increase" title="Aumentar tempo">+</button>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <!-- Painel de próximo vídeo -->
                <div class="next-video-container">
                    <div class="next-video-box">
                        <div class="next-video-info">
                            <span>Próximo episódio</span>
                            <h3>Episódio 2: O Despertar</h3>
                            <p class="next-video-description">Novos poderes são descobertos enquanto o mal se aproxima da vila.</p>
                        </div>
                        <button class="btn-next" data-index="1" data-src="media/videos/episode2.m3u8" data-poster="media/thumbnails/episode2.jpg" title="Assistir próximo episódio">
                            <i class="fas fa-forward"></i>
                            <span>Próximo</span>
                        </button>
                    </div>
                </div>
                
                <!-- Controles do player -->
                <div class="player-controls">
                    <div class="progress-area">
                        <div class="buffer-bar"></div>
                        <div class="progress-bar"></div>
                    </div>
                    
                    <div class="controls-main">
                        <div class="controls-left">
                            <button id="play-pause-btn" title="Reproduzir/Pausar"><i class="fas fa-play"></i></button>
                            
                            <div class="volume-container">
                                <button id="volume-btn" title="Mudo/Ajustar volume"><i class="fas fa-volume-up"></i></button>
                                <input type="range" class="volume-slider" min="0" max="1" step="0.1" value="1" title="Controle de volume" aria-label="Ajuste o volume" placeholder="Volume">
                            </div>
                            
                            <div class="time-display">
                                <span class="current-time">0:00</span>
                                <span>/</span>
                                <span class="duration">0:00</span>
                            </div>
                        </div>
                        
                        <div class="controls-right">
                            <button id="captions-btn" class="icon-button" title="Ativar/Desativar legendas">
                                <i class="fas fa-closed-captioning"></i>
                                <span>Legendas</span>
                            </button>
                            
                            <button id="quality-btn" class="icon-button" title="Selecionar qualidade do vídeo">
                                <i class="fas fa-cog"></i>
                                <span>Auto</span>
                            </button>
                            
                            <button id="speed-btn" class="icon-button" title="Ajustar velocidade de reprodução">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>1x</span>
                            </button>
                            
                            <button id="pip-btn" class="icon-button" title="Picture-in-Picture">
                                <i class="fas fa-external-link-alt"></i>
                            </button>
                            
                            <button id="settings-btn" title="Configurações do player">
                                <i class="fas fa-sliders-h"></i>
                            </button>
                            
                            <button id="fullscreen-btn" title="Tela cheia">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Menus de configuração -->
                    <div id="main-settings-menu" class="settings-menu">
                        <ul>
                            <li data-menu="quality">
                                <span>Qualidade</span>
                                <span>Auto</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li data-menu="captions">
                                <span>Legendas</span>
                                <span>Desativadas</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li data-menu="speed">
                                <span>Velocidade</span>
                                <span>1x</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div id="quality-menu" class="settings-menu">
                        <div class="menu-header">
                            <button class="back-button" title="Voltar para o menu principal">
                                <i class="fas fa-arrow-left"></i>
                                <span>Qualidade</span>
                            </button>
                        </div>
                        <ul>
                            <li class="active" data-level-index="-1">Auto</li>
                        </ul>
                    </div>
                    
                    <div id="captions-menu" class="settings-menu">
                        <div class="menu-header">
                            <button class="back-button" title="Voltar para o menu principal">
                                <i class="fas fa-arrow-left"></i>
                                <span>Legendas</span>
                            </button>
                        </div>
                        <ul>
                            <li class="active" data-srclang="off">Desativadas</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Informações do vídeo e playlist -->
            <div class="player-info">
                <div class="video-details">
                    <h2>Série: O Caminho do Herói</h2>
                    <p class="description">Uma jornada épica através de mundos fantásticos, onde um jovem descobre seu verdadeiro destino.</p>
                    
                    <!-- Analytics -->
                    <div class="analytics-panel">
                        <h3>Estatísticas de visualização</h3>
                        <div class="analytics-grid">
                            <div class="analytics-item">
                                <span class="analytics-label">Tempo assistido:</span>
                                <span id="watch-time" class="analytics-value">0 segundos</span>
                            </div>
                            <div class="analytics-item">
                                <span class="analytics-label">Percentual:</span>
                                <span id="watch-percent" class="analytics-value">0%</span>
                            </div>
                            <div class="analytics-item">
                                <span class="analytics-label">Pausas:</span>
                                <span id="pause-count" class="analytics-value">0</span>
                            </div>
                            <div class="analytics-item">
                                <span class="analytics-label">Qualidade:</span>
                                <span id="current-quality" class="analytics-value">Auto</span>
                            </div>
                            <div class="analytics-item">
                                <span class="analytics-label">Trocas de qualidade:</span>
                                <span id="quality-switches" class="analytics-value">0</span>
                            </div>
                        </div>
                        
                        <div class="heatmap">
                            <div class="heatmap-label">Progresso de visualização:</div>
                            <div class="heatmap-container">
                                <div class="heatmap-bar"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="playlist-container">
                    <h3>Episódios</h3>
                    <div class="playlist">
                        <div class="playlist-item <?php echo ($videoIndex ?? 0) == 0 ? 'active' : ''; ?>" data-video-id="1" data-video-src="media/videos/episode1.m3u8" data-poster-src="media/thumbnails/episode1.jpg">
                            <div class="playlist-thumbnail">
                                <img src="media/thumbnails/episode1.jpg" alt="Episódio 1">
                                <span class="duration">24:15</span>
                            </div>
                            <div class="video-info">
                                <h3>Episódio 1: O Início</h3>
                                <p>O jovem herói descobre uma antiga profecia que mudará seu destino para sempre.</p>
                            </div>
                        </div>
                        
                        <div class="playlist-item <?php echo ($videoIndex ?? 0) == 1 ? 'active' : ''; ?>" data-video-id="2" data-video-src="media/videos/episode2.m3u8" data-poster-src="media/thumbnails/episode2.jpg">
                            <div class="playlist-thumbnail">
                                <img src="media/thumbnails/episode2.jpg" alt="Episódio 2">
                                <span class="duration">22:40</span>
                            </div>
                            <div class="video-info">
                                <h3>Episódio 2: O Despertar</h3>
                                <p>Novos poderes são descobertos enquanto o mal se aproxima da vila.</p>
                            </div>
                        </div>
                        
                        <div class="playlist-item <?php echo ($videoIndex ?? 0) == 2 ? 'active' : ''; ?>" data-video-id="3" data-video-src="media/videos/episode3.m3u8" data-poster-src="media/thumbnails/episode3.jpg">
                            <div class="playlist-thumbnail">
                                <img src="media/thumbnails/episode3.jpg" alt="Episódio 3">
                                <span class="duration">25:10</span>
                            </div>
                            <div class="video-info">
                                <h3>Episódio 3: A Jornada</h3>
                                <p>Uma perigosa missão leva o herói e seus amigos a terras desconhecidas.</p>
                            </div>
                        </div>
                        
                        <div class="playlist-item <?php echo ($videoIndex ?? 0) == 3 ? 'active' : ''; ?>" data-video-id="4" data-video-src="media/videos/episode4.m3u8" data-poster-src="media/thumbnails/episode4.jpg">
                            <div class="playlist-thumbnail">
                                <img src="media/thumbnails/episode4.jpg" alt="Episódio 4">
                                <span class="duration">23:55</span>
                            </div>
                            <div class="video-info">
                                <h3>Episódio 4: Alianças</h3>
                                <p>Novas alianças são formadas enquanto segredos antigos são revelados.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; Site de Filmes e Series. Todos os direitos reservados.</p>
    </div>
    
    <!-- Scripts JavaScript -->
    <script>
        // Configuração inicial do player de vídeo
        document.addEventListener('DOMContentLoaded', function() {
            const videoPlayer = document.getElementById('main-video');
            
            // Definir a fonte do vídeo com base nos parâmetros PHP
            <?php if (isset($videoSrc)): ?>
            loadVideo('<?php echo $videoSrc; ?>');
            <?php endif; ?>
            
            function loadVideo(src) {
                if (Hls.isSupported()) {
                    const hls = new Hls();
                    hls.loadSource(src);
                    hls.attachMedia(videoPlayer);
                } else if (videoPlayer.canPlayType('application/vnd.apple.mpegurl')) {
                    videoPlayer.src = src;
                }
            }
        });
    </script>
    <script src="js/user-preferences.js"></script>
    <script src="js/next-episode.js"></script>
    <script src="js/player.js"></script>
    <script src="js/main.js"></script>
</body>
</html> 