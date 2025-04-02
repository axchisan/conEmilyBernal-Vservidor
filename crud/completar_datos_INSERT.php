<?php
session_start();
require_once '../php/conexionDB.php';

if (!isset($_SESSION['id_paciente']) || !isset($_SESSION['google_new_user'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['completar'])) {
    $nombre = trim(filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS));
    $apellido = trim(filter_var($_POST['apellido'], FILTER_SANITIZE_SPECIAL_CHARS));
    $telefono = trim(filter_var($_POST['cell'], FILTER_SANITIZE_SPECIAL_CHARS));
    $sexo = trim(filter_var($_POST['sexo'], FILTER_SANITIZE_SPECIAL_CHARS));
    $fecha = trim(filter_var($_POST['nacimiento'], FILTER_SANITIZE_SPECIAL_CHARS));

    $query = "UPDATE pacientes SET nombre = ?, apellido = ?, telefono = ?, sexo = ?, fecha_nacimiento = ? WHERE id_paciente = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'sssssi', $nombre, $apellido, $telefono, $sexo, $fecha, $_SESSION['id_paciente']);
    $success = mysqli_stmt_execute($stmt);

    if ($success) {
        unset($_SESSION['google_new_user']); // Limpiar el indicador
        $_SESSION['MensajeTexto'] = "Datos completados con éxito.";
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-info text-white";
        header("Location: ../principal.php");
    } else {
        $_SESSION['MensajeTexto'] = "Error al guardar los datos.";
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        header("Location: completar_datos.php");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($link);
    exit;
}
?>