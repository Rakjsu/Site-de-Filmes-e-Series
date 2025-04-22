<?php
/**
 * Componente Banner de Destaque
 * 
 * Exibe o banner do conteúdo em destaque na página inicial.
 * 
 * @param array $conteudoDestaque Dados do conteúdo em destaque
 */
if (!isset($conteudoDestaque) || empty($conteudoDestaque)) {
    return;
}
?>

<section class="banner-destaque" style="background-image: url('<?php echo $conteudoDestaque['imagem']; ?>');">
    <div class="banner-overlay"></div>
    <div class="banner-content">
        <div class="banner-info">
            <div class="banner-badges">
                <span class="banner-tipo"><?php echo htmlspecialchars($conteudoDestaque['tipo']); ?></span>
                <?php if (isset($conteudoDestaque['classificacao'])): ?>
                <span class="banner-classificacao"><?php echo htmlspecialchars($conteudoDestaque['classificacao']); ?>+</span>
                <?php endif; ?>
            </div>
            <h1 class="banner-titulo"><?php echo htmlspecialchars($conteudoDestaque['titulo']); ?></h1>
            <div class="banner-meta">
                <span class="ano"><i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($conteudoDestaque['ano']); ?></span>
                <?php if (isset($conteudoDestaque['temporadas'])): ?>
                <span class="temporadas"><i class="fas fa-film"></i> <?php echo htmlspecialchars($conteudoDestaque['temporadas']); ?> Temporadas</span>
                <?php endif; ?>
                <span class="hd-tag">FULL HD</span>
            </div>
            <p class="banner-descricao"><?php echo htmlspecialchars($conteudoDestaque['descricao']); ?></p>
            <div class="banner-actions">
                <a href="assistir.php?id=<?php echo htmlspecialchars($conteudoDestaque['id']); ?>" class="btn-assistir">
                    <i class="fas fa-play"></i> Assistir Agora
                </a>
                <a href="detalhes.php?id=<?php echo htmlspecialchars($conteudoDestaque['id']); ?>" class="btn-mais-info">
                    <i class="fas fa-info-circle"></i> Informações
                </a>
            </div>
        </div>
    </div>
    
    <!-- Elemento decorativo de gradiente -->
    <div class="banner-gradient-bottom"></div>
    
    <!-- Indicadores de temporada com miniaturas -->
    <?php if (isset($conteudoDestaque['temporadas']) && $conteudoDestaque['temporadas'] > 1): ?>
    <div class="banner-thumbnails">
        <div class="thumbnails-container">
            <?php for ($i = 1; $i <= min(3, $conteudoDestaque['temporadas']); $i++): ?>
            <div class="thumbnail-item">
                <img src="assets/thumbnails/season<?php echo $i; ?>-thumb.jpg" alt="Temporada <?php echo $i; ?>">
                <span>T<?php echo $i; ?></span>
                <div class="thumbnail-hover">
                    <i class="fas fa-play"></i>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Elementos decorativos para um visual mais moderno -->
    <div class="banner-floating-elements">
        <div class="floating-element element-1"></div>
        <div class="floating-element element-2"></div>
        <div class="floating-element element-3"></div>
    </div>
</section>

<style>
/* Animações para os elementos do banner */
@keyframes float {
    0% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
    100% { transform: translateY(0px) rotate(0deg); }
}

@keyframes pulse {
    0% { opacity: 0.7; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.05); }
    100% { opacity: 0.7; transform: scale(1); }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.banner-badges {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    animation: fadeInUp 0.8s ease-out 0.2s both;
}

.banner-classificacao {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    font-weight: 600;
    font-size: 14px;
    padding: 5px 12px;
    border-radius: 4px;
}

.banner-titulo {
    animation: fadeInUp 0.8s ease-out 0.4s both;
}

.banner-meta {
    animation: fadeInUp 0.8s ease-out 0.6s both;
}

.banner-descricao {
    animation: fadeInUp 0.8s ease-out 0.8s both;
}

.banner-actions {
    animation: fadeInUp 0.8s ease-out 1s both;
}

.hd-tag {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 3px;
}

.banner-meta i {
    margin-right: 5px;
    color: var(--primary-color);
}

.banner-gradient-bottom {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 200px;
    background: linear-gradient(to top, var(--bg-main), transparent);
    z-index: 1;
}

.banner-thumbnails {
    position: absolute;
    bottom: 30px;
    right: 5%;
    z-index: 2;
    animation: fadeInRight 1s ease-out 1.2s both;
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.thumbnails-container {
    display: flex;
    gap: 15px;
}

.thumbnail-item {
    position: relative;
    width: 120px;
    height: 68px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
    cursor: pointer;
}

.thumbnail-item:hover {
    transform: scale(1.1);
    border-color: var(--primary-color);
    box-shadow: 0 12px 25px rgba(229, 9, 20, 0.3);
}

.thumbnail-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.thumbnail-item:hover img {
    transform: scale(1.1);
}

.thumbnail-item span {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 12px;
    text-align: center;
    padding: 2px 0;
}

.thumbnail-hover {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(229, 9, 20, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.thumbnail-item:hover .thumbnail-hover {
    opacity: 1;
}

.thumbnail-hover i {
    color: white;
    font-size: 24px;
}

/* Elementos decorativos flutuantes */
.banner-floating-elements {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none;
    z-index: 1;
}

.floating-element {
    position: absolute;
    background: linear-gradient(135deg, var(--primary-color), transparent);
    border-radius: 50%;
    opacity: 0.1;
}

.element-1 {
    width: 300px;
    height: 300px;
    top: -50px;
    right: 10%;
    animation: float 15s ease-in-out infinite;
}

.element-2 {
    width: 200px;
    height: 200px;
    bottom: 30%;
    left: 5%;
    animation: float 12s ease-in-out infinite reverse;
}

.element-3 {
    width: 150px;
    height: 150px;
    top: 20%;
    left: 20%;
    animation: pulse 10s ease-in-out infinite;
}

@media (max-width: 768px) {
    .banner-thumbnails {
        display: none;
    }
    
    .floating-element {
        opacity: 0.05;
    }
}
</style> 