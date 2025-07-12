<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$response = ['success' => false, 'message' => '', 'errors' => []];

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $response['message'] = 'No se recibieron datos.';
    echo json_encode($response);
    exit;
}

$username = isset($data['loginUsername']) ? trim($data['loginUsername']) : '';
$password = isset($data['loginPassword']) ? $data['loginPassword'] : '';

if (empty($username)) {
    $response['errors']['loginUsername'] = 'El nombre de usuario es requerido.';
}
if (empty($password)) {
    $response['errors']['loginPassword'] = 'La contraseña es requerida.';
}

if (!empty($response['errors'])) {
    $response['message'] = 'Por favor completa los campos requeridos.';
    echo json_encode($response);
    exit;
}

if (!$conn || $conn->connect_error) {
    $response['message'] = 'Error de conexión a la base de datos.';
    echo json_encode($response);
    exit;
}

$stmt = $conn->prepare('SELECT id, password, name, lastname FROM users WHERE user = ? OR email = ? LIMIT 1');
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $hashedPassword, $name, $lastname);
    $stmt->fetch();
    if (password_verify($password, $hashedPassword)) {
        session_start();
        $_SESSION['user_id'] = $id;
        $response['success'] = true;
        $response['message'] = 'Inicio de sesión exitoso.';
        $response['user'] = [
            'id' => $id,
            'name' => $name,
            'lastname' => $lastname
        ];
    } else {
        $response['message'] = 'Usuario o contraseña incorrectos.';
        $response['errors']['loginPassword'] = 'Contraseña incorrecta.';
    }
} else {
    $response['message'] = 'Usuario o contraseña incorrectos.';
    $response['errors']['loginUsername'] = 'Usuario no encontrado.';
}
$stmt->close();
$conn->close();
echo json_encode($response);
