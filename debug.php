<?php
// Iniciar la sesión para acceder a $_SESSION
session_start();

// Incluir conexión a la base de datos y consultas (opcional, por si necesitas verificar algo)
include_once './php/conexionDB.php';
include_once './php/consultas.php';

// Configurar el encabezado para mostrar texto plano (mejor legibilidad)
header('Content-Type: text/plain; charset=utf-8');

// Imprimir toda la información relevante
echo "=== DEPURACIÓN DE DATOS ===\n\n";

// 1. Variables de sesión ($_SESSION)
echo "Variables de sesión ($_SESSION):\n";
var_dump($_SESSION);
echo "\n";

// 2. Datos enviados por POST
echo "Datos enviados por POST ($_POST):\n";
var_dump($_POST);
echo "\n";

// 3. Datos enviados por GET
echo "Datos enviados por GET ($_GET):\n";
var_dump($_GET);
echo "\n";

// 4. Información del servidor ($_SERVER)
echo "Información del servidor ($_SERVER) - Selección relevante:\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "HTTP_REFERER: " . ($_SERVER['HTTP_REFERER'] ?? 'No disponible') . "\n";
echo "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "\n";
echo "\n";

// 5. Verificar si id_paciente existe y consultar datos del usuario (si aplica)
if (isset($_SESSION['id_paciente'])) {
    echo "Datos del paciente desde la base de datos (id_paciente: " . $_SESSION['id_paciente'] . "):\n";
    $row = consultarPaciente($link, $_SESSION['id_paciente']);
    var_dump($row);
} elseif (isset($_SESSION['id_doctor'])) {
    echo "Datos del doctor desde la base de datos (id_doctor: " . $_SESSION['id_doctor'] . "):\n";
    $row = consultarDoctor($link, $_SESSION['id_doctor']);
    var_dump($row);
} else {
    echo "No hay id_paciente ni id_doctor en la sesión.\n";
}

echo "\n=== FIN DE DEPURACIÓN ===\n";

// Opcional: no redirigimos, solo mostramos los datos
?>