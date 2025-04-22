<?php
/**
 * Componente Carrossel de Conteúdo
 * Versão: 1.0.0
 * 
 * Exibe um carrossel horizontal de filmes, séries ou conteúdos similares.
 * 
 * @param string $id ID único para o carrossel
 * @param string $titulo Título da seção de carrossel
 * @param array $itens Lista de itens para exibir no carrossel
 * @param string $verTodosLink Link opcional para a página "ver todos"
 * @param bool $mostrarControles Se deve mostrar os controles de navegação
 * @param bool $mostrarPaginacao Se deve mostrar os indicadores de paginação
 */
if (!isset($id) || !isset($itens) || empty($itens)) {
    return;
}

// Valores padrão
$titulo = $titulo ?? 'Conteúdos';
$verTodosLink = $verTodosLink ?? '';
$mostrarControles = $mostrarControles ?? true;
$mostrarPaginacao = $mostrarPaginacao ?? false;
$classesAdicionais = $classesAdicionais ?? '';
?>

<section class="carrossel-secao <?php echo htmlspecialchars($classesAdicionais); ?>" data-aos="fade-up">
    <div class="container">
        <div class="secao-cabecalho">
            <h2 class="secao-titulo"><?php echo htmlspecialchars($titulo); ?></h2>
            
            <?php if (!empty($verTodosLink)): ?>
            <a href="<?php echo htmlspecialchars($verTodosLink); ?>" class="ver-todos">
                Ver todos <i class="fas fa-arrow-right"></i>
            </a>
            <?php endif; ?>
        </div>
        
        <div class="carrossel-container">
            <?php if ($mostrarControles): ?>
            <button type="button" class="carrossel-nav carrossel-prev" data-target="<?php echo htmlspecialchars($id); ?>" aria-label="Conteúdo anterior">
                <i class="fas fa-chevron-left"></i>
            </button>
            <?php endif; ?>
            
            <div id="<?php echo htmlspecialchars($id); ?>" class="carrossel">
                <div class="carrossel-items">
                    <?php foreach ($itens as $indice => $item): ?>
                    <div class="carrossel-item" data-aos="fade-up" data-aos-delay="<?php echo $indice % 5 * 100; ?>">
                        <div class="conteudo-card">
                            <div class="conteudo-thumbnail">
                                <img 
                                    src="<?php echo isset($item['thumbnail']) ? htmlspecialchars($item['thumbnail']) : 'img/placeholder.jpg'; ?>" 
                                    alt="<?php echo isset($item['titulo']) ? htmlspecialchars($item['titulo']) : 'Conteúdo'; ?>"
                                    loading="lazy"
                                />
                                
                                <?php if (isset($item['tipo'])): ?>
                                <span class="conteudo-tipo"><?php echo htmlspecialchars($item['tipo']); ?></span>
                                <?php endif; ?>
                                
                                <a href="<?php echo isset($item['link']) ? htmlspecialchars($item['link']) : '#'; ?>" class="overlay-play">
                                    <i class="fas fa-play"></i>
                                </a>
                            </div>
                            
                            <div class="conteudo-info">
                                <h3 class="conteudo-titulo">
                                    <a href="<?php echo isset($item['link']) ? htmlspecialchars($item['link']) : '#'; ?>">
                                        <?php echo htmlspecialchars($item['titulo']); ?>
                                    </a>
                                </h3>
                                
                                <?php if (isset($item['progresso']) && $item['progresso'] > 0): ?>
                                <div class="conteudo-progresso">
                                    <div class="progresso-bar">
                                        <div class="progresso-valor" style="width: <?php echo htmlspecialchars($item['progresso']); ?>%;"></div>
                                    </div>
                                    
                                    <?php if (isset($item['tempoRestante'])): ?>
                                    <span class="tempo-restante"><?php echo htmlspecialchars($item['tempoRestante']); ?> restantes</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($item['lancamento']) && $item['lancamento']): ?>
                                <span class="tag-lancamento">Novo</span>
                                <?php endif; ?>
                                
                                <div class="conteudo-meta">
                                    <?php if (isset($item['ano'])): ?>
                                    <span class="ano"><?php echo htmlspecialchars($item['ano']); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($item['classificacao'])): ?>
                                    <span class="conteudo-classificacao"><?php echo htmlspecialchars($item['classificacao']); ?>+</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if ($mostrarControles): ?>
            <button type="button" class="carrossel-nav carrossel-next" data-target="<?php echo htmlspecialchars($id); ?>" aria-label="Próximo conteúdo">
                <i class="fas fa-chevron-right"></i>
            </button>
            <?php endif; ?>
        </div>
        
        <?php if ($mostrarPaginacao): ?>
        <div class="carrossel-paginacao" id="pagination-<?php echo htmlspecialchars($id); ?>">
            <?php 
            // Calcular número aproximado de páginas (6 itens por página em desktop)
            $totalPaginas = ceil(count($itens) / 6);
            for ($i = 0; $i < $totalPaginas; $i++): 
            ?>
            <button class="pagination-dot <?php echo $i === 0 ? 'active' : ''; ?>" aria-label="Página <?php echo $i + 1; ?>"></button>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.carrossel-secao {
    margin: 80px 0;
    position: relative;
    overflow: hidden;
    padding-bottom: 40px;
}

.secao-cabecalho {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 30px;
    padding: 0 40px;
}

.secao-titulo {
    position: relative;
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    padding-bottom: 15px;
}

.secao-titulo:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 4px;
    background-color: var(--primary-color);
    border-radius: 2px;
}

.ver-todos {
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.ver-todos i {
    margin-left: 5px;
    font-size: 12px;
    transition: transform 0.3s ease;
}

.ver-todos:hover {
    color: var(--primary-color);
}

.ver-todos:hover i {
    transform: translateX(3px);
}

.carrossel-container {
    position: relative;
    margin: 0 20px;
    perspective: 1000px;
}

.carrossel {
    position: relative;
    overflow: hidden;
}

.carrossel-wrapper {
    perspective: 1000px;
    transform-style: preserve-3d;
}

.carrossel-items {
    display: flex;
    scroll-behavior: smooth;
    scrollbar-width: none;
    padding: 20px 40px;
    margin: 0 -40px;
    gap: 20px;
    overflow-x: auto;
    transition: transform 0.5s ease;
}

.carrossel-items::-webkit-scrollbar {
    display: none;
}

.carrossel-item {
    flex: 0 0 auto;
    width: 250px;
    transform: translateZ(0);
    transition: all 0.3s ease;
    animation: fadeInRight 0.8s ease-out;
    animation-delay: calc(var(--item-index) * 0.1s);
    animation-fill-mode: both;
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.conteudo-card {
    position: relative;
    display: block;
    border-radius: 10px;
    overflow: hidden;
    text-decoration: none;
    transform-style: preserve-3d;
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    transform-origin: center center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    height: 100%;
}

.conteudo-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
}

.conteudo-card:hover {
    transform: scale(1.08);
    z-index: 20;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
}

.conteudo-card:hover .conteudo-card-inner {
    transform: translateZ(10px);
}

.conteudo-thumbnail {
    position: relative;
    width: 100%;
    height: 0;
    padding-top: 150%;
    overflow: hidden;
}

.conteudo-thumbnail img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
}

.conteudo-card:hover .conteudo-thumbnail img {
    transform: scale(1.1);
    filter: brightness(1.1);
}

.conteudo-tipo {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    z-index: 2;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.overlay-play {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 40%, rgba(0, 0, 0, 0) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.overlay-play i {
    color: white;
    font-size: 60px;
    transform: scale(0.8) translateY(20px);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    opacity: 0;
}

.conteudo-card:hover .overlay-play {
    opacity: 1;
}

.conteudo-card:hover .overlay-play i {
    transform: scale(1) translateY(0);
    opacity: 1;
}

.tag-lancamento {
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: var(--primary-color);
    color: white;
    font-weight: 600;
    font-size: 10px;
    padding: 4px 10px;
    border-radius: 4px;
    z-index: 2;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

.conteudo-info {
    background-color: var(--bg-card);
    padding: 15px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}

.conteudo-card:hover .conteudo-info {
    background-color: var(--bg-card-hover);
}

.conteudo-titulo {
    color: var(--text-primary);
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 8px;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.3s ease;
}

.conteudo-card:hover .conteudo-titulo {
    color: var(--primary-color);
}

.conteudo-progresso {
    margin-top: 10px;
}

.progresso-bar {
    height: 4px;
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
    overflow: hidden;
}

.progresso-valor {
    height: 100%;
    background-color: var(--primary-color);
    border-radius: 2px;
    transition: width 0.3s ease;
}

.tempo-restante {
    display: block;
    font-size: 12px;
    color: var(--text-tertiary);
    margin-top: 5px;
}

.conteudo-meta {
    display: flex;
    gap: 10px;
    margin-top: 8px;
    font-size: 12px;
    color: var(--text-secondary);
}

.conteudo-classificacao {
    display: inline-block;
    background-color: rgba(255, 255, 255, 0.1);
    padding: 1px 5px;
    border-radius: 3px;
}

.carrossel-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: rgba(0, 0, 0, 0.7);
    border: 2px solid rgba(255, 255, 255, 0.2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.carrossel-nav:hover {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    transform: translateY(-50%) scale(1.1);
}

.carrossel-prev {
    left: 0;
}

.carrossel-next {
    right: 0;
}

/* Reflexos para efeito 3D */
.card-reflections {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.conteudo-card:hover .card-reflections {
    opacity: 1;
}

.reflection-top,
.reflection-bottom,
.reflection-left,
.reflection-right {
    position: absolute;
    background: linear-gradient(rgba(255, 255, 255, 0.1), transparent);
    border-radius: 10px;
}

.reflection-top {
    top: 0;
    left: 0;
    right: 0;
    height: 20px;
    background: linear-gradient(to bottom, rgba(255, 255, 255, 0.1), transparent);
    opacity: 0.7;
}

.reflection-bottom {
    bottom: 0;
    left: 0;
    right: 0;
    height: 20px;
    background: linear-gradient(to top, rgba(255, 255, 255, 0.1), transparent);
    opacity: 0.5;
}

.reflection-left {
    top: 0;
    left: 0;
    bottom: 0;
    width: 20px;
    background: linear-gradient(to right, rgba(255, 255, 255, 0.1), transparent);
    opacity: 0.5;
}

.reflection-right {
    top: 0;
    right: 0;
    bottom: 0;
    width: 20px;
    background: linear-gradient(to left, rgba(255, 255, 255, 0.1), transparent);
    opacity: 0.5;
}

/* Paginação */
.carrossel-pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 25px;
}

.pagination-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.3);
    cursor: pointer;
    transition: all 0.3s ease;
}

.pagination-dot.active {
    background-color: var(--primary-color);
    transform: scale(1.2);
}

.pagination-dot:hover {
    background-color: rgba(255, 255, 255, 0.5);
}

@media (max-width: 1024px) {
    .carrossel-item {
        width: 220px;
    }
}

@media (max-width: 768px) {
    .secao-cabecalho {
        padding: 0 20px;
    }
    
    .carrossel-items {
        padding: 10px 30px;
        margin: 0 -30px;
        gap: 15px;
    }
    
    .carrossel-item {
        width: 180px;
    }
    
    .overlay-play i {
        font-size: 40px;
    }
}

@media (max-width: 576px) {
    .carrossel-item {
        width: 160px;
    }
    
    .carrossel-nav {
        width: 30px;
        height: 30px;
    }
    
    .secao-titulo {
        font-size: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar carrosséis
    const carrosseis = document.querySelectorAll('.carrossel');
    
    carrosseis.forEach(carrossel => {
        const id = carrossel.id;
        const items = carrossel.querySelector('.carrossel-items');
        const prevBtn = document.querySelector(`.carrossel-prev[data-target="${id}"]`);
        const nextBtn = document.querySelector(`.carrossel-next[data-target="${id}"]`);
        const pagination = document.getElementById(`pagination-${id}`);
        const dots = pagination ? pagination.querySelectorAll('.pagination-dot') : [];
        
        // Scroll amount for each click
        const scrollAmount = carrossel.offsetWidth * 0.8;
        let currentPage = 0;
        
        // Next button click
        nextBtn.addEventListener('click', () => {
            items.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            
            if (currentPage < dots.length - 1) {
                currentPage++;
                updatePagination();
            }
        });
        
        // Previous button click
        prevBtn.addEventListener('click', () => {
            items.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            
            if (currentPage > 0) {
                currentPage--;
                updatePagination();
            }
        });
        
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
        
        // Adicionar efeito 3D nos cards ao passar o mouse
        const cards = carrossel.querySelectorAll('.conteudo-card');
        
        cards.forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width;
                const y = (e.clientY - rect.top) / rect.height;
                
                const tiltX = (y - 0.5) * 10; // Tilt vertical
                const tiltY = (x - 0.5) * -10; // Tilt horizontal
                
                // Aplicar transformação 3D
                this.style.transform = `scale(1.08) rotateX(${tiltX}deg) rotateY(${tiltY}deg)`;
                
                // Efeito de luz/brilho
                const shine = this.querySelector('.card-reflections');
                if (shine) {
                    shine.style.backgroundPosition = `${x * 100}% ${y * 100}%`;
                }
            });
            
            // Reset transform on mouse leave
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    });
});
</script> 