<?php
/**
 * Componente Grid de Categorias
 * 
 * Exibe uma grade de categorias disponíveis no site.
 * 
 * @param array $categorias Lista de categorias para exibir
 */
if (!isset($categorias) || empty($categorias)) {
    return;
}
?>

<section class="categorias-secao">
    <div class="container">
        <h2 class="secao-titulo">Categorias</h2>
        
        <div class="categorias-grid">
            <?php foreach ($categorias as $index => $categoria): ?>
            <a href="categoria.php?id=<?php echo htmlspecialchars($categoria['id']); ?>" class="categoria-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>">
                <div class="categoria-glow"></div>
                <div class="categoria-icon">
                    <i class="fas <?php echo htmlspecialchars($categoria['icone']); ?>"></i>
                </div>
                <h3 class="categoria-nome"><?php echo htmlspecialchars($categoria['nome']); ?></h3>
                <div class="categoria-indicador"></div>
                <div class="categoria-hover">
                    <span>Explorar</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Elementos decorativos para a seção de categorias -->
    <div class="categorias-decorative-bg">
        <div class="decor-circle circle-1"></div>
        <div class="decor-circle circle-2"></div>
        <div class="decor-line line-1"></div>
        <div class="decor-line line-2"></div>
    </div>
</section>

<style>
.categorias-secao {
    margin: 80px 0;
    padding: 0 20px;
    position: relative;
    overflow: hidden;
}

.categorias-secao .secao-titulo {
    position: relative;
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 40px;
    padding-bottom: 15px;
    text-align: left;
}

.categorias-secao .secao-titulo:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 4px;
    background-color: var(--primary-color);
    border-radius: 2px;
}

.categorias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 25px;
    max-width: 1280px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}

.categoria-card {
    background: linear-gradient(145deg, #1c1c1c, #232323);
    border-radius: 12px;
    overflow: hidden;
    padding: 30px 15px;
    text-decoration: none;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    border: 1px solid #333;
    min-height: 180px;
}

.categoria-card:hover {
    transform: translateY(-15px) scale(1.03);
    box-shadow: 0 20px 35px rgba(229, 9, 20, 0.2);
    border-color: var(--primary-color);
    z-index: 10;
}

.categoria-glow {
    position: absolute;
    top: -20px;
    left: -20px;
    right: -20px;
    bottom: -20px;
    background: radial-gradient(circle at center, rgba(229, 9, 20, 0.15), transparent 70%);
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: -1;
}

.categoria-card:hover .categoria-glow {
    opacity: 1;
}

.categoria-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary-color), #ff4d4d);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(229, 9, 20, 0.3);
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
}

.categoria-card:hover .categoria-icon {
    transform: scale(1.15) rotate(5deg);
    box-shadow: 0 10px 25px rgba(229, 9, 20, 0.4);
}

.categoria-icon:after {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: conic-gradient(from 0deg, var(--primary-color), #ff4d4d, #ff9999, #ff4d4d, var(--primary-color));
    border-radius: 50%;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.categoria-card:hover .categoria-icon:after {
    opacity: 0.7;
    animation: spin 10s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.categoria-icon i {
    font-size: 28px;
    color: white;
    filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.3));
    transition: all 0.3s ease;
}

.categoria-card:hover .categoria-icon i {
    transform: scale(1.1);
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.categoria-nome {
    color: var(--text-primary);
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    line-height: 1.3;
    transition: all 0.3s ease;
}

.categoria-card:hover .categoria-nome {
    color: var(--primary-color);
    transform: translateY(-5px);
}

.categoria-indicador {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 3px;
    background: linear-gradient(to right, var(--primary-color), #ff4d4d);
    transition: width 0.3s ease;
}

.categoria-card:hover .categoria-indicador {
    width: 100%;
    height: 4px;
    animation: pulse-width 1.5s infinite;
}

@keyframes pulse-width {
    0% { opacity: 0.7; }
    50% { opacity: 1; }
    100% { opacity: 0.7; }
}

.categoria-hover {
    position: absolute;
    bottom: 10px;
    left: 0;
    right: 0;
    text-align: center;
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.3s ease;
}

.categoria-hover span {
    display: inline-block;
    background-color: var(--primary-color);
    color: white;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 3px 10px rgba(229, 9, 20, 0.3);
}

.categoria-card:hover .categoria-hover {
    transform: translateY(0);
    opacity: 1;
}

/* Elementos decorativos */
.categorias-decorative-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    z-index: 1;
}

.decor-circle {
    position: absolute;
    border-radius: 50%;
    opacity: 0.05;
}

.circle-1 {
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, var(--primary-color) 0%, transparent 70%);
    top: -200px;
    right: -100px;
}

.circle-2 {
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, var(--primary-color) 0%, transparent 70%);
    bottom: -150px;
    left: -100px;
}

.decor-line {
    position: absolute;
    background-color: var(--primary-color);
    opacity: 0.05;
}

.line-1 {
    width: 150px;
    height: 3px;
    transform: rotate(-45deg);
    top: 30%;
    left: 5%;
}

.line-2 {
    width: 200px;
    height: 3px;
    transform: rotate(30deg);
    bottom: 20%;
    right: 10%;
}

@media (max-width: 768px) {
    .categorias-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 15px;
    }
    
    .categoria-icon {
        width: 60px;
        height: 60px;
    }
    
    .categoria-nome {
        font-size: 16px;
    }
    
    .categoria-card {
        min-height: 160px;
    }
}

@media (max-width: 480px) {
    .categorias-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .categorias-secao .secao-titulo {
        font-size: 1.8rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar efeito de hover para as categorias
    const cards = document.querySelectorAll('.categoria-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            this.style.setProperty('--x-pos', `${x}px`);
            this.style.setProperty('--y-pos', `${y}px`);
        });
    });
});
</script> 