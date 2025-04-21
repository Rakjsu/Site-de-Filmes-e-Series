<?php
/**
 * Sistema de Banco de Dados
 * 
 * Este arquivo contém funções para gerenciar operações de banco de dados.
 * Implementa um wrapper PDO para operações mais seguras com banco de dados.
 * 
 * @version 1.0.0
 */

// Configurações do banco de dados
// Em produção, deve-se utilizar variáveis de ambiente ou arquivo externo de configuração
define('DB_HOST', 'localhost');
define('DB_NAME', 'player_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Obtém uma conexão com o banco de dados usando PDO
 * 
 * @return PDO Objeto PDO de conexão com o banco de dados
 * @throws PDOException Se a conexão falhar
 */
function getConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log do erro em arquivo para o admin, mas não expor detalhes ao usuário
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new PDOException('Falha na conexão com o banco de dados. Por favor, tente novamente mais tarde.');
        }
    }
    
    return $conn;
}

/**
 * Executa uma consulta SQL e retorna todos os resultados
 * 
 * @param string $sql Consulta SQL preparada
 * @param array $params Parâmetros para a consulta preparada
 * @return array Resultados da consulta
 */
function dbQuery($sql, $params = []) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Database Query Error: ' . $e->getMessage() . ' - SQL: ' . $sql);
        throw new PDOException('Erro ao processar a consulta. Por favor, tente novamente.');
    }
}

/**
 * Executa uma consulta SQL e retorna apenas a primeira linha
 * 
 * @param string $sql Consulta SQL preparada
 * @param array $params Parâmetros para a consulta preparada
 * @return array|null Primeira linha dos resultados ou null se não houver resultados
 */
function dbQuerySingle($sql, $params = []) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    } catch (PDOException $e) {
        error_log('Database Query Error: ' . $e->getMessage() . ' - SQL: ' . $sql);
        throw new PDOException('Erro ao processar a consulta. Por favor, tente novamente.');
    }
}

/**
 * Executa uma consulta SQL e retorna um valor único
 * 
 * @param string $sql Consulta SQL preparada
 * @param array $params Parâmetros para a consulta preparada
 * @return mixed Valor único resultante da consulta ou null
 */
function dbQueryValue($sql, $params = []) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Database Query Error: ' . $e->getMessage() . ' - SQL: ' . $sql);
        throw new PDOException('Erro ao processar a consulta. Por favor, tente novamente.');
    }
}

/**
 * Executa uma instrução SQL sem retornar resultados
 * 
 * @param string $sql Instrução SQL preparada
 * @param array $params Parâmetros para a instrução preparada
 * @return int Número de linhas afetadas
 */
function dbExecute($sql, $params = []) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Database Execute Error: ' . $e->getMessage() . ' - SQL: ' . $sql);
        throw new PDOException('Erro ao executar a operação. Por favor, tente novamente.');
    }
}

/**
 * Insere dados em uma tabela
 * 
 * @param string $table Nome da tabela
 * @param array $data Dados a serem inseridos (coluna => valor)
 * @return int ID do registro inserido
 */
function dbInsert($table, $data) {
    try {
        if (empty($data)) {
            throw new InvalidArgumentException('Dados vazios para inserção.');
        }
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $conn = getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log('Database Insert Error: ' . $e->getMessage() . ' - Table: ' . $table);
        throw new PDOException('Erro ao inserir dados. Por favor, tente novamente.');
    }
}

/**
 * Atualiza dados em uma tabela
 * 
 * @param string $table Nome da tabela
 * @param array $data Dados a serem atualizados (coluna => valor)
 * @param string $where Condição WHERE
 * @param array $whereParams Parâmetros para a condição WHERE
 * @return int Número de linhas afetadas
 */
function dbUpdate($table, $data, $where, $whereParams = []) {
    try {
        if (empty($data)) {
            throw new InvalidArgumentException('Dados vazios para atualização.');
        }
        
        $setClauses = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setClauses[] = "$column = ?";
            $params[] = $value;
        }
        
        $params = array_merge($params, $whereParams);
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $setClauses),
            $where
        );
        
        $conn = getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Database Update Error: ' . $e->getMessage() . ' - Table: ' . $table);
        throw new PDOException('Erro ao atualizar dados. Por favor, tente novamente.');
    }
}

/**
 * Exclui dados de uma tabela
 * 
 * @param string $table Nome da tabela
 * @param string $where Condição WHERE
 * @param array $params Parâmetros para a condição WHERE
 * @return int Número de linhas afetadas
 */
function dbDelete($table, $where, $params = []) {
    try {
        $sql = sprintf('DELETE FROM %s WHERE %s', $table, $where);
        
        $conn = getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Database Delete Error: ' . $e->getMessage() . ' - Table: ' . $table);
        throw new PDOException('Erro ao excluir dados. Por favor, tente novamente.');
    }
}

/**
 * Inicia uma transação no banco de dados
 * 
 * @return void
 */
function dbBeginTransaction() {
    getConnection()->beginTransaction();
}

/**
 * Confirma uma transação no banco de dados
 * 
 * @return void
 */
function dbCommit() {
    getConnection()->commit();
}

/**
 * Reverte uma transação no banco de dados
 * 
 * @return void
 */
function dbRollback() {
    getConnection()->rollBack();
}

/**
 * Escapa uma string para uso seguro em consultas SQL
 * 
 * @param string $value Valor a ser escapado
 * @return string Valor escapado
 */
function dbEscape($value) {
    $conn = getConnection();
    return substr($conn->quote($value), 1, -1);
}

/**
 * Verifica se uma tabela existe
 * 
 * @param string $table Nome da tabela
 * @return bool Verdadeiro se a tabela existir
 */
function dbTableExists($table) {
    try {
        $conn = getConnection();
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        return $result->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Database Check Table Error: ' . $e->getMessage() . ' - Table: ' . $table);
        return false;
    }
}

/**
 * Obtém as colunas de uma tabela
 * 
 * @param string $table Nome da tabela
 * @return array Lista de colunas
 */
function dbGetTableColumns($table) {
    try {
        $columns = [];
        $conn = getConnection();
        $result = $conn->query("SHOW COLUMNS FROM $table");
        
        while ($row = $result->fetch()) {
            $columns[] = $row['Field'];
        }
        
        return $columns;
    } catch (PDOException $e) {
        error_log('Database Get Columns Error: ' . $e->getMessage() . ' - Table: ' . $table);
        return [];
    }
}

/**
 * Prepara um conjunto de dados removendo colunas que não existem na tabela
 * 
 * @param string $table Nome da tabela
 * @param array $data Dados a serem filtrados
 * @return array Dados filtrados
 */
function dbFilterTableData($table, $data) {
    $columns = dbGetTableColumns($table);
    
    if (empty($columns)) {
        return [];
    }
    
    $filteredData = [];
    
    foreach ($data as $key => $value) {
        if (in_array($key, $columns)) {
            $filteredData[$key] = $value;
        }
    }
    
    return $filteredData;
}

/**
 * Cria uma tabela se ela não existir
 * 
 * @param string $table Nome da tabela
 * @param string $schema Definição da tabela
 * @return bool Verdadeiro se a tabela for criada ou já existir
 */
function dbCreateTableIfNotExists($table, $schema) {
    try {
        if (!dbTableExists($table)) {
            $conn = getConnection();
            $conn->exec("CREATE TABLE IF NOT EXISTS $table ($schema)");
        }
        return true;
    } catch (PDOException $e) {
        error_log('Database Create Table Error: ' . $e->getMessage() . ' - Table: ' . $table);
        return false;
    }
}

/**
 * Executa um script SQL a partir de um arquivo
 * 
 * @param string $file Caminho do arquivo SQL
 * @return bool Verdadeiro se executado com sucesso
 */
function dbExecuteFile($file) {
    try {
        if (!file_exists($file)) {
            throw new InvalidArgumentException('Arquivo SQL não encontrado: ' . $file);
        }
        
        $sql = file_get_contents($file);
        
        if (empty($sql)) {
            throw new InvalidArgumentException('Arquivo SQL vazio: ' . $file);
        }
        
        // Dividir o script em instruções individuais
        $conn = getConnection();
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
        
        // Executar as instruções
        $conn->exec($sql);
        
        return true;
    } catch (PDOException $e) {
        error_log('Database Execute File Error: ' . $e->getMessage() . ' - File: ' . $file);
        return false;
    }
}

/**
 * Realiza uma busca paginada
 * 
 * @param string $table Nome da tabela
 * @param array $conditions Condições de busca (WHERE) [coluna => valor]
 * @param int $page Página atual
 * @param int $itemsPerPage Itens por página
 * @param string $orderBy Ordenação
 * @return array Resultados paginados e informações de paginação
 */
function dbPaginate($table, $conditions = [], $page = 1, $itemsPerPage = 20, $orderBy = 'id DESC') {
    try {
        $page = max(1, (int)$page);
        $itemsPerPage = max(1, (int)$itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        
        // Construir cláusula WHERE
        $where = '';
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    if (isset($value['operator']) && isset($value['value'])) {
                        $whereClauses[] = "$column {$value['operator']} ?";
                        $params[] = $value['value'];
                    }
                } else {
                    $whereClauses[] = "$column = ?";
                    $params[] = $value;
                }
            }
            $where = 'WHERE ' . implode(' AND ', $whereClauses);
        }
        
        // Contar total de registros
        $countSql = "SELECT COUNT(*) FROM $table $where";
        $totalItems = (int)dbQueryValue($countSql, $params);
        
        // Consulta principal
        $sql = "SELECT * FROM $table $where ORDER BY $orderBy LIMIT $itemsPerPage OFFSET $offset";
        $items = dbQuery($sql, $params);
        
        // Informações de paginação
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $itemsPerPage,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages
            ]
        ];
    } catch (PDOException $e) {
        error_log('Database Pagination Error: ' . $e->getMessage() . ' - Table: ' . $table);
        throw new PDOException('Erro ao buscar dados paginados. Por favor, tente novamente.');
    }
}

/**
 * Realiza uma busca simples por termo em múltiplas colunas
 * 
 * @param string $table Nome da tabela
 * @param array $searchColumns Colunas a serem pesquisadas
 * @param string $searchTerm Termo de busca
 * @param int $page Página atual
 * @param int $itemsPerPage Itens por página
 * @param string $orderBy Ordenação
 * @return array Resultados da busca e informações de paginação
 */
function dbSearch($table, $searchColumns, $searchTerm, $page = 1, $itemsPerPage = 20, $orderBy = 'id DESC') {
    try {
        $page = max(1, (int)$page);
        $itemsPerPage = max(1, (int)$itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        
        // Construir cláusula WHERE para busca
        $whereClauses = [];
        $params = [];
        
        foreach ($searchColumns as $column) {
            $whereClauses[] = "$column LIKE ?";
            $params[] = "%$searchTerm%";
        }
        
        $where = 'WHERE ' . implode(' OR ', $whereClauses);
        
        // Contar total de registros
        $countSql = "SELECT COUNT(*) FROM $table $where";
        $totalItems = (int)dbQueryValue($countSql, $params);
        
        // Consulta principal
        $sql = "SELECT * FROM $table $where ORDER BY $orderBy LIMIT $itemsPerPage OFFSET $offset";
        $items = dbQuery($sql, $params);
        
        // Informações de paginação
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $itemsPerPage,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages,
                'search_term' => $searchTerm
            ]
        ];
    } catch (PDOException $e) {
        error_log('Database Search Error: ' . $e->getMessage() . ' - Table: ' . $table);
        throw new PDOException('Erro ao buscar dados. Por favor, tente novamente.');
    }
}

// Registrar função para fechar a conexão ao finalizar o script
register_shutdown_function('dbClose');
?> 