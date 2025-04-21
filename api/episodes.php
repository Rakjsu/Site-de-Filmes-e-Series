<?php
/**
 * API de Episódios
 * 
 * Gerencia informações sobre episódios de séries, incluindo
 * detalhes, marcação de progresso e interações do usuário.
 * 
 * @version 1.0.0
 */

// Carrega configuração do banco de dados
require_once 'db_config.php';

// Definir cabeçalhos para respostas JSON
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar solicitações preflight OPTIONS 
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obter caminho da URL
$request_uri = $_SERVER['REQUEST_URI'];
$uri_parts = explode('?', $request_uri);
$path = $uri_parts[0];

// Remover parte inicial da URL (provavelmente "/api/episodes")
$path_parts = explode('/api/episodes', $path);
$endpoint = end($path_parts);

// Definir endpoints e rotas
if ($endpoint === '' || $endpoint === '/') {
    // Verifica o método para determinar a ação
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // GET /api/episodes?recent=1 - Listar episódios recentes
        if (isset($_GET['recent'])) {
            getRecentEpisodes();
        } else {
            // GET /api/episodes - Exibir erro, endpoint requer parâmetros
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros insuficientes para listar episódios']);
            exit;
        }
    } else {
        // Método não permitido
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido. Use GET ou POST.']);
        exit;
    }
} elseif (preg_match('/^\/(\d+)$/', $endpoint, $matches)) {
    // GET /api/episodes/123 - Obter detalhes de um episódio específico
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        getEpisodeDetails($matches[1]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST /api/episodes/123 - Registrar visualização ou progresso
        updateEpisodeProgress($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido. Use GET ou POST.']);
        exit;
    }
} elseif (preg_match('/^\/(\d+)\/next$/', $endpoint, $matches)) {
    // GET /api/episodes/123/next - Obter próximo episódio na sequência
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        getNextEpisode($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido. Use GET.']);
        exit;
    }
} elseif (preg_match('/^\/season\/(\d+)$/', $endpoint, $matches)) {
    // GET /api/episodes/season/5 - Obter episódios de uma temporada
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        getSeasonEpisodes($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido. Use GET.']);
        exit;
    }
} elseif (preg_match('/^\/popular$/', $endpoint)) {
    // GET /api/episodes/popular - Obter episódios populares
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        getPopularEpisodes();
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido. Use GET.']);
        exit;
    }
} else {
    // Endpoint não encontrado
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint não encontrado']);
    exit;
}

/**
 * Obtém detalhes de um episódio específico
 * 
 * @param int $episodeId ID do episódio
 */
function getEpisodeDetails($episodeId) {
    try {
        // Consulta principal para obter o episódio
        $sql = "SELECT 
                    e.id, e.title, e.description, e.duration, e.episode_number,
                    e.thumbnail_url, e.video_url, e.view_count, e.created_at, e.updated_at,
                    s.id as season_id, s.title as season_title, s.season_number,
                    sr.id as series_id, sr.title as series_title, sr.poster_url as series_poster
                FROM episodes e
                JOIN seasons s ON e.season_id = s.id
                JOIN series sr ON s.series_id = sr.id
                WHERE e.id = ?";
        
        $episode = dbQuerySingle($sql, [$episodeId]);
        
        if (!$episode) {
            http_response_code(404);
            echo json_encode(['error' => 'Episódio não encontrado']);
            exit;
        }
        
        // Obter capturas de tela, se houver
        $screenshotsSql = "SELECT id, url, timestamp 
                           FROM episode_screenshots 
                           WHERE episode_id = ? 
                           ORDER BY timestamp ASC";
        
        $screenshots = dbQuery($screenshotsSql, [$episodeId]);
        
        // Formatar a resposta
        $formattedEpisode = [
            'id' => (int)$episode['id'],
            'title' => $episode['title'],
            'description' => $episode['description'],
            'duration' => (int)$episode['duration'],
            'episode_number' => (int)$episode['episode_number'],
            'thumbnail_url' => $episode['thumbnail_url'],
            'video_url' => $episode['video_url'],
            'view_count' => (int)$episode['view_count'],
            'created_at' => $episode['created_at'],
            'updated_at' => $episode['updated_at'],
            'season' => [
                'id' => (int)$episode['season_id'],
                'title' => $episode['season_title'],
                'season_number' => (int)$episode['season_number']
            ],
            'series' => [
                'id' => (int)$episode['series_id'],
                'title' => $episode['series_title'],
                'poster_url' => $episode['series_poster']
            ],
            'screenshots' => $screenshots
        ];
        
        // Incrementar contador de visualizações (sem considerar como uma visualização completa)
        $updateSql = "UPDATE episodes SET view_count = view_count + 1 WHERE id = ?";
        dbExecute($updateSql, [$episodeId]);
        
        echo json_encode(['data' => $formattedEpisode]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter detalhes do episódio']);
        exit;
    }
}

/**
 * Obtém o próximo episódio na sequência
 * 
 * @param int $currentEpisodeId ID do episódio atual
 */
function getNextEpisode($currentEpisodeId) {
    try {
        // Primeiro, obter informações do episódio atual
        $currentSql = "SELECT 
                          e.id, e.season_id, e.episode_number,
                          s.series_id, s.season_number
                       FROM episodes e
                       JOIN seasons s ON e.season_id = s.id
                       WHERE e.id = ?";
        
        $currentEpisode = dbQuerySingle($currentSql, [$currentEpisodeId]);
        
        if (!$currentEpisode) {
            http_response_code(404);
            echo json_encode(['error' => 'Episódio atual não encontrado']);
            exit;
        }
        
        // Tentar encontrar o próximo episódio na mesma temporada
        $nextInSeasonSql = "SELECT 
                               e.id, e.title, e.description, e.duration, e.episode_number,
                               e.thumbnail_url, e.video_url, e.view_count, e.created_at, e.updated_at,
                               s.id as season_id, s.title as season_title, s.season_number,
                               sr.id as series_id, sr.title as series_title, sr.poster_url as series_poster
                            FROM episodes e
                            JOIN seasons s ON e.season_id = s.id
                            JOIN series sr ON s.series_id = sr.id
                            WHERE e.season_id = ? AND e.episode_number > ?
                            ORDER BY e.episode_number ASC
                            LIMIT 1";
        
        $nextEpisode = dbQuerySingle($nextInSeasonSql, [
            $currentEpisode['season_id'], 
            $currentEpisode['episode_number']
        ]);
        
        // Se não encontrou o próximo episódio na mesma temporada, procurar na próxima temporada
        if (!$nextEpisode) {
            $nextSeasonSql = "SELECT 
                                 s.id as season_id
                              FROM seasons s
                              WHERE s.series_id = ? AND s.season_number > ?
                              ORDER BY s.season_number ASC
                              LIMIT 1";
            
            $nextSeason = dbQuerySingle($nextSeasonSql, [
                $currentEpisode['series_id'], 
                $currentEpisode['season_number']
            ]);
            
            if ($nextSeason) {
                // Encontrar o primeiro episódio da próxima temporada
                $firstEpisodeSql = "SELECT 
                                       e.id, e.title, e.description, e.duration, e.episode_number,
                                       e.thumbnail_url, e.video_url, e.view_count, e.created_at, e.updated_at,
                                       s.id as season_id, s.title as season_title, s.season_number,
                                       sr.id as series_id, sr.title as series_title, sr.poster_url as series_poster
                                    FROM episodes e
                                    JOIN seasons s ON e.season_id = s.id
                                    JOIN series sr ON s.series_id = sr.id
                                    WHERE e.season_id = ?
                                    ORDER BY e.episode_number ASC
                                    LIMIT 1";
                
                $nextEpisode = dbQuerySingle($firstEpisodeSql, [$nextSeason['season_id']]);
            }
        }
        
        if (!$nextEpisode) {
            // Não há próximo episódio
            http_response_code(404);
            echo json_encode(['error' => 'Não há próximo episódio disponível']);
            exit;
        }
        
        // Formatar a resposta
        $formattedEpisode = [
            'id' => (int)$nextEpisode['id'],
            'title' => $nextEpisode['title'],
            'description' => $nextEpisode['description'],
            'duration' => (int)$nextEpisode['duration'],
            'episode_number' => (int)$nextEpisode['episode_number'],
            'thumbnail_url' => $nextEpisode['thumbnail_url'],
            'video_url' => $nextEpisode['video_url'],
            'view_count' => (int)$nextEpisode['view_count'],
            'created_at' => $nextEpisode['created_at'],
            'updated_at' => $nextEpisode['updated_at'],
            'season' => [
                'id' => (int)$nextEpisode['season_id'],
                'title' => $nextEpisode['season_title'],
                'season_number' => (int)$nextEpisode['season_number']
            ],
            'series' => [
                'id' => (int)$nextEpisode['series_id'],
                'title' => $nextEpisode['series_title'],
                'poster_url' => $nextEpisode['series_poster']
            ]
        ];
        
        echo json_encode(['data' => $formattedEpisode]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter próximo episódio']);
        exit;
    }
}

/**
 * Obtém todos os episódios de uma temporada
 * 
 * @param int $seasonId ID da temporada
 */
function getSeasonEpisodes($seasonId) {
    try {
        // Verificar se a temporada existe
        $seasonSql = "SELECT 
                          s.id, s.title, s.season_number, s.description, s.poster_url,
                          sr.id as series_id, sr.title as series_title
                       FROM seasons s
                       JOIN series sr ON s.series_id = sr.id
                       WHERE s.id = ?";
        
        $season = dbQuerySingle($seasonSql, [$seasonId]);
        
        if (!$season) {
            http_response_code(404);
            echo json_encode(['error' => 'Temporada não encontrada']);
            exit;
        }
        
        // Obter episódios da temporada
        $episodesSql = "SELECT 
                           id, title, description, duration, episode_number,
                           thumbnail_url, video_url, view_count, created_at, updated_at
                        FROM episodes
                        WHERE season_id = ?
                        ORDER BY episode_number ASC";
        
        $episodes = dbQuery($episodesSql, [$seasonId]);
        
        // Formatar a resposta
        $response = [
            'data' => [
                'season' => [
                    'id' => (int)$season['id'],
                    'title' => $season['title'],
                    'season_number' => (int)$season['season_number'],
                    'description' => $season['description'],
                    'poster_url' => $season['poster_url'],
                    'series' => [
                        'id' => (int)$season['series_id'],
                        'title' => $season['series_title']
                    ]
                ],
                'episodes' => array_map(function($episode) {
                    return [
                        'id' => (int)$episode['id'],
                        'title' => $episode['title'],
                        'description' => $episode['description'],
                        'duration' => (int)$episode['duration'],
                        'episode_number' => (int)$episode['episode_number'],
                        'thumbnail_url' => $episode['thumbnail_url'],
                        'video_url' => $episode['video_url'],
                        'view_count' => (int)$episode['view_count'],
                        'created_at' => $episode['created_at'],
                        'updated_at' => $episode['updated_at']
                    ];
                }, $episodes)
            ]
        ];
        
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter episódios da temporada']);
        exit;
    }
}

/**
 * Obtém uma lista de episódios populares
 */
function getPopularEpisodes() {
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 10;
    
    try {
        $sql = "SELECT 
                    e.id, e.title, e.description, e.duration, e.episode_number,
                    e.thumbnail_url, e.video_url, e.view_count, e.created_at, e.updated_at,
                    s.id as season_id, s.title as season_title, s.season_number,
                    sr.id as series_id, sr.title as series_title, sr.poster_url as series_poster
                FROM episodes e
                JOIN seasons s ON e.season_id = s.id
                JOIN series sr ON s.series_id = sr.id
                ORDER BY e.view_count DESC
                LIMIT ?";
        
        $episodes = dbQuery($sql, [$limit]);
        
        // Formatar a resposta
        $formattedEpisodes = array_map(function($episode) {
            return [
                'id' => (int)$episode['id'],
                'title' => $episode['title'],
                'description' => $episode['description'],
                'duration' => (int)$episode['duration'],
                'episode_number' => (int)$episode['episode_number'],
                'thumbnail_url' => $episode['thumbnail_url'],
                'video_url' => $episode['video_url'],
                'view_count' => (int)$episode['view_count'],
                'created_at' => $episode['created_at'],
                'updated_at' => $episode['updated_at'],
                'season' => [
                    'id' => (int)$episode['season_id'],
                    'title' => $episode['season_title'],
                    'season_number' => (int)$episode['season_number']
                ],
                'series' => [
                    'id' => (int)$episode['series_id'],
                    'title' => $episode['series_title'],
                    'poster_url' => $episode['series_poster']
                ]
            ];
        }, $episodes);
        
        echo json_encode(['data' => $formattedEpisodes]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter episódios populares']);
        exit;
    }
}

/**
 * Obtém uma lista de episódios recentes
 */
function getRecentEpisodes() {
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 10;
    
    try {
        $sql = "SELECT 
                    e.id, e.title, e.description, e.duration, e.episode_number,
                    e.thumbnail_url, e.video_url, e.view_count, e.created_at, e.updated_at,
                    s.id as season_id, s.title as season_title, s.season_number,
                    sr.id as series_id, sr.title as series_title, sr.poster_url as series_poster
                FROM episodes e
                JOIN seasons s ON e.season_id = s.id
                JOIN series sr ON s.series_id = sr.id
                ORDER BY e.created_at DESC
                LIMIT ?";
        
        $episodes = dbQuery($sql, [$limit]);
        
        // Formatar a resposta
        $formattedEpisodes = array_map(function($episode) {
            return [
                'id' => (int)$episode['id'],
                'title' => $episode['title'],
                'description' => $episode['description'],
                'duration' => (int)$episode['duration'],
                'episode_number' => (int)$episode['episode_number'],
                'thumbnail_url' => $episode['thumbnail_url'],
                'video_url' => $episode['video_url'],
                'view_count' => (int)$episode['view_count'],
                'created_at' => $episode['created_at'],
                'updated_at' => $episode['updated_at'],
                'season' => [
                    'id' => (int)$episode['season_id'],
                    'title' => $episode['season_title'],
                    'season_number' => (int)$episode['season_number']
                ],
                'series' => [
                    'id' => (int)$episode['series_id'],
                    'title' => $episode['series_title'],
                    'poster_url' => $episode['series_poster']
                ]
            ];
        }, $episodes);
        
        echo json_encode(['data' => $formattedEpisodes]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter episódios recentes']);
        exit;
    }
}

/**
 * Atualiza o progresso de visualização de um episódio
 * 
 * @param int $episodeId ID do episódio
 */
function updateEpisodeProgress($episodeId) {
    try {
        // Verificar se o episódio existe
        $episodeSql = "SELECT id, duration FROM episodes WHERE id = ?";
        $episode = dbQuerySingle($episodeSql, [$episodeId]);
        
        if (!$episode) {
            http_response_code(404);
            echo json_encode(['error' => 'Episódio não encontrado']);
            exit;
        }
        
        // Obter dados do corpo da requisição
        $requestData = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($requestData['current_time']) || !isset($requestData['user_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros insuficientes. Necessário current_time e user_id']);
            exit;
        }
        
        $currentTime = floatval($requestData['current_time']);
        $userId = intval($requestData['user_id']);
        $completed = isset($requestData['completed']) ? (bool)$requestData['completed'] : false;
        
        // Se o tempo atual for maior que 90% da duração ou completed=true, marcar como assistido
        $watchedThreshold = $episode['duration'] * 0.9;
        $isWatched = $completed || $currentTime >= $watchedThreshold;
        
        // Verificar se já existe um registro de progresso para este usuário e episódio
        $checkSql = "SELECT id, watched FROM user_episode_progress WHERE user_id = ? AND episode_id = ?";
        $existingProgress = dbQuerySingle($checkSql, [$userId, $episodeId]);
        
        dbBeginTransaction();
        
        if ($existingProgress) {
            // Atualizar registro existente
            $updateSql = "UPDATE user_episode_progress 
                          SET current_time = ?, watched = ?, updated_at = NOW() 
                          WHERE id = ?";
            
            dbExecute($updateSql, [$currentTime, $isWatched ? 1 : 0, $existingProgress['id']]);
            
            // Se o episódio foi assistido pela primeira vez, incrementar contador
            if ($isWatched && $existingProgress['watched'] == 0) {
                $updateEpisodeSql = "UPDATE episodes SET completed_count = completed_count + 1 WHERE id = ?";
                dbExecute($updateEpisodeSql, [$episodeId]);
            }
        } else {
            // Criar novo registro
            $insertSql = "INSERT INTO user_episode_progress 
                          (user_id, episode_id, current_time, watched, created_at, updated_at) 
                          VALUES (?, ?, ?, ?, NOW(), NOW())";
            
            dbExecute($insertSql, [$userId, $episodeId, $currentTime, $isWatched ? 1 : 0]);
            
            // Se o episódio foi assistido, incrementar contador
            if ($isWatched) {
                $updateEpisodeSql = "UPDATE episodes SET completed_count = completed_count + 1 WHERE id = ?";
                dbExecute($updateEpisodeSql, [$episodeId]);
            }
        }
        
        dbCommit();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'episode_id' => (int)$episodeId,
                'user_id' => $userId,
                'current_time' => $currentTime,
                'watched' => $isWatched,
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } catch (Exception $e) {
        if (dbInTransaction()) {
            dbRollback();
        }
        
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar progresso do episódio']);
        exit;
    }
} 