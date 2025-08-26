-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS fearsome_game;
USE fearsome_game;

-- Crear la tabla de jugadores con puntuaciones
CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_name VARCHAR(50) NOT NULL,
    score INT DEFAULT 0,
    level_reached INT DEFAULT 1,
    best_time FLOAT DEFAULT 999.0,
    total_games INT DEFAULT 0,
    perfect_games INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar algunos jugadores de ejemplo (opcional)
INSERT INTO players (player_name, score, level_reached, best_time, total_games, perfect_games) VALUES 
('Sheriff John', 1850, 3, 2.8, 5, 2),
('Marshall Will', 2200, 3, 2.1, 8, 3),
('Deputy Bob', 1200, 2, 4.2, 3, 0);

-- Ver la tabla de jugadores
SELECT * FROM players ORDER BY score DESC;

-- Ver el ranking
SELECT 
    player_name, 
    score, 
    level_reached, 
    best_time, 
    total_games, 
    perfect_games,
    RANK() OVER (ORDER BY score DESC) as ranking
FROM players 
ORDER BY score DESC;
