/**
 * player.js - Funções relacionadas ao player de vídeo
 * 
 * Este arquivo contém todas as funcionalidades relacionadas ao player de vídeo, incluindo:
 * - Inicialização do player Video.js
 * - Controle de reprodução de conteúdo
 * - Salvamento do progresso de reprodução
 * - Marcação de conteúdo como assistido
 * - Gerenciamento de favoritos
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializa o player de vídeo quando existir no DOM
    const videoElement = document.getElementById('content-player');
    
    if (videoElement) {
        initializeVideoPlayer();
    }
    
    // Configura os botões de ações do usuário
    setupUserActionButtons();
});

/**
 * Inicializa o player de vídeo utilizando Video.js
 */
function initializeVideoPlayer() {
    const videoElement = document.getElementById('content-player');
    
    if (!videoElement) return;
    
    // Cria nova instância do player
    const player = videojs('content-player', {
        autoplay: false,
        controlBar: {
            children: [
                'playToggle',
                'progressControl',
                'currentTimeDisplay',
                'timeDivider',
                'durationDisplay',
                'volumePanel',
                'qualitySelector',
                'fullscreenToggle',
            ]
        },
        responsive: true,
        fluid: true,
        playbackRates: [0.5, 1, 1.25, 1.5, 2],
        html5: {
            vhs: {
                overrideNative: true
            }
        }
    });
    
    // Obtém o timestamp salvo (se houver)
    const contentId = videoElement.dataset.contentId;
    const contentType = videoElement.dataset.contentType;
    const savedTime = getSavedPlaybackTime(contentId, contentType);
    
    // Configura eventos do player
    player.on('ready', function() {
        console.log('Player está pronto');
        
        // Restaura o tempo salvo de reprodução
        if (savedTime && savedTime > 0) {
            const confirmResume = confirm("Deseja continuar de onde parou? (" + formatTime(savedTime) + ")");
            if (confirmResume) {
                player.currentTime(savedTime);
            }
        }
    });
    
    // Salva progresso a cada 5 segundos durante a reprodução
    player.on('timeupdate', function() {
        const currentTime = player.currentTime();
        const duration = player.duration();
        
        // Não salva se o conteúdo estiver no início ou no final
        if (currentTime < 5 || (duration - currentTime) < 5) return;
        
        // Salva a cada 5 segundos para evitar muitas chamadas
        if (Math.floor(currentTime) % 5 === 0) {
            savePlaybackTime(contentId, contentType, currentTime);
        }
        
        // Marca como assistido automaticamente ao assistir 90%
        if (currentTime >= (duration * 0.9)) {
            markAsWatched(contentId, contentType);
        }
    });
    
    // Evento de finalização do vídeo
    player.on('ended', function() {
        console.log('Vídeo finalizado');
        markAsWatched(contentId, contentType);
        clearPlaybackTime(contentId, contentType);
        
        // Verifica se existe próximo episódio/filme para reproduzir automaticamente
        const nextItemUrl = document.querySelector('.btn-next-item')?.getAttribute('href');
        if (nextItemUrl) {
            setTimeout(function() {
                window.location.href = nextItemUrl;
            }, 3000);
        }
    });
    
    return player;
}

/**
 * Configura os botões de interação do usuário (marcar como assistido, favoritar, etc)
 */
function setupUserActionButtons() {
    const watchedBtn = document.querySelector('.btn-mark-watched');
    const favoriteBtn = document.querySelector('.btn-favorite');
    
    if (watchedBtn) {
        watchedBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const contentId = this.dataset.contentId;
            const contentType = this.dataset.contentType;
            markAsWatched(contentId, contentType);
        });
    }
    
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const contentId = this.dataset.contentId;
            const contentType = this.dataset.contentType;
            toggleFavorite(contentId, contentType);
        });
    }
}

/**
 * Salva o tempo de reprodução atual no localStorage e no servidor
 */
function savePlaybackTime(contentId, contentType, currentTime) {
    if (!contentId || !contentType) return;
    
    // Chave para o localStorage
    const storageKey = `playback_${contentType}_${contentId}`;
    
    // Salva localmente primeiro (fallback)
    localStorage.setItem(storageKey, currentTime);
    
    // Salva no servidor via AJAX
    fetch('ajax/save_playback_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content_id: contentId,
            content_type: contentType,
            current_time: currentTime
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Progresso salvo no servidor');
        }
    })
    .catch(error => {
        console.error('Erro ao salvar progresso:', error);
    });
}

/**
 * Obtém o tempo de reprodução salvo
 */
function getSavedPlaybackTime(contentId, contentType) {
    if (!contentId || !contentType) return 0;
    
    // Chave para o localStorage
    const storageKey = `playback_${contentType}_${contentId}`;
    
    // Obtém do localStorage (fallback rápido)
    const savedTime = localStorage.getItem(storageKey);
    return savedTime ? parseFloat(savedTime) : 0;
}

/**
 * Limpa o tempo de reprodução salvo (após assistir completo)
 */
function clearPlaybackTime(contentId, contentType) {
    if (!contentId || !contentType) return;
    
    // Chave para o localStorage
    const storageKey = `playback_${contentType}_${contentId}`;
    
    // Remove do localStorage
    localStorage.removeItem(storageKey);
    
    // Remove do servidor
    fetch('ajax/clear_playback_progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content_id: contentId,
            content_type: contentType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Progresso limpo no servidor');
        }
    })
    .catch(error => {
        console.error('Erro ao limpar progresso:', error);
    });
}

/**
 * Marca um conteúdo como assistido
 */
function markAsWatched(contentId, contentType) {
    if (!contentId || !contentType) return;
    
    const watchedBtn = document.querySelector('.btn-mark-watched');
    
    // Atualiza no servidor
    fetch('ajax/mark_as_watched.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content_id: contentId,
            content_type: contentType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Marcado como assistido');
            if (watchedBtn) {
                watchedBtn.textContent = 'Assistido';
                watchedBtn.classList.add('watched');
                watchedBtn.disabled = true;
            }
        }
    })
    .catch(error => {
        console.error('Erro ao marcar como assistido:', error);
    });
}

/**
 * Alterna o status de favorito de um conteúdo
 */
function toggleFavorite(contentId, contentType) {
    if (!contentId || !contentType) return;
    
    const favoriteBtn = document.querySelector('.btn-favorite');
    
    // Atualiza no servidor
    fetch('ajax/toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            content_id: contentId,
            content_type: contentType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (favoriteBtn) {
                if (data.is_favorite) {
                    favoriteBtn.innerHTML = '<i class="fas fa-heart"></i> Remover dos Favoritos';
                    favoriteBtn.classList.add('favorited');
                } else {
                    favoriteBtn.innerHTML = '<i class="far fa-heart"></i> Adicionar aos Favoritos';
                    favoriteBtn.classList.remove('favorited');
                }
            }
        }
    })
    .catch(error => {
        console.error('Erro ao alterar favorito:', error);
    });
}

/**
 * Formata um timestamp em segundos para formato MM:SS ou HH:MM:SS
 */
function formatTime(seconds) {
    seconds = Math.floor(seconds);
    
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;
    
    if (hours > 0) {
        return `${padZero(hours)}:${padZero(minutes)}:${padZero(remainingSeconds)}`;
    }
    
    return `${padZero(minutes)}:${padZero(remainingSeconds)}`;
}

/**
 * Adiciona zero à esquerda para números menores que 10
 */
function padZero(num) {
    return num < 10 ? `0${num}` : num;
} 