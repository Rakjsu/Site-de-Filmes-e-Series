<?php
/**
 * Componente Carrossel de Conteúdo
 * 
 * Esse componente cria um carrossel de conteúdo reutilizável 
 * com funcionalidades avançadas e responsivo.
 * 
 * @package Player
 * @version 1.0.0
 */

// Evitar acesso direto ao arquivo
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}

/**
 * Renderiza um carrossel de conteúdo
 * 
 * @param array $params Parâmetros do carrossel
 *    - string $id ID único do carrossel
 *    - string $titulo Título da seção
 *    - string $ver_todos_link Link para ver todos os itens (opcional)
 *    - string $ver_todos_texto Texto do link ver todos (opcional, padrão: "Ver Todos")
 *    - array $itens Array de itens do carrossel
 *    - boolean $mostrar_paginacao Exibir paginação (opcional, padrão: true)
 *    - string $tema Tema do carrossel (opcional, 'light' ou 'dark', padrão: 'light')
 *    - boolean $auto_iniciar Se o carrossel deve iniciar automaticamente (opcional, padrão: true)
 * @return void
 */
function renderizar_carrossel($params) {
    // Validar parâmetros obrigatórios
    if (empty($params['id']) || empty($params['titulo']) || empty($params['itens'])) {
        echo '<div class="erro-carrossel">Parâmetros insuficientes para o carrossel.</div>';
        return;
    }

    // Extrair parâmetros
    $id = $params['id'];
    $titulo = $params['titulo'];
    $itens = $params['itens'];
    
    // Parâmetros opcionais com valores padrão
    $ver_todos_link = isset($params['ver_todos_link']) ? $params['ver_todos_link'] : '#';
    $ver_todos_texto = isset($params['ver_todos_texto']) ? $params['ver_todos_texto'] : 'Ver Todos';
    $mostrar_paginacao = isset($params['mostrar_paginacao']) ? $params['mostrar_paginacao'] : true;
    $tema = isset($params['tema']) ? $params['tema'] : 'light';
    $auto_iniciar = isset($params['auto_iniciar']) ? $params['auto_iniciar'] : true;
    
    // Classes condicionais
    $tema_class = $tema === 'dark' ? 'carrossel-tema-dark' : 'carrossel-tema-light';
?>
    <section id="<?php echo htmlspecialchars($id); ?>" class="carrossel-secao <?php echo $tema_class; ?>" data-auto-iniciar="<?php echo $auto_iniciar ? 'true' : 'false'; ?>">
        <div class="carrossel-cabecalho">
            <h2 class="carrossel-titulo">
                <?php if (!empty($params['icone'])): ?>
                    <i class="<?php echo htmlspecialchars($params['icone']); ?>"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($titulo); ?>
            </h2>
            <?php if (!empty($ver_todos_link) && $ver_todos_link !== '#'): ?>
                <a href="<?php echo htmlspecialchars($ver_todos_link); ?>" class="carrossel-ver-todos">
                    <?php echo htmlspecialchars($ver_todos_texto); ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <div class="carrossel-wrapper">
            <button class="carrossel-nav carrossel-prev" aria-label="Anterior">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="carrossel-items-wrapper">
                <div class="carrossel-items">
                    <?php foreach ($itens as $item): ?>
                        <div class="carrossel-item" data-id="<?php echo htmlspecialchars($item['id'] ?? ''); ?>">
                            <div class="conteudo-card">
                                <?php if (!empty($item['thumbnail'])): ?>
                                    <img 
                                        src="<?php echo htmlspecialchars($item['thumbnail']); ?>" 
                                        alt="<?php echo htmlspecialchars($item['titulo']); ?>"
                                        class="conteudo-imagem"
                                        loading="lazy"
                                    />
                                <?php endif; ?>
                                
                                <?php if (!empty($item['badge'])): ?>
                                    <div class="conteudo-badge"><?php echo htmlspecialchars($item['badge']); ?></div>
                                <?php endif; ?>
                                
                                <div class="conteudo-info">
                                    <h3 class="conteudo-titulo"><?php echo htmlspecialchars($item['titulo']); ?></h3>
                                    
                                    <?php if (!empty($item['metadados'])): ?>
                                        <div class="conteudo-metadados">
                                            <?php foreach ($item['metadados'] as $metadado): ?>
                                                <span><?php echo htmlspecialchars($metadado); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($item['categorias'])): ?>
                                        <div class="conteudo-categorias">
                                            <?php foreach ($item['categorias'] as $categoria): ?>
                                                <span class="conteudo-categoria"><?php echo htmlspecialchars($categoria); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($item['avaliacao'])): ?>
                                        <div class="conteudo-avaliacao">
                                            <?php 
                                            $avaliacao = (float)$item['avaliacao'];
                                            for ($i = 1; $i <= 5; $i++):
                                                if ($i <= floor($avaliacao)): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php elseif ($i == ceil($avaliacao) && $avaliacao != floor($avaliacao)): ?>
                                                    <i class="fas fa-star-half-alt"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif;
                                            endfor; ?>
                                            <span><?php echo number_format($avaliacao, 1); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="conteudo-botoes">
                                    <button class="conteudo-botao" data-action="favorite" title="Favoritar">
                                        <i class="<?php echo !empty($item['e_favorito']) ? 'fas' : 'far'; ?> fa-heart"></i>
                                    </button>
                                    <button class="conteudo-botao" data-action="add-list" title="Adicionar à lista">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="conteudo-botao" data-action="details" title="Ver detalhes">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                                
                                <div class="carrossel-item-overlay">
                                    <h4><?php echo htmlspecialchars($item['titulo']); ?></h4>
                                    <?php if (!empty($item['descricao'])): ?>
                                        <p><?php echo htmlspecialchars($item['descricao']); ?></p>
                                    <?php endif; ?>
                                    <a href="<?php echo htmlspecialchars($item['link'] ?? '#'); ?>" class="overlay-button">
                                        <i class="fas fa-play"></i> Assistir
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button class="carrossel-nav carrossel-next" aria-label="Próximo">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <?php if ($mostrar_paginacao): ?>
            <div class="carrossel-paginacao"></div>
        <?php endif; ?>
    </section>
<?php
}

/**
 * Função auxiliar para formatar a duração em minutos para o formato hh:mm:ss
 * 
 * @param int $minutos Duração em minutos
 * @return string Duração formatada
 */
function formatar_duracao($minutos) {
    $horas = floor($minutos / 60);
    $min = $minutos % 60;
    
    if ($horas > 0) {
        return sprintf('%dh %02dm', $horas, $min);
    } else {
        return sprintf('%d min', $min);
    }
} 