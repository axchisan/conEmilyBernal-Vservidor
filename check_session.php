<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['id_paciente'])) {
    echo json_encode(['isLoggedIn' => true]);
} else {
    echo json_encode(['isLoggedIn' => false]);
}
?>