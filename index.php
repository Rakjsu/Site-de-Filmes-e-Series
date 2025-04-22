<?php
/**
 * Página Inicial - Site de Filmes e Series
 * 
 * Página principal do sistema de streaming de vídeos
 * com funcionalidades de reprodução e navegação.
 * 
 * @version 1.0.1
 */

// Iniciar sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivo de autenticação
require_once 'auth.php';

// Verificar se o usuário está autenticado
$isLoggedIn = isAuthenticated();
$userName = $isLoggedIn && isset($_SESSION['user_data']['username']) ? $_SESSION['user_data']['username'] : '';
$isAdmin = $isLoggedIn && isset($_SESSION['user_data']['is_admin']) ? $_SESSION['user_data']['is_admin'] : false;

// Categorias populares (em produção viriam do banco de dados)
$categorias = [
    ['id' => 1, 'nome' => 'Filmes', 'icone' => 'fa-film'],
    ['id' => 2, 'nome' => 'Séries', 'icone' => 'fa-tv'],
    ['id' => 3, 'nome' => 'Documentários', 'icone' => 'fa-book'],
    ['id' => 4, 'nome' => 'Esportes', 'icone' => 'fa-futbol'],
    ['id' => 5, 'nome' => 'Música', 'icone' => 'fa-music'],
    ['id' => 6, 'nome' => 'Infantil', 'icone' => 'fa-child']
];

// Conteúdo destacado (em produção viria do banco de dados)
$conteudoDestaque = [
    'id' => 'destaque1',
    'titulo' => 'O Caminho do Herói',
    'descricao' => 'Uma jornada épica através de mundos fantásticos, onde um jovem descobre seu verdadeiro destino.',
    'imagem' => 'assets/images/banner-destaque.jpg',
    'video_url' => 'assets/videos/episode1.m3u8',
    'tipo' => 'Série',
    'ano' => '2023',
    'classificacao' => '14',
    'temporadas' => 2
];

// Conteúdos populares (em produção viriam do banco de dados)
$conteudosPopulares = [
    [
        'id' => 'pop1', 
        'titulo' => 'O Início', 
        'imagem' => 'assets/thumbnails/thumb-serie1.jpg',
        'tipo' => 'Episódio 1'
    ],
    [
        'id' => 'pop2', 
        'titulo' => 'O Despertar', 
        'imagem' => 'assets/thumbnails/thumb-serie2.jpg',
        'tipo' => 'Episódio 2'
    ],
    [
        'id' => 'pop3', 
        'titulo' => 'A Jornada', 
        'imagem' => 'assets/thumbnails/thumb-serie3.jpg',
        'tipo' => 'Episódio 3'
    ],
    [
        'id' => 'pop4', 
        'titulo' => 'Alianças', 
        'imagem' => 'assets/thumbnails/thumb-serie4.jpg',
        'tipo' => 'Episódio 4'
    ],
    [
        'id' => 'pop5', 
        'titulo' => 'A Revelação', 
        'imagem' => 'assets/thumbnails/thumb-serie5.jpg',
        'tipo' => 'Episódio 5'
    ],
    [
        'id' => 'pop6', 
        'titulo' => 'O Confronto', 
        'imagem' => 'assets/thumbnails/thumb-serie6.jpg',
        'tipo' => 'Episódio 6'
    ]
];

// Conteúdos recentemente adicionados (em produção viriam do banco de dados)
$conteudosRecentes = [
    [
        'id' => 'rec1', 
        'titulo' => 'Novo Horizonte', 
        'imagem' => 'assets/thumbnails/thumb-filme1.jpg',
        'tipo' => 'Filme'
    ],
    [
        'id' => 'rec2', 
        'titulo' => 'Além das Estrelas', 
        'imagem' => 'assets/thumbnails/thumb-serie1.jpg',
        'tipo' => 'Série'
    ],
    [
        'id' => 'rec3', 
        'titulo' => 'Mistérios do Oceano', 
        'imagem' => 'assets/thumbnails/thumb-doc1.jpg',
        'tipo' => 'Documentário'
    ],
    [
        'id' => 'rec4', 
        'titulo' => 'Final Decisivo', 
        'imagem' => 'assets/thumbnails/thumb-esporte1.jpg',
        'tipo' => 'Esporte'
    ],
    [
        'id' => 'rec5', 
        'titulo' => 'A Grande Sinfonia', 
        'imagem' => 'assets/thumbnails/thumb-musica1.jpg',
        'tipo' => 'Música'
    ],
    [
        'id' => 'rec6', 
        'titulo' => 'Aventuras Mágicas', 
        'imagem' => 'assets/thumbnails/thumb-infantil1.jpg',
        'tipo' => 'Infantil'
    ]
];

// Continuar assistindo (para usuários logados)
$continuarAssistindo = [];
if ($isLoggedIn) {
    $continuarAssistindo = [
        [
            'id' => 'cont1', 
            'titulo' => 'O Início', 
            'imagem' => 'assets/thumbnails/thumb-serie1.jpg',
            'progresso' => 75,
            'tempo_restante' => '15 min'
        ],
        [
            'id' => 'cont2', 
            'titulo' => 'Além das Estrelas', 
            'imagem' => 'assets/thumbnails/thumb-serie2.jpg',
            'progresso' => 45,
            'tempo_restante' => '32 min'
        ],
        [
            'id' => 'cont3', 
            'titulo' => 'Mistérios do Oceano', 
            'imagem' => 'assets/thumbnails/thumb-doc1.jpg',
            'progresso' => 60,
            'tempo_restante' => '22 min'
        ],
        [
            'id' => 'cont4', 
            'titulo' => 'A Revelação', 
            'imagem' => 'assets/thumbnails/thumb-serie3.jpg',
            'progresso' => 30,
            'tempo_restante' => '42 min'
        ]
    ];
}

// Configurar variáveis para o template
$pageTitle = 'Site de Filmes e Series - Sua Plataforma de Streaming';
$bodyClass = 'home-page';
$additionalCSS = ['css/home.css'];
$headerScripts = '';
$footerScripts = '<script src="js/carrossel.js"></script>';

// Incluir o cabeçalho
include 'includes/templates/header.php';
?>

<!-- Banner de Destaque -->
<?php include 'includes/components/banner-destaque.php'; ?>

<!-- Categorias -->
<?php include 'includes/components/categorias-grid.php'; ?>

<!-- Continuar Assistindo (para usuários logados) -->
<?php if ($isLoggedIn && !empty($continuarAssistindo)): ?>
    <?php 
    $itens = $continuarAssistindo;
    $titulo = 'Continuar Assistindo';
    $id = 'continuar';
    $mostrarProgresso = true;
    include 'includes/components/carrossel-conteudo.php';
    ?>
<?php endif; ?>

<!-- Conteúdos Populares -->
<?php 
$itens = $conteudosPopulares;
$titulo = 'Populares';
$id = 'populares';
$mostrarProgresso = false;
include 'includes/components/carrossel-conteudo.php';
?>

<!-- Adicionados Recentemente -->
<?php 
$itens = $conteudosRecentes;
$titulo = 'Adicionados Recentemente';
$id = 'recentes';
$mostrarProgresso = false;
include 'includes/components/carrossel-conteudo.php';
?>

<?php
// Incluir o rodapé
include 'includes/templates/footer.php';
?> 