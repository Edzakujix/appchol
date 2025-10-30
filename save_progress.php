<?php
// -----------------------------------------------------------
// 1. CONFIGURACIÓN Y CONEXIÓN A MYSQL
// -----------------------------------------------------------
header('Content-Type: application/json');
$response = [];

// *** ACTUALIZA ESTAS CREDENCIALES CON LAS TUYAS DE MYSQL ***
$servername = "localhost"; // O la IP de tu servidor MySQL
$username = "root";        // Tu usuario de MySQL
$password = "tu_contraseña"; // ¡TU CONTRASEÑA! (Si usas XAMPP/WAMP puede ser vacía "")
$dbname = "tu_base_de_datos"; // Reemplaza con el nombre real de tu BD (Ej: appchol)

// Intenta conectar a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica la conexión
if ($conn->connect_error) {
    // Retorna un error si no puede conectar
    $response = [
        "success" => false,
        "message" => "Error de conexión a la base de datos: " . $conn->connect_error
    ];
    echo json_encode($response);
    exit();
}

// -----------------------------------------------------------
// 2. RECEPCIÓN DE DATOS (DEL JAVASCRIPT DE TU APP)
// -----------------------------------------------------------

// Leer el contenido JSON enviado por el método POST (fetch en JavaScript)
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

if (!isset($data['google_id'], $data['total_score_module'], $data['activities_completed'])) {
    $response = ["success" => false, "message" => "Faltan datos esenciales en la petición."];
    echo json_encode($response);
    exit();
}

// Sanitizar y obtener variables
$google_id = $conn->real_escape_string($data['google_id']);
$total_score_module = (int)$data['total_score_module'];
$activities_completed = $data['activities_completed']; // Array de {activity_id, score_obtained}

// -----------------------------------------------------------
// 3. OBTENER user_id (Clave foránea para las tablas)
// -----------------------------------------------------------

// Paso crucial: Obtener el ID interno de la tabla Users
$sql_user = "SELECT user_id, total_score FROM Users WHERE google_id = '$google_id'";
$result_user = $conn->query($sql_user);

if ($result_user->num_rows == 0) {
    // Esto no debería pasar si el login POST /api/auth/login funcionó
    $response = ["success" => false, "message" => "Usuario no encontrado. Asegúrate de iniciar sesión."];
    echo json_encode($response);
    exit();
}
$user = $result_user->fetch_assoc();
$user_id = $user['user_id'];
$current_total_score = (int)$user['total_score'];


// -----------------------------------------------------------
// 4. INICIAR TRANSACCIÓN (Seguridad de Datos)
// -----------------------------------------------------------

// Asegura que todas las consultas se ejecuten o que ninguna lo haga (Atomicidad)
$conn->begin_transaction();
$all_queries_successful = true;

try {
    // A) REGISTRAR CADA ACTIVIDAD EN LA TABLA PROGRESS
    $completion_date = date('Y-m-d H:i:s'); // Marca de tiempo actual
    
    foreach ($activities_completed as $activity) {
        $activity_id = (int)$activity['activity_id'];
        $score = (int)$activity['score_obtained'];
        $is_completed = $score > 0 ? 1 : 0; // Asumimos que si tiene > 0 puntos, está completada
        
        $sql_insert_progress = "
            INSERT INTO Progress (user_id, activity_id, score_obtained, is_completed, completion_date)
            VALUES ($user_id, $activity_id, $score, $is_completed, '$completion_date')
        ";
        
        if (!$conn->query($sql_insert_progress)) {
            $all_queries_successful = false;
            break; // Detener si hay un error
        }
    }

    // B) ACTUALIZAR EL PUNTAJE TOTAL DEL USUARIO (Tabla Users)
    if ($all_queries_successful) {
        $new_total_score = $current_total_score + $total_score_module;
        
        $sql_update_user = "
            UPDATE Users 
            SET total_score = $new_total_score 
            WHERE user_id = $user_id
        ";
        
        if (!$conn->query($sql_update_user)) {
            $all_queries_successful = false;
        }
    }
    
    // -----------------------------------------------------------
    // 5. FINALIZAR TRANSACCIÓN
    // -----------------------------------------------------------
    
    if ($all_queries_successful) {
        $conn->commit(); // Guardar todos los cambios
        $response = [
            "success" => true,
            "message" => "Progreso y puntuación guardados exitosamente.",
            "new_total_score" => $new_total_score
        ];
    } else {
        $conn->rollback(); // Deshacer todos los cambios si hubo algún error
        $response = [
            "success" => false,
            "message" => "Error al guardar el progreso. Transacción revertida."
        ];
    }

} catch (Exception $e) {
    $conn->rollback();
    $response = [
        "success" => false,
        "message" => "Error inesperado: " . $e->getMessage()
    ];
}

$conn->close();
echo json_encode($response);
?>