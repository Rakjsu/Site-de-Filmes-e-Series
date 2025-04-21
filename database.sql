-- Script SQL para configuração do banco de dados do Player de Vídeo
-- Host: localhost:3306
-- Usuário: Rakjsu
-- Senha: 05062981
-- Base de dados: player

-- Criar a base de dados se não existir
CREATE DATABASE IF NOT EXISTS player CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar a base de dados
USE player;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    preferences JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de categorias
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),
    slug VARCHAR(100) NOT NULL UNIQUE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de séries
CREATE TABLE IF NOT EXISTS series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    poster VARCHAR(255),
    banner VARCHAR(255),
    category_id INT,
    release_year INT,
    slug VARCHAR(100) NOT NULL UNIQUE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabela de temporadas
CREATE TABLE IF NOT EXISTS seasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    series_id INT NOT NULL,
    number INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    overview TEXT,
    poster VARCHAR(255),
    release_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (series_id) REFERENCES series(id) ON DELETE CASCADE,
    UNIQUE KEY unique_season (series_id, number)
);

-- Tabela de episódios
CREATE TABLE IF NOT EXISTS episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    series_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    number INT NOT NULL,
    duration INT COMMENT 'Duração em segundos',
    thumbnail VARCHAR(255),
    video_url VARCHAR(255) NOT NULL,
    poster VARCHAR(255),
    release_date DATE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
    FOREIGN KEY (series_id) REFERENCES series(id) ON DELETE CASCADE,
    UNIQUE KEY unique_episode (season_id, number)
);

-- Tabela de histórico de visualização
CREATE TABLE IF NOT EXISTS watch_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    episode_id INT NOT NULL,
    progress FLOAT DEFAULT 0 COMMENT 'Progresso de 0 a 1',
    watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_history (user_id, episode_id)
);

-- Tabela de favoritos
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    series_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (series_id) REFERENCES series(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, series_id)
);

-- Tabela de configurações do sistema
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(50) NOT NULL UNIQUE,
    value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir configurações padrão
INSERT INTO settings (`key`, value, description) VALUES
('site_name', 'Video Player', 'Nome do site'),
('default_autoplay', 'true', 'Autoplay ativado por padrão'),
('countdown_time', '15', 'Tempo de contagem regressiva para próximo episódio'),
('default_theme', 'dark', 'Tema padrão da interface');

-- Inserir categoria de exemplo
INSERT INTO categories (name, description, slug) VALUES
('Ação', 'Séries de ação e aventura', 'acao');

-- Inserir série de exemplo
INSERT INTO series (title, description, category_id, release_year, slug) VALUES
('Serie de Exemplo', 'Uma descrição da série de exemplo', 1, 2023, 'serie-exemplo');

-- Inserir temporada de exemplo
INSERT INTO seasons (series_id, number, title, overview) VALUES
(1, 1, 'Temporada 1', 'Primeira temporada da série');

-- Inserir episódios de exemplo
INSERT INTO episodes (season_id, series_id, title, description, number, duration, video_url) VALUES
(1, 1, 'Episódio 1', 'Descrição do episódio 1', 1, 1200, '/videos/ep1.mp4'),
(1, 1, 'Episódio 2', 'Descrição do episódio 2', 2, 1320, '/videos/ep2.mp4'),
(1, 1, 'Episódio 3', 'Descrição do episódio 3', 3, 1260, '/videos/ep3.mp4');

-- Inserir usuário admin (senha: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@exemplo.com', '$2y$10$XcOAFQ0HfyQwMIbwnXbq/ebLiMt1sP8zTw66lk6aSuPvEjaOBIZmm', 'admin'); 