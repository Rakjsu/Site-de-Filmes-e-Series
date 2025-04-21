/**
 * Gerenciador de Autenticação para o Player
 * Versão: 1.0.0
 */
class AuthManager {
    constructor() {
        this.initElements();
        this.initEventListeners();
        this.checkSession();
    }

    /**
     * Inicializa os elementos do DOM necessários
     */
    initElements() {
        // Modal e elementos do formulário
        this.loginModal = document.getElementById('loginModal');
        this.loginForm = document.getElementById('loginForm');
        this.usernameInput = document.getElementById('username');
        this.passwordInput = document.getElementById('password');
        this.rememberMeCheckbox = document.getElementById('rememberMe');
        this.loginBtn = document.getElementById('loginBtn');
        this.loginErrorMsg = document.querySelector('.login-error');
        
        // Botões de controle
        this.adminBtn = document.querySelector('.admin-button');
        this.closeBtn = document.querySelector('.close-login');
    }

    /**
     * Inicializa os event listeners
     */
    initEventListeners() {
        // Abrir modal de login
        if (this.adminBtn) {
            this.adminBtn.addEventListener('click', () => this.openLoginModal());
        }
        
        // Fechar modal de login
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.closeLoginModal());
        }
        
        // Fechar modal ao clicar fora
        if (this.loginModal) {
            this.loginModal.addEventListener('click', (e) => {
                if (e.target === this.loginModal) {
                    this.closeLoginModal();
                }
            });
        }
        
        // Processar envio do formulário
        if (this.loginForm) {
            this.loginForm.addEventListener('submit', (e) => this.handleLoginSubmit(e));
        }
        
        // Evento de tecla Escape para fechar o modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.loginModal && this.loginModal.style.display === 'flex') {
                this.closeLoginModal();
            }
        });
    }

    /**
     * Verifica se o usuário já possui uma sessão ativa
     */
    checkSession() {
        // Verifica se há um cookie de autenticação
        const cookies = document.cookie.split(';');
        let authToken = null;
        
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            if (cookie.startsWith('auth_token=')) {
                authToken = cookie.substring('auth_token='.length, cookie.length);
                break;
            }
        }
        
        if (authToken) {
            // Verifica o token no servidor
            fetch('check_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ token: authToken })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Usuário já autenticado:', data.username);
                    this.updateUIForLoggedUser(data.username, data.role);
                }
            })
            .catch(error => {
                console.error('Erro ao verificar sessão:', error);
            });
        }
    }

    /**
     * Atualiza a interface para usuários logados
     */
    updateUIForLoggedUser(username, role) {
        if (this.adminBtn) {
            // Atualiza o texto da tooltip
            const tooltip = this.adminBtn.nextElementSibling;
            if (tooltip) {
                tooltip.textContent = `Logado como ${username}`;
            }
            
            // Adiciona classe indicando que está logado
            this.adminBtn.classList.add('logged-in');
            
            // Atualiza o ícone se necessário
            const icon = this.adminBtn.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-user-check';
            }
        }
        
        // Adiciona opções adicionais ao menu para usuários logados
        if (role === 'admin') {
            // Implementar funcionalidades específicas de admin
            this.addAdminFunctionality();
        }
    }

    /**
     * Adiciona funcionalidades específicas para administradores
     */
    addAdminFunctionality() {
        // Cria um botão para acessar o painel de administração
        const playerControls = document.querySelector('.player-controls');
        if (playerControls) {
            const adminPanelBtn = document.createElement('button');
            adminPanelBtn.classList.add('admin-panel-btn');
            adminPanelBtn.innerHTML = '<i class="fas fa-cog"></i>';
            adminPanelBtn.setAttribute('title', 'Painel Admin');
            
            adminPanelBtn.addEventListener('click', () => {
                window.location.href = 'admin/dashboard.php';
            });
            
            playerControls.appendChild(adminPanelBtn);
        }
    }

    /**
     * Gerencia o envio do formulário de login
     */
    handleLoginSubmit(e) {
        e.preventDefault();
        
        // Validação básica
        const username = this.usernameInput.value.trim();
        const password = this.passwordInput.value;
        const rememberMe = this.rememberMeCheckbox.checked;
        
        if (!username || !password) {
            this.showError('Por favor, preencha todos os campos.');
            return;
        }
        
        // Desabilita o botão de login durante o processamento
        if (this.loginBtn) {
            this.loginBtn.disabled = true;
            this.loginBtn.textContent = 'Entrando...';
        }
        
        // Envia requisição de login
        fetch('login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                password: password,
                remember_me: rememberMe
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showSuccess('Login realizado com sucesso! Redirecionando...');
                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        this.closeLoginModal();
                        this.updateUIForLoggedUser(data.username, data.role);
                    }
                }, 1500);
            } else {
                this.showError(data.message || 'Erro ao fazer login. Verifique suas credenciais.');
                
                // Reativa o botão de login
                if (this.loginBtn) {
                    this.loginBtn.disabled = false;
                    this.loginBtn.textContent = 'Entrar';
                }
            }
        })
        .catch(error => {
            console.error('Erro ao processar login:', error);
            this.showError('Ocorreu um erro ao processar sua solicitação. Tente novamente.');
            
            // Reativa o botão de login
            if (this.loginBtn) {
                this.loginBtn.disabled = false;
                this.loginBtn.textContent = 'Entrar';
            }
        });
    }

    /**
     * Exibe uma mensagem de erro
     */
    showError(message) {
        if (this.loginErrorMsg) {
            this.loginErrorMsg.textContent = message;
            this.loginErrorMsg.classList.remove('success');
            this.loginErrorMsg.classList.add('error');
            this.loginErrorMsg.style.display = 'block';
        }
    }

    /**
     * Exibe uma mensagem de sucesso
     */
    showSuccess(message) {
        if (this.loginErrorMsg) {
            this.loginErrorMsg.textContent = message;
            this.loginErrorMsg.classList.remove('error');
            this.loginErrorMsg.classList.add('success');
            this.loginErrorMsg.style.display = 'block';
        }
    }

    /**
     * Abre o modal de login
     */
    openLoginModal() {
        if (this.loginModal) {
            this.loginModal.style.display = 'flex';
            
            // Pequeno delay para a animação funcionar
            setTimeout(() => {
                this.loginModal.classList.add('show');
                
                // Foca no campo de usuário
                if (this.usernameInput) {
                    this.usernameInput.focus();
                }
            }, 10);
            
            // Limpa mensagens de erro anteriores
            if (this.loginErrorMsg) {
                this.loginErrorMsg.style.display = 'none';
            }
            
            // Reseta campos do formulário
            if (this.loginForm) {
                this.loginForm.reset();
            }
        }
    }

    /**
     * Fecha o modal de login
     */
    closeLoginModal() {
        if (this.loginModal) {
            this.loginModal.classList.remove('show');
            
            // Pequeno delay para a animação completar
            setTimeout(() => {
                this.loginModal.style.display = 'none';
            }, 300);
        }
    }

    /**
     * Realiza logout do usuário
     */
    logout() {
        fetch('logout.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualiza a UI para usuário deslogado
                if (this.adminBtn) {
                    const tooltip = this.adminBtn.nextElementSibling;
                    if (tooltip) {
                        tooltip.textContent = 'Admin';
                    }
                    
                    this.adminBtn.classList.remove('logged-in');
                    
                    const icon = this.adminBtn.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-user-shield';
                    }
                }
                
                // Remove funcionalidades de admin
                const adminPanelBtn = document.querySelector('.admin-panel-btn');
                if (adminPanelBtn) {
                    adminPanelBtn.remove();
                }
                
                // Se estiver em uma página admin, redireciona para a página principal
                if (window.location.href.includes('/admin/')) {
                    window.location.href = '../index.html';
                }
            }
        })
        .catch(error => {
            console.error('Erro ao processar logout:', error);
        });
    }
}

// Inicializa o gerenciador de autenticação quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.authManager = new AuthManager();
}); 