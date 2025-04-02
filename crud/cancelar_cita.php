<?php
session_start();
require_once '../php/conexionDB.php';
require_once '../php/consultas.php';

if (!isset($_GET['id'])) {
    $_SESSION['MensajeTexto'] = "Error: No se proporcionó el ID de la cita.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ../gestionar_citas.php");
    exit;
}

$id_cita = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$id_paciente = $_SESSION['id_paciente'];

// Verificar que la cita pertenece al paciente y está pendiente
$query = "SELECT c.id_cita, c.id_doctor, c.fecha_cita, c.hora_cita, c.estado, p.nombre, p.apellido 
         FROM citas c 
         JOIN pacientes p ON c.id_paciente = p.id_paciente 
         WHERE c.id_cita = ? AND c.id_paciente = ? AND c.estado = 'I'";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "ii", $id_cita, $id_paciente);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['MensajeTexto'] = "Error: No se puede eliminar esta cita.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ../gestionar_citas.php");
    exit;
}

$cita = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Eliminar la cita de la base de datos
$query_delete = "DELETE FROM citas WHERE id_cita = ?";
$stmt_delete = mysqli_prepare($link, $query_delete);
mysqli_stmt_bind_param($stmt_delete, "i", $id_cita);

if (mysqli_stmt_execute($stmt_delete)) {
    // Generar notificación para el doctor con el tipo 'cancelar_cita'
    $nombre_paciente = $cita['nombre'] . ' ' . $cita['apellido'];
    $fecha_cita = $cita['fecha_cita'];
    $hora_cita = $cita['hora_cita'];
    $id_doctor = $cita['id_doctor'];
    $mensaje = "El paciente $nombre_paciente ha eliminado su cita del $fecha_cita a las $hora_cita.";

    GenerarNotificacion($link, $id_doctor, 'cancelacion', $mensaje);

    $_SESSION['MensajeTexto'] = "Cita eliminada con éxito.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-info text-white";
} else {
    $_SESSION['MensajeTexto'] = "Error al eliminar la cita.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
}

mysqli_stmt_close($stmt_delete);
mysqli_close($link);
header("Location: ../gestionar_citas.php");
exit;
?>