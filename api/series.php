<?php
/**
 * API de Séries
 * 
 * Gerencia informações sobre séries, incluindo listagens,
 * detalhes de séries específicas, temporadas e episódios.
 * 
 * @version 1.0.0
 */

// Carrega configuração do banco de dados
require_once 'db_config.php';

// Definir cabeçalhos para respostas JSON
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar solicitações preflight OPTIONS 
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verificar se a requisição é do tipo GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido. Use GET.']);
    exit;
}

// Obter caminho da URL
$request_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('?', $request_uri);
$path = $uri_parts[0];

// Remover parte inicial da URL (provavelmente "/api/series")
$path_parts = explode('/api/series', $path);
$endpoint = end($path_parts);

// Definir endpoints e rotas
if ($endpoint === '' || $endpoint === '/') {
    // GET /api/series - Listar todas as séries
    getSeriesList();
} elseif (preg_match('/^\/search$/', $endpoint)) {
    // GET /api/series/search?q=termo - Pesquisar séries
    searchSeries();
} elseif (preg_match('/^\/(\d+)$/', $endpoint, $matches)) {
    // GET /api/series/123 - Obter detalhes de uma série específica
    getSeries($matches[1]);
} elseif (preg_match('/^\/(\d+)\/seasons$/', $endpoint, $matches)) {
    // GET /api/series/123/seasons - Obter temporadas de uma série
    getSeriesSeasons($matches[1]);
} elseif (preg_match('/^\/popular$/', $endpoint)) {
    // GET /api/series/popular - Obter séries populares
    getPopularSeries();
} elseif (preg_match('/^\/recent$/', $endpoint)) {
    // GET /api/series/recent - Obter séries recentes
    getRecentSeries();
} elseif (preg_match('/^\/category\/(\d+)$/', $endpoint, $matches)) {
    // GET /api/series/category/5 - Obter séries por categoria
    getSeriesByCategory($matches[1]);
} else {
    // Endpoint não encontrado
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint não encontrado']);
    exit;
}

/**
 * Obtém uma lista de todas as séries com paginação
 */
function getSeriesList() {
    // Parâmetros de paginação
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    try {
        // Consulta principal para obter séries
        $sql = "SELECT 
                    s.id, s.title, s.original_title, s.description, s.poster_url, 
                    s.backdrop_url, s.year, s.created_at, s.updated_at, s.status,
                    (SELECT COUNT(*) FROM seasons WHERE series_id = s.id) as season_count,
                    (SELECT COUNT(*) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as episode_count,
                    (SELECT SUM(view_count) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as view_count
                FROM series s 
                ORDER BY s.title ASC 
                LIMIT ? OFFSET ?";
        
        $series = dbQuery($sql, [$limit, $offset]);
        
        // Consulta para contar o total de séries
        $countSql = "SELECT COUNT(*) as total FROM series";
        $countResult = dbQuerySingle($countSql);
        $total = $countResult['total'];
        
        $formattedSeries = formatSeriesList($series);
        
        // Montar resposta com metadados de paginação
        $response = [
            'data' => $formattedSeries,
            'meta' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
        
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter lista de séries']);
        exit;
    }
}

/**
 * Pesquisa séries pelo termo informado
 */
function searchSeries() {
    // Verificar se o termo de pesquisa foi informado
    if (!isset($_GET['q']) || strlen($_GET['q']) < 3) {
        http_response_code(400);
        echo json_encode(['error' => 'Termo de pesquisa deve ter pelo menos 3 caracteres']);
        exit;
    }
    
    $searchTerm = '%' . $_GET['q'] . '%';
    
    // Parâmetros de paginação
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    try {
        // Consulta para pesquisar séries
        $sql = "SELECT 
                    s.id, s.title, s.original_title, s.description, s.poster_url, 
                    s.backdrop_url, s.year, s.created_at, s.updated_at, s.status,
                    (SELECT COUNT(*) FROM seasons WHERE series_id = s.id) as season_count,
                    (SELECT COUNT(*) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as episode_count,
                    (SELECT SUM(view_count) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as view_count
                FROM series s 
                WHERE s.title LIKE ? OR s.original_title LIKE ? OR s.description LIKE ?
                ORDER BY s.title ASC 
                LIMIT ? OFFSET ?";
        
        $series = dbQuery($sql, [$searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
        
        // Consulta para contar o total de resultados
        $countSql = "SELECT COUNT(*) as total FROM series s WHERE s.title LIKE ? OR s.original_title LIKE ? OR s.description LIKE ?";
        $countResult = dbQuerySingle($countSql, [$searchTerm, $searchTerm, $searchTerm]);
        $total = $countResult['total'];
        
        $formattedSeries = formatSeriesList($series);
        
        // Montar resposta com metadados de paginação
        $response = [
            'data' => $formattedSeries,
            'meta' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
        
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao pesquisar séries']);
        exit;
    }
}

/**
 * Obtém detalhes de uma série específica
 * 
 * @param int $id ID da série
 */
function getSeries($id) {
    try {
        // Consulta principal para obter série
        $sql = "SELECT 
                    s.id, s.title, s.original_title, s.description, s.poster_url, 
                    s.backdrop_url, s.year, s.created_at, s.updated_at, s.status,
                    (SELECT COUNT(*) FROM seasons WHERE series_id = s.id) as season_count,
                    (SELECT COUNT(*) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as episode_count,
                    (SELECT SUM(view_count) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as view_count
                FROM series s 
                WHERE s.id = ?";
        
        $series = dbQuerySingle($sql, [$id]);
        
        if (!$series) {
            http_response_code(404);
            echo json_encode(['error' => 'Série não encontrada']);
            exit;
        }
        
        // Obter categorias relacionadas à série
        $categoriesSql = "SELECT c.id, c.name 
                          FROM categories c 
                          JOIN series_categories sc ON c.id = sc.category_id 
                          WHERE sc.series_id = ?";
        
        $categories = dbQuery($categoriesSql, [$id]);
        
        // Obter temporadas da série
        $seasonsSql = "SELECT 
                          id, season_number, title, description, poster_url, 
                          created_at, updated_at,
                          (SELECT COUNT(*) FROM episodes WHERE season_id = seasons.id) as episode_count
                       FROM seasons 
                       WHERE series_id = ? 
                       ORDER BY season_number ASC";
        
        $seasons = dbQuery($seasonsSql, [$id]);
        
        // Formatar a resposta
        $formattedSeries = [
            'id' => (int)$series['id'],
            'title' => $series['title'],
            'original_title' => $series['original_title'],
            'description' => $series['description'],
            'poster_url' => $series['poster_url'],
            'backdrop_url' => $series['backdrop_url'],
            'year' => (int)$series['year'],
            'status' => $series['status'],
            'season_count' => (int)$series['season_count'],
            'episode_count' => (int)$series['episode_count'],
            'view_count' => (int)$series['view_count'],
            'created_at' => $series['created_at'],
            'updated_at' => $series['updated_at'],
            'categories' => $categories,
            'seasons' => $seasons
        ];
        
        echo json_encode(['data' => $formattedSeries]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter detalhes da série']);
        exit;
    }
}

/**
 * Obtém temporadas de uma série específica
 * 
 * @param int $seriesId ID da série
 */
function getSeriesSeasons($seriesId) {
    try {
        // Verificar se a série existe
        $seriesSql = "SELECT id, title FROM series WHERE id = ?";
        $series = dbQuerySingle($seriesSql, [$seriesId]);
        
        if (!$series) {
            http_response_code(404);
            echo json_encode(['error' => 'Série não encontrada']);
            exit;
        }
        
        // Obter temporadas da série
        $seasonsSql = "SELECT 
                          id, season_number, title, description, poster_url, 
                          created_at, updated_at,
                          (SELECT COUNT(*) FROM episodes WHERE season_id = seasons.id) as episode_count
                       FROM seasons 
                       WHERE series_id = ? 
                       ORDER BY season_number ASC";
        
        $seasons = dbQuery($seasonsSql, [$seriesId]);
        
        // Para cada temporada, obter episódios
        foreach ($seasons as &$season) {
            $episodesSql = "SELECT 
                              id, episode_number, title, description, duration, 
                              thumbnail_url, video_url, view_count, created_at, updated_at
                           FROM episodes 
                           WHERE season_id = ? 
                           ORDER BY episode_number ASC";
            
            $episodes = dbQuery($episodesSql, [$season['id']]);
            $season['episodes'] = $episodes;
        }
        
        echo json_encode([
            'data' => [
                'series_id' => (int)$series['id'],
                'series_title' => $series['title'],
                'seasons' => $seasons
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter temporadas da série']);
        exit;
    }
}

/**
 * Obtém uma lista de séries populares
 */
function getPopularSeries() {
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 10;
    
    try {
        $sql = "SELECT 
                    s.id, s.title, s.original_title, s.description, s.poster_url, 
                    s.backdrop_url, s.year, s.created_at, s.updated_at, s.status,
                    (SELECT COUNT(*) FROM seasons WHERE series_id = s.id) as season_count,
                    (SELECT COUNT(*) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as episode_count,
                    (SELECT SUM(view_count) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as view_count
                FROM series s 
                ORDER BY view_count DESC 
                LIMIT ?";
        
        $series = dbQuery($sql, [$limit]);
        $formattedSeries = formatSeriesList($series);
        
        echo json_encode(['data' => $formattedSeries]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter séries populares']);
        exit;
    }
}

/**
 * Obtém uma lista de séries recentes
 */
function getRecentSeries() {
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 10;
    
    try {
        $sql = "SELECT 
                    s.id, s.title, s.original_title, s.description, s.poster_url, 
                    s.backdrop_url, s.year, s.created_at, s.updated_at, s.status,
                    (SELECT COUNT(*) FROM seasons WHERE series_id = s.id) as season_count,
                    (SELECT COUNT(*) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as episode_count,
                    (SELECT SUM(view_count) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as view_count
                FROM series s 
                ORDER BY s.created_at DESC 
                LIMIT ?";
        
        $series = dbQuery($sql, [$limit]);
        $formattedSeries = formatSeriesList($series);
        
        echo json_encode(['data' => $formattedSeries]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter séries recentes']);
        exit;
    }
}

/**
 * Obtém séries de uma categoria específica
 * 
 * @param int $categoryId ID da categoria
 */
function getSeriesByCategory($categoryId) {
    // Parâmetros de paginação
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    try {
        // Verificar se a categoria existe
        $categorySql = "SELECT id, name FROM categories WHERE id = ?";
        $category = dbQuerySingle($categorySql, [$categoryId]);
        
        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada']);
            exit;
        }
        
        // Consulta principal para obter séries por categoria
        $sql = "SELECT 
                    s.id, s.title, s.original_title, s.description, s.poster_url, 
                    s.backdrop_url, s.year, s.created_at, s.updated_at, s.status,
                    (SELECT COUNT(*) FROM seasons WHERE series_id = s.id) as season_count,
                    (SELECT COUNT(*) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as episode_count,
                    (SELECT SUM(view_count) FROM episodes e JOIN seasons se ON e.season_id = se.id WHERE se.series_id = s.id) as view_count
                FROM series s 
                JOIN series_categories sc ON s.id = sc.series_id 
                WHERE sc.category_id = ? 
                ORDER BY s.title ASC 
                LIMIT ? OFFSET ?";
        
        $series = dbQuery($sql, [$categoryId, $limit, $offset]);
        
        // Consulta para contar o total de séries na categoria
        $countSql = "SELECT COUNT(*) as total FROM series s JOIN series_categories sc ON s.id = sc.series_id WHERE sc.category_id = ?";
        $countResult = dbQuerySingle($countSql, [$categoryId]);
        $total = $countResult['total'];
        
        $formattedSeries = formatSeriesList($series);
        
        // Montar resposta com metadados de paginação
        $response = [
            'data' => $formattedSeries,
            'meta' => [
                'category' => [
                    'id' => (int)$category['id'],
                    'name' => $category['name']
                ],
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
        
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter séries por categoria']);
        exit;
    }
}

/**
 * Formata uma lista de séries para saída padronizada
 * 
 * @param array $seriesList Lista de séries do banco de dados
 * @return array Lista formatada de séries
 */
function formatSeriesList($seriesList) {
    $formatted = [];
    
    foreach ($seriesList as $series) {
        $formatted[] = [
            'id' => (int)$series['id'],
            'title' => $series['title'],
            'original_title' => $series['original_title'],
            'description' => $series['description'],
            'poster_url' => $series['poster_url'],
            'backdrop_url' => $series['backdrop_url'],
            'year' => (int)$series['year'],
            'status' => $series['status'],
            'season_count' => (int)$series['season_count'],
            'episode_count' => (int)$series['episode_count'],
            'view_count' => (int)$series['view_count'],
            'created_at' => $series['created_at'],
            'updated_at' => $series['updated_at']
        ];
    }
    
    return $formatted;
} 