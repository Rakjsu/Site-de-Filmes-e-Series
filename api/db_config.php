<?php
/**
 * Configuração do banco de dados
 * 
 * Este arquivo contém funções para conexão e operações com o banco de dados.
 * É importado por todos os arquivos da API.
 * 
 * @version 1.1.0
 */

// Determinar ambiente atual
$environment = getenv('APP_ENV') ?: 'development';

// Configurações de conexão por ambiente
$db_configs = [
    'development' => [
        'host' => 'localhost',
        'dbname' => 'player_db',
        'username' => 'player_user',
        'password' => 'player_password',
        'charset' => 'utf8mb4',
        'port' => 3306,
        'log_queries' => true
    ],
    'testing' => [
        'host' => 'localhost',
        'dbname' => 'player_db_test',
        'username' => 'player_test',
        'password' => 'player_test',
        'charset' => 'utf8mb4',
        'port' => 3306,
        'log_queries' => true
    ],
    'production' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'dbname' => getenv('DB_NAME') ?: 'player_db',
        'username' => getenv('DB_USER') ?: 'player_user',
        'password' => getenv('DB_PASS') ?: 'player_password',
        'charset' => 'utf8mb4',
        'port' => getenv('DB_PORT') ?: 3306,
        'log_queries' => false
    ]
];

// Seleciona a configuração do ambiente atual
$db_config = $db_configs[$environment];

// Conexão PDO global
$pdo = null;

/**
 * Obter conexão com o banco de dados
 * 
 * @return PDO Objeto PDO para conexão com o banco
 * @throws PDOException Se houver erro na conexão
 */
function getDbConnection() {
    global $pdo, $db_config;
    
    if ($pdo === null) {
        $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']};port={$db_config['port']}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        try {
            $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
        } catch (PDOException $e) {
            // Registra o erro mas não expõe detalhes da conexão
            error_log("Erro de conexão com o banco: " . $e->getMessage());
            throw new Exception("Erro ao conectar ao banco de dados");
        }
    }
    
    return $pdo;
}

/**
 * Verifica se a conexão com o banco de dados está ativa
 * 
 * @return bool True se estiver conectado, False caso contrário
 */
function isDbConnected() {
    global $pdo;
    
    if ($pdo === null) {
        return false;
    }
    
    try {
        $pdo->query('SELECT 1');
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Registra uma consulta SQL no log se o logging estiver ativado
 * 
 * @param string $sql SQL executado
 * @param array $params Parâmetros usados
 * @param float $executionTime Tempo de execução em segundos
 * @param string $result Resultado da operação
 */
function logQuery($sql, $params, $executionTime, $result = 'success') {
    global $db_config;
    
    if (!isset($db_config['log_queries']) || !$db_config['log_queries']) {
        return;
    }
    
    $logMessage = sprintf(
        "[SQL] [%s] Tempo: %.4fs - Query: %s - Params: %s",
        $result,
        $executionTime,
        $sql,
        json_encode($params)
    );
    
    error_log($logMessage);
}

/**
 * Executa uma consulta e retorna todos os resultados
 * 
 * @param string $sql Query SQL com placeholders
 * @param array $params Parâmetros para a query
 * @return array Resultados da consulta
 * @throws Exception Se houver erro na consulta
 */
function dbQuery($sql, $params = []) {
    try {
        $startTime = microtime(true);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        $executionTime = microtime(true) - $startTime;
        
        logQuery($sql, $params, $executionTime);
        return $result;
    } catch (PDOException $e) {
        $executionTime = microtime(true) - $startTime;
        logQuery($sql, $params, $executionTime, 'error: ' . $e->getMessage());
        error_log("Erro de consulta SQL: " . $e->getMessage() . " - SQL: $sql");
        throw new Exception("Erro ao executar consulta no banco de dados");
    }
}

/**
 * Executa uma consulta e retorna uma única linha
 * 
 * @param string $sql Query SQL com placeholders
 * @param array $params Parâmetros para a query
 * @return array|false Resultado da consulta ou false se não encontrado
 * @throws Exception Se houver erro na consulta
 */
function dbQuerySingle($sql, $params = []) {
    try {
        $startTime = microtime(true);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        $executionTime = microtime(true) - $startTime;
        
        logQuery($sql, $params, $executionTime);
        return $result;
    } catch (PDOException $e) {
        $executionTime = microtime(true) - $startTime;
        logQuery($sql, $params, $executionTime, 'error: ' . $e->getMessage());
        error_log("Erro de consulta SQL: " . $e->getMessage() . " - SQL: $sql");
        throw new Exception("Erro ao executar consulta no banco de dados");
    }
}

/**
 * Executa uma instrução SQL para inserção/atualização/exclusão
 * 
 * @param string $sql Query SQL com placeholders
 * @param array $params Parâmetros para a query
 * @return int Número de linhas afetadas
 * @throws Exception Se houver erro na operação
 */
function dbExecute($sql, $params = []) {
    try {
        $startTime = microtime(true);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rowCount = $stmt->rowCount();
        $executionTime = microtime(true) - $startTime;
        
        logQuery($sql, $params, $executionTime);
        return $rowCount;
    } catch (PDOException $e) {
        $executionTime = microtime(true) - $startTime;
        logQuery($sql, $params, $executionTime, 'error: ' . $e->getMessage());
        error_log("Erro de execução SQL: " . $e->getMessage() . " - SQL: $sql");
        throw new Exception("Erro ao executar operação no banco de dados");
    }
}

/**
 * Insere dados em uma tabela e retorna o ID inserido
 * 
 * @param string $table Nome da tabela
 * @param array $data Dados a serem inseridos (coluna => valor)
 * @return int ID do registro inserido
 * @throws Exception Se houver erro na inserção
 */
function dbInsert($table, $data) {
    try {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $startTime = microtime(true);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        $lastId = $pdo->lastInsertId();
        $executionTime = microtime(true) - $startTime;
        
        logQuery($sql, array_values($data), $executionTime);
        return $lastId;
    } catch (PDOException $e) {
        $executionTime = microtime(true) - $startTime;
        logQuery($sql, array_values($data), $executionTime, 'error: ' . $e->getMessage());
        error_log("Erro de inserção SQL: " . $e->getMessage() . " - Tabela: $table");
        throw new Exception("Erro ao inserir dados no banco");
    }
}

/**
 * Atualiza dados em uma tabela
 * 
 * @param string $table Nome da tabela
 * @param array $data Dados a serem atualizados (coluna => valor)
 * @param string $where Condição WHERE (com placeholders)
 * @param array $whereParams Parâmetros para a condição WHERE
 * @return int Número de linhas afetadas
 * @throws Exception Se houver erro na atualização
 */
function dbUpdate($table, $data, $where, $whereParams = []) {
    try {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "$column = ?";
        }
        
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE $table SET $setClause WHERE $where";
        
        $params = array_merge(array_values($data), $whereParams);
        
        $startTime = microtime(true);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rowCount = $stmt->rowCount();
        $executionTime = microtime(true) - $startTime;
        
        logQuery($sql, $params, $executionTime);
        return $rowCount;
    } catch (PDOException $e) {
        $executionTime = microtime(true) - $startTime;
        logQuery($sql, $params, $executionTime, 'error: ' . $e->getMessage());
        error_log("Erro de atualização SQL: " . $e->getMessage() . " - Tabela: $table");
        throw new Exception("Erro ao atualizar dados no banco");
    }
}

/**
 * Prepara uma consulta IN() segura com vários parâmetros
 * 
 * @param array $values Array de valores para cláusula IN
 * @return array Tupla contendo [string de placeholders, array de valores]
 */
function dbPrepareIn($values) {
    if (empty($values)) {
        return ['NULL', []];
    }
    
    $placeholders = implode(', ', array_fill(0, count($values), '?'));
    return [$placeholders, $values];
}

/**
 * Inicia uma transação no banco de dados
 */
function dbBeginTransaction() {
    getDbConnection()->beginTransaction();
}

/**
 * Verifica se há uma transação ativa
 * 
 * @return bool True se houver uma transação ativa
 */
function dbInTransaction() {
    return getDbConnection()->inTransaction();
}

/**
 * Confirma uma transação no banco de dados
 */
function dbCommit() {
    getDbConnection()->commit();
}

/**
 * Reverte uma transação no banco de dados
 */
function dbRollback() {
    getDbConnection()->rollBack();
}

/**
 * Retorna o último ID inserido
 * 
 * @return string O último ID inserido
 */
function dbLastInsertId() {
    return getDbConnection()->lastInsertId();
}

/**
 * Fecha a conexão com o banco de dados
 */
function dbClose() {
    global $pdo;
    $pdo = null;
} 