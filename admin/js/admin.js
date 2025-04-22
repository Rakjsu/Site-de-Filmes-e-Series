/**
 * Script principal para o Painel Administrativo
 * Versão: 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Inicializar todos os elementos
    initSidebar();
    initDropdowns();
    initToasts();
    initThemeToggle();
    initPreloader();
    
    /**
     * Inicializa a barra lateral e o seu comportamento
     */
    function initSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const adminWrapper = document.querySelector('.admin-wrapper');
        
        // Toggle para menu lateral em dispositivos móveis
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                adminWrapper.classList.toggle('sidebar-collapsed');
                
                // Salvar estado no localStorage
                const isCollapsed = adminWrapper.classList.contains('sidebar-collapsed');
                localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
            });
        }
        
        // Restaurar estado da sidebar do localStorage
        const savedState = localStorage.getItem('sidebar_collapsed');
        if (savedState === 'true') {
            adminWrapper.classList.add('sidebar-collapsed');
        }
        
        // Fechar sidebar automaticamente em telas pequenas
        function checkScreenSize() {
            if (window.innerWidth < 992 && !adminWrapper.classList.contains('sidebar-collapsed')) {
                adminWrapper.classList.add('sidebar-collapsed');
            } else if (window.innerWidth >= 992 && adminWrapper.classList.contains('sidebar-collapsed') && savedState !== 'true') {
                adminWrapper.classList.remove('sidebar-collapsed');
            }
        }
        
        // Verificar tamanho da tela ao carregar e ao redimensionar
        checkScreenSize();
        window.addEventListener('resize', checkScreenSize);
    }
    
    /**
     * Inicializa todos os dropdowns da interface
     */
    function initDropdowns() {
        // Dropdowns do header
        const dropdownButtons = document.querySelectorAll('.notification-btn, .user-btn');
        const dropdownMenus = document.querySelectorAll('.dropdown-menu');
        
        // Função para fechar todos os dropdowns
        function closeAllDropdowns() {
            dropdownMenus.forEach(menu => {
                menu.classList.remove('show');
            });
        }
        
        // Toggle para cada botão de dropdown
        dropdownButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const parent = this.closest('div');
                const menu = parent.querySelector('.dropdown-menu');
                
                // Fechar outros dropdowns
                dropdownMenus.forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        otherMenu.classList.remove('show');
                    }
                });
                
                // Toggle do menu atual
                menu.classList.toggle('show');
            });
        });
        
        // Fechar dropdowns quando clicar fora deles
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-menu') && !e.target.closest('.notification-btn') && !e.target.closest('.user-btn')) {
                closeAllDropdowns();
            }
        });
        
        // Fechar dropdowns ao pressionar ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllDropdowns();
            }
        });
    }
    
    /**
     * Inicializa notificações toast
     */
    function initToasts() {
        if (typeof toastr !== 'undefined') {
            // Configuração do Toastr
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 5000,
                extendedTimeOut: 2000,
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut"
            };
        }
    }
    
    /**
     * Inicializa o toggle de tema (claro/escuro)
     */
    function initThemeToggle() {
        const themeToggleBtn = document.getElementById('theme-toggle-btn');
        
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                // Atualizar o atributo data-theme
                document.documentElement.setAttribute('data-theme', newTheme);
                
                // Salvar a preferência no localStorage
                localStorage.setItem('admin_theme', newTheme);
                
                // Atualizar a aparência do botão
                themeToggleBtn.classList.toggle('dark-active', newTheme === 'dark');
                themeToggleBtn.classList.toggle('light-active', newTheme === 'light');
            });
            
            // Definir estado inicial baseado no tema atual
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            themeToggleBtn.classList.toggle('dark-active', currentTheme === 'dark');
            themeToggleBtn.classList.toggle('light-active', currentTheme === 'light');
        }
    }
    
    /**
     * Inicializa o preloader e o remove quando a página estiver carregada
     */
    function initPreloader() {
        const preloader = document.getElementById('preloader');
        
        if (preloader) {
            // Remover o preloader após a página carregar completamente
            window.addEventListener('load', function() {
                preloader.classList.add('fade-out');
                
                // Remover completamente após a animação
                setTimeout(function() {
                    preloader.style.display = 'none';
                }, 500);
            });
            
            // Caso já esteja carregada
            if (document.readyState === 'complete') {
                preloader.classList.add('fade-out');
                setTimeout(function() {
                    preloader.style.display = 'none';
                }, 500);
            }
        }
    }
    
    /**
     * Funções utilitárias globais
     */
    window.adminUtils = {
        /**
         * Exibe uma mensagem de confirmação
         * @param {string} message - Mensagem a ser exibida
         * @param {Function} onConfirm - Função a ser executada ao confirmar
         * @param {Function} onCancel - Função a ser executada ao cancelar
         */
        confirm: function(message, onConfirm, onCancel) {
            if (confirm(message)) {
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
            } else {
                if (typeof onCancel === 'function') {
                    onCancel();
                }
            }
        },
        
        /**
         * Exibe uma notificação toast
         * @param {string} message - Mensagem a ser exibida
         * @param {string} type - Tipo da notificação (success, error, warning, info)
         */
        notify: function(message, type = 'info') {
            if (typeof toastr !== 'undefined') {
                switch (type) {
                    case 'success':
                        toastr.success(message);
                        break;
                    case 'error':
                        toastr.error(message);
                        break;
                    case 'warning':
                        toastr.warning(message);
                        break;
                    default:
                        toastr.info(message);
                }
            } else {
                alert(message);
            }
        },
        
        /**
         * Formata um número para exibição
         * @param {number} number - Número a ser formatado
         * @param {number} decimals - Quantidade de casas decimais
         * @param {string} decimalSeparator - Separador decimal
         * @param {string} thousandsSeparator - Separador de milhares
         * @returns {string} Número formatado
         */
        formatNumber: function(number, decimals = 0, decimalSeparator = ',', thousandsSeparator = '.') {
            const fixed = Number(number).toFixed(decimals);
            const [intPart, decPart] = fixed.split('.');
            
            const formattedInt = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSeparator);
            
            return decimals ? formattedInt + decimalSeparator + decPart : formattedInt;
        }
    };

    // Alternância de tema claro/escuro
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('admin_theme', theme);
        document.body.classList.add('theme-transition');
        setTimeout(() => document.body.classList.remove('theme-transition'), 400);
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        setTheme(current === 'dark' ? 'light' : 'dark');
    }

    const headerBtn = document.getElementById('theme-toggle-header');
    const sidebarBtn = document.getElementById('theme-toggle-btn');
    if (headerBtn) headerBtn.addEventListener('click', toggleTheme);
    if (sidebarBtn) sidebarBtn.addEventListener('click', toggleTheme);

    // Adicionar transição suave ao body
    const style = document.createElement('style');
    style.innerHTML = `body.theme-transition { transition: background 0.4s, color 0.4s; }`;
    document.head.appendChild(style);

    // Sidebar animação e feedback visual
    animateSidebar();
    highlightActiveSidebarItem();
});

function animateSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    if (!sidebar) return;
    sidebar.addEventListener('transitionend', function() {
        sidebar.classList.toggle('sidebar-animated');
    });
}

function highlightActiveSidebarItem() {
    const links = document.querySelectorAll('.sidebar-nav .nav-link');
    links.forEach(link => {
        if (link.classList.contains('active')) {
            link.style.transform = 'scale(1.06)';
            link.style.background = 'var(--color-red-light)';
            link.style.boxShadow = '0 2px 8px rgba(229,57,53,0.08)';
        } else {
            link.style.transform = '';
            link.style.background = '';
            link.style.boxShadow = '';
        }
    });
} 