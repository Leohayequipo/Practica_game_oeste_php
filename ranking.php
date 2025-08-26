<?php
// ========================================
// RANKING DE JUGADORES - VERSIÓN SIMPLE
// ========================================
require_once 'config.php';

try {
    // Verificar que la conexión esté establecida
    if (!$pdo) {
        throw new Exception("No se pudo establecer conexión con la base de datos");
    }
    
    // Crear la tabla si no existe
    $sql = "CREATE TABLE IF NOT EXISTS players (
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_name VARCHAR(50) NOT NULL,
        score INT DEFAULT 0,
        level_reached INT DEFAULT 1,
        best_time FLOAT DEFAULT 999.0,
        total_games INT DEFAULT 0,
        perfect_games INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Obtener el ranking de jugadores
    $stmt = $pdo->query("
        SELECT 
            player_name, 
            score, 
            level_reached, 
            best_time, 
            total_games, 
            perfect_games
        FROM players 
        ORDER BY score DESC 
        LIMIT 50
    ");
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener estadísticas generales
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_players,
            AVG(score) as average_score,
            MAX(score) as highest_score,
            SUM(total_games) as total_games_played
        FROM players
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Error de conexión: " . $e->getMessage();
    $players = [];
    $stats = [];
} catch(Exception $e) {
    $error = "Error: " . $e->getMessage();
    $players = [];
    $stats = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - Fearsome Game</title>
    <link href="https://fonts.googleapis.com/css?family=VT323" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: "VT323", monospace !important;
            margin: 0;
            padding: 0;
        }
        
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #F59600;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #F59600;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 3em;
            color: #F59600;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(0,0,0,0.8);
            border: 2px solid #F59600;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #FFB84D;
        }
        
        .stat-card .value {
            font-size: 2em;
            font-weight: bold;
            color: #F59600;
        }
        
        .ranking-table {
            background: rgba(0,0,0,0.9);
            border: 3px solid #F59600;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .ranking-header {
            background: #F59600;
            color: #000;
            padding: 15px;
            font-size: 1.3em;
            font-weight: bold;
        }
        
        .ranking-row {
            display: grid;
            grid-template-columns: 80px 2fr 1fr 1fr 1fr 1fr 1fr;
            padding: 15px;
            border-bottom: 1px solid #333;
            align-items: center;
        }
        
        .ranking-row:nth-child(even) {
            background: rgba(245, 150, 0, 0.1);
        }
        
        .ranking-row:hover {
            background: rgba(245, 150, 0, 0.2);
        }
        
        .ranking-row.header {
            background: #F59600;
            color: #000;
            font-weight: bold;
        }
        
        .rank {
            text-align: center;
            font-weight: bold;
        }
        
        .rank.gold { color: #FFD700; }
        .rank.silver { color: #C0C0C0; }
        .rank.bronze { color: #CD7F32; }
        
        .player-name {
            font-weight: bold;
            color: #FFB84D;
        }
        
        .score {
            text-align: center;
            font-weight: bold;
            color: #F59600;
        }
        
        .level, .time, .games, .perfect {
            text-align: center;
        }
        
        .back-button, .refresh-button {
            background: #F59600;
            color: #000;
            padding: 12px 24px;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            margin: 10px;
            display: inline-block;
        }
        
        .back-button:hover, .refresh-button:hover {
            background: #FFB84D;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #FFB84D;
        }
        
        .error {
            text-align: center;
            padding: 20px;
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid #ff6b6b;
            border-radius: 5px;
        }
        
        @media (max-width: 768px) {
            .ranking-row {
                grid-template-columns: 60px 1.5fr 1fr 1fr;
                font-size: 0.9em;
            }
            
            .ranking-row .time,
            .ranking-row .perfect {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏆 Ranking de Jugadores</h1>
            <p style="font-size: 1.2em; margin-top: 10px;">Los mejores sheriffs del Oeste</p>
        </div>
        
        <div class="stats-grid">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php else: ?>
                <div class="stat-card">
                    <h3>Total de Jugadores</h3>
                    <div class="value"><?php echo $stats['total_players'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Puntuación Promedio</h3>
                    <div class="value"><?php echo round($stats['average_score'] ?? 0); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Puntuación Más Alta</h3>
                    <div class="value"><?php echo $stats['highest_score'] ?? 0; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total de Partidas</h3>
                    <div class="value"><?php echo $stats['total_games_played'] ?? 0; ?></div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="ranking-table">
            <div class="ranking-header">
                Top 50 Jugadores
            </div>
            <div id="rankingTable">
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php elseif (empty($players)): ?>
                    <div class="loading">No hay jugadores registrados aún</div>
                <?php else: ?>
                    <div class="ranking-row header">
                        <div class="rank">#</div>
                        <div class="player-name">Jugador</div>
                        <div class="score">Puntos</div>
                        <div class="level">Nivel</div>
                        <div class="time">Mejor Tiempo</div>
                        <div class="games">Partidas</div>
                        <div class="perfect">Perfectas</div>
                    </div>
                    <?php foreach ($players as $index => $player): ?>
                        <?php 
                        $rankClass = $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : ''));
                        $rankText = $index === 0 ? '🥇' : ($index === 1 ? '🥈' : ($index === 2 ? '🥉' : ($index + 1)));
                        ?>
                        <div class="ranking-row">
                            <div class="rank <?php echo $rankClass; ?>"><?php echo $rankText; ?></div>
                            <div class="player-name"><?php echo htmlspecialchars($player['player_name']); ?></div>
                            <div class="score"><?php echo $player['score']; ?></div>
                            <div class="level"><?php echo $player['level_reached']; ?></div>
                            <div class="time"><?php echo number_format($player['best_time'], 2); ?>s</div>
                            <div class="games"><?php echo $player['total_games']; ?></div>
                            <div class="perfect"><?php echo $player['perfect_games']; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="text-align: center;">
            <a href="index.html" class="back-button">← Volver al Juego</a>
            <button class="refresh-button" onclick="location.reload()">🔄 Actualizar</button>
        </div>
    </div>

    <script>
        function showScoringInfo() {
            alert(`🏆 SISTEMA DE PUNTUACIÓN:

NIVEL 1 (100 puntos base):
• < 0.5s: ×2 = 200 puntos
• < 1.0s: ×1.5 = 150 puntos
• ≥ 1.0s: ×1 = 100 puntos

NIVEL 2 (250 puntos base):
• < 0.3s: ×2 = 500 puntos
• < 0.5s: ×1.5 = 375 puntos
• ≥ 0.5s: ×1 = 250 puntos

NIVEL 3 (500 puntos base):
• < 0.2s: ×3 = 1,500 puntos
• < 0.3s: ×2 = 1,000 puntos
• ≥ 0.3s: ×1 = 500 puntos

BONIFICACIÓN POR HONOR: +50 puntos por nivel
(Se pierde si disparas antes de tiempo)`);
        }
        
        document.querySelector('.header h1').addEventListener('dblclick', showScoringInfo);
        
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
