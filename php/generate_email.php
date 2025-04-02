<?php
include_once('../php/conexionDB.php');

header('Content-Type: application/json');

if (isset($_POST['nombre']) && isset($_POST['apellido'])) {
    $nombre = trim(filter_var($_POST['nombre'], FILTER_SANITIZE_SPECIAL_CHARS));
    $apellido = trim(filter_var($_POST['apellido'], FILTER_SANITIZE_SPECIAL_CHARS));

    // Función para generar un correo único
    $base_email = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nombre) . '.' . preg_replace('/[^a-zA-Z0-9]/', '', $apellido));
    $domain = '@consultorioemilybernal.com';
    $email = $base_email . $domain;
    $counter = 1;

    while (true) {
        $query = "SELECT COUNT(*) as total FROM pacientes WHERE correo_electronico = ?";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($row['total'] == 0) {
            break; // Correo único encontrado
        }
        $email = $base_email . '.' . $counter . $domain;
        $counter++;
    }

    echo json_encode(['email' => $email]);
} else {
    echo json_encode(['email' => '']);
}

mysqli_close($link);
?>