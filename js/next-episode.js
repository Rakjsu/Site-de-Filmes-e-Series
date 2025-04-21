/**
 * NextEpisodeManager - Gerencia a funcionalidade "Próximo Episódio"
 * Versão: 3.2.0 - Otimizada para maior legibilidade e desempenho
 */
class NextEpisodeManager {
    constructor() {
        // Constantes
        this.AUTOPLAY_THRESHOLD = 0.90;
        this.DEFAULT_COUNTDOWN = 15;
        
        // Inicialização
        this.initElements();
        this.initState();
        this.loadUserPreferences();
        
        if (this.validateElements()) {
            this.setupEventListeners();
            this.fetchNextEpisodeData();
            console.info('NextEpisodeManager inicializado com sucesso.');
        }
    }

    // Métodos de inicialização
    initElements() {
        // Elementos do player
        this.videoPlayer = document.querySelector('#main-video');
        
        // Elementos da interface do próximo episódio
        this.nextEpisodeContainer = document.querySelector('.next-episode-container');
        this.nextEpisodeButton = document.querySelector('.next-episode-button');
        this.nextEpisodeInfo = document.querySelector('.next-episode-info');
        this.nextEpisodeThumbnail = document.querySelector('.next-episode-thumbnail img');
        
        // Elementos de controle
        this.autoplayToggle = document.querySelector('#autoplay-toggle');
        this.countdownDisplay = document.querySelector('.autoplay-countdown');
        this.progressBar = document.querySelector('.next-episode-progress-bar');
    }

    initState() {
        this.isVideoEnded = false;
        this.isCountdownActive = false;
        this.countdownTime = this.DEFAULT_COUNTDOWN;
        this.countdownInterval = null;
        this.nextEpisodeData = null;
        this.isAutoplayEnabled = true;
        this.isUserInteracting = false;
    }

    loadUserPreferences() {
        try {
            // Verificar se a classe UserPreferences está disponível
            if (typeof UserPreferences !== 'undefined') {
                this.preferences = new UserPreferences();
                this.preferences.init();
                this.isAutoplayEnabled = this.preferences.autoplay;
                this.countdownTime = this.preferences.countdownTime || this.DEFAULT_COUNTDOWN;
                
                // Sincronizar UI com preferências
                if (this.autoplayToggle) {
                    this.autoplayToggle.checked = this.isAutoplayEnabled;
                }
            } else {
                // Fallback para preferências padrão
                this._createDefaultPreferences();
            }
        } catch (error) {
            console.error('Erro ao carregar preferências:', error);
            this._createDefaultPreferences();
        }
    }

    _createDefaultPreferences() {
        console.warn('UserPreferences não encontrado, usando valores padrão.');
        this.preferences = {
            autoplay: true,
            countdownTime: this.DEFAULT_COUNTDOWN,
            savePreferences: () => {}
        };
        this.isAutoplayEnabled = true;
        this.countdownTime = this.DEFAULT_COUNTDOWN;
    }

    validateElements() {
        if (!this.videoPlayer) {
            console.error('Elemento de vídeo não encontrado.');
            return false;
        }
        
        if (!this.nextEpisodeContainer || !this.nextEpisodeButton) {
            console.warn('Elementos de próximo episódio não encontrados. A funcionalidade será desativada.');
            return false;
        }
        
        return true;
    }

    // Configuração de eventos
    setupEventListeners() {
        // Eventos do player de vídeo
        this.videoPlayer.addEventListener('timeupdate', () => this.checkVideoProgress());
        this.videoPlayer.addEventListener('ended', () => this._handleVideoEnd());
        
        // Eventos de interação do usuário
        this.nextEpisodeButton.addEventListener('click', () => this.playNextEpisode());
        
        if (this.autoplayToggle) {
            this.autoplayToggle.addEventListener('change', (e) => this.toggleAutoplay(e.target.checked));
        }
        
        // Detectar interação do usuário para mostrar/esconder o botão
        document.addEventListener('mousemove', () => this._handleUserInteraction());
    }

    _handleVideoEnd() {
        this.isVideoEnded = true;
        this.showNextEpisodeButton();
        
        if (this.isAutoplayEnabled) {
            this.startCountdown();
        }
    }

    _handleUserInteraction() {
        this.isUserInteracting = true;
        
        // Reset do timer de interação
        clearTimeout(this.interactionTimeout);
        this.interactionTimeout = setTimeout(() => {
            this.isUserInteracting = false;
        }, 3000);
    }

    // Métodos de dados
    fetchNextEpisodeData() {
        // Simulação de API - em produção, substituir por chamada real
        setTimeout(() => {
            const currentVideoId = this.videoPlayer.dataset.videoId || '1';
            const nextEpisodeId = String(Number(currentVideoId) + 1);
            
            // Dados simulados do próximo episódio
            this.nextEpisodeData = {
                id: nextEpisodeId,
                title: this._getNextEpisodeTitle(nextEpisodeId),
                duration: this._getNextEpisodeDuration(nextEpisodeId),
                thumbnail: this._getNextEpisodeThumbnail(nextEpisodeId),
                url: `?video=${nextEpisodeId}`
            };
            
            this.updateNextEpisodeUI();
        }, 1000);
    }
    
    _getNextEpisodeTitle(id) {
        return document.querySelector(`.playlist-item[data-video-id="${id}"] .video-info h3`)?.textContent || 'Próximo Episódio';
    }
    
    _getNextEpisodeDuration(id) {
        return document.querySelector(`.playlist-item[data-video-id="${id}"] .duration`)?.textContent || '25:00';
    }
    
    _getNextEpisodeThumbnail(id) {
        return document.querySelector(`.playlist-item[data-video-id="${id}"] img`)?.src || 'assets/thumbnails/next-episode.jpg';
    }
    
    updateNextEpisodeUI() {
        if (!this.nextEpisodeData) return;
        
        // Atualizar título
        if (this.nextEpisodeInfo) {
            const titleElement = this.nextEpisodeInfo.querySelector('h3');
            if (titleElement) {
                titleElement.textContent = this.nextEpisodeData.title;
            }
        }
        
        // Atualizar thumbnail
        if (this.nextEpisodeThumbnail) {
            this.nextEpisodeThumbnail.src = this.nextEpisodeData.thumbnail;
            this.nextEpisodeThumbnail.alt = this.nextEpisodeData.title;
        }
    }

    // Métodos de controle de vídeo
    checkVideoProgress() {
        if (this.isVideoEnded) return;
        
        const progress = this._calculateVideoProgress();
        
        if (progress >= this.AUTOPLAY_THRESHOLD) {
            this.showNextEpisodeButton();
        } else {
            this.hideNextEpisodeButton();
        }
    }
    
    _calculateVideoProgress() {
        const currentTime = this.videoPlayer.currentTime;
        const duration = this.videoPlayer.duration;
        
        if (isNaN(duration) || duration === 0) return 0;
        
        return currentTime / duration;
    }
    
    showNextEpisodeButton() {
        if (!this.nextEpisodeContainer) return;
        this.nextEpisodeContainer.classList.add('active');
    }
    
    hideNextEpisodeButton() {
        if (!this.nextEpisodeContainer || this.isUserInteracting) return;
        this.nextEpisodeContainer.classList.remove('active');
        this.stopCountdown();
    }

    // Métodos de contagem regressiva
    startCountdown() {
        if (this.isCountdownActive || !this.isAutoplayEnabled) return;
        
        this.isCountdownActive = true;
        this.countdownTime = this.preferences.countdownTime || this.DEFAULT_COUNTDOWN;
        this.updateCountdownDisplay();
        
        this.countdownInterval = setInterval(() => {
            this.countdownTime--;
            this.updateCountdownDisplay();
            
            if (this.countdownTime <= 0) {
                this.stopCountdown();
                this.playNextEpisode();
            }
        }, 1000);
    }
    
    stopCountdown() {
        if (!this.isCountdownActive) return;
        
        clearInterval(this.countdownInterval);
        this.isCountdownActive = false;
        this.countdownTime = this.preferences.countdownTime || this.DEFAULT_COUNTDOWN;
    }
    
    updateCountdownDisplay() {
        if (!this.countdownDisplay) return;
        
        this.countdownDisplay.textContent = this.countdownTime;
        
        if (this.progressBar) {
            const progressPercentage = (this.countdownTime / (this.preferences.countdownTime || this.DEFAULT_COUNTDOWN)) * 100;
            this.progressBar.style.width = `${100 - progressPercentage}%`;
        }
    }

    // Métodos de reprodução
    playNextEpisode() {
        if (!this.nextEpisodeData) {
            console.warn('Dados do próximo episódio não disponíveis.');
            return;
        }
        
        this.stopCountdown();
        this.updateAnalytics(this.nextEpisodeData.id);
        
        const nextEpisodeElement = document.querySelector(`.playlist-item[data-video-id="${this.nextEpisodeData.id}"]`);
        
        if (nextEpisodeElement) {
            // Trocar o vídeo no mesmo player
            nextEpisodeElement.click();
            this.hideNextEpisodeButton();
            this.isVideoEnded = false;
        } else {
            // Redirecionar para a página do próximo episódio
            window.location.href = this.nextEpisodeData.url;
        }
    }
    
    // Métodos auxiliares
    updateAnalytics(videoId) {
        try {
            // Em produção, substituir por chamada real à API de analytics
            console.info(`Análise atualizada: Reprodução do episódio ${videoId}`);
            
            // Exemplo de envio para API
            // fetch('/api/analytics', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ videoId, action: 'next-episode' })
            // });
        } catch (error) {
            console.error('Erro ao atualizar analytics:', error);
        }
    }
    
    toggleAutoplay(enabled) {
        this.isAutoplayEnabled = enabled;
        
        // Salvar preferência
        if (this.preferences && typeof this.preferences.savePreferences === 'function') {
            this.preferences.autoplay = enabled;
            this.preferences.savePreferences();
        }
        
        // Atualizar estado da contagem regressiva
        if (!enabled) {
            this.stopCountdown();
        } else if (this.isVideoEnded) {
            this.startCountdown();
        }
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.nextEpisodeManager = new NextEpisodeManager();
}); 