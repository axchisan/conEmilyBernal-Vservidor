<?php
include_once('conexionDB.php');

if (isset($_POST['id_paciente'])) {
    $id_paciente = filter_var($_POST['id_paciente'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT id_paciente, nombre, apellido FROM pacientes WHERE id_paciente = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_paciente);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $patient = mysqli_fetch_assoc($result);
    echo json_encode($patient);
} else {
    echo json_encode(null);
}
mysqli_close($link);
?>