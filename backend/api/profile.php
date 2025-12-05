<?php
/**
 * API de Perfil de Usuario (CORREGIDO)
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: PUT, OPTIONS'); 
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Manejo de petición OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';
require_once 'auth.php'; 

class Profile {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function actualizarPerfil($usuario_id, $datos) {
        try {
            $campos_permitidos = ['alias', 'avatar', 'rango_edad'];
            $campos_actualizar = [];
            $valores = [];

            foreach ($campos_permitidos as $campo) {
                if (isset($datos[$campo])) {
                    $campos_actualizar[] = "$campo = :$campo";
                    $valores[":$campo"] = strip_tags($datos[$campo]); 
                }
            }

            // Si se envía una foto nueva (Base64), la guardamos
            if (isset($datos['foto_custom']) && !empty($datos['foto_custom'])) {
                $campos_actualizar[] = "foto_perfil_url = :foto";
                $valores[":foto"] = $datos['foto_custom'];
                // Si pone foto, quitamos el avatar de emoji para que se vea la foto
                $campos_actualizar[] = "avatar = NULL"; 
            }

            if (empty($campos_actualizar)) {
                return ['error' => 'No hay campos válidos para actualizar'];
            }

            $query = "UPDATE usuarios SET " . implode(', ', $campos_actualizar) . " WHERE id = :usuario_id";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($valores as $param => $valor) {
                $stmt->bindValue($param, $valor);
            }
            $stmt->bindValue(':usuario_id', $usuario_id);

            if($stmt->execute()) {
                return ['success' => true];
            } else {
                return ['error' => 'No se pudo ejecutar la actualización'];
            }

        } catch (PDOException $e) {
            return ['error' => 'Error interno: ' . $e->getMessage()];
        }
    }
}

// --- EJECUCIÓN PRINCIPAL ---

// 1. Verificar Sesión (Aquí estaba el fallo antes)
$auth = new Auth();
$usuario_id = $auth->verificarSesion(); // ¡Obtenemos el ID de la sesión!

if (!$usuario_id) {
    http_response_code(401);
    echo json_encode(['error' => 'No has iniciado sesión']);
    exit;
}

// 2. Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// 3. Procesar datos
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data)) {
    $perfil = new Profile();
    // Usamos el $usuario_id seguro de la sesión
    echo json_encode($perfil->actualizarPerfil($usuario_id, $data));
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Datos vacíos']);
}
?>