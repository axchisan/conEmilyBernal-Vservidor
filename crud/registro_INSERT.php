<?php
require_once '../php/conexionDB.php';
require_once '../php/consultas.php';

// Iniciar la sesión al principio
session_start();

if (!isset($_GET['opciones'])) {
    die("Advertencia: Acción no permitida.");
}

$opcion = $_GET['opciones'];

function insertarUsuario($link, $query, $params, $tipo = 'paciente') {
    $stmt = mysqli_prepare($link, $query);
    if (!$stmt) {
        die("Error preparando la consulta: " . mysqli_error($link));
    }
    
    mysqli_stmt_bind_param($stmt, str_repeat("s", count($params)), ...$params);
    $success = mysqli_stmt_execute($stmt);

    if ($tipo === 'paciente' && $success) {
        // Establecer id_paciente en la sesión (ya iniciada)
        $id_paciente = mysqli_insert_id($link);
        $_SESSION['id_paciente'] = $id_paciente;
    }
    
    mysqli_stmt_close($stmt);
    return $success;
}

try {
    switch ($opcion) {
        case 'INS':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ingresar'])) {
                $nombre = trim(filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS));
                $apellido = trim(filter_var($_POST['apellido'], FILTER_SANITIZE_SPECIAL_CHARS));
                $telefono = trim(filter_var($_POST['cell'], FILTER_SANITIZE_SPECIAL_CHARS));
                $sexo = trim(filter_var($_POST['sexo'], FILTER_SANITIZE_SPECIAL_CHARS));
                $fecha = trim(filter_var($_POST['nacimiento'], FILTER_SANITIZE_SPECIAL_CHARS));
                $correo = trim(filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL));
                $clave = password_hash($_POST['password'], PASSWORD_DEFAULT);

                // Validar si el correo ya existe
                $query_check = "SELECT COUNT(*) as total FROM pacientes WHERE correo_electronico = ?";
                $stmt_check = mysqli_prepare($link, $query_check);
                mysqli_stmt_bind_param($stmt_check, "s", $correo);
                mysqli_stmt_execute($stmt_check);
                $result_check = mysqli_stmt_get_result($stmt_check);
                $row_check = mysqli_fetch_assoc($result_check);
                mysqli_stmt_close($stmt_check);

                if ($row_check['total'] > 0) {
                    $_SESSION['MensajeTexto'] = "Error: El correo ya está registrado.";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
                    session_write_close();
                    header("Location: ../registro.php");
                    exit;
                }

                // Insertar sin referencia a session_token
                $query = "INSERT INTO pacientes (nombre, apellido, telefono, sexo, fecha_nacimiento, correo_electronico, clave) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";

                $success = insertarUsuario($link, $query, [$nombre, $apellido, $telefono, $sexo, $fecha, $correo, $clave], 'paciente');
                
                if ($success) {
                    $_SESSION['MensajeTexto'] = "¡Cuenta creada exitosamente! Bienvenido(a) $nombre";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-success text-white";
                    session_write_close();
                    header("Location: ../principal.php");
                    exit;
                } else {
                    die("Error insertando el contenido: " . mysqli_error($link));
                }
            }
            break;

        case 'INSDOCT':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
                $nombre = trim(filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS));
                $apellido = trim(filter_var($_POST['apellido'], FILTER_SANITIZE_SPECIAL_CHARS));
                $sexo = trim(filter_var($_POST['sexo'], FILTER_SANITIZE_SPECIAL_CHARS));
                $fecha = trim(filter_var($_POST['nacimiento'], FILTER_SANITIZE_SPECIAL_CHARS));
                $correo = trim(filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL));
                $telefono = trim(filter_var($_POST['cell'], FILTER_SANITIZE_SPECIAL_CHARS));
                $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
                $especialidad = trim(filter_var($_POST['especialidad'], FILTER_SANITIZE_SPECIAL_CHARS));

                // Validar si el correo ya existe
                $query_check = "SELECT COUNT(*) as total FROM doctor WHERE correo_eletronico = ?";
                $stmt_check = mysqli_prepare($link, $query_check);
                mysqli_stmt_bind_param($stmt_check, "s", $correo);
                mysqli_stmt_execute($stmt_check);
                $result_check = mysqli_stmt_get_result($stmt_check);
                $row_check = mysqli_fetch_assoc($result_check);
                mysqli_stmt_close($stmt_check);

                if ($row_check['total'] > 0) {
                    $_SESSION['MensajeTexto'] = "Error: El correo ya está registrado.";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
                    session_write_close();
                    header("Location: ../Admin/doctores.php");
                    exit;
                }

                // Insertar sin referencia a session_token
                $query = "INSERT INTO doctor (nombreD, apellido, sexo, fecha_nacimiento, telefono, correo_eletronico, clave, id_especialidad) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $success = insertarUsuario($link, $query, [$nombre, $apellido, $sexo, $fecha, $telefono, $correo, $clave, $especialidad], 'doctor');
                
                if ($success) {
                    $_SESSION['MensajeTexto'] = "¡Doctor registrado exitosamente!";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-success text-white";
                    session_write_close();
                    header("Location: ../Admin/doctores.php");
                    exit;
                } else {
                    $_SESSION['MensajeTexto'] = "Error al registrar el doctor: " . mysqli_error($link);
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
                    session_write_close();
                    header("Location: ../Admin/doctores.php");
                    exit;
                }
            }
            break;

        default:
            die("Advertencia: No se pudo identificar la acción a realizar.");
    }
} catch (Exception $e) {
    error_log("Excepción no controlada: " . $e->getMessage());
    $_SESSION['MensajeTexto'] = "Error: " . $e->getMessage();
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    session_write_close();
    header("Location: ../registro.php");
    exit;
} catch (Error $e) {
    error_log("Error no controlado: " . $e->getMessage());
    $_SESSION['MensajeTexto'] = "Error grave: " . $e->getMessage();
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    session_write_close();
    header("Location: ../registro.php");
    exit;
}

mysqli_close($link);
?>