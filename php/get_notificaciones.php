<?php
session_start();
date_default_timezone_set('America/Bogota');
require_once 'conexionDB.php';
require_once 'consultas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_doctor = isset($_POST['id_doctor']) ? filter_var($_POST['id_doctor'], FILTER_SANITIZE_NUMBER_INT) : null;
    $mostrar_todas = isset($_POST['mostrar_todas']) && $_POST['mostrar_todas'] === 'true';

    if (!$id_doctor) {
        echo json_encode(['error' => 'Faltan datos requeridos (id_doctor).']);
        exit;
    }

    // Obtener notificaciones
    $resultado = ObtenerNotificaciones($link, $id_doctor, $mostrar_todas);
    $notificaciones = [];
    while ($notificacion = mysqli_fetch_assoc($resultado)) {
        $notificaciones[] = [
            'id_notificacion' => $notificacion['id_notificacion'],
            'tipo' => htmlspecialchars($notificacion['tipo']),
            'mensaje' => htmlspecialchars($notificacion['mensaje']),
            'fecha' => date('d/m/Y H:i', strtotime($notificacion['fecha'])),
            'leida' => $notificacion['leida']
        ];
    }

    // Contar notificaciones no leídas
    $no_leidas = ContarNotificacionesNoLeidas($link, $id_doctor);

    echo json_encode([
        'notificaciones' => $notificaciones,
        'no_leidas' => $no_leidas
    ]);
    exit;
}

echo json_encode(['error' => 'Método no permitido.']);
exit;