/**
 * advanced-player.js v1.0.0
 * 
 * Scripts para o player de vídeo avançado, incluindo:
 * - Gerenciamento de marcadores de cena
 * - Exibição de contagem regressiva para próximo episódio
 * - Atalhos de teclado
 * - Controles administrativos
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos os módulos do player avançado
    AdvancedPlayer.init();
});

/**
 * Namespace para o player avançado
 */
const AdvancedPlayer = {
    player: null,
    videoElement: null,
    playerContainer: null,
    isAdmin: false,
    contentId: null,
    contentType: null,
    markers: [],
    savedMarkers: [],
    countdownActive: false,
    countdownTimer: null,
    countdownDuration: 10, // Duração em segundos
    
    /**
     * Inicializa o player avançado
     */
    init: function() {
        // Referências aos elementos do DOM
        this.playerContainer = document.querySelector('.player-container');
        
        if (!this.playerContainer) return;
        
        this.videoElement = this.playerContainer.querySelector('video');
        
        if (!this.videoElement) return;
        
        // Obter dados do data attributes
        this.isAdmin = this.playerContainer.dataset.isAdmin === 'true';
        this.contentId = this.playerContainer.dataset.contentId;
        this.contentType = this.playerContainer.dataset.contentType;
        
        // Inicializar módulos
        this.initVideoPlayer();
        this.initKeyboardShortcuts();
        
        if (this.contentType === 'episode') {
            this.initNextEpisodeCountdown();
        }
        
        // Inicializar marcadores
        this.loadMarkers();
        
        // Inicializar controles de admin
        if (this.isAdmin) {
            this.initAdminControls();
        }
    },
    
    /**
     * Inicializa o player de vídeo
     */
    initVideoPlayer: function() {
        // Verificar se o videoElement existe
        if (!this.videoElement) return;
        
        // Adicionar eventos ao player de vídeo
        this.videoElement.addEventListener('timeupdate', this.onTimeUpdate.bind(this));
        this.videoElement.addEventListener('play', this.onPlay.bind(this));
        this.videoElement.addEventListener('pause', this.onPause.bind(this));
        this.videoElement.addEventListener('ended', this.onEnded.bind(this));
        
        // Adicionar botão de Picture-in-Picture
        this.addPictureInPictureButton();
    },
    
    /**
     * Adiciona o botão de Picture-in-Picture ao player
     */
    addPictureInPictureButton: function() {
        // Verificar se o navegador suporta PiP
        if ('pictureInPictureEnabled' in document) {
            const pipButton = document.createElement('button');
            pipButton.className = 'pip-button';
            pipButton.title = 'Picture-in-Picture';
            pipButton.innerHTML = '<i class="fas fa-external-link-alt"></i>';
            
            pipButton.addEventListener('click', () => {
                if (document.pictureInPictureElement === this.videoElement) {
                    document.exitPictureInPicture();
                } else {
                    this.videoElement.requestPictureInPicture();
                }
            });
            
            const controlsContainer = this.playerContainer.querySelector('.video-controls');
            if (controlsContainer) {
                controlsContainer.appendChild(pipButton);
            }
        }
    },
    
    /**
     * Inicializa a contagem regressiva para o próximo episódio
     */
    initNextEpisodeCountdown: function() {
        // Obter referência ao elemento de próximo episódio
        const nextEpisodeElement = this.playerContainer.querySelector('.next-episode-info');
        
        if (!nextEpisodeElement) return;
        
        // Obter href do próximo episódio
        const nextEpisodeUrl = nextEpisodeElement.dataset.nextEpisodeUrl;
        
        if (!nextEpisodeUrl) return;
        
        // Criar container de contagem regressiva
        const countdownContainer = document.createElement('div');
        countdownContainer.className = 'next-episode-countdown';
        countdownContainer.style.display = 'none';
        
        // Criar conteúdo da contagem regressiva
        countdownContainer.innerHTML = `
            <div class="countdown-content">
                <div class="countdown-title">Próximo episódio em:</div>
                <div class="countdown-timer">10</div>
                <div class="countdown-buttons">
                    <button class="cancel-countdown">Cancelar</button>
                    <button class="play-next">Assistir agora</button>
                </div>
            </div>
        `;
        
        // Adicionar ao playerContainer
        this.playerContainer.appendChild(countdownContainer);
        
        // Adicionar eventos aos botões
        const cancelButton = countdownContainer.querySelector('.cancel-countdown');
        const playNextButton = countdownContainer.querySelector('.play-next');
        
        cancelButton.addEventListener('click', () => {
            this.cancelCountdown();
        });
        
        playNextButton.addEventListener('click', () => {
            window.location.href = nextEpisodeUrl;
        });
    },
    
    /**
     * Inicia a contagem regressiva para o próximo episódio
     */
    startCountdown: function() {
        if (this.countdownActive) return;
        
        const countdownContainer = this.playerContainer.querySelector('.next-episode-countdown');
        const countdownTimerElement = countdownContainer.querySelector('.countdown-timer');
        const nextEpisodeElement = this.playerContainer.querySelector('.next-episode-info');
        
        if (!countdownContainer || !countdownTimerElement || !nextEpisodeElement) return;
        
        const nextEpisodeUrl = nextEpisodeElement.dataset.nextEpisodeUrl;
        
        if (!nextEpisodeUrl) return;
        
        // Mostrar container de contagem regressiva
        countdownContainer.style.display = 'flex';
        
        // Definir valor inicial
        let secondsLeft = this.countdownDuration;
        countdownTimerElement.textContent = secondsLeft;
        
        this.countdownActive = true;
        
        // Iniciar contagem regressiva
        this.countdownTimer = setInterval(() => {
            secondsLeft--;
            countdownTimerElement.textContent = secondsLeft;
            
            if (secondsLeft <= 0) {
                clearInterval(this.countdownTimer);
                this.countdownActive = false;
                window.location.href = nextEpisodeUrl;
            }
        }, 1000);
    },
    
    /**
     * Cancela a contagem regressiva para o próximo episódio
     */
    cancelCountdown: function() {
        if (!this.countdownActive) return;
        
        const countdownContainer = this.playerContainer.querySelector('.next-episode-countdown');
        
        if (!countdownContainer) return;
        
        // Esconder container de contagem regressiva
        countdownContainer.style.display = 'none';
        
        // Limpar contagem
        clearInterval(this.countdownTimer);
        this.countdownActive = false;
    },
    
    /**
     * Inicializa os atalhos de teclado
     */
    initKeyboardShortcuts: function() {
        document.addEventListener('keydown', (e) => {
            // Verificar se o player está em foco ou se está em um campo de texto
            if (document.activeElement.tagName === 'INPUT' || 
                document.activeElement.tagName === 'TEXTAREA') {
                return;
            }
            
            // Mapear os atalhos de teclado
            switch (e.key.toLowerCase()) {
                case ' ':
                case 'k':
                    // Play/Pause
                    e.preventDefault();
                    this.togglePlayPause();
                    break;
                case 'f':
                    // Fullscreen
                    e.preventDefault();
                    this.toggleFullscreen();
                    break;
                case 'm':
                    // Mute/Unmute
                    e.preventDefault();
                    this.toggleMute();
                    break;
                case 'arrowleft':
                    // Retroceder 10 segundos
                    e.preventDefault();
                    this.seek(-10);
                    break;
                case 'arrowright':
                    // Avançar 10 segundos
                    e.preventDefault();
                    this.seek(10);
                    break;
                case 'arrowup':
                    // Aumentar volume
                    e.preventDefault();
                    this.changeVolume(0.1);
                    break;
                case 'arrowdown':
                    // Diminuir volume
                    e.preventDefault();
                    this.changeVolume(-0.1);
                    break;
                case 'h':
                    // Mostrar/esconder atalhos
                    e.preventDefault();
                    this.toggleKeyboardShortcutsHelp();
                    break;
            }
        });
        
        // Criar e adicionar o painel de ajuda de atalhos
        this.createKeyboardShortcutsHelp();
    },
    
    /**
     * Cria o painel de ajuda de atalhos de teclado
     */
    createKeyboardShortcutsHelp: function() {
        const keyboardHelp = document.createElement('div');
        keyboardHelp.className = 'keyboard-shortcuts-help';
        keyboardHelp.style.display = 'none';
        
        keyboardHelp.innerHTML = `
            <div class="keyboard-shortcuts-content">
                <div class="keyboard-shortcuts-header">
                    <h3>Atalhos de Teclado</h3>
                    <button class="close-keyboard-help">×</button>
                </div>
                <div class="keyboard-shortcuts-body">
                    <ul>
                        <li><span class="key">Espaço</span> ou <span class="key">K</span> - Play/Pause</li>
                        <li><span class="key">F</span> - Tela cheia</li>
                        <li><span class="key">M</span> - Ativar/Desativar áudio</li>
                        <li><span class="key">←</span> - Retroceder 10 segundos</li>
                        <li><span class="key">→</span> - Avançar 10 segundos</li>
                        <li><span class="key">↑</span> - Aumentar volume</li>
                        <li><span class="key">↓</span> - Diminuir volume</li>
                        <li><span class="key">H</span> - Mostrar/esconder atalhos</li>
                    </ul>
                </div>
            </div>
        `;
        
        this.playerContainer.appendChild(keyboardHelp);
        
        // Adicionar evento ao botão de fechar
        const closeButton = keyboardHelp.querySelector('.close-keyboard-help');
        closeButton.addEventListener('click', () => {
            keyboardHelp.style.display = 'none';
        });
    },
    
    /**
     * Mostra/esconde o painel de ajuda de atalhos
     */
    toggleKeyboardShortcutsHelp: function() {
        const keyboardHelp = this.playerContainer.querySelector('.keyboard-shortcuts-help');
        
        if (!keyboardHelp) return;
        
        const isVisible = keyboardHelp.style.display === 'block';
        keyboardHelp.style.display = isVisible ? 'none' : 'block';
    },
    
    /**
     * Inicializa os controles administrativos
     */
    initAdminControls: function() {
        // Criar container de controles administrativos
        const adminControls = document.createElement('div');
        adminControls.className = 'admin-controls';
        
        adminControls.innerHTML = `
            <div class="admin-controls-header">
                <h3>Controles de Administrador</h3>
                <button class="toggle-admin-controls">◄</button>
            </div>
            <div class="admin-controls-body">
                <div class="markers-section">
                    <h4>Marcadores de Cena</h4>
                    <div class="markers-list"></div>
                    <div class="add-marker-form">
                        <input type="text" placeholder="Título do marcador" class="marker-title">
                        <button class="add-marker-button">Adicionar no tempo atual</button>
                    </div>
                    <div class="markers-actions">
                        <button class="save-markers-button">Salvar Marcadores</button>
                    </div>
                </div>
            </div>
        `;
        
        this.playerContainer.appendChild(adminControls);
        
        // Adicionar eventos aos botões
        const toggleButton = adminControls.querySelector('.toggle-admin-controls');
        const addMarkerButton = adminControls.querySelector('.add-marker-button');
        const saveMarkersButton = adminControls.querySelector('.save-markers-button');
        
        toggleButton.addEventListener('click', () => {
            const adminControlsBody = adminControls.querySelector('.admin-controls-body');
            const isCollapsed = adminControlsBody.style.display === 'none';
            
            adminControlsBody.style.display = isCollapsed ? 'block' : 'none';
            toggleButton.textContent = isCollapsed ? '◄' : '►';
            
            // Ajustar a largura do container
            adminControls.style.width = isCollapsed ? '300px' : '40px';
        });
        
        addMarkerButton.addEventListener('click', () => {
            this.addMarker();
        });
        
        saveMarkersButton.addEventListener('click', () => {
            this.saveMarkers();
        });
    },
    
    /**
     * Carrega os marcadores de cena do servidor
     */
    loadMarkers: function() {
        if (!this.contentId || !this.contentType) return;
        
        // Fazer requisição para a API
        fetch(`/api/load-markers.php?contentId=${this.contentId}&contentType=${this.contentType}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.markers) {
                    this.savedMarkers = [...data.data.markers];
                    this.markers = [...data.data.markers];
                    
                    // Renderizar marcadores
                    this.renderMarkers();
                }
            })
            .catch(error => {
                console.error('Erro ao carregar marcadores:', error);
            });
    },
    
    /**
     * Renderiza os marcadores na timeline e na lista
     */
    renderMarkers: function() {
        // Remover marcadores existentes
        const existingMarkers = this.playerContainer.querySelectorAll('.marker');
        existingMarkers.forEach(marker => marker.remove());
        
        // Obter a timeline do player
        const timeline = this.playerContainer.querySelector('.video-progress');
        
        if (!timeline) return;
        
        // Renderizar marcadores na timeline
        this.markers.forEach(marker => {
            const videoDuration = this.videoElement.duration;
            
            if (!videoDuration) return;
            
            // Calcular posição como porcentagem do tempo total
            const position = (marker.time / videoDuration) * 100;
            
            // Criar elemento de marcador
            const markerElement = document.createElement('div');
            markerElement.className = 'marker';
            markerElement.style.left = `${position}%`;
            markerElement.title = marker.title;
            markerElement.dataset.time = marker.time;
            
            // Adicionar tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'marker-tooltip';
            tooltip.textContent = marker.title;
            markerElement.appendChild(tooltip);
            
            // Adicionar evento de clique
            markerElement.addEventListener('click', (e) => {
                e.stopPropagation();
                this.seekToTime(marker.time);
            });
            
            // Adicionar à timeline
            timeline.appendChild(markerElement);
        });
        
        // Atualizar lista de marcadores (na área de admin)
        if (this.isAdmin) {
            this.updateMarkersList();
        }
    },
    
    /**
     * Atualiza a lista de marcadores na área de admin
     */
    updateMarkersList: function() {
        const markersList = this.playerContainer.querySelector('.markers-list');
        
        if (!markersList) return;
        
        // Limpar lista
        markersList.innerHTML = '';
        
        // Ordenar marcadores por tempo
        const sortedMarkers = [...this.markers].sort((a, b) => a.time - b.time);
        
        // Adicionar cada marcador à lista
        sortedMarkers.forEach((marker, index) => {
            const markerItem = document.createElement('div');
            markerItem.className = 'marker-item';
            
            const timeFormatted = this.formatTime(marker.time);
            
            markerItem.innerHTML = `
                <span class="marker-time">${timeFormatted}</span>
                <span class="marker-title">${marker.title}</span>
                <div class="marker-actions">
                    <button class="marker-seek" data-index="${index}">Ir</button>
                    <button class="marker-remove" data-index="${index}">×</button>
                </div>
            `;
            
            markersList.appendChild(markerItem);
        });
        
        // Adicionar eventos aos botões
        const seekButtons = markersList.querySelectorAll('.marker-seek');
        const removeButtons = markersList.querySelectorAll('.marker-remove');
        
        seekButtons.forEach(button => {
            button.addEventListener('click', () => {
                const index = parseInt(button.dataset.index);
                if (this.markers[index]) {
                    this.seekToTime(this.markers[index].time);
                }
            });
        });
        
        removeButtons.forEach(button => {
            button.addEventListener('click', () => {
                const index = parseInt(button.dataset.index);
                if (this.markers[index]) {
                    this.markers.splice(index, 1);
                    this.renderMarkers();
                }
            });
        });
    },
    
    /**
     * Adiciona um marcador no tempo atual
     */
    addMarker: function() {
        if (!this.videoElement) return;
        
        const titleInput = this.playerContainer.querySelector('.marker-title');
        
        if (!titleInput) return;
        
        const title = titleInput.value.trim();
        
        if (!title) {
            alert('Digite um título para o marcador');
            return;
        }
        
        const time = this.videoElement.currentTime;
        
        // Verificar se já existe um marcador próximo (2 segundos)
        const existingMarker = this.markers.find(m => 
            Math.abs(m.time - time) < 2
        );
        
        if (existingMarker) {
            if (!confirm(`Já existe um marcador próximo "${existingMarker.title}" em ${this.formatTime(existingMarker.time)}. Deseja adicionar mesmo assim?`)) {
                return;
            }
        }
        
        // Adicionar marcador
        this.markers.push({
            title: title,
            time: time
        });
        
        // Limpar input
        titleInput.value = '';
        
        // Renderizar marcadores
        this.renderMarkers();
    },
    
    /**
     * Salva os marcadores no servidor
     */
    saveMarkers: function() {
        if (!this.contentId || !this.contentType) return;
        
        // Verificar se há alterações
        const markersChanged = JSON.stringify(this.markers) !== JSON.stringify(this.savedMarkers);
        
        if (!markersChanged) {
            alert('Não há alterações nos marcadores para salvar.');
            return;
        }
        
        // Ordenar marcadores por tempo
        const sortedMarkers = [...this.markers].sort((a, b) => a.time - b.time);
        
        // Preparar dados para envio
        const data = {
            contentId: this.contentId,
            contentType: this.contentType,
            markers: sortedMarkers
        };
        
        // Enviar para a API
        fetch('/api/save-markers.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Marcadores salvos com sucesso. Total: ${data.data.savedCount}`);
                this.savedMarkers = [...this.markers];
            } else {
                alert(`Erro ao salvar marcadores: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Erro ao salvar marcadores:', error);
            alert('Erro ao salvar marcadores. Verifique o console para mais detalhes.');
        });
    },
    
    /**
     * Eventos do player
     */
    onTimeUpdate: function() {
        // Atualizar marcadores ativos
        this.updateActiveMarkers();
    },
    
    onPlay: function() {
        // Cancelar contagem regressiva se estiver ativa
        if (this.countdownActive) {
            this.cancelCountdown();
        }
    },
    
    onPause: function() {
        // Nada a fazer por enquanto
    },
    
    onEnded: function() {
        // Se for episódio, iniciar contagem regressiva para o próximo
        if (this.contentType === 'episode') {
            this.startCountdown();
        }
    },
    
    /**
     * Atualiza os marcadores ativos com base no tempo atual
     */
    updateActiveMarkers: function() {
        const currentTime = this.videoElement.currentTime;
        
        // Atualizar classe ativa nos marcadores
        const markerElements = this.playerContainer.querySelectorAll('.marker');
        
        markerElements.forEach(marker => {
            const markerTime = parseFloat(marker.dataset.time);
            const isActive = Math.abs(currentTime - markerTime) < 0.5;
            
            marker.classList.toggle('active', isActive);
        });
    },
    
    /**
     * Busca para um tempo específico no vídeo
     */
    seekToTime: function(time) {
        if (!this.videoElement) return;
        
        // Definir o tempo de reprodução
        this.videoElement.currentTime = time;
    },
    
    /**
     * Funções de controle do player
     */
    togglePlayPause: function() {
        if (!this.videoElement) return;
        
        if (this.videoElement.paused) {
            this.videoElement.play();
        } else {
            this.videoElement.pause();
        }
    },
    
    toggleFullscreen: function() {
        if (!this.playerContainer) return;
        
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            this.playerContainer.requestFullscreen();
        }
    },
    
    toggleMute: function() {
        if (!this.videoElement) return;
        
        this.videoElement.muted = !this.videoElement.muted;
    },
    
    seek: function(seconds) {
        if (!this.videoElement) return;
        
        const newTime = this.videoElement.currentTime + seconds;
        this.videoElement.currentTime = Math.max(0, Math.min(newTime, this.videoElement.duration));
    },
    
    changeVolume: function(delta) {
        if (!this.videoElement) return;
        
        const newVolume = Math.max(0, Math.min(1, this.videoElement.volume + delta));
        this.videoElement.volume = newVolume;
    },
    
    /**
     * Formata o tempo em segundos para o formato MM:SS
     */
    formatTime: function(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        
        return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
}; 