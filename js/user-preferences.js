/**
 * Gerencia as preferências do usuário para o player
 * Versão: 1.0.0
 */
class UserPreferences {
    constructor() {
        // Chave para armazenamento local
        this.STORAGE_KEY = 'player_preferences';
        
        // Valores padrão
        this.defaults = {
            autoplay: true,            // Reproduzir automaticamente o próximo episódio
            countdownTime: 15,         // Tempo de contagem regressiva (segundos)
            volume: 0.8,               // Volume (0-1)
            playbackSpeed: 1,          // Velocidade de reprodução
            quality: 'auto',           // Qualidade do vídeo
            subtitles: 'off',          // Legendas
            theme: 'dark',             // Tema da interface
            historyEnabled: true,      // Guardar histórico de visualização
            resumePlayback: true       // Continuar de onde parou
        };
        
        // Estado atual (será preenchido no init)
        this.autoplay = this.defaults.autoplay;
        this.countdownTime = this.defaults.countdownTime;
        this.volume = this.defaults.volume;
        this.playbackSpeed = this.defaults.playbackSpeed;
        this.quality = this.defaults.quality;
        this.subtitles = this.defaults.subtitles;
        this.theme = this.defaults.theme;
        this.historyEnabled = this.defaults.historyEnabled;
        this.resumePlayback = this.defaults.resumePlayback;
    }
    
    /**
     * Inicializa as preferências, carregando do armazenamento local
     */
    init() {
        // Carregar preferências salvas
        this.loadPreferences();
        
        // Aplicar preferências carregadas ao player
        this.applyPreferences();
        
        console.log('Preferências do usuário inicializadas');
    }
    
    /**
     * Carrega as preferências do armazenamento local
     */
    loadPreferences() {
        try {
            // Tentar obter do localStorage
            const savedPrefs = localStorage.getItem(this.STORAGE_KEY);
            
            if (savedPrefs) {
                const parsedPrefs = JSON.parse(savedPrefs);
                
                // Mesclar preferências salvas com os valores padrão
                this.autoplay = parsedPrefs.autoplay !== undefined ? parsedPrefs.autoplay : this.defaults.autoplay;
                this.countdownTime = parsedPrefs.countdownTime || this.defaults.countdownTime;
                this.volume = parsedPrefs.volume !== undefined ? parsedPrefs.volume : this.defaults.volume;
                this.playbackSpeed = parsedPrefs.playbackSpeed || this.defaults.playbackSpeed;
                this.quality = parsedPrefs.quality || this.defaults.quality;
                this.subtitles = parsedPrefs.subtitles || this.defaults.subtitles;
                this.theme = parsedPrefs.theme || this.defaults.theme;
                this.historyEnabled = parsedPrefs.historyEnabled !== undefined ? parsedPrefs.historyEnabled : this.defaults.historyEnabled;
                this.resumePlayback = parsedPrefs.resumePlayback !== undefined ? parsedPrefs.resumePlayback : this.defaults.resumePlayback;
            }
        } catch (error) {
            console.error('Erro ao carregar preferências:', error);
            // Em caso de erro, usar os valores padrão
            this.resetToDefaults();
        }
    }
    
    /**
     * Salva as preferências no armazenamento local
     */
    savePreferences() {
        try {
            // Criar objeto com todas as preferências
            const prefsToSave = {
                autoplay: this.autoplay,
                countdownTime: this.countdownTime,
                volume: this.volume,
                playbackSpeed: this.playbackSpeed,
                quality: this.quality,
                subtitles: this.subtitles,
                theme: this.theme,
                historyEnabled: this.historyEnabled,
                resumePlayback: this.resumePlayback
            };
            
            // Salvar no localStorage
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(prefsToSave));
            
            console.log('Preferências salvas com sucesso');
        } catch (error) {
            console.error('Erro ao salvar preferências:', error);
        }
    }
    
    /**
     * Aplica as preferências ao player
     */
    applyPreferences() {
        // Aplicar tema
        this.applyTheme();
        
        // Aplicar volume se houver um elemento de vídeo
        const videoElement = document.querySelector('video');
        if (videoElement) {
            videoElement.volume = this.volume;
            videoElement.playbackRate = this.playbackSpeed;
        }
        
        // Outras aplicações de preferências podem ser adicionadas aqui
    }
    
    /**
     * Aplica o tema da interface
     */
    applyTheme() {
        // Remover classes de tema anteriores
        document.body.classList.remove('theme-light', 'theme-dark', 'theme-custom');
        
        // Adicionar classe do tema atual
        document.body.classList.add(`theme-${this.theme}`);
    }
    
    /**
     * Redefine todas as preferências para os valores padrão
     */
    resetToDefaults() {
        this.autoplay = this.defaults.autoplay;
        this.countdownTime = this.defaults.countdownTime;
        this.volume = this.defaults.volume;
        this.playbackSpeed = this.defaults.playbackSpeed;
        this.quality = this.defaults.quality;
        this.subtitles = this.defaults.subtitles;
        this.theme = this.defaults.theme;
        this.historyEnabled = this.defaults.historyEnabled;
        this.resumePlayback = this.defaults.resumePlayback;
        
        // Salvar os valores padrão
        this.savePreferences();
        
        // Aplicar os valores padrão
        this.applyPreferences();
    }
    
    /**
     * Define o volume
     */
    setVolume(value) {
        // Validar valor
        if (value < 0) value = 0;
        if (value > 1) value = 1;
        
        this.volume = value;
        this.savePreferences();
        
        // Aplicar ao vídeo
        const videoElement = document.querySelector('video');
        if (videoElement) {
            videoElement.volume = value;
        }
    }
    
    /**
     * Define a velocidade de reprodução
     */
    setPlaybackSpeed(value) {
        // Validar valor
        if (value < 0.25) value = 0.25;
        if (value > 2) value = 2;
        
        this.playbackSpeed = value;
        this.savePreferences();
        
        // Aplicar ao vídeo
        const videoElement = document.querySelector('video');
        if (videoElement) {
            videoElement.playbackRate = value;
        }
    }
    
    /**
     * Define a qualidade do vídeo
     */
    setQuality(value) {
        this.quality = value;
        this.savePreferences();
        
        // A aplicação da qualidade geralmente é feita pelo player HLS
    }
    
    /**
     * Define o estado das legendas
     */
    setSubtitles(value) {
        this.subtitles = value;
        this.savePreferences();
        
        // A aplicação das legendas geralmente é feita pelo player
    }
    
    /**
     * Define o tema da interface
     */
    setTheme(value) {
        if (['light', 'dark', 'custom'].indexOf(value) === -1) {
            value = 'dark'; // Valor padrão
        }
        
        this.theme = value;
        this.savePreferences();
        this.applyTheme();
    }
}

// Exportar para uso em outros arquivos
export { UserPreferences }; 