<?php
/**
 * Template Footer
 * 
 * Rodapé comum para todas as páginas do site.
 * 
 * @param string $footerScripts Scripts adicionais no rodapé
 */

// Valores padrão
$footerScripts = $footerScripts ?? '';
?>
    </main>
    
    <!-- Rodapé -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-logo">
                <img src="assets/logo.png" alt="Site de Filmes e Series">
                <p>Sua plataforma de entretenimento completa, com os melhores filmes e séries em um só lugar, com qualidade e conforto.</p>
            </div>
            
            <div class="footer-links">
                <div class="footer-col">
                    <h4>Site de Filmes e Series</h4>
                    <ul>
                        <li><a href="sobre.php">Sobre nós</a></li>
                        <li><a href="contato.php">Contato</a></li>
                        <li><a href="imprensa.php">Imprensa</a></li>
                        <li><a href="carreiras.php">Carreiras</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Links Rápidos</h4>
                    <ul>
                        <li><a href="filmes.php">Filmes</a></li>
                        <li><a href="series.php">Series</a></li>
                        <li><a href="categorias.php">Categorias</a></li>
                        <li><a href="novidades.php">Novidades</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Categorias</h4>
                    <ul>
                        <li><a href="categoria.php?id=1">Ação</a></li>
                        <li><a href="categoria.php?id=2">Comédia</a></li>
                        <li><a href="categoria.php?id=3">Drama</a></li>
                        <li><a href="categoria.php?id=4">Sci-Fi & Fantasia</a></li>
                        <li><a href="categoria.php?id=5">Documentários</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Suporte</h4>
                    <ul>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="termos.php">Termos de Uso</a></li>
                        <li><a href="privacidade.php">Política de Privacidade</a></li>
                        <li><a href="ajuda.php">Centro de Ajuda</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-social">
                <h4>Siga-nos</h4>
                <div class="social-icons">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Site de Filmes e Series. Todos os direitos reservados.</p>
        </div>
        
        <!-- Botão de voltar ao topo -->
        <button id="back-to-top" aria-label="Voltar ao topo">
            <i class="fas fa-arrow-up"></i>
        </button>
    </footer>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="js/main.js"></script>
    
    <!-- Scripts específicos da página -->
    <?php if (!empty($footerScripts)): ?>
    <?php echo $footerScripts; ?>
    <?php endif; ?>
    
    <script>
    // Inicializar AOS (Animate on Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease-out',
        once: true
    });
    
    // Preloader
    window.addEventListener('load', function() {
        const preloader = document.querySelector('.preloader');
        if (preloader) {
            preloader.classList.add('hidden');
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        }
    });
    
    // Header scroll effect
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.main-header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
    
    // Toggle user dropdown
    const userBtn = document.querySelector('.user-btn');
    if (userBtn) {
        userBtn.addEventListener('click', function() {
            this.parentElement.classList.toggle('active');
            this.nextElementSibling.classList.toggle('active');
        });
    }
    
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            mainNav.classList.toggle('active');
        });
    }
    
    // Back to top button
    const backToTopBtn = document.getElementById('back-to-top');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Adicionar efeito 3D aos cards
    document.addEventListener('mousemove', function(e) {
        const cards = document.querySelectorAll('.conteudo-card');
        
        cards.forEach(card => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            if (x > 0 && x < rect.width && y > 0 && y < rect.height) {
                const xPercent = ((x / rect.width) - 0.5) * 2;
                const yPercent = ((y / rect.height) - 0.5) * 2;
                
                card.style.transform = `perspective(1000px) rotateY(${xPercent * 5}deg) rotateX(${yPercent * -5}deg) scale3d(1.05, 1.05, 1.05)`;
            } else {
                card.style.transform = 'perspective(1000px) rotateY(0) rotateX(0) scale3d(1, 1, 1)';
            }
        });
    });
    </script>
    
    <style>
    /* Estilos para o botão de voltar ao topo */
    #back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 99;
    }
    
    #back-to-top.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    #back-to-top:hover {
        background-color: var(--primary-hover);
        transform: translateY(-5px);
    }
    
    /* Estilos adicionais do footer */
    .footer-social h4 {
        position: relative;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .footer-social h4:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 3px;
        background-color: var(--primary-color);
        border-radius: 1.5px;
    }
    
    .social-icons {
        display: flex;
        gap: 15px;
    }
    
    .social-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: var(--text-primary);
        font-size: 18px;
        transition: all 0.3s ease;
    }
    
    .social-icon:hover {
        background-color: var(--primary-color);
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(229, 9, 20, 0.3);
    }
    </style>
</body>
</html> 