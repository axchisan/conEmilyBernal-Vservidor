<?php
include_once('conexionDB.php');

if (isset($_POST['id_doctor']) && isset($_POST['fecha_cita'])) {
    $id_doctor = filter_var($_POST['id_doctor'], FILTER_SANITIZE_NUMBER_INT);
    $fecha_cita = mysqli_real_escape_string($link, trim($_POST['fecha_cita']));

    // Consulta para obtener los horarios ocupados (todas las citas existentes)
    $query = "SELECT hora_cita FROM citas WHERE id_doctor = ? AND fecha_cita = ?";
    $stmt = mysqli_prepare($link, $query);
    if (!$stmt) {
        echo json_encode(['error' => 'Error al preparar la consulta: ' . mysqli_error($link)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "is", $id_doctor, $fecha_cita);
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['error' => 'Error al ejecutar la consulta: ' . mysqli_error($link)]);
        exit;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        echo json_encode(['error' => 'Error al obtener resultados: ' . mysqli_error($link)]);
        exit;
    }

    $unavailableTimes = [];
    while ($row = mysqli_fetch_array($result)) {
        $unavailableTimes[] = $row['hora_cita'];
    }

    mysqli_stmt_close($stmt);
    echo json_encode($unavailableTimes);
} else {
    echo json_encode(['error' => 'Faltan parámetros: id_doctor o fecha_cita']);
}

mysqli_close($link);
?>