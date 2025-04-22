/**
 * Site de Filmes e Series - JavaScript Principal
 * 
 * Funções e interações para o site de streaming
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    initPreloader();
    initHeaderScroll();
    initUserMenu();
    initMobileMenu();
    initBackToTop();
    init3DEffects();
    initCarousels();
});

/**
 * Preloader
 * Oculta o preloader quando a página termina de carregar
 */
function initPreloader() {
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        window.addEventListener('load', function() {
            preloader.classList.add('hidden');
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        });
    }
}

/**
 * Header Scroll
 * Altera a aparência do cabeçalho ao rolar a página
 */
function initHeaderScroll() {
    const header = document.querySelector('.main-header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
}

/**
 * Menu do Usuário
 * Controla a exibição do dropdown do menu do usuário
 */
function initUserMenu() {
    const userBtn = document.querySelector('.user-btn');
    if (userBtn) {
        userBtn.addEventListener('click', function() {
            this.parentElement.classList.toggle('active');
            this.nextElementSibling.classList.toggle('active');
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            if (userMenu && !userMenu.contains(event.target)) {
                userMenu.classList.remove('active');
                userMenu.querySelector('.user-dropdown').classList.remove('active');
            }
        });
    }
}

/**
 * Menu Mobile
 * Controla a exibição do menu em dispositivos móveis
 */
function initMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            mainNav.classList.toggle('active');
        });
    }
}

/**
 * Botão Voltar ao Topo
 * Controla a exibição e comportamento do botão de voltar ao topo
 */
function initBackToTop() {
    const backToTopBtn = document.getElementById('back-to-top');
    
    if (backToTopBtn) {
        // Exibir botão após rolar a página
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });
        
        // Comportamento de clique
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

/**
 * Efeitos 3D
 * Aplica efeitos 3D aos cards quando o mouse passa sobre eles
 */
function init3DEffects() {
    // Efeito 3D para os cards
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
                
                // Efeito de luz
                const shine = this.querySelector('.card-reflections');
                if (shine) {
                    shine.style.opacity = '1';
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
        });
    }
    
    // Efeito para os cards de categoria
    const categoryCards = document.querySelectorAll('.categoria-card');
    
    if (categoryCards.length > 0) {
        categoryCards.forEach(card => {
            card.addEventListener('mouseenter', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                this.style.setProperty('--x-pos', `${x}px`);
                this.style.setProperty('--y-pos', `${y}px`);
            });
        });
    }
}

/**
 * Carrosséis
 * Inicializa e controla os carrosséis de conteúdo
 */
function initCarousels() {
    const carousels = document.querySelectorAll('.carrossel');
    
    if (carousels.length > 0) {
        carousels.forEach(carousel => {
            const id = carousel.id;
            const items = carousel.querySelector('.carrossel-items');
            const prevBtn = document.querySelector(`.carrossel-prev[data-target="${id}"]`);
            const nextBtn = document.querySelector(`.carrossel-next[data-target="${id}"]`);
            const pagination = document.getElementById(`pagination-${id}`);
            const dots = pagination ? pagination.querySelectorAll('.pagination-dot') : [];
            
            // Scroll amount for each click
            const scrollAmount = carousel.offsetWidth * 0.8;
            let currentPage = 0;
            
            // Next button click
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    items.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                    
                    if (currentPage < dots.length - 1) {
                        currentPage++;
                        updatePagination();
                    }
                });
            }
            
            // Previous button click
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    items.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
                    
                    if (currentPage > 0) {
                        currentPage--;
                        updatePagination();
                    }
                });
            }
            
            // Pagination dots click
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
    
            // Update pagination
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
            
            // Detectar fim do scroll para atualizar paginação
            if (items) {
                items.addEventListener('scroll', function() {
                    const scrollPosition = items.scrollLeft;
                    const maxScroll = items.scrollWidth - items.clientWidth;
                    
                    // Desativar botão "anterior" quando no início
                    if (prevBtn) {
                        if (scrollPosition <= 10) {
                            prevBtn.classList.add('disabled');
        } else {
                            prevBtn.classList.remove('disabled');
                        }
                    }
                    
                    // Desativar botão "próximo" quando no fim
                    if (nextBtn) {
                        if (scrollPosition >= maxScroll - 10) {
                            nextBtn.classList.add('disabled');
        } else {
                            nextBtn.classList.remove('disabled');
                        }
                    }
                    
                    // Atualizar paginação
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
            });
        }
    }
    
    /**
 * Animações em Scroll
 * Anima elementos quando eles entram na viewport
 */
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    if (animatedElements.length > 0) {
        // Função para verificar se elemento está visível
        function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.8 &&
                rect.bottom >= 0
            );
        }
        
        // Verificar elementos visíveis
        function checkVisibleElements() {
            animatedElements.forEach(element => {
                if (isElementInViewport(element)) {
                    element.classList.add('animated');
                }
            });
        }
        
        // Verificar na carga e no scroll
        window.addEventListener('load', checkVisibleElements);
        window.addEventListener('scroll', checkVisibleElements);
    }
}