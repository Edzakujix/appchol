<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_session'])) {
    if (isset($_SESSION['usuario_id'])) {
        echo json_encode(['autenticado' => true, 'usuario_id' => $_SESSION['usuario_id']]);
    } else {
        http_response_code(401);
        echo json_encode(['autenticado' => false]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['google_id']) && isset($data['email'])) {
        $db = (new Database())->getConnection();
        
        $stmt = $db->prepare("SELECT id, alias FROM usuarios WHERE google_id = ?");
        $stmt->execute([$data['google_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['id'];
            
            $nuevoAlias = $user['alias'];
            if ($user['alias'] === 'Aventurero') {
                $nuevoAlias = explode(' ', $data['nombre'])[0];
            }

            $update = $db->prepare("UPDATE usuarios SET 
                                    ultima_conexion = NOW(), 
                                    foto_perfil_url = ?, 
                                    nombre_completo = ?, 
                                    alias = ? 
                                    WHERE id = ?");
            $update->execute([$data['foto'], $data['nombre'], $nuevoAlias, $userId]);

        } else {
            $insert = $db->prepare("INSERT INTO usuarios (google_id, email, nombre_completo, foto_perfil_url, alias, avatar) VALUES (?, ?, ?, ?, ?, ?)");
            
            $aliasDefault = explode(' ', $data['nombre'])[0]; 
            $insert->execute([
                $data['google_id'], 
                $data['email'], 
                $data['nombre'], 
                $data['foto'], 
                $aliasDefault, 
                '?' 
            ]);
            $userId = $db->lastInsertId();
        }

        $_SESSION['usuario_id'] = $userId;
        $_SESSION['email'] = $data['email'];
        
        echo json_encode(['success' => true, 'redirect' => 'panprin.html']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    }
}
?>