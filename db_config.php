<?php
/**
 * Configuração do Banco de Dados
 * 
 * Este arquivo contém as configurações de conexão com o banco de dados
 * e funções auxiliares para operações de banco de dados.
 * 
 * @version 1.0.0
 */

// Definir configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'player');
define('DB_USER', 'root');
define('DB_PASS', '05062981');
define('DB_CHARSET', 'utf8mb4');

// Conexão PDO
$pdo = null;

/**
 * Obtém uma conexão PDO com o banco de dados
 * 
 * @return PDO Objeto de conexão PDO
 */
function getConnection() {
    global $pdo;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            // Log para depuração
            error_log("Tentando conectar ao banco de dados: " . DB_HOST);
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            error_log("Conexão com o banco de dados estabelecida com sucesso");
            
        } catch (PDOException $e) {
            error_log('Erro de conexão: ' . $e->getMessage());
            die('Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.');
        }
    }
    
    return $pdo;
}

/**
 * Executa uma consulta SQL e retorna todos os resultados
 * 
 * @param string $sql Consulta SQL
 * @param array $params Parâmetros para a consulta
 * @return array Resultados da consulta
 */
function dbQuery($sql, $params = []) {
    try {
        $stmt = getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro na consulta: ' . $e->getMessage() . ' - SQL: ' . $sql);
        throw $e;
    }
}

/**
 * Executa uma consulta SQL e retorna uma única linha
 * 
 * @param string $sql Consulta SQL
 * @param array $params Parâmetros para a consulta
 * @return array|false Resultado da consulta ou false se não encontrar
 */
function dbQuerySingle($sql, $params = []) {
    try {
        $stmt = getConnection()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    } catch (PDOException $e) {
        error_log('Erro na consulta: ' . $e->getMessage() . ' - SQL: ' . $sql);
        throw $e;
    }
}

/**
 * Executa uma instrução SQL sem retorno de dados
 * 
 * @param string $sql Instrução SQL
 * @param array $params Parâmetros para a instrução
 * @return int Número de linhas afetadas
 */
function dbExecute($sql, $params = []) {
    try {
        $stmt = getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Erro na execução: ' . $e->getMessage() . ' - SQL: ' . $sql);
        throw $e;
    }
}

/**
 * Insere um registro e retorna o ID gerado
 * 
 * @param string $sql Instrução SQL de inserção
 * @param array $params Parâmetros para a instrução
 * @return int ID do último registro inserido
 */
function dbInsert($sql, $params = []) {
    try {
        $stmt = getConnection()->prepare($sql);
        $stmt->execute($params);
        return getConnection()->lastInsertId();
    } catch (PDOException $e) {
        error_log('Erro na inserção: ' . $e->getMessage() . ' - SQL: ' . $sql);
        throw $e;
    }
}

/**
 * Inicia uma transação
 */
function dbBeginTransaction() {
    getConnection()->beginTransaction();
}

/**
 * Confirma uma transação
 */
function dbCommit() {
    getConnection()->commit();
}

/**
 * Cancela uma transação
 */
function dbRollback() {
    if (getConnection()->inTransaction()) {
        getConnection()->rollBack();
    }
}

/**
 * Escapa um valor para uso seguro em consultas SQL
 * 
 * @param string $value Valor a ser escapado
 * @return string Valor escapado
 */
function dbEscape($value) {
    return getConnection()->quote($value);
}

/**
 * Verifica se a tabela existe no banco de dados
 * 
 * @param string $tableName Nome da tabela
 * @return bool Verdadeiro se a tabela existe
 */
function dbTableExists($tableName) {
    try {
        $sql = "SHOW TABLES LIKE ?";
        $stmt = getConnection()->prepare($sql);
        $stmt->execute([$tableName]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Erro ao verificar tabela: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtém informações sobre as colunas de uma tabela
 * 
 * @param string $tableName Nome da tabela
 * @return array Lista de colunas e seus atributos
 */
function dbGetTableColumns($tableName) {
    try {
        $sql = "SHOW COLUMNS FROM " . $tableName;
        $stmt = getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao obter colunas: ' . $e->getMessage());
        return [];
    }
}
?> 