<?php
header('Content-Type: application/json');

// Incluir archivo de conexión reutilizable
require_once 'conexion.php';


$response = ['success' => false, 'errors' => [], 'message' => ''];

// Obtener datos JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validaciones básicas
if (!$data) {
    $response['message'] = 'No se recibieron datos.';
    echo json_encode($response);
    exit;
}

$required = [
    'firstName', 'lastName', 'registerEmail', 'username', 'phoneNumber', 'departmentSelect', 'registerPassword'
];
foreach ($required as $field) {
    if (empty($data[$field])) {
        $response['errors'][$field] = 'Este campo es obligatorio.';
    }
}

if (!empty($data['registerEmail']) && !filter_var($data['registerEmail'], FILTER_VALIDATE_EMAIL)) {
    $response['errors']['registerEmail'] = 'Correo electrónico no válido.';
}

if (!empty($response['errors'])) {
    $response['message'] = 'Por favor corrige los campos marcados.';
    echo json_encode($response);
    exit;
}

// La conexión ya está disponible en $conn desde conexion.php
if (!$conn || $conn->connect_error) {
    $response['message'] = 'Error de conexión a la base de datos.';
    echo json_encode($response);
    exit;
}

// Comprobar si el usuario o email ya existen
$stmt = $conn->prepare('SELECT id FROM users WHERE user = ? OR email = ? LIMIT 1');
$stmt->bind_param('ss', $data['username'], $data['registerEmail']);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $response['errors']['username'] = 'El usuario o correo ya existe.';
    $response['errors']['registerEmail'] = 'El usuario o correo ya existe.';
    $response['message'] = 'El usuario o correo ya está registrado.';
    echo json_encode($response);
    exit;
}
$stmt->close();

// Insertar usuario
$stmt = $conn->prepare('INSERT INTO users (`user`, `password`, `name`, `lastname`, `email`, `phone`, `department`) VALUES (?, ?, ?, ?, ?, ?, ?)');
$hashedPassword = password_hash($data['registerPassword'], PASSWORD_DEFAULT);
$stmt->bind_param(
    'sssssss',
    $data['username'],
    $hashedPassword,
    $data['firstName'],
    $data['lastName'],
    $data['registerEmail'],
    $data['phoneNumber'],
    $data['departmentSelect']
);
if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Usuario registrado correctamente.';
} else {
    $response['message'] = 'No se pudo registrar el usuario.';
}
$stmt->close();
$conn->close();
echo json_encode($response);
