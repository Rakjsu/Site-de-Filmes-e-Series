<?php
/**
 * Configurações de Conexão com o Banco de Dados
 * 
 * Este arquivo contém as constantes de configuração para conexão ao banco de dados.
 * 
 * @version 1.0.0
 */

// Ambiente de desenvolvimento
define('DB_HOST', 'localhost');
define('DB_NAME', 'player_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Tempo limite para tentativas de conexão em segundos
define('DB_TIMEOUT', 5);

// Configurações de depuração do banco de dados
define('DB_DEBUG', true); // Definir como false em produção
?> 