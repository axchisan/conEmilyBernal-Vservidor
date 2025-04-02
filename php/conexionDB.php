<?php
include_once 'configuracion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$link = new mysqli(host, user, password, database);

if ($link->connect_errno) {
    $_SESSION['MensajeTexto'] = "El sistema está en mantenimiento, intente más tarde.";
    $_SESSION['MensajeTipo'] = "bg-warning text-dark";
   
    exit;
}

$link->set_charset("utf8");

?>