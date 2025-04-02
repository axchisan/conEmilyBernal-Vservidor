<?php
ob_start();
session_start();
require_once './conexionDB.php';

header('Content-Type: application/json');

if (!$link) {
    echo json_encode(['error' => 'No se pudo conectar a la base de datos']);
    ob_end_flush();
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$name = filter_var($data['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

if (empty($email)) {
    echo json_encode(['error' => 'Email no proporcionado']);
    ob_end_flush();
    exit;
}

$query = "SELECT * FROM pacientes WHERE correo_electronico = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$response = [];

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $_SESSION['id_paciente'] = $user['id_paciente'];
    $response = ['redirect' => './principal.php'];
} else {
    session_regenerate_id(true);
    $_SESSION['google_email'] = $email;
    $_SESSION['google_name'] = $name;
    $response = ['redirect' => './registro.php']; 
}

mysqli_stmt_close($stmt);
mysqli_close($link);

session_write_close();
echo json_encode($response);
ob_end_flush();
exit;