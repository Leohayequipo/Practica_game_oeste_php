<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $playerName = trim($data['playerName'] ?? '');
    $score = intval($data['score'] ?? 0);
    $levelReached = intval($data['levelReached'] ?? 1);
    $totalTime = floatval($data['totalTime'] ?? 0);
    $perfectGame = boolval($data['perfectGame'] ?? false);
    $honorMaintained = boolval($data['honorMaintained'] ?? true);
    
    if (empty($playerName)) {
        echo json_encode(['success' => false, 'message' => 'El nombre del jugador es requerido']);
        exit;
    }
    
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
        
        // Buscar si el jugador ya existe
        $stmt = $pdo->prepare("SELECT id, score, level_reached, best_time, total_games, perfect_games FROM players WHERE player_name = ?");
        $stmt->execute([$playerName]);
        $existingPlayer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingPlayer) {
            // Solo actualizar si la nueva puntuación es mayor
            $shouldUpdate = $score > $existingPlayer['score'];
            
            if ($shouldUpdate) {
                // Solo actualizar la puntuación si supera el récord - NO sumar contadores
                $newLevelReached = max($existingPlayer['level_reached'], $levelReached);
                $newBestTime = min($existingPlayer['best_time'], $totalTime);
                
                $stmt = $pdo->prepare("UPDATE players SET score = ?, level_reached = ?, best_time = ? WHERE id = ?");
                $stmt->execute([$score, $newLevelReached, $newBestTime, $existingPlayer['id']]);
            } else {
                // NO actualizar NADA si no supera el récord - mantener todo igual
                // No hacer ningún UPDATE a la base de datos
            }
            
            $playerId = $existingPlayer['id'];
        } else {
            // Crear nuevo jugador
            $stmt = $pdo->prepare("INSERT INTO players (player_name, score, level_reached, best_time, total_games, perfect_games) VALUES (?, ?, ?, ?, 1, ?)");
            $stmt->execute([$playerName, $score, $levelReached, $totalTime, $perfectGame ? 1 : 0]);
            
            $playerId = $pdo->lastInsertId();
        }
        
        $message = $existingPlayer ? 
            ($shouldUpdate ? '¡Nueva puntuación más alta guardada!' : 'No se guardó la puntuación (no superó tu récord anterior)') : 
            '¡Primera puntuación guardada exitosamente!';
            
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'playerId' => $playerId,
            'playerName' => $playerName,
            'score' => $score,
            'newHighScore' => $existingPlayer ? $shouldUpdate : true,
            'previousScore' => $existingPlayer ? $existingPlayer['score'] : null
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
