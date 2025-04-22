/**
 * Site de Filmes e Series - Carrossel de Conteúdo
 * Versão: 1.0.0
 * 
 * Script para controlar os carrosséis de conteúdo do site de streaming
 */

document.addEventListener('DOMContentLoaded', function() {
    initCarousels();
    addCardEffects();
});

/**
 * Inicializa todos os carrosséis de conteúdo da página
 */
function initCarousels() {
    const carousels = document.querySelectorAll('.carrossel');
    
    if (carousels.length === 0) return;
    
    carousels.forEach(carousel => {
        const id = carousel.id;
        const items = carousel.querySelector('.carrossel-items');
        const prevBtn = document.querySelector(`.carrossel-prev[data-target="${id}"]`);
        const nextBtn = document.querySelector(`.carrossel-next[data-target="${id}"]`);
        const pagination = document.getElementById(`pagination-${id}`);
        const dots = pagination ? pagination.querySelectorAll('.pagination-dot') : [];
        
        // Cálculo do scroll para cada clique
        const scrollAmount = carousel.offsetWidth * 0.8;
        let currentPage = 0;
        
        // Botão Próximo
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                items.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                
                if (currentPage < dots.length - 1) {
                    currentPage++;
                    updatePagination();
                }
            });
        }
        
        // Botão Anterior
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                items.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                
                if (currentPage > 0) {
                    currentPage--;
                    updatePagination();
                }
            });
        }
        
        // Cliques na paginação
        if (pagination) {
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    currentPage = index;
                    items.scrollTo({ 
                        left: index * scrollAmount, 
                        behavior: 'smooth' 
                    });
                    updatePagination();
                });
            });
        }
        
        // Atualiza a paginação
        function updatePagination() {
            if (pagination) {
                dots.forEach((dot, index) => {
                    if (index === currentPage) {
                        dot.classList.add('active');
                    } else {
                        dot.classList.remove('active');
                    }
                });
            }
        }
        
        // Detecta fim do scroll para atualizar paginação
        if (items) {
            items.addEventListener('scroll', function() {
                const scrollPosition = items.scrollLeft;
                const maxScroll = items.scrollWidth - items.clientWidth;
                
                // Desativa botão "anterior" quando no início
                if (prevBtn) {
                    if (scrollPosition <= 10) {
                        prevBtn.classList.add('disabled');
                    } else {
                        prevBtn.classList.remove('disabled');
                    }
                }
                
                // Desativa botão "próximo" quando no fim
                if (nextBtn) {
                    if (scrollPosition >= maxScroll - 10) {
                        nextBtn.classList.add('disabled');
                    } else {
                        nextBtn.classList.remove('disabled');
                    }
                }
                
                // Atualiza paginação com base na posição do scroll
                if (pagination && dots.length > 0) {
                    const pageWidth = scrollAmount;
                    const currentPageEstimate = Math.round(scrollPosition / pageWidth);
                    
                    if (currentPageEstimate !== currentPage) {
                        currentPage = Math.min(currentPageEstimate, dots.length - 1);
                        updatePagination();
                    }
                }
            });
        }
        
        // Adiciona navegação por teclado para acessibilidade
        carousel.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                if (prevBtn) prevBtn.click();
            } else if (e.key === 'ArrowRight') {
                if (nextBtn) nextBtn.click();
            }
        });
        
        // Torna o carrossel focável para navegação por teclado
        carousel.setAttribute('tabindex', '0');
    });
}

/**
 * Adiciona efeitos de hover e 3D aos cards
 */
function addCardEffects() {
    // Efeito 3D para os cards de conteúdo
    const cards = document.querySelectorAll('.conteudo-card');
    
    if (cards.length > 0) {
        cards.forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const xPercent = ((x / rect.width) - 0.5) * 2;
                const yPercent = ((y / rect.height) - 0.5) * 2;
                
                // Limita a rotação para tornar o efeito mais suave
                const maxRotation = 5;
                const xRotation = yPercent * -maxRotation; // Invertido para movimento natural
                const yRotation = xPercent * maxRotation;
                
                this.style.transform = `perspective(1000px) rotateX(${xRotation}deg) rotateY(${yRotation}deg) scale3d(1.05, 1.05, 1.05)`;
                
                // Efeito de brilho
                const shine = this.querySelector('.card-reflections');
                if (shine) {
                    shine.style.opacity = '1';
                    // Posiciona o brilho na posição do mouse
                    shine.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 80%)`;
                }
            });
            
            // Resetar transformação quando o mouse sai
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
                
                const shine = this.querySelector('.card-reflections');
                if (shine) {
                    shine.style.opacity = '0';
                }
            });
            
            // Adiciona elemento de reflexo se não existir
            if (!card.querySelector('.card-reflections')) {
                const reflection = document.createElement('div');
                reflection.classList.add('card-reflections');
                reflection.style.position = 'absolute';
                reflection.style.top = '0';
                reflection.style.left = '0';
                reflection.style.width = '100%';
                reflection.style.height = '100%';
                reflection.style.opacity = '0';
                reflection.style.pointerEvents = 'none';
                reflection.style.transition = 'opacity 0.3s ease';
                reflection.style.zIndex = '5';
                card.appendChild(reflection);
            }
        });
    }
    
    // Efeito de hover para os botões de reprodução
    const overlayPlays = document.querySelectorAll('.overlay-play');
    
    if (overlayPlays.length > 0) {
        overlayPlays.forEach(overlay => {
            overlay.addEventListener('mouseenter', function() {
                // Anima o ícone de play
                const playIcon = this.querySelector('i');
                if (playIcon) {
                    playIcon.style.transform = 'scale(1.2)';
                    playIcon.style.transition = 'transform 0.3s ease';
                }
                
                // Adiciona um brilho ao fundo
                this.style.background = 'radial-gradient(circle, rgba(229,9,20,0.7) 0%, rgba(229,9,20,0.5) 70%)';
            });
            
            overlay.addEventListener('mouseleave', function() {
                const playIcon = this.querySelector('i');
                if (playIcon) {
                    playIcon.style.transform = 'scale(1)';
                }
                
                this.style.background = 'radial-gradient(circle, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.5) 70%)';
            });
        });
    }
}

/**
 * Inicializa os carrosséis de destaque com transição automática
 * @param {string} selector - Seletor do carrossel de destaque
 * @param {number} interval - Intervalo em ms para transição automática
 */
function initFeaturedCarousel(selector, interval = 5000) {
    const carousel = document.querySelector(selector);
    if (!carousel) return;
    
    const items = carousel.querySelectorAll('.featured-item');
    const dots = carousel.querySelectorAll('.featured-dot');
    let currentIndex = 0;
    let autoPlayTimer;
    
    function showSlide(index) {
        // Oculta todos os slides
        items.forEach(item => {
            item.classList.remove('active');
        });
        
        // Remove classe ativa de todos os dots
        dots.forEach(dot => {
            dot.classList.remove('active');
        });
        
        // Mostra o slide atual
        items[index].classList.add('active');
        dots[index].classList.add('active');
        
        // Atualiza o índice atual
        currentIndex = index;
    }
    
    function nextSlide() {
        let next = currentIndex + 1;
        if (next >= items.length) {
            next = 0;
        }
        showSlide(next);
    }
    
    function prevSlide() {
        let prev = currentIndex - 1;
        if (prev < 0) {
            prev = items.length - 1;
        }
        showSlide(prev);
    }
    
    // Iniciar autoplay
    function startAutoPlay() {
        autoPlayTimer = setInterval(nextSlide, interval);
    }
    
    // Parar autoplay
    function stopAutoPlay() {
        clearInterval(autoPlayTimer);
    }
    
    // Configurar controles de navegação
    const prevBtn = carousel.querySelector('.featured-prev');
    const nextBtn = carousel.querySelector('.featured-next');
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            stopAutoPlay();
            startAutoPlay();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            stopAutoPlay();
            startAutoPlay();
        });
    }
    
    // Configurar dots de navegação
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            stopAutoPlay();
            startAutoPlay();
        });
    });
    
    // Pausar autoplay ao passar o mouse
    carousel.addEventListener('mouseenter', stopAutoPlay);
    carousel.addEventListener('mouseleave', startAutoPlay);
    
    // Iniciar o primeiro slide e o autoplay
    showSlide(0);
    startAutoPlay();
}

// Função para exportar para uso em outros arquivos
window.StreamingApp = window.StreamingApp || {};
window.StreamingApp.initCarousels = initCarousels;
window.StreamingApp.addCardEffects = addCardEffects;
window.StreamingApp.initFeaturedCarousel = initFeaturedCarousel;

/**
 * Carrossel de Conteúdo - Funcionalidades JavaScript
 * Versão: 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos os carrosséis na página
    const carrosseis = document.querySelectorAll('.carrossel-secao');
    carrosseis.forEach(inicializarCarrossel);
    
    // Observar carrosséis adicionados dinamicamente ao DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Elemento
                        const novosCarrosseis = node.querySelectorAll('.carrossel-secao');
                        if (novosCarrosseis.length > 0) {
                            novosCarrosseis.forEach(inicializarCarrossel);
                        }
                        
                        // Verificar se o próprio nó é um carrossel
                        if (node.classList && node.classList.contains('carrossel-secao')) {
                            inicializarCarrossel(node);
                        }
                    }
                });
            }
        });
    });
    
    observer.observe(document.body, { 
        childList: true, 
        subtree: true 
    });
});

/**
 * Inicializa um carrossel específico
 * @param {HTMLElement} carrosselSecao - Elemento contendo o carrossel
 */
function inicializarCarrossel(carrosselSecao) {
    const id = carrosselSecao.id;
    const carrosselContainer = carrosselSecao.querySelector('.carrossel-container');
    const carrosselItems = carrosselSecao.querySelector('.carrossel-items');
    const items = carrosselSecao.querySelectorAll('.carrossel-item');
    const prevBtn = carrosselSecao.querySelector('.carrossel-prev');
    const nextBtn = carrosselSecao.querySelector('.carrossel-next');
    const paginacaoContainer = carrosselSecao.querySelector('.carrossel-paginacao');
    
    if (!carrosselItems || items.length === 0) return;
    
    // Configuração do carrossel
    let itemsPerPage = calcularItemsPorPagina();
    let currentPage = 0;
    let totalPages = Math.ceil(items.length / itemsPerPage);
    let isDragging = false;
    let startPos = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;
    
    // Configurar paginação
    if (paginacaoContainer) {
        renderizarPaginacao();
    }
    
    // Listeners para navegação
    if (prevBtn) {
        prevBtn.addEventListener('click', () => navegarPagina('prev'));
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => navegarPagina('next'));
    }
    
    // Eventos de toque/arrasto para dispositivos móveis
    carrosselItems.addEventListener('mousedown', iniciarArrasto);
    carrosselItems.addEventListener('touchstart', iniciarArrasto);
    carrosselItems.addEventListener('mouseup', finalizarArrasto);
    carrosselItems.addEventListener('touchend', finalizarArrasto);
    carrosselItems.addEventListener('mouseleave', finalizarArrasto);
    carrosselItems.addEventListener('mousemove', arrasto);
    carrosselItems.addEventListener('touchmove', arrasto);
    
    // Listener para redimensionamento da janela
    window.addEventListener('resize', () => {
        // Recalcular itens por página e total de páginas
        const novoItemsPerPage = calcularItemsPorPagina();
        
        if (novoItemsPerPage !== itemsPerPage) {
            itemsPerPage = novoItemsPerPage;
            totalPages = Math.ceil(items.length / itemsPerPage);
            
            // Ajustar página atual se necessário
            if (currentPage >= totalPages) {
                currentPage = totalPages - 1;
            }
            
            // Atualizar paginação e posição
            if (paginacaoContainer) {
                renderizarPaginacao();
            }
            
            atualizarPosicao();
        }
    });
    
    // Adicionar efeito 3D nos cards
    items.forEach(item => {
        const card = item.querySelector('.conteudo-card');
        if (card) {
            card.classList.add('card-3d-effect');
            
            // Adicionar elementos de reflexo
            const reflexoes = ['top', 'right', 'bottom', 'left'];
            reflexoes.forEach(posicao => {
                const reflexao = document.createElement('div');
                reflexao.className = `reflection-${posicao}`;
                card.appendChild(reflexao);
            });
            
            // Adicionar efeito 3D ao mover o mouse
            card.addEventListener('mousemove', aplicarEfeito3D);
            card.addEventListener('mouseleave', resetarEfeito3D);
        }
    });
    
    /**
     * Calcula quantos itens devem ser exibidos por página com base na largura da tela
     * @returns {number} Número de itens por página
     */
    function calcularItemsPorPagina() {
        const larguraTela = window.innerWidth;
        
        if (larguraTela > 1400) return 6;
        if (larguraTela > 1200) return 5;
        if (larguraTela > 992) return 4;
        if (larguraTela > 768) return 3;
        if (larguraTela > 576) return 2;
        return 1;
    }
    
    /**
     * Navega para a próxima ou página anterior
     * @param {string} direcao - Direção da navegação ('prev' ou 'next')
     */
    function navegarPagina(direcao) {
        if (direcao === 'next' && currentPage < totalPages - 1) {
            currentPage++;
        } else if (direcao === 'prev' && currentPage > 0) {
            currentPage--;
        }
        
        atualizarPosicao();
        atualizarPaginacaoAtiva();
    }
    
    /**
     * Navega para uma página específica
     * @param {number} pagina - Número da página para navegar
     */
    function irParaPagina(pagina) {
        if (pagina >= 0 && pagina < totalPages) {
            currentPage = pagina;
            atualizarPosicao();
            atualizarPaginacaoAtiva();
        }
    }
    
    /**
     * Atualiza a posição do carrossel com animação
     */
    function atualizarPosicao() {
        const itemWidth = items[0].offsetWidth + 16; // Largura + margem
        const posicao = -currentPage * itemsPerPage * itemWidth;
        
        // Atualizar transform com animação
        carrosselItems.style.transition = 'transform 0.5s ease';
        carrosselItems.style.transform = `translateX(${posicao}px)`;
        
        // Atualizar translate atual para o controle de arrasto
        currentTranslate = posicao;
        prevTranslate = posicao;
    }
    
    /**
     * Renderiza os pontos de paginação
     */
    function renderizarPaginacao() {
        if (!paginacaoContainer) return;
        
        // Limpar paginação existente
        paginacaoContainer.innerHTML = '';
        
        // Criar pontos de paginação
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('button');
            dot.className = 'pagination-dot';
            dot.setAttribute('aria-label', `Página ${i + 1}`);
            dot.dataset.page = i;
            
            if (i === currentPage) {
                dot.classList.add('active');
            }
            
            dot.addEventListener('click', () => irParaPagina(i));
            paginacaoContainer.appendChild(dot);
        }
    }
    
    /**
     * Atualiza o estado ativo na paginação
     */
    function atualizarPaginacaoAtiva() {
        if (!paginacaoContainer) return;
        
        const dots = paginacaoContainer.querySelectorAll('.pagination-dot');
        dots.forEach((dot, index) => {
            if (index === currentPage) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
        }
    });
}

    /**
     * Inicia o processo de arrasto
     * @param {Event} e - Evento de mouse ou toque
     */
    function iniciarArrasto(e) {
        isDragging = true;
        startPos = getPositionX(e);
        
        // Desativar transição durante o arrasto
        carrosselItems.style.transition = 'none';
        
        // Impedir comportamento padrão (scroll)
        e.preventDefault();
    }
    
    /**
     * Finaliza o processo de arrasto
     */
    function finalizarArrasto() {
        if (!isDragging) return;
        
        isDragging = false;
        
        // Calcular deslocamento
        const movedBy = currentTranslate - prevTranslate;
        
        // Determinar direção e verificar se o deslocamento é significativo
        if (movedBy < -100 && currentPage < totalPages - 1) {
            currentPage++;
        } else if (movedBy > 100 && currentPage > 0) {
            currentPage--;
        }
        
        // Restaurar transição e atualizar posição
        carrosselItems.style.transition = 'transform 0.5s ease';
        atualizarPosicao();
        atualizarPaginacaoAtiva();
    }
    
    /**
     * Processa o movimento de arrasto
     * @param {Event} e - Evento de mouse ou toque
     */
    function arrasto(e) {
        if (!isDragging) return;
        
        const currentPosition = getPositionX(e);
        const moveBy = currentPosition - startPos;
        
        // Atualizar posição durante o arrasto
        currentTranslate = prevTranslate + moveBy;
        carrosselItems.style.transform = `translateX(${currentTranslate}px)`;
    }
    
    /**
     * Obtém a posição X do evento (mouse ou toque)
     * @param {Event} e - Evento de mouse ou toque
     * @returns {number} Posição X
     */
    function getPositionX(e) {
        return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
    }
    
    /**
     * Aplica efeito 3D ao mover o mouse sobre um card
     * @param {Event} e - Evento de mouse
     */
    function aplicarEfeito3D(e) {
        const card = e.currentTarget;
        const rect = card.getBoundingClientRect();
        
        // Calcular posição relativa do mouse dentro do card (de -1 a 1)
        const xPos = (e.clientX - rect.left) / rect.width * 2 - 1;
        const yPos = (e.clientY - rect.top) / rect.height * 2 - 1;
        
        // Aplicar rotação (limitar a 5 graus)
        const rotateY = xPos * 5;
        const rotateX = -yPos * 5;
        
        // Aplicar transform
        card.style.transform = `
            rotateY(${rotateY}deg) 
            rotateX(${rotateX}deg)
            translateZ(10px)
        `;
        
        // Ajustar sombras e reflexos
        card.style.boxShadow = `
            ${-xPos * 5}px ${-yPos * 5}px 10px rgba(0,0,0,0.2),
            ${xPos * 15}px ${yPos * 15}px 20px rgba(0,0,0,0.1)
        `;
    }
    
    /**
     * Reseta o efeito 3D ao sair do card
     * @param {Event} e - Evento de mouse
     */
    function resetarEfeito3D(e) {
        const card = e.currentTarget;
        
        // Restaurar transform e sombra
        card.style.transform = '';
        card.style.boxShadow = '';
        
        // Adicionar transição para retorno suave
        card.style.transition = 'transform 0.5s ease, box-shadow 0.5s ease';
        
        // Remover transição após o efeito
        setTimeout(() => {
            card.style.transition = '';
        }, 500);
    }
}

/**
 * Inicializa um novo carrossel após sua inserção no DOM
 * @param {string} id - ID do carrossel
 */
function initCarrossel(id) {
    const carrosselSecao = document.getElementById(id);
    if (carrosselSecao) {
        inicializarCarrossel(carrosselSecao);
        return true;
    }
    return false;
}

// Expor funções globalmente
window.initCarrossel = initCarrossel; 