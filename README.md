# Fearsome Game - Sistema de Base de Datos

## Instalación

### 1. Configurar la Base de Datos
1. Abre phpMyAdmin o tu cliente MySQL preferido
2. Ejecuta el archivo `database.sql` para crear la base de datos y tabla
3. O ejecuta manualmente:
   ```sql
   CREATE DATABASE fearsome_game;
   USE fearsome_game;
   CREATE TABLE players (
       id INT AUTO_INCREMENT PRIMARY KEY,
       player_name VARCHAR(50) NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

### 2. Configurar la Conexión
1. Edita `config.php` con tus credenciales de MySQL:
   ```php
   $host = 'localhost';        // Tu servidor MySQL
   $dbname = 'fearsome_game';  // Nombre de la base de datos
   $username = 'root';         // Tu usuario MySQL
   $password = '';             // Tu contraseña MySQL
   ```

### 3. Verificar Permisos
- Asegúrate de que tu servidor web tenga permisos para escribir en la base de datos
- El usuario MySQL debe tener permisos INSERT, SELECT, CREATE en la base de datos

## Funcionalidad

- **save_score.php**: Guarda las puntuaciones y crea/actualiza jugadores en la base de datos
- **get_players.php**: Obtiene la lista de todos los jugadores (para debugging)
- **config.php**: Configuración de conexión a la base de datos

## Flujo del Juego

1. El jugador ve un formulario para ingresar su nombre
2. Al hacer clic en "Guardar y Jugar", el nombre se guarda localmente
3. Al finalizar el juego, la puntuación se envía a `save_score.php` junto con el nombre
4. Solo después de guardar exitosamente, aparece el botón "Comenzar" del juego original
5. El juego continúa normalmente

## Estructura de la Base de Datos

```sql
players
├── id (INT, AUTO_INCREMENT, PRIMARY KEY)
├── player_name (VARCHAR(50), NOT NULL)
└── created_at (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
```

## Solución de Problemas

- **Error de conexión**: Verifica que MySQL esté corriendo y las credenciales sean correctas
- **Permisos denegados**: Asegúrate de que el usuario MySQL tenga permisos suficientes
- **Archivo no encontrado**: Verifica que todos los archivos PHP estén en el mismo directorio
# Practica_game_oeste_php
