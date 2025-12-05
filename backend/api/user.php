<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

class User {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function obtenerDatosUsuario($usuario_id) {
        try {
            $query = "SELECT id, google_id, email, nombre_completo, alias, avatar, 
                            rango_edad, foto_perfil_url, fecha_registro, ultima_conexion
                     FROM usuarios 
                     WHERE id = :usuario_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['error' => 'Usuario no encontrado'];
            }

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return ['error' => 'Error al obtener datos'];
        }
    }

    public function obtenerEstadisticas($usuario_id) {
        try {
            $query_completados = "SELECT COUNT(*) as total FROM progreso_usuario 
                                 WHERE usuario_id = :usuario_id AND completado = 1";
            $stmt = $this->db->prepare($query_completados);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
            $completados = $stmt->fetch()['total'];

            $query_total = "SELECT COUNT(*) as total FROM temas";
            $stmt_total = $this->db->prepare($query_total);
            $stmt_total->execute();
            $total_temas = $stmt_total->fetch()['total'];

            $query_puntos = "SELECT COALESCE(SUM(puntos_obtenidos), 0) as puntos 
                            FROM progreso_usuario WHERE usuario_id = :usuario_id";
            $stmt_puntos = $this->db->prepare($query_puntos);
            $stmt_puntos->bindParam(':usuario_id', $usuario_id);
            $stmt_puntos->execute();
            $puntos_totales = $stmt_puntos->fetch()['puntos'];

            $porcentaje_progreso = $total_temas > 0 ? round(($completados / $total_temas) * 100, 2) : 0;

            $nivel = floor($puntos_totales / 10) + 1;

            $query_insignias = "SELECT i.nombre, i.descripcion, i.icono, iu.fecha_obtencion
                               FROM insignias_usuario iu
                               JOIN insignias i ON iu.insignia_id = i.id
                               WHERE iu.usuario_id = :usuario_id
                               ORDER BY iu.fecha_obtencion DESC";
            $stmt_insignias = $this->db->prepare($query_insignias);
            $stmt_insignias->bindParam(':usuario_id', $usuario_id);
            $stmt_insignias->execute();
            $insignias = $stmt_insignias->fetchAll();

            return [
                'temas_completados' => $completados,
                'total_temas' => $total_temas,
                'porcentaje_progreso' => $porcentaje_progreso,
                'puntos_totales' => floatval($puntos_totales),
                'nivel' => $nivel,
                'insignias' => $insignias
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return ['error' => 'Error al calcular estadísticas'];
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No has iniciado sesión']);
        exit;
    }
    
    $usuario_id = $_SESSION['usuario_id'];
    $user = new User();
    
    $action = $_GET['action'] ?? 'datos';

    switch ($action) {
        case 'datos':
            echo json_encode($user->obtenerDatosUsuario($usuario_id));
            break;
        case 'estadisticas':
            echo json_encode($user->obtenerEstadisticas($usuario_id));
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>