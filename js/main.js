document.addEventListener('DOMContentLoaded', function() {
    // Elementos
    const mainVideo = document.getElementById('main-video');
    const videoContainer = document.querySelector('.video-container');
    const playPauseBtn = document.getElementById('play-pause-btn');
    const progressArea = document.querySelector('.progress-area');
    const progressBar = document.querySelector('.progress-bar');
    const bufferBar = document.querySelector('.buffer-bar');
    const volumeBtn = document.getElementById('volume-btn');
    const volumeSlider = document.querySelector('.volume-slider');
    const currentTimeEl = document.querySelector('.current-time');
    const durationEl = document.querySelector('.duration');
    const speedBtn = document.getElementById('speed-btn');
    const pipBtn = document.getElementById('pip-btn');
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    const playlist = document.querySelector('.playlist');
    const nextVideoButton = document.querySelector('.btn-next');
    const playlistItems = document.querySelectorAll('.playlist-item');
    const qualityBtn = document.getElementById('quality-btn');
    const captionsBtn = document.getElementById('captions-btn');
    const settingsBtn = document.getElementById('settings-btn');
    const loadingOverlay = document.querySelector('.loading-overlay');
    const qualityMenu = document.getElementById('quality-menu');
    const captionsMenu = document.getElementById('captions-menu');
    const mainSettingsMenu = document.getElementById('main-settings-menu');
    const backButtons = document.querySelectorAll('.back-button');
    
    // Analytics
    const watchTimeElement = document.getElementById('watch-time');
    const watchPercentElement = document.getElementById('watch-percent');
    const pauseCountElement = document.getElementById('pause-count');
    const currentQualityElement = document.getElementById('current-quality');
    const qualitySwitchesElement = document.getElementById('quality-switches');
    const heatmapBar = document.querySelector('.heatmap-bar');
    
    // Variáveis para estatísticas
    let watchStartTime = 0;
    let totalWatchTime = 0;
    let qualitySwitches = 0;
    let pauseCount = 0;
    let videoLoaded = false;
    let currentQuality = 'Auto';

    // Instância de HLS
    let hls = null;
    
    // Legendas disponíveis
    const availableCaptions = [
        { label: 'Português', srclang: 'pt', src: 'media/subtitles/portuguese.vtt' },
        { label: 'English', srclang: 'en', src: 'media/subtitles/english.vtt' },
        { label: 'Español', srclang: 'es', src: 'media/subtitles/spanish.vtt' }
    ];
    
    // Inicializar o player
    initPlayer();
    
    function initPlayer() {
        // Carregar primeiro vídeo
        const firstItem = playlistItems[0];
        if (firstItem) {
            const videoSrc = firstItem.dataset.videoSrc;
            const posterSrc = firstItem.dataset.posterSrc;
            const videoTitle = firstItem.querySelector('.video-info h3').textContent;
            
            initializeHLS(videoSrc);
            mainVideo.poster = posterSrc;
            
            // Marcar primeiro item como ativo
            firstItem.classList.add('active');
        }
        
        // Event listeners
        mainVideo.addEventListener('timeupdate', updateProgress);
        mainVideo.addEventListener('progress', updateBufferBar);
        mainVideo.addEventListener('play', function() {
            playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
            watchStartTime = new Date().getTime();
        });
        mainVideo.addEventListener('pause', function() {
            playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
            updateWatchTime();
            pauseCount++;
            updateAnalytics();
        });
        mainVideo.addEventListener('ended', checkShowNextButton);
        
        // Carregamento
        mainVideo.addEventListener('loadstart', showLoading);
        mainVideo.addEventListener('canplay', hideLoading);
        mainVideo.addEventListener('waiting', showLoading);
        mainVideo.addEventListener('playing', hideLoading);
        
        // Controles
        playPauseBtn.addEventListener('click', togglePlayPause);
        volumeBtn.addEventListener('click', toggleMute);
        volumeSlider.addEventListener('input', changeVolume);
        progressArea.addEventListener('click', seek);
        fullscreenBtn.addEventListener('click', toggleFullscreen);
        pipBtn.addEventListener('click', togglePiP);
        settingsBtn.addEventListener('click', toggleMainSettings);
        
        // Botões de menu
        qualityBtn.addEventListener('click', showQualityMenu);
        captionsBtn.addEventListener('click', showCaptionsMenu);
        
        // Botões de voltar
        backButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                hideAllMenus();
                mainSettingsMenu.classList.add('active');
            });
        });
        
        // Itens da playlist
        playlistItems.forEach((item, index) => {
            item.addEventListener('click', function() {
                // Remover classe ativa de todos os itens
                playlistItems.forEach(i => i.classList.remove('active'));
                
                // Adicionar classe ativa ao item clicado
                this.classList.add('active');
                
                // Obter informações do vídeo
                const videoSrc = this.dataset.videoSrc;
                const posterSrc = this.dataset.posterSrc;
                const videoTitle = this.querySelector('.video-info h3').textContent;
                
                // Mudar vídeo
                changeVideo(videoSrc, posterSrc, videoTitle, index);
            });
        });
        
        // Botão de próximo vídeo (antigo, mantido para compatibilidade)
        if (nextVideoButton) {
            nextVideoButton.addEventListener('click', function() {
                const nextIndex = parseInt(this.dataset.index);
                const nextItem = playlistItems[nextIndex];
                
                if (nextItem) {
                    nextItem.click();
                }
            });
        }
        
        // Configurar legendas
        setupCaptions();
        
        // Definir volume inicial
        mainVideo.volume = 0.8;
        volumeSlider.value = 0.8;
        
        // Atualizar display de qualidade
        updateQualityDisplay();
    }
    
    // Inicializar HLS caso o navegador não suporte nativamente
    function initializeHLS(videoSrc) {
        if (mainVideo.canPlayType('application/vnd.apple.mpegurl')) {
            // Navegador suporta HLS nativamente (Safari)
            mainVideo.src = videoSrc;
        } else if (Hls.isSupported()) {
            if (hls) {
                hls.destroy();
            }
            
            showLoading();
            
            hls = new Hls({
                autoStartLoad: true,
                startLevel: -1, // Automático
                capLevelToPlayerSize: true,
                maxBufferLength: 30,
                maxMaxBufferLength: 60
            });
            
            hls.loadSource(videoSrc);
            hls.attachMedia(mainVideo);
            
            hls.on(Hls.Events.MANIFEST_PARSED, function() {
                hideLoading();
                setupQualityLevels();
            });
            
            hls.on(Hls.Events.ERROR, function(event, data) {
                console.error('HLS error:', data);
                if (data.fatal) {
                    switch(data.type) {
                        case Hls.ErrorTypes.NETWORK_ERROR:
                            console.log('Network error - trying to recover');
                            hls.startLoad();
                            break;
                        case Hls.ErrorTypes.MEDIA_ERROR:
                            console.log('Media error - trying to recover');
                            hls.recoverMediaError();
                            break;
                        default:
                            // Erro fatal irrecuperável
                            console.error('Fatal error - cannot recover');
                            break;
                    }
                }
            });
            
            // Eventos para acompanhar buffer
            hls.on(Hls.Events.BUFFER_APPENDING, updateBufferBar);
        } else {
            // Fallback para navegadores que não suportam HLS
            console.warn('HLS não é suportado neste navegador. Usando vídeo normal.');
            mainVideo.src = videoSrc.replace('.m3u8', '.mp4');
        }
    }
    
    // Configurar os níveis de qualidade
    function setupQualityLevels() {
        if (!hls) return;
        
        const levels = hls.levels;
        const qualityLevels = document.querySelector('#quality-menu ul');
        qualityLevels.innerHTML = '';
        
        // Adicionar opção Auto
        const autoOption = document.createElement('li');
        autoOption.textContent = 'Auto';
        autoOption.dataset.levelIndex = -1;
        autoOption.classList.add('active');
        qualityLevels.appendChild(autoOption);
        
        // Adicionar opções de qualidade disponíveis
        if (levels && levels.length > 0) {
            levels.forEach((level, index) => {
                const li = document.createElement('li');
                li.textContent = `${level.height}p`;
                li.dataset.levelIndex = index;
                
                qualityLevels.appendChild(li);
                
                li.addEventListener('click', function() {
                    hls.currentLevel = parseInt(this.dataset.levelIndex);
                    currentQuality = this.textContent;
                    updateQualityDisplay();
                    qualitySwitches++;
                    updateAnalytics();
                    
                    // Atualizar indicador ativo
                    document.querySelectorAll('#quality-menu ul li').forEach(item => {
                        item.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Fechar menu
                    qualityMenu.classList.remove('active');
                    mainSettingsMenu.classList.add('active');
                });
            });
        }
        
        // Adicionar evento para Auto
        autoOption.addEventListener('click', function() {
            hls.currentLevel = -1; // Auto
            currentQuality = 'Auto';
            updateQualityDisplay();
            
            // Atualizar indicador ativo
            document.querySelectorAll('#quality-menu ul li').forEach(item => {
                item.classList.remove('active');
            });
            this.classList.add('active');
            
            // Fechar menu
            qualityMenu.classList.remove('active');
            mainSettingsMenu.classList.add('active');
        });
    }
    
    // Atualizar exibição da qualidade atual
    function updateQualityDisplay() {
        const qualityDisplay = document.querySelector('#quality-btn span');
        if (qualityDisplay) {
            qualityDisplay.textContent = currentQuality;
        }
        
        // Atualizar no analytics
        currentQualityElement.textContent = currentQuality;
    }
    
    // Configurar legendas
    function setupCaptions() {
        const captionsList = document.querySelector('#captions-menu ul');
        captionsList.innerHTML = '';
        
        // Opção para desligar legendas
        const offOption = document.createElement('li');
        offOption.textContent = 'Desativadas';
        offOption.dataset.srclang = 'off';
        offOption.classList.add('active');
        captionsList.appendChild(offOption);
        
        // Remover legendas existentes
        const existingTracks = mainVideo.querySelectorAll('track');
        existingTracks.forEach(track => track.remove());
        
        // Adicionar opções de legendas disponíveis
        availableCaptions.forEach(caption => {
            const li = document.createElement('li');
            li.textContent = caption.label;
            li.dataset.srclang = caption.srclang;
            captionsList.appendChild(li);
            
            // Adicionar track ao vídeo
            const track = document.createElement('track');
            track.kind = 'subtitles';
            track.label = caption.label;
            track.srclang = caption.srclang;
            track.src = caption.src;
            
            // Esconder a legenda por padrão
            track.mode = 'hidden';
            
            mainVideo.appendChild(track);
            
            // Evento de clique para ativar legenda
            li.addEventListener('click', function() {
                // Desativar todas as legendas
                mainVideo.textTracks.forEach(track => {
                    track.mode = 'hidden';
                });
                
                const srclang = this.dataset.srclang;
                
                // Ativar legenda selecionada
                if (srclang !== 'off') {
                    const selectedTrack = Array.from(mainVideo.textTracks)
                        .find(track => track.language === srclang);
                    
                    if (selectedTrack) {
                        selectedTrack.mode = 'showing';
                    }
                    
                    // Atualizar texto do botão
                    document.querySelector('#captions-btn span').textContent = this.textContent;
                } else {
                    // Atualizar texto do botão para desativado
                    document.querySelector('#captions-btn span').textContent = 'Desativadas';
                }
                
                // Atualizar item ativo
                document.querySelectorAll('#captions-menu ul li').forEach(item => {
                    item.classList.remove('active');
                });
                this.classList.add('active');
                
                // Fechar menu
                captionsMenu.classList.remove('active');
                mainSettingsMenu.classList.add('active');
            });
        });
    }
    
    // Mostrar overlay de carregamento
    function showLoading() {
        loadingOverlay.classList.add('active');
    }
    
    // Esconder overlay de carregamento
    function hideLoading() {
        loadingOverlay.classList.remove('active');
    }
    
    // Alternar entre play e pause
    function togglePlayPause() {
        if (mainVideo.paused) {
            mainVideo.play();
            playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
            watchStartTime = new Date().getTime();
        } else {
            mainVideo.pause();
            playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
            updateWatchTime();
            pauseCount++;
            updateAnalytics();
        }
    }
    
    // Atualizar barra de progresso
    function updateProgress() {
        if (mainVideo.duration) {
            const percentage = (mainVideo.currentTime / mainVideo.duration) * 100;
            progressBar.style.width = `${percentage}%`;
            
            // Atualizar tempo exibido
            currentTimeEl.textContent = formatTime(mainVideo.currentTime);
            durationEl.textContent = formatTime(mainVideo.duration);
            
            // Atualizar analytics
            updateAnalytics();
        }
    }
    
    // Atualizar barra de buffer
    function updateBufferBar() {
        if (mainVideo.buffered.length > 0) {
            const bufferedEnd = mainVideo.buffered.end(mainVideo.buffered.length - 1);
            const duration = mainVideo.duration;
            const bufferedPercent = (bufferedEnd / duration) * 100;
            
            bufferBar.style.width = `${bufferedPercent}%`;
        }
    }
    
    // Buscar no vídeo
    function seek(e) {
        const percent = e.offsetX / progressArea.clientWidth;
        mainVideo.currentTime = percent * mainVideo.duration;
    }
    
    // Alternar mudo
    function toggleMute() {
        if (mainVideo.muted) {
            mainVideo.muted = false;
            volumeBtn.innerHTML = mainVideo.volume > 0.5 ? 
                '<i class="fas fa-volume-up"></i>' : 
                '<i class="fas fa-volume-down"></i>';
        } else {
            mainVideo.muted = true;
            volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
        }
    }
    
    // Alterar volume
    function changeVolume() {
        mainVideo.volume = volumeSlider.value;
        mainVideo.muted = false;
        
        if (mainVideo.volume === 0) {
            volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
        } else if (mainVideo.volume < 0.5) {
            volumeBtn.innerHTML = '<i class="fas fa-volume-down"></i>';
        } else {
            volumeBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
        }
    }
    
    // Alternar velocidade
    function toggleSpeed() {
        const speedOptions = document.querySelector('#speed-menu');
        speedOptions.classList.toggle('show');
    }
    
    // Alterar velocidade de reprodução
    window.changeSpeed = function(speed) {
        mainVideo.playbackRate = speed;
        document.querySelector('#speed-btn span').textContent = speed + 'x';
        
        // Remover classe ativa de todas as opções
        document.querySelectorAll('#speed-menu li').forEach(item => {
            item.classList.remove('active');
        });
        
        // Adicionar classe ativa à opção selecionada
        document.querySelector(`#speed-menu li[data-speed="${speed}"]`).classList.add('active');
        
        // Fechar menu
        hideAllMenus();
    };
    
    // Alternar Picture-in-Picture
    function togglePiP() {
        if (document.pictureInPictureElement) {
            document.exitPictureInPicture();
        } else if (document.pictureInPictureEnabled) {
            mainVideo.requestPictureInPicture();
        }
    }
    
    // Alternar tela cheia
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            videoContainer.requestFullscreen();
            fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
        } else {
            document.exitFullscreen();
            fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
        }
    }
    
    // Mudar vídeo
    function changeVideo(videoSrc, posterSrc, videoTitle, index) {
        // Pausar vídeo atual e resetar
        mainVideo.pause();
        
        // Resetar analytics
        resetAnalytics();
        
        // Atualizar índice atual
        const currentIndex = index;
        
        // Atualizar poster
        mainVideo.poster = posterSrc;
        
        // Carregar nova fonte
        initializeHLS(videoSrc);
        
        // Atualizar título
        const titleElement = document.querySelector('.video-details h2');
        if (titleElement) {
            titleElement.textContent = videoTitle;
        }
        
        // Reproduzir quando estiver pronto
        mainVideo.addEventListener('canplay', function onCanPlay() {
            mainVideo.play();
            mainVideo.removeEventListener('canplay', onCanPlay);
        });
        
        // Ocultar painel de próximo vídeo se estiver visível
        const nextVideoContainer = document.querySelector('.next-video-container');
        if (nextVideoContainer && nextVideoContainer.classList.contains('active')) {
            nextVideoContainer.classList.remove('active');
        }
        
        // Ocultar botão do novo próximo episódio se estiver visível
        const nextEpisodeContainer = document.querySelector('.next-episode-container');
        if (nextEpisodeContainer && nextEpisodeContainer.classList.contains('active')) {
            nextEpisodeContainer.classList.remove('active');
        }
        
        // Atualizar botão de próximo vídeo
        updateNextVideoButton(currentIndex);
    }
    
    // Atualizar botão de próximo vídeo
    function updateNextVideoButton(currentIndex) {
        const nextIndex = currentIndex + 1 < playlistItems.length ? currentIndex + 1 : 0;
        const nextItem = playlistItems[nextIndex];
        
        if (nextVideoButton && nextItem) {
            const nextTitle = nextItem.querySelector('.video-info h3').textContent;
            const nextDesc = nextItem.querySelector('.video-info p').textContent;
            const nextSrc = nextItem.dataset.videoSrc;
            const nextPoster = nextItem.dataset.posterSrc;
            
            // Atualizar texto
            const titleEl = document.querySelector('.next-video-info h3');
            const descEl = document.querySelector('.next-video-description');
            
            if (titleEl) titleEl.textContent = nextTitle;
            if (descEl) descEl.textContent = nextDesc;
            
            // Atualizar atributos
            nextVideoButton.dataset.index = nextIndex;
            nextVideoButton.dataset.src = nextSrc;
            nextVideoButton.dataset.poster = nextPoster;
        }
    }
    
    // Verificar e mostrar botão de próximo episódio
    function checkShowNextButton() {
        // Esta função agora não faz nada, pois o gerenciamento 
        // da exibição do próximo episódio foi movido para o next-episode.js
        // O old container é ocultado via CSS para dar preferência ao novo componente
    }
    
    // Resetar estatísticas de analytics
    function resetAnalytics() {
        totalWatchTime = 0;
        pauseCount = 0;
        qualitySwitches = 0;
        watchStartTime = 0;
        
        updateAnalytics();
    }
    
    // Atualizar tempo assistido
    function updateWatchTime() {
        if (watchStartTime > 0) {
            const now = new Date().getTime();
            const elapsed = (now - watchStartTime) / 1000; // em segundos
            totalWatchTime += elapsed;
            watchStartTime = 0;
        }
    }
    
    // Atualizar analytics
    function updateAnalytics() {
        // Atualizar tempo atual assistido durante reprodução
        if (!mainVideo.paused && watchStartTime > 0) {
            const now = new Date().getTime();
            const currentSessionTime = (now - watchStartTime) / 1000;
            const currentTotal = totalWatchTime + currentSessionTime;
            
            // Atualizar elementos de UI
            watchTimeElement.textContent = formatTime(currentTotal);
        } else {
            watchTimeElement.textContent = formatTime(totalWatchTime);
        }
        
        // Atualizar contador de pausas
        pauseCountElement.textContent = pauseCount;
        
        // Atualizar trocas de qualidade
        qualitySwitchesElement.textContent = qualitySwitches;
        
        // Atualizar percentual visualizado
        if (mainVideo.duration) {
            const percent = Math.round((mainVideo.currentTime / mainVideo.duration) * 100);
            watchPercentElement.textContent = `${percent}%`;
            
            // Atualizar heatmap
            heatmapBar.style.width = `${percent}%`;
        }
    }
    
    // Formatar tempo (segundos para MM:SS)
    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
    }
    
    // Toggle menu principal de configurações
    function toggleMainSettings() {
        if (mainSettingsMenu.classList.contains('active')) {
            hideAllMenus();
        } else {
            hideAllMenus();
            mainSettingsMenu.classList.add('active');
        }
    }
    
    // Mostrar menu de qualidade
    function showQualityMenu() {
        hideAllMenus();
        qualityMenu.classList.add('active');
    }
    
    // Mostrar menu de legendas
    function showCaptionsMenu() {
        hideAllMenus();
        captionsMenu.classList.add('active');
    }
    
    // Esconder todos os menus
    function hideAllMenus() {
        mainSettingsMenu.classList.remove('active');
        qualityMenu.classList.remove('active');
        captionsMenu.classList.remove('active');
    }

    // Funcionalidade do botão de administração e modal de login
    const adminButton = document.getElementById('adminButton');
    const loginModal = document.getElementById('loginModal');
    const closeLogin = document.getElementById('closeLogin');
    const loginForm = document.getElementById('loginForm');
    
    // Mostrar o modal de login quando o botão de admin for clicado
    adminButton.addEventListener('click', function() {
        loginModal.classList.add('show');
        document.getElementById('username').focus();
    });
    
    // Fechar o modal quando o botão de fechar for clicado
    closeLogin.addEventListener('click', function() {
        loginModal.classList.remove('show');
    });
    
    // Fechar o modal quando clicar fora dele
    window.addEventListener('click', function(event) {
        if (event.target === loginModal) {
            loginModal.classList.remove('show');
        }
    });
    
    // Processar o envio do formulário
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const remember = document.getElementById('remember').checked;
        
        // Aqui você pode adicionar a lógica para autenticar o usuário
        // Por exemplo, fazer uma requisição AJAX para o servidor
        
        console.log('Tentativa de login:', { username, password, remember });
        
        // Exemplo de verificação simples (deve ser substituído por uma autenticação real)
        if (username === 'admin' && password === 'admin123') {
            // Login bem-sucedido
            alert('Login bem-sucedido! Redirecionando para o painel administrativo...');
            // Redirecionar para o painel admin ou atualizar a interface
            setTimeout(() => {
                window.location.href = 'admin/dashboard.html';
            }, 1000);
        } else {
            // Login falhou
            alert('Usuário ou senha incorretos. Tente novamente.');
        }
    });

    // Login Modal Functions
    function openLoginModal() {
        const modal = document.getElementById('loginModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.body.style.overflow = 'hidden';
        }
    }

    function closeLoginModal() {
        const modal = document.getElementById('loginModal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }, 300);
        }
    }

    // Close modal when clicking outside content
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('loginModal');
        const loginButtons = document.querySelectorAll('.login-button');
        const closeButtons = document.querySelectorAll('.close-login');
        
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeLoginModal();
                }
            });
            
            loginButtons.forEach(button => {
                button.addEventListener('click', openLoginModal);
            });
            
            closeButtons.forEach(button => {
                button.addEventListener('click', closeLoginModal);
            });
        }
        
        // Toggle password visibility
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');
        togglePasswordButtons.forEach(button => {
            button.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    });
}); 