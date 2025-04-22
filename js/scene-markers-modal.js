/**
 * scene-markers-modal.js v1.0.0
 * 
 * Script para o modal de visualização de marcadores de cena.
 * Permite que usuários visualizem e naveguem facilmente para os marcadores.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar o modal de marcadores
    SceneMarkersModal.init();
});

/**
 * Namespace para o modal de marcadores de cena
 */
const SceneMarkersModal = {
    modalElement: null,
    isVisible: false,
    contentId: null,
    contentType: null,
    markers: [],
    
    /**
     * Inicializa o modal de marcadores
     */
    init: function() {
        // Criar e adicionar o botão para abrir o modal de marcadores
        this.createMarkersButton();
        
        // Criar o modal
        this.createModal();
        
        // Verificar se estamos em uma página de conteúdo
        const playerContainer = document.querySelector('.player-container');
        if (!playerContainer) return;
        
        // Obter informações do conteúdo
        this.contentId = playerContainer.dataset.contentId;
        this.contentType = playerContainer.dataset.contentType;
        
        if (!this.contentId || !this.contentType) return;
        
        // Carregar marcadores
        this.loadMarkers();
    },
    
    /**
     * Cria o botão para abrir o modal de marcadores
     */
    createMarkersButton: function() {
        const playerContainer = document.querySelector('.player-container');
        if (!playerContainer) return;
        
        const videoControls = playerContainer.querySelector('.video-controls');
        if (!videoControls) return;
        
        const markersButton = document.createElement('button');
        markersButton.className = 'control-button markers-button';
        markersButton.title = 'Marcadores de cena';
        markersButton.innerHTML = '<i class="fas fa-bookmark"></i>';
        
        markersButton.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleModal();
        });
        
        // Inserir botão nos controles
        videoControls.appendChild(markersButton);
    },
    
    /**
     * Cria o modal de marcadores
     */
    createModal: function() {
        // Verificar se o modal já existe
        if (document.querySelector('.markers-modal')) return;
        
        // Criar elemento do modal
        const modal = document.createElement('div');
        modal.className = 'markers-modal';
        modal.id = 'scene-markers-modal';
        
        modal.innerHTML = `
            <div class="markers-modal-content">
                <div class="markers-modal-header">
                    <h3>Marcadores de Cena</h3>
                    <button class="close-modal-button">&times;</button>
                </div>
                <div class="markers-list">
                    <p>Carregando marcadores...</p>
                </div>
            </div>
        `;
        
        // Adicionar evento para fechar o modal
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.hideModal();
            }
        });
        
        // Adicionar evento ao botão de fechar
        modal.querySelector('.close-modal-button').addEventListener('click', () => {
            this.hideModal();
        });
        
        // Adicionar ao documento
        document.body.appendChild(modal);
        
        // Guardar referência
        this.modalElement = modal;
    },
    
    /**
     * Carrega os marcadores de cena
     */
    loadMarkers: function() {
        fetch(`/api/load-markers.php?contentId=${this.contentId}&contentType=${this.contentType}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.markers) {
                    this.markers = [...data.data.markers];
                    this.renderMarkersList();
                } else {
                    this.showNoMarkersMessage();
                }
            })
            .catch(error => {
                console.error('Erro ao carregar marcadores:', error);
                this.showNoMarkersMessage();
            });
    },
    
    /**
     * Renderiza a lista de marcadores no modal
     */
    renderMarkersList: function() {
        if (!this.modalElement) return;
        
        const markersListContainer = this.modalElement.querySelector('.markers-list');
        if (!markersListContainer) return;
        
        // Limpar conteúdo atual
        markersListContainer.innerHTML = '';
        
        // Verificar se há marcadores
        if (this.markers.length === 0) {
            this.showNoMarkersMessage();
            return;
        }
        
        // Ordenar marcadores por tempo
        const sortedMarkers = [...this.markers].sort((a, b) => a.time - b.time);
        
        // Criar elementos para cada marcador
        sortedMarkers.forEach((marker, index) => {
            const markerItem = document.createElement('div');
            markerItem.className = 'marker-item';
            
            const timeFormatted = this.formatTime(marker.time);
            
            markerItem.innerHTML = `
                <span class="marker-time">${timeFormatted}</span>
                <span class="marker-title">${marker.title}</span>
                <div class="marker-actions">
                    <button class="jump-to-marker" data-time="${marker.time}" title="Ir para este marcador">
                        <i class="fas fa-play"></i>
                    </button>
                </div>
            `;
            
            // Adicionar evento para ir para o marcador
            const jumpButton = markerItem.querySelector('.jump-to-marker');
            jumpButton.addEventListener('click', () => {
                this.jumpToMarker(marker.time);
            });
            
            markersListContainer.appendChild(markerItem);
        });
    },
    
    /**
     * Mostra mensagem de que não há marcadores
     */
    showNoMarkersMessage: function() {
        if (!this.modalElement) return;
        
        const markersListContainer = this.modalElement.querySelector('.markers-list');
        if (!markersListContainer) return;
        
        markersListContainer.innerHTML = '<p>Nenhum marcador disponível para este conteúdo.</p>';
    },
    
    /**
     * Pula para um tempo específico no vídeo
     */
    jumpToMarker: function(time) {
        const videoElement = document.querySelector('.player-container video');
        if (!videoElement) return;
        
        // Definir o tempo
        videoElement.currentTime = time;
        
        // Iniciar reprodução
        videoElement.play();
        
        // Fechar o modal
        this.hideModal();
    },
    
    /**
     * Mostra/esconde o modal
     */
    toggleModal: function() {
        if (!this.modalElement) return;
        
        if (this.isVisible) {
            this.hideModal();
        } else {
            this.showModal();
        }
    },
    
    /**
     * Mostra o modal
     */
    showModal: function() {
        if (!this.modalElement) return;
        
        this.modalElement.style.display = 'block';
        this.isVisible = true;
        
        // Atualizar lista de marcadores ao abrir
        this.loadMarkers();
    },
    
    /**
     * Esconde o modal
     */
    hideModal: function() {
        if (!this.modalElement) return;
        
        this.modalElement.style.display = 'none';
        this.isVisible = false;
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