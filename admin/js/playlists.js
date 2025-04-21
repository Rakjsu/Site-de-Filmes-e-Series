/**
 * Gerenciador de Playlists - JavaScript principal
 * Funções para manipulação de playlists e vídeos
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTables para a tabela de playlists
    const playlistsTable = $('#playlistsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
        },
        responsive: true,
        columnDefs: [
            { 
                targets: -1, 
                orderable: false, 
                searchable: false
            }
        ],
        order: [[1, 'asc']],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]]
    });

    // Inicializar o Sortable.js para arrastar e soltar vídeos
    const playlistVideosList = document.getElementById('playlistVideosList');
    if (playlistVideosList) {
        new Sortable(playlistVideosList, {
            handle: '.video-item-drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function(evt) {
                updateVideoPositions();
            }
        });
    }

    // Variáveis para controle do estado
    let currentPlaylistId = null;
    let currentPlaylistData = null;
    
    // Manipuladores de eventos
    bindEventHandlers();
    
    // Funções
    
    /**
     * Vincular todos os manipuladores de eventos necessários
     */
    function bindEventHandlers() {
        // Clicar em uma playlist na tabela
        $('#playlistsTable').on('click', '.btn-edit-playlist', function(e) {
            e.preventDefault();
            const playlistId = $(this).data('id');
            loadPlaylistDetails(playlistId);
        });

        // Excluir uma playlist
        $('#playlistsTable').on('click', '.btn-delete-playlist', function(e) {
            e.preventDefault();
            const playlistId = $(this).data('id');
            const playlistName = $(this).data('name');
            confirmDeletePlaylist(playlistId, playlistName);
        });

        // Salvar playlist
        $('#savePlaylistBtn').on('click', function(e) {
            e.preventDefault();
            savePlaylist();
        });

        // Adicionar um vídeo à playlist
        $('#addVideoToPlaylistBtn').on('click', function(e) {
            e.preventDefault();
            $('#addVideoModal').modal('show');
            loadAvailableVideos();
        });

        // Adicionar vídeos selecionados do modal
        $('#confirmAddVideosBtn').on('click', function(e) {
            e.preventDefault();
            addSelectedVideosToPlaylist();
            $('#addVideoModal').modal('hide');
        });

        // Remover vídeo da playlist
        $('#playlistVideosList').on('click', '.btn-remove-video', function(e) {
            e.preventDefault();
            const videoItem = $(this).closest('.list-group-item');
            const videoId = videoItem.data('video-id');
            removeVideoFromPlaylist(videoId);
            videoItem.remove();
            updateVideoPositions();
        });

        // Selecionar/deselecionar vídeos no modal
        $('#availableVideosList').on('click', '.list-group-item', function(e) {
            $(this).toggleClass('selected');
        });
        
        // Nova playlist (limpa o formulário)
        $('#newPlaylistBtn').on('click', function(e) {
            e.preventDefault();
            resetPlaylistForm();
        });
        
        // Filtrar vídeos disponíveis
        $('#videoSearchInput').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#availableVideosList .list-group-item').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    }

    /**
     * Carrega os detalhes de uma playlist para edição
     * @param {number} playlistId - ID da playlist a ser carregada
     */
    function loadPlaylistDetails(playlistId) {
        // Mostrar indicador de carregamento
        $('#playlistDetailCard').addClass('loading');
        $('#playlistVideosList').empty();
        
        // Simular uma requisição AJAX para a API
        setTimeout(function() {
            // Esta é uma função de simulação
            // Em produção, isso seria uma chamada API real
            fetchPlaylistById(playlistId)
                .then(playlist => {
                    currentPlaylistId = playlistId;
                    currentPlaylistData = playlist;
                    
                    // Preencher formulário
                    $('#playlistName').val(playlist.name);
                    $('#playlistDescription').val(playlist.description);
                    $('#playlistSlug').val(playlist.slug);
                    $('#playlistStatus').val(playlist.status);
                    
                    // Mostrar a lista de vídeos
                    if (playlist.videos && playlist.videos.length > 0) {
                        playlist.videos.forEach((video, index) => {
                            appendVideoToPlaylist(video, index);
                        });
                    } else {
                        $('#playlistVideosList').html('<div class="alert alert-info">Esta playlist não possui vídeos. Adicione vídeos usando o botão acima.</div>');
                    }
                    
                    // Mostrar o card de detalhes
                    $('#playlistDetailCard').removeClass('d-none loading');
                    $('#playlistEmptyState').addClass('d-none');
                })
                .catch(error => {
                    showAlert('danger', 'Erro ao carregar playlist: ' + error.message);
                });
        }, 800);
    }
    
    /**
     * Adiciona um vídeo à lista de vídeos da playlist
     * @param {Object} video - Objeto contendo dados do vídeo
     * @param {number} index - Índice do vídeo na lista
     */
    function appendVideoToPlaylist(video, index) {
        const videoItem = `
            <div class="list-group-item" data-video-id="${video.id}">
                <div class="video-item-drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </div>
                <div class="video-thumbnail">
                    <img src="${video.thumbnail}" alt="${video.title}">
                </div>
                <div class="video-info">
                    <h6 class="mb-0">${video.title}</h6>
                    <small class="text-muted">${video.duration} | ${video.views} visualizações</small>
                </div>
                <div class="video-actions">
                    <button class="btn btn-sm btn-outline-danger btn-remove-video" title="Remover da playlist">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        $('#playlistVideosList').append(videoItem);
    }
    
    /**
     * Carrega vídeos disponíveis para adicionar à playlist
     */
    function loadAvailableVideos() {
        $('#availableVideosList').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Carregando vídeos...</div>');
        
        // Simular uma requisição AJAX para a API
        setTimeout(function() {
            // Esta é uma função de simulação
            // Em produção, isso seria uma chamada API real
            fetchAvailableVideos()
                .then(videos => {
                    // Filtrar vídeos que já estão na playlist
                    const existingVideoIds = Array.from(
                        document.querySelectorAll('#playlistVideosList .list-group-item')
                    ).map(item => parseInt(item.dataset.videoId));
                    
                    const availableVideos = videos.filter(
                        video => !existingVideoIds.includes(video.id)
                    );
                    
                    // Exibir vídeos disponíveis
                    $('#availableVideosList').empty();
                    
                    if (availableVideos.length === 0) {
                        $('#availableVideosList').html('<div class="alert alert-info">Não há mais vídeos disponíveis para adicionar a esta playlist.</div>');
                        return;
                    }
                    
                    availableVideos.forEach(video => {
                        const videoItem = `
                            <div class="list-group-item" data-video-id="${video.id}">
                                <div class="d-flex align-items-center w-100">
                                    <div class="video-thumbnail">
                                        <img src="${video.thumbnail}" alt="${video.title}">
                                    </div>
                                    <div class="video-info">
                                        <h6 class="mb-0">${video.title}</h6>
                                        <small class="text-muted">${video.duration} | ${video.views} visualizações</small>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#availableVideosList').append(videoItem);
                    });
                })
                .catch(error => {
                    $('#availableVideosList').html(`<div class="alert alert-danger">Erro ao carregar vídeos: ${error.message}</div>`);
                });
        }, 800);
    }
    
    /**
     * Adiciona vídeos selecionados à playlist atual
     */
    function addSelectedVideosToPlaylist() {
        const selectedVideos = Array.from(
            document.querySelectorAll('#availableVideosList .list-group-item.selected')
        );
        
        if (selectedVideos.length === 0) {
            showAlert('warning', 'Nenhum vídeo selecionado');
            return;
        }
        
        // Limpar mensagem de lista vazia se existir
        const emptyMessage = $('#playlistVideosList .alert');
        if (emptyMessage.length > 0) {
            emptyMessage.remove();
        }
        
        // Adicionar cada vídeo selecionado à playlist
        const currentCount = $('#playlistVideosList .list-group-item').length;
        
        selectedVideos.forEach((item, index) => {
            const videoId = item.dataset.videoId;
            // Simular API para obter detalhes completos do vídeo
            fetchVideoById(parseInt(videoId))
                .then(video => {
                    appendVideoToPlaylist(video, currentCount + index);
                });
        });
        
        showAlert('success', `${selectedVideos.length} vídeo(s) adicionado(s) à playlist`);
        updateVideoPositions();
    }
    
    /**
     * Remove um vídeo da playlist
     * @param {number} videoId - ID do vídeo a ser removido
     */
    function removeVideoFromPlaylist(videoId) {
        // Em produção, seria feita uma chamada API para remover o vídeo
        showAlert('success', 'Vídeo removido da playlist');
        
        // Verificar se a lista ficou vazia
        if ($('#playlistVideosList .list-group-item').length === 0) {
            $('#playlistVideosList').html('<div class="alert alert-info">Esta playlist não possui vídeos. Adicione vídeos usando o botão acima.</div>');
        }
    }
    
    /**
     * Atualiza as posições dos vídeos após arrastar e soltar
     */
    function updateVideoPositions() {
        // Em produção, seria feita uma chamada API para atualizar as posições
        console.log('Posições de vídeos atualizadas');
    }
    
    /**
     * Salva uma playlist (nova ou existente)
     */
    function savePlaylist() {
        const playlistData = {
            id: currentPlaylistId,
            name: $('#playlistName').val(),
            description: $('#playlistDescription').val(),
            slug: $('#playlistSlug').val(),
            status: $('#playlistStatus').val(),
            videos: Array.from(
                document.querySelectorAll('#playlistVideosList .list-group-item')
            ).map(item => parseInt(item.dataset.videoId))
        };
        
        // Validar formulário
        if (!playlistData.name) {
            showAlert('danger', 'O nome da playlist é obrigatório');
            return;
        }
        
        // Mostrar indicador de carregamento
        $('#savePlaylistBtn').html('<i class="fas fa-spinner fa-spin"></i> Salvando...').attr('disabled', true);
        
        // Simular uma requisição AJAX para a API
        setTimeout(function() {
            // Esta é uma função de simulação
            // Em produção, isso seria uma chamada API real
            savePlaylistData(playlistData)
                .then(response => {
                    showAlert('success', `Playlist "${playlistData.name}" salva com sucesso!`);
                    
                    if (!currentPlaylistId) {
                        // Nova playlist - resetar formulário e atualizar tabela
                        resetPlaylistForm();
                        // Atualizar tabela de playlists
                        refreshPlaylistsTable();
                    } else {
                        currentPlaylistId = response.id;
                        // Atualizar tabela de playlists
                        refreshPlaylistsTable();
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Erro ao salvar playlist: ' + error.message);
                })
                .finally(() => {
                    $('#savePlaylistBtn').html('Salvar Playlist').attr('disabled', false);
                });
        }, 1000);
    }
    
    /**
     * Confirma a exclusão de uma playlist
     * @param {number} playlistId - ID da playlist a ser excluída
     * @param {string} playlistName - Nome da playlist para exibir na confirmação
     */
    function confirmDeletePlaylist(playlistId, playlistName) {
        if (confirm(`Tem certeza que deseja excluir a playlist "${playlistName}"?`)) {
            deletePlaylist(playlistId);
        }
    }
    
    /**
     * Exclui uma playlist
     * @param {number} playlistId - ID da playlist a ser excluída
     */
    function deletePlaylist(playlistId) {
        // Simular uma requisição AJAX para a API
        setTimeout(function() {
            // Esta é uma função de simulação
            // Em produção, isso seria uma chamada API real
            deletePlaylistData(playlistId)
                .then(() => {
                    showAlert('success', 'Playlist excluída com sucesso!');
                    // Atualizar tabela de playlists
                    refreshPlaylistsTable();
                    
                    // Se a playlist atual foi excluída, resetar o formulário
                    if (currentPlaylistId === playlistId) {
                        resetPlaylistForm();
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Erro ao excluir playlist: ' + error.message);
                });
        }, 800);
    }
    
    /**
     * Reseta o formulário de detalhes da playlist
     */
    function resetPlaylistForm() {
        currentPlaylistId = null;
        currentPlaylistData = null;
        
        $('#playlistName').val('');
        $('#playlistDescription').val('');
        $('#playlistSlug').val('');
        $('#playlistStatus').val('active');
        
        $('#playlistVideosList').empty();
        $('#playlistVideosList').html('<div class="alert alert-info">Salve a playlist primeiro para adicionar vídeos.</div>');
        
        $('#playlistDetailCard').removeClass('d-none loading');
        $('#playlistEmptyState').addClass('d-none');
    }
    
    /**
     * Atualiza a tabela de playlists após mudanças
     */
    function refreshPlaylistsTable() {
        // Simular uma requisição AJAX para a API
        setTimeout(function() {
            // Esta é uma função de simulação
            // Em produção, isso seria uma chamada API real
            fetchPlaylists()
                .then(playlists => {
                    // Limpar tabela
                    playlistsTable.clear();
                    
                    // Adicionar playlists à tabela
                    playlists.forEach(playlist => {
                        playlistsTable.row.add([
                            playlist.id,
                            playlist.name,
                            playlist.videos_count + ' vídeos',
                            playlist.status === 'active' 
                                ? '<span class="badge bg-success">Ativo</span>' 
                                : '<span class="badge bg-danger">Inativo</span>',
                            `<div class="d-flex">
                                <button class="btn btn-sm btn-primary btn-action btn-edit-playlist me-2" 
                                    data-id="${playlist.id}" title="Editar playlist">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-action btn-delete-playlist" 
                                    data-id="${playlist.id}" data-name="${playlist.name}" title="Excluir playlist">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>`
                        ]);
                    });
                    
                    // Redesenhar tabela
                    playlistsTable.draw();
                })
                .catch(error => {
                    showAlert('danger', 'Erro ao carregar playlists: ' + error.message);
                });
        }, 800);
    }
    
    /**
     * Mostra um alerta temporário para o usuário
     * @param {string} type - Tipo do alerta (success, danger, warning, info)
     * @param {string} message - Mensagem a ser exibida
     */
    function showAlert(type, message) {
        const alert = `
            <div class="alert alert-${type} alert-dismissible alert-floating fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        `;
        
        const alertContainer = $('#alertsContainer');
        alertContainer.append(alert);
        
        // Auto-remover após 5 segundos
        setTimeout(function() {
            alertContainer.find('.alert').first().alert('close');
        }, 5000);
    }

    // Funções de simulação de API
    // Estas funções simulariam chamadas reais à API em produção
    
    function fetchPlaylists() {
        return Promise.resolve([
            { id: 1, name: 'Melhores Filmes de 2023', videos_count: 12, status: 'active' },
            { id: 2, name: 'Séries Populares', videos_count: 8, status: 'active' },
            { id: 3, name: 'Documentários Imperdíveis', videos_count: 5, status: 'inactive' },
            { id: 4, name: 'Lançamentos da Semana', videos_count: 3, status: 'active' }
        ]);
    }
    
    function fetchPlaylistById(id) {
        const playlists = {
            1: {
                id: 1,
                name: 'Melhores Filmes de 2023',
                description: 'Uma seleção dos melhores filmes lançados em 2023.',
                slug: 'melhores-filmes-2023',
                status: 'active',
                videos: [
                    { id: 101, title: 'Filme 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '2h 15min', views: '1.2M' },
                    { id: 102, title: 'Filme 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 48min', views: '890K' },
                    { id: 103, title: 'Filme 3', thumbnail: 'https://via.placeholder.com/160x90', duration: '2h 05min', views: '1.5M' }
                ]
            },
            2: {
                id: 2,
                name: 'Séries Populares',
                description: 'Séries mais assistidas na plataforma.',
                slug: 'series-populares',
                status: 'active',
                videos: [
                    { id: 201, title: 'Série 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '45min', views: '2.3M' },
                    { id: 202, title: 'Série 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '50min', views: '1.8M' }
                ]
            },
            3: {
                id: 3,
                name: 'Documentários Imperdíveis',
                description: 'Documentários que você precisa assistir.',
                slug: 'documentarios-imperdiveis',
                status: 'inactive',
                videos: [
                    { id: 301, title: 'Documentário 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 30min', views: '450K' },
                    { id: 302, title: 'Documentário 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '2h', views: '320K' },
                    { id: 303, title: 'Documentário 3', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 15min', views: '280K' }
                ]
            },
            4: {
                id: 4,
                name: 'Lançamentos da Semana',
                description: 'Conteúdos adicionados recentemente à plataforma.',
                slug: 'lancamentos-semana',
                status: 'active',
                videos: [
                    { id: 401, title: 'Lançamento 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 55min', views: '120K' },
                    { id: 402, title: 'Lançamento 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 40min', views: '90K' }
                ]
            }
        };
        
        if (playlists[id]) {
            return Promise.resolve(playlists[id]);
        } else {
            return Promise.reject(new Error('Playlist não encontrada'));
        }
    }
    
    function fetchAvailableVideos() {
        return Promise.resolve([
            { id: 101, title: 'Filme 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '2h 15min', views: '1.2M' },
            { id: 102, title: 'Filme 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 48min', views: '890K' },
            { id: 103, title: 'Filme 3', thumbnail: 'https://via.placeholder.com/160x90', duration: '2h 05min', views: '1.5M' },
            { id: 201, title: 'Série 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '45min', views: '2.3M' },
            { id: 202, title: 'Série 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '50min', views: '1.8M' },
            { id: 301, title: 'Documentário 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 30min', views: '450K' },
            { id: 302, title: 'Documentário 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '2h', views: '320K' },
            { id: 303, title: 'Documentário 3', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 15min', views: '280K' },
            { id: 401, title: 'Lançamento 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 55min', views: '120K' },
            { id: 402, title: 'Lançamento 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 40min', views: '90K' },
            { id: 501, title: 'Vídeo Musical 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '4min', views: '3.5M' },
            { id: 502, title: 'Vídeo Musical 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '3min', views: '2.8M' }
        ]);
    }
    
    function fetchVideoById(id) {
        const allVideos = [
            { id: 101, title: 'Filme 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '2h 15min', views: '1.2M' },
            { id: 102, title: 'Filme 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 48min', views: '890K' },
            { id: 103, title: 'Filme 3', thumbnail: 'https://via.placeholder.com/160x90', duration: '2h 05min', views: '1.5M' },
            { id: 201, title: 'Série 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '45min', views: '2.3M' },
            { id: 202, title: 'Série 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '50min', views: '1.8M' },
            { id: 301, title: 'Documentário 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 30min', views: '450K' },
            { id: 302, title: 'Documentário 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '2h', views: '320K' },
            { id: 303, title: 'Documentário 3', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 15min', views: '280K' },
            { id: 401, title: 'Lançamento 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 55min', views: '120K' },
            { id: 402, title: 'Lançamento 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '1h 40min', views: '90K' },
            { id: 501, title: 'Vídeo Musical 1', thumbnail: 'https://via.placeholder.com/160x90', duration: '4min', views: '3.5M' },
            { id: 502, title: 'Vídeo Musical 2', thumbnail: 'https://via.placeholder.com/160x90', duration: '3min', views: '2.8M' }
        ];
        
        const video = allVideos.find(v => v.id === id);
        
        if (video) {
            return Promise.resolve(video);
        } else {
            return Promise.reject(new Error('Vídeo não encontrado'));
        }
    }
    
    function savePlaylistData(playlistData) {
        // Simulação de resposta da API
        return Promise.resolve({
            id: playlistData.id || Math.floor(Math.random() * 1000) + 5,
            name: playlistData.name,
            description: playlistData.description,
            slug: playlistData.slug,
            status: playlistData.status,
            videos_count: playlistData.videos.length
        });
    }
    
    function deletePlaylistData(playlistId) {
        // Simulação de resposta da API
        return Promise.resolve({ success: true });
    }
    
    // Inicializar a tabela de playlists
    refreshPlaylistsTable();
}); 