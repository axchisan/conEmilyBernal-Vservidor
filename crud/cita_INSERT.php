<?php
session_start();
require_once '../php/conexionDB.php';
require_once '../php/consultas.php';

if (!isset($_GET['opciones'])) {
    $_SESSION['MensajeTexto'] = "Advertencia: Acción no permitida.";
    $_SESSION['MensajeTipo'] = "is-warning";
    header("Location: ../index.php");
    exit;
}

$opcion = $_GET['opciones'];

try {
    switch ($opcion) {
        case 'INS':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar'])) {
                // Determinar si la solicitud viene de un paciente o un doctor
                if (isset($_SESSION['id_paciente'])) {
                    // Flujo para pacientes
                    $id_paciente = $_SESSION['id_paciente'];
                    $id_doctor = filter_var($_POST['dentistas'], FILTER_SANITIZE_NUMBER_INT);
                    $redirect_success = "../principal.php#top";
                    $redirect_error = "../principal.php#appointment";
                } elseif (isset($_SESSION['id_doctor'])) {
                    // Flujo para doctores
                    $id_paciente = filter_var($_POST['paciente'], FILTER_SANITIZE_NUMBER_INT);
                    $id_doctor = $_SESSION['id_doctor'];
                    $redirect_success = "../Admin/inicioAdmin.php";
                    $redirect_error = "../Admin/agregar_cita.php";
                } else {
                    $_SESSION['MensajeTexto'] = "Error: Sesión no válida.";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
                    header("Location: ../index.php");
                    exit;
                }

                $fecha_cita = mysqli_real_escape_string($link, trim($_POST['fecha_cita']));
                $hora_rango = mysqli_real_escape_string($link, trim($_POST['hora'])); // Guardamos el rango completo, como "09:00 AM - 10:00 AM"

                $id_consultas = filter_var($_POST['consultas'], FILTER_SANITIZE_NUMBER_INT);

                // Validar si el horario ya está ocupado (cualquier cita existente)
                // Usamos $hora_rango directamente, ya que es el formato que está en la base de datos
                $query_check = "SELECT COUNT(*) as total FROM citas WHERE id_doctor = ? AND fecha_cita = ? AND hora_cita = ?";
                $stmt_check = mysqli_prepare($link, $query_check);
                mysqli_stmt_bind_param($stmt_check, "iss", $id_doctor, $fecha_cita, $hora_rango);
                mysqli_stmt_execute($stmt_check);
                $result_check = mysqli_stmt_get_result($stmt_check);
                $row_check = mysqli_fetch_assoc($result_check);
                $total_citas = $row_check['total'];
                mysqli_stmt_close($stmt_check);

                if ($total_citas > 0) {
                    // El horario ya está ocupado
                    $_SESSION['MensajeTexto'] = "Error: El horario seleccionado ya está ocupado. Por favor, elija otro horario.";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
                    if (isset($_SESSION['id_paciente'])) {
                        $_SESSION['FormData'] = [
                            'name' => $_POST['name'],
                            'email' => $_POST['email'],
                            'fecha_cita' => $_POST['fecha_cita'],
                            'hora' => $_POST['hora'],
                            'consultas' => $_POST['consultas'],
                            'dentistas' => $_POST['dentistas'],
                            'phone' => $_POST['phone'],
                            'apellido' => $_POST['apellido']
                        ];
                    }
                    header("Location: $redirect_error");
                    exit;
                }

                // Validar CAPTCHA solo para pacientes
                if (isset($_SESSION['id_paciente'])) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $captcha = $_POST['g-recaptcha-response'];
                    $secretKey = "6LezIuwqAAAAAEwjDrlb4Vc-CmG_VwqU-sARMVMI";

                    $respuesta = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha&remoteip=$ip");
                    $atributos = json_decode($respuesta, true);

                    if (!$atributos["success"]) {
                        $_SESSION['CaptchaError'] = "Por favor, verifica que eres un humano.";
                        $_SESSION['FormData'] = [
                            'name' => $_POST['name'],
                            'email' => $_POST['email'],
                            'fecha_cita' => $_POST['fecha_cita'],
                            'hora' => $_POST['hora'],
                            'consultas' => $_POST['consultas'],
                            'dentistas' => $_POST['dentistas'],
                            'phone' => $_POST['phone'],
                            'apellido' => $_POST['apellido']
                        ];
                        header("Location: $redirect_error");
                        exit;
                    }
                }

                // Insertar la cita si el horario está disponible, usando el rango completo
                $query = "INSERT INTO citas (id_paciente, id_doctor, fecha_cita, hora_cita, id_consultas, estado) 
                          VALUES (?, ?, ?, ?, ?, 'I')";
                $stmt = mysqli_prepare($link, $query);
                mysqli_stmt_bind_param($stmt, "iissi", $id_paciente, $id_doctor, $fecha_cita, $hora_rango, $id_consultas);

                if (mysqli_stmt_execute($stmt)) {
                    // Solo generar notificación si la cita la realiza un paciente
                    if (isset($_SESSION['id_paciente'])) {
                        // Obtener el nombre del paciente para el mensaje de la notificación
                        $query_paciente = "SELECT nombre, apellido FROM pacientes WHERE id_paciente = ?";
                        $stmt_paciente = mysqli_prepare($link, $query_paciente);
                        mysqli_stmt_bind_param($stmt_paciente, "i", $id_paciente);
                        mysqli_stmt_execute($stmt_paciente);
                        $result_paciente = mysqli_stmt_get_result($stmt_paciente);
                        $paciente = mysqli_fetch_assoc($result_paciente);
                        mysqli_stmt_close($stmt_paciente);

                        $nombre_paciente = $paciente['nombre'] . ' ' . $paciente['apellido'];
                        $mensaje = "El paciente $nombre_paciente ha registrado una nueva cita para el $fecha_cita de $hora_rango.";

                        // Generar notificación para el doctor
                        GenerarNotificacion($link, $id_doctor, 'nueva_cita', $mensaje);
                    }

                    $_SESSION['MensajeTexto'] = "Cita realizada con éxito!";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-info text-white";
                    if (isset($_SESSION['id_paciente'])) {
                        unset($_SESSION['FormData']);
                    }
                    header("Location: $redirect_success");
                } else {
                    $_SESSION['MensajeTexto'] = "Error al insertar la cita.";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
                    error_log("Error al insertar la cita: " . mysqli_error($link));
                    header("Location: $redirect_error");
                }

                mysqli_stmt_close($stmt);
                mysqli_close($link);
                exit;
            }
            break;

        default:
            $_SESSION['MensajeTexto'] = "Advertencia: No se pudo identificar la acción a realizar.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-warning text-white";
            header("Location: ../principal.php");
            exit;
    }
} catch (Exception $e) {
    error_log("Excepción no controlada: " . $e->getMessage());
    $_SESSION['MensajeTexto'] = "Ha ocurrido un error inesperado.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ../principal.php");
    exit;
} catch (Error $e) {
    error_log("Error no controlado: " . $e->getMessage());
    $_SESSION['MensajeTexto'] = "Ha ocurrido un error inesperado.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ../principal.php");
    exit;
}