document.addEventListener('DOMContentLoaded', function() {
    // Elementos do player
    const videoContainer = document.querySelector('.video-container');
    const mainVideo = document.getElementById('main-video');
    const progressArea = document.querySelector('.progress-area');
    const progressBar = document.querySelector('.progress-bar');
    const bufferBar = document.querySelector('.buffer-bar');
    const playPauseBtn = document.querySelector('.play-pause');
    const volumeBtn = document.querySelector('.volume-btn');
    const volumeSlider = document.querySelector('.volume-slider');
    const timeDisplay = document.querySelector('.time-display');
    const settingsBtn = document.querySelector('.settings-btn');
    const settingsMenu = document.querySelector('.settings-menu');
    const qualityMenu = document.getElementById('quality-menu');
    const captionsMenu = document.getElementById('captions-menu');
    const mainMenu = document.getElementById('main-menu');
    const speedBtn = document.querySelector('.speed-btn');
    const qualityBtn = document.querySelector('.quality-btn');
    const captionsBtn = document.querySelector('.captions-btn');
    const pipBtn = document.querySelector('.pip-btn');
    const fullscreenBtn = document.querySelector('.fullscreen-btn');
    const loadingOverlay = document.querySelector('.loading-overlay');
    const backButtons = document.querySelectorAll('.back-button');
    const nextVideoContainer = document.querySelector('.next-video-container');
    const playlistItems = document.querySelectorAll('.playlist-item');
    
    // Variáveis de estado
    let userActivity = null;
    let controlsTimeout = null;
    let currentVolume = 0.8;
    let isSettingsOpen = false;
    
    // Inicializar o player
    init();
    
    // Função de inicialização
    function init() {
        // Definir volume inicial
        mainVideo.volume = currentVolume;
        volumeSlider.value = currentVolume * 100;
        
        // Atualizar buffer durante o carregamento
        mainVideo.addEventListener('progress', updateBuffer);
        
        // Mostrar loading ao iniciar o carregamento
        mainVideo.addEventListener('loadstart', () => {
            loadingOverlay.classList.add('active');
        });
        
        // Esconder loading quando o vídeo estiver pronto
        mainVideo.addEventListener('canplay', () => {
            loadingOverlay.classList.remove('active');
        });
        
        // Mostrar o container do próximo vídeo quando o vídeo atual terminar
        mainVideo.addEventListener('ended', () => {
            if (nextVideoContainer) {
                nextVideoContainer.classList.add('active');
            }
        });
        
        // Inicializar event listeners
        setupEventListeners();
    }
    
    // Configurar todos os event listeners
    function setupEventListeners() {
        // Play/Pause ao clicar no vídeo
        videoContainer.addEventListener('click', function(e) {
            if (e.target.closest('.player-controls') || e.target.closest('.settings-menu')) return;
            togglePlayPause();
        });
        
        // Controle de Play/Pause
        playPauseBtn.addEventListener('click', togglePlayPause);
        
        // Atualizar barra de progresso durante a reprodução
        mainVideo.addEventListener('timeupdate', updateProgress);
        
        // Controle de clique na barra de progresso
        progressArea.addEventListener('click', setProgress);
        
        // Controle de volume
        volumeBtn.addEventListener('click', toggleMute);
        volumeSlider.addEventListener('input', handleVolumeChange);
        
        // Botão de configurações
        settingsBtn.addEventListener('click', toggleSettings);
        
        // Controle de tela cheia
        fullscreenBtn.addEventListener('click', toggleFullscreen);
        
        // PiP (Picture-in-Picture)
        pipBtn.addEventListener('click', togglePiP);
        
        // Voltar para o menu principal
        backButtons.forEach(button => {
            button.addEventListener('click', showMainMenu);
        });
        
        // Controle de velocidade
        speedBtn.addEventListener('click', () => {
            hideMainMenu();
            document.getElementById('speed-menu').classList.add('active');
        });
        
        // Botão de qualidade
        qualityBtn.addEventListener('click', () => {
            hideMainMenu();
            qualityMenu.classList.add('active');
        });
        
        // Botão de legendas
        captionsBtn.addEventListener('click', () => {
            hideMainMenu();
            captionsMenu.classList.add('active');
        });
        
        // Esconder controles quando o mouse sai do player
        videoContainer.addEventListener('mouseleave', () => {
            if (!mainVideo.paused) {
                scheduleHideControls();
            }
        });
        
        // Mostrar controles quando o mouse entra no player
        videoContainer.addEventListener('mouseenter', () => {
            clearTimeout(controlsTimeout);
        });
        
        // Teclado
        document.addEventListener('keydown', handleKeyPress);
        
        // Itens da playlist
        if (playlistItems) {
            playlistItems.forEach(item => {
                item.addEventListener('click', handlePlaylistClick);
            });
        }
    }
    
    // Alternar entre play e pause
    function togglePlayPause() {
        if (mainVideo.paused) {
            mainVideo.play();
            playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
            scheduleHideControls();
        } else {
            mainVideo.pause();
            playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
            clearTimeout(controlsTimeout);
        }
    }
    
    // Atualizar a barra de progresso
    function updateProgress() {
        const percent = (mainVideo.currentTime / mainVideo.duration) * 100;
        progressBar.style.width = `${percent}%`;
        
        // Atualizar o tempo mostrado
        const currentMinutes = Math.floor(mainVideo.currentTime / 60);
        const currentSeconds = Math.floor(mainVideo.currentTime % 60);
        const durationMinutes = Math.floor(mainVideo.duration / 60);
        const durationSeconds = Math.floor(mainVideo.duration % 60);
        
        timeDisplay.textContent = `${currentMinutes}:${currentSeconds < 10 ? '0' : ''}${currentSeconds} / ${durationMinutes}:${durationSeconds < 10 ? '0' : ''}${durationSeconds}`;
    }
    
    // Atualizar o buffer do vídeo
    function updateBuffer() {
        if (mainVideo.buffered.length > 0) {
            const bufferedEnd = mainVideo.buffered.end(mainVideo.buffered.length - 1);
            const duration = mainVideo.duration;
            const bufferedPercent = (bufferedEnd / duration) * 100;
            bufferBar.style.width = `${bufferedPercent}%`;
        }
    }
    
    // Definir o progresso ao clicar na barra
    function setProgress(e) {
        const width = this.clientWidth;
        const clickX = e.offsetX;
        const duration = mainVideo.duration;
        
        mainVideo.currentTime = (clickX / width) * duration;
    }
    
    // Alternar mudo/som
    function toggleMute() {
        if (mainVideo.volume === 0) {
            mainVideo.volume = currentVolume;
            volumeBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
            volumeSlider.value = currentVolume * 100;
        } else {
            currentVolume = mainVideo.volume;
            mainVideo.volume = 0;
            volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            volumeSlider.value = 0;
        }
    }
    
    // Controlar mudança de volume
    function handleVolumeChange() {
        const volume = this.value / 100;
        mainVideo.volume = volume;
        currentVolume = volume;
        
        // Atualizar ícone com base no nível do volume
        if (volume === 0) {
            volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
        } else if (volume < 0.5) {
            volumeBtn.innerHTML = '<i class="fas fa-volume-down"></i>';
        } else {
            volumeBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
        }
    }
    
    // Alternar menu de configurações
    function toggleSettings() {
        if (isSettingsOpen) {
            settingsMenu.classList.remove('active');
            isSettingsOpen = false;
        } else {
            settingsMenu.classList.add('active');
            showMainMenu();
            isSettingsOpen = true;
        }
    }
    
    // Mostrar menu principal
    function showMainMenu() {
        document.querySelectorAll('.settings-submenu').forEach(menu => {
            menu.classList.remove('active');
        });
        mainMenu.classList.add('active');
    }
    
    // Esconder menu principal
    function hideMainMenu() {
        mainMenu.classList.remove('active');
    }
    
    // Alternar tela cheia
    function toggleFullscreen() {
        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
            if (videoContainer.requestFullscreen) {
                videoContainer.requestFullscreen();
            } else if (videoContainer.webkitRequestFullscreen) {
                videoContainer.webkitRequestFullscreen();
            }
            fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
            fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
        }
    }
    
    // Alternar Picture-in-Picture
    function togglePiP() {
        if (document.pictureInPictureElement) {
            document.exitPictureInPicture();
        } else if (document.pictureInPictureEnabled) {
            mainVideo.requestPictureInPicture();
        }
    }
    
    // Manipular teclas do teclado
    function handleKeyPress(e) {
        switch (e.key.toLowerCase()) {
            case ' ':
            case 'k':
                // Espaço ou K para play/pause
                togglePlayPause();
                break;
            case 'm':
                // M para mutar
                toggleMute();
                break;
            case 'f':
                // F para tela cheia
                toggleFullscreen();
                break;
            case 'arrowright':
                // Seta direita para avançar 5 segundos
                mainVideo.currentTime += 5;
                break;
            case 'arrowleft':
                // Seta esquerda para retroceder 5 segundos
                mainVideo.currentTime -= 5;
                break;
            case 'arrowup':
                // Seta para cima para aumentar volume
                if (mainVideo.volume + 0.1 > 1) {
                    mainVideo.volume = 1;
                } else {
                    mainVideo.volume += 0.1;
                }
                volumeSlider.value = mainVideo.volume * 100;
                break;
            case 'arrowdown':
                // Seta para baixo para diminuir volume
                if (mainVideo.volume - 0.1 < 0) {
                    mainVideo.volume = 0;
                } else {
                    mainVideo.volume -= 0.1;
                }
                volumeSlider.value = mainVideo.volume * 100;
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                // Números para pular para porcentagem do vídeo
                const percent = parseInt(e.key) * 10;
                mainVideo.currentTime = (mainVideo.duration * percent) / 100;
                break;
        }
    }
    
    // Esconder controles após inatividade
    function scheduleHideControls() {
        clearTimeout(controlsTimeout);
        if (!isSettingsOpen) {
            controlsTimeout = setTimeout(() => {
                document.querySelector('.player-controls').style.opacity = 0;
            }, 3000);
        }
    }
    
    // Manipular clique em item da playlist
    function handlePlaylistClick() {
        const videoId = this.dataset.videoId;
        const videoUrl = this.dataset.videoUrl;
        const videoTitle = this.querySelector('.video-info h3').textContent;
        
        // Atualizar o vídeo atual
        mainVideo.src = videoUrl;
        document.querySelector('.video-details h2').textContent = videoTitle;
        
        // Atualizar classe ativa
        document.querySelector('.playlist-item.active')?.classList.remove('active');
        this.classList.add('active');
        
        // Iniciar reprodução
        mainVideo.play();
        playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
        
        // Esconder o container do próximo vídeo se estiver visível
        if (nextVideoContainer && nextVideoContainer.classList.contains('active')) {
            nextVideoContainer.classList.remove('active');
        }
    }
    
    // Funções para alterar a velocidade de reprodução
    window.changeSpeed = function(speed) {
        mainVideo.playbackRate = speed;
        document.querySelector('.speed-btn span').textContent = speed + 'x';
        document.querySelectorAll('#speed-menu li').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.speed == speed) {
                item.classList.add('active');
            }
        });
        toggleSettings();
    };
    
    // Função para mudar a qualidade
    window.changeQuality = function(quality) {
        // Aqui você implementaria a lógica para mudar a qualidade
        // Por exemplo, carregando uma fonte diferente do vídeo
        console.log('Qualidade alterada para: ' + quality);
        document.querySelector('.quality-btn span').textContent = quality;
        document.querySelectorAll('#quality-menu li').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.quality == quality) {
                item.classList.add('active');
            }
        });
        toggleSettings();
    };
    
    // Função para ligar/desligar legendas
    window.toggleCaptions = function(value) {
        // Implementar lógica para mostrar/esconder legendas
        if (value === 'on') {
            mainVideo.textTracks[0].mode = 'showing';
            document.querySelector('.captions-btn span').textContent = 'PT-BR';
        } else {
            mainVideo.textTracks[0].mode = 'hidden';
            document.querySelector('.captions-btn span').textContent = 'Desligado';
        }
        document.querySelectorAll('#captions-menu li').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.caption == value) {
                item.classList.add('active');
            }
        });
        toggleSettings();
    };
}); 