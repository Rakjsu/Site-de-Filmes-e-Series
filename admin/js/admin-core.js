/**
 * admin-core.js - Funções principais do painel administrativo
 * Version: 1.0.0
 * 
 * Este arquivo contém funcionalidades principais do painel administrativo:
 * - Toggle de sidebar (mobile e desktop)
 * - Sistema de tema escuro
 * - Dropdowns
 * - Notificações
 * - Inicialização de componentes
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos os módulos
    SidebarManager.init();
    ThemeManager.init();
    DropdownManager.init();
    NotificationManager.init();
    
    // Inicializar preloader
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        setTimeout(() => {
            preloader.classList.add('hide');
            setTimeout(() => preloader.style.display = 'none', 500);
        }, 800);
    }
});

/**
 * Gerenciador da Sidebar
 */
const SidebarManager = {
    init: function() {
        // Toggle da sidebar no desktop
        const desktopToggle = document.querySelector('.sidebar-toggle.desktop-only');
        if (desktopToggle) {
            desktopToggle.addEventListener('click', this.toggleSidebar);
        }
        
        // Toggle da sidebar no mobile
        const mobileToggle = document.querySelector('.sidebar-toggle.mobile-only');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', this.toggleSidebar);
        }
        
        // Fecha a sidebar no mobile quando clica fora
        document.addEventListener('click', this.handleOutsideClick.bind(this));
        
        // Verifica o tamanho da tela para ajustar a sidebar
        this.checkScreenSize();
        window.addEventListener('resize', this.checkScreenSize.bind(this));
    },
    
    toggleSidebar: function() {
        document.querySelector('.admin-wrapper').classList.toggle('sidebar-collapsed');
    },
    
    handleOutsideClick: function(e) {
        // Se estiver no mobile, verificar cliques fora da sidebar
        if (window.innerWidth <= 991) {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            
            if (sidebar && !sidebar.contains(e.target) && 
                sidebarToggle && !sidebarToggle.contains(e.target)) {
                document.querySelector('.admin-wrapper').classList.add('sidebar-collapsed');
            }
        }
    },
    
    checkScreenSize: function() {
        // Colapsar automaticamente no mobile
        if (window.innerWidth <= 991) {
            document.querySelector('.admin-wrapper').classList.add('sidebar-collapsed');
        } else {
            document.querySelector('.admin-wrapper').classList.remove('sidebar-collapsed');
        }
    }
};

/**
 * Gerenciador de Temas
 */
const ThemeManager = {
    init: function() {
        // Verificar preferência salva
        if (localStorage.getItem('admin-theme') === 'dark') {
            document.body.classList.add('dark-theme');
            this.updateToggleButton();
        }
        
        // Botão de alternar tema
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', this.toggleTheme.bind(this));
        }
    },
    
    toggleTheme: function() {
        document.body.classList.toggle('dark-theme');
        
        // Salvar preferência
        if (document.body.classList.contains('dark-theme')) {
            localStorage.setItem('admin-theme', 'dark');
        } else {
            localStorage.setItem('admin-theme', 'light');
        }
        
        this.updateToggleButton();
    },
    
    updateToggleButton: function() {
        const themeToggle = document.getElementById('theme-toggle');
        if (!themeToggle) return;
        
        const icon = themeToggle.querySelector('i');
        if (document.body.classList.contains('dark-theme')) {
            icon.className = 'fas fa-sun';
        } else {
            icon.className = 'fas fa-moon';
        }
    }
};

/**
 * Gerenciador de Dropdowns
 */
const DropdownManager = {
    init: function() {
        // Inicializar todos os dropdowns
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', this.toggleDropdown);
        });
        
        // Fechar dropdowns quando clicar fora
        document.addEventListener('click', this.closeAllDropdowns);
    },
    
    toggleDropdown: function(e) {
        e.stopPropagation();
        const parent = this.closest('.user-dropdown');
        const menu = parent.querySelector('.dropdown-menu');
        
        // Fechar outros dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(item => {
            if (item !== menu) {
                item.classList.remove('show');
            }
        });
        
        // Alternar este dropdown
        menu.classList.toggle('show');
    },
    
    closeAllDropdowns: function(e) {
        if (!e.target.matches('.dropdown-toggle') && 
            !e.target.closest('.dropdown-toggle')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    }
};

/**
 * Gerenciador de Notificações
 */
const NotificationManager = {
    init: function() {
        // Inicializar botões de notificação
        const notificationBtn = document.querySelector('.action-btn.notifications');
        if (notificationBtn) {
            notificationBtn.addEventListener('click', this.showNotifications);
        }
        
        // Inicializar botões de mensagem
        const messageBtn = document.querySelector('.action-btn.messages');
        if (messageBtn) {
            messageBtn.addEventListener('click', this.showMessages);
        }
    },
    
    showNotifications: function(e) {
        e.preventDefault();
        alert('Funcionalidade de notificações em desenvolvimento');
        // Implementar overlay de notificações
    },
    
    showMessages: function(e) {
        e.preventDefault();
        alert('Funcionalidade de mensagens em desenvolvimento');
        // Implementar overlay de mensagens
    }
};

/**
 * Utilitários
 */
const AdminUtils = {
    // Formatar data
    formatDate: function(date) {
        if (!date) return '';
        const d = new Date(date);
        return d.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    },
    
    // Formatar número com separadores
    formatNumber: function(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },
    
    // Mostrar alerta personalizado
    showAlert: function(message, type = 'info', duration = 3000) {
        const alertElement = document.createElement('div');
        alertElement.className = `admin-alert ${type}`;
        alertElement.innerHTML = `
            <div class="alert-icon">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 
                                  type === 'error' ? 'exclamation-circle' : 
                                  type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
            </div>
            <div class="alert-content">${message}</div>
            <button class="alert-close"><i class="fas fa-times"></i></button>
        `;
        
        document.body.appendChild(alertElement);
        
        // Adicionar evento para fechar alerta
        alertElement.querySelector('.alert-close').addEventListener('click', () => {
            alertElement.classList.add('hide');
            setTimeout(() => alertElement.remove(), 300);
        });
        
        // Auto fechar após duração
        setTimeout(() => {
            alertElement.classList.add('hide');
            setTimeout(() => alertElement.remove(), 300);
        }, duration);
        
        // Animar entrada
        setTimeout(() => alertElement.classList.add('show'), 10);
    }
}; 