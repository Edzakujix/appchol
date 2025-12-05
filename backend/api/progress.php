<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, GET, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once 'auth.php';

class Progress {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function registrarProgreso($usuario_id, $tema_id, $completado = true) {
        try {
            $this->db->beginTransaction();

            $query_tema = "SELECT puntos_maximos FROM temas WHERE id = :tema_id";
            $stmt_tema = $this->db->prepare($query_tema);
            $stmt_tema->bindParam(':tema_id', $tema_id);
            $stmt_tema->execute();
            $tema = $stmt_tema->fetch();

            if (!$tema) {
                throw new Exception('Tema no encontrado');
            }

            $puntos = $completado ? $tema['puntos_maximos'] : 0;

            $query_existe = "SELECT id, completado FROM progreso_usuario 
                            WHERE usuario_id = :usuario_id AND tema_id = :tema_id";
            $stmt_existe = $this->db->prepare($query_existe);
            $stmt_existe->bindParam(':usuario_id', $usuario_id);
            $stmt_existe->bindParam(':tema_id', $tema_id);
            $stmt_existe->execute();

            if ($stmt_existe->rowCount() > 0) {
                $progreso_existente = $stmt_existe->fetch();
                
                if (!$progreso_existente['completado'] && $completado) {
                    $query_update = "UPDATE progreso_usuario 
                                   SET completado = 1, 
                                       puntos_obtenidos = :puntos,
                                       fecha_completado = CURRENT_TIMESTAMP,
                                       intentos = intentos + 1
                                   WHERE id = :id";
                    $stmt_update = $this->db->prepare($query_update);
                    $stmt_update->bindParam(':puntos', $puntos);
                    $stmt_update->bindParam(':id', $progreso_existente['id']);
                    $stmt_update->execute();
                } else {
                    $query_update = "UPDATE progreso_usuario 
                                   SET intentos = intentos + 1
                                   WHERE id = :id";
                    $stmt_update = $this->db->prepare($query_update);
                    $stmt_update->bindParam(':id', $progreso_existente['id']);
                    $stmt_update->execute();
                }
            } else {
                $query_insert = "INSERT INTO progreso_usuario 
                               (usuario_id, tema_id, completado, puntos_obtenidos, intentos, fecha_completado) 
                               VALUES (:usuario_id, :tema_id, :completado, :puntos, 1, 
                                      " . ($completado ? "CURRENT_TIMESTAMP" : "NULL") . ")";
                $stmt_insert = $this->db->prepare($query_insert);
                $stmt_insert->bindParam(':usuario_id', $usuario_id);
                $stmt_insert->bindParam(':tema_id', $tema_id);
                $stmt_insert->bindParam(':completado', $completado, PDO::PARAM_BOOL);
                $stmt_insert->bindParam(':puntos', $puntos);
                $stmt_insert->execute();
            }

            $this->verificarInsignias($usuario_id);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Progreso registrado',
                'puntos_obtenidos' => floatval($puntos)
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al registrar progreso: " . $e->getMessage());
            return ['error' => 'Error al guardar progreso'];
        }
    }

    private function verificarInsignias($usuario_id) {
        try {
            $query_stats = "SELECT 
                COUNT(CASE WHEN completado = 1 THEN 1 END) as temas_completados,
                COALESCE(SUM(puntos_obtenidos), 0) as puntos_totales
                FROM progreso_usuario 
                WHERE usuario_id = :usuario_id";
            $stmt = $this->db->prepare($query_stats);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
            $stats = $stmt->fetch();

            $query_insignias = "SELECT i.* FROM insignias i
                WHERE i.id NOT IN (
                    SELECT insignia_id FROM insignias_usuario 
                    WHERE usuario_id = :usuario_id
                )";
            $stmt_insignias = $this->db->prepare($query_insignias);
            $stmt_insignias->bindParam(':usuario_id', $usuario_id);
            $stmt_insignias->execute();
            $insignias_disponibles = $stmt_insignias->fetchAll();

            foreach ($insignias_disponibles as $insignia) {
                $otorgar = false;

                switch ($insignia['requisito_tipo']) {
                    case 'temas_completados':
                        if ($stats['temas_completados'] >= $insignia['requisito_valor']) {
                            $otorgar = true;
                        }
                        break;
                    case 'puntos_totales':
                        if ($stats['puntos_totales'] >= $insignia['requisito_valor']) {
                            $otorgar = true;
                        }
                        break;
                }

                if ($otorgar) {
                    $query_insert = "INSERT INTO insignias_usuario (usuario_id, insignia_id) 
                                   VALUES (:usuario_id, :insignia_id)";
                    $stmt_insert = $this->db->prepare($query_insert);
                    $stmt_insert->bindParam(':usuario_id', $usuario_id);
                    $stmt_insert->bindParam(':insignia_id', $insignia['id']);
                    $stmt_insert->execute();
                }
            }
        } catch (PDOException $e) {
            error_log("Error al verificar insignias: " . $e->getMessage());
        }
    }

    public function obtenerProgresoModulo($usuario_id, $modulo_id) {
        try {
            $query = "SELECT t.id, t.nombre, t.orden, t.puntos_maximos,
                            COALESCE(p.completado, 0) as completado,
                            COALESCE(p.puntos_obtenidos, 0) as puntos_obtenidos
                     FROM temas t
                     LEFT JOIN progreso_usuario p ON t.id = p.tema_id AND p.usuario_id = :usuario_id
                     WHERE t.modulo_id = :modulo_id
                     ORDER BY t.orden";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':modulo_id', $modulo_id);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener progreso: " . $e->getMessage());
            return ['error' => 'Error al obtener progreso'];
        }
    }
}

$auth = new Auth();
$usuario_id = $auth->verificarSesion();

$progress = new Progress();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['tema_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'tema_id requerido']);
        exit();
    }

    $tema_id = $data['tema_id'];
    $completado = $data['completado'] ?? true;

    echo json_encode($progress->registrarProgreso($usuario_id, $tema_id, $completado));

} elseif ($method === 'GET') {
    if (!isset($_GET['modulo_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'modulo_id requerido']);
        exit();
    }

    echo json_encode($progress->obtenerProgresoModulo($usuario_id, $_GET['modulo_id']));

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>