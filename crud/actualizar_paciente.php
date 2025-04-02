<?php
session_start();
require_once '../php/conexionDB.php';
require_once '../php/consultas.php';

try {
    // Verifica si se recibió la acción
    if (empty($_GET['accion'])) {
        $_SESSION['MensajeTexto'] = "Advertencia: Acción no permitida.";
        $_SESSION['MensajeTipo'] = "is-warning";
        header("Location: ../principal.php");
        exit();
    }

    $opcion = $_GET['accion'];

    switch ($opcion) {
        case 'UDT':
            // Sanitizar y validar datos de entrada
            $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
            $nombre = mysqli_real_escape_string($link, trim($_POST['name']));
            $apellido = mysqli_real_escape_string($link, trim($_POST['apellido']));
            $telefono = mysqli_real_escape_string($link, trim($_POST['cell']));
            $sexo = mysqli_real_escape_string($link, trim($_POST['sexo']));
            $fecha = mysqli_real_escape_string($link, trim($_POST['nacimiento']));
            $correo = mysqli_real_escape_string($link, trim($_POST['correo']));
            $clave = trim($_POST['clave']);

            // Preparar consulta base para actualización
            $query = "UPDATE pacientes SET 
                      nombre = ?, 
                      apellido = ?, 
                      telefono = ?,  
                      sexo = ?,  
                      fecha_nacimiento = ?,  
                      correo_electronico = ?";
            $params = [$nombre, $apellido, $telefono, $sexo, $fecha, $correo];
            $paramTypes = "ssssss";

            // Si se proporciona una nueva contraseña, hashearla y añadirla a la consulta
            if (!empty($clave)) {
                $claveEncriptada = password_hash($clave, PASSWORD_BCRYPT);
                $query .= ", clave = ?";
                $params[] = $claveEncriptada;
                $paramTypes .= "s";
            }

            // Añadir condición WHERE para el ID
            $query .= " WHERE id_paciente = ?";
            $params[] = $id;
            $paramTypes .= "i";

            // Preparar y ejecutar la consulta con parámetros dinámicos
            $stmt = mysqli_prepare($link, $query);
            if (!$stmt) {
                throw new Exception("Error preparando la consulta: " . mysqli_error($link));
            }

            mysqli_stmt_bind_param($stmt, $paramTypes, ...$params);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['MensajeTexto'] = "Registro actualizado con éxito.";
                $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-info text-white";
            } else {
                $_SESSION['MensajeTexto'] = "Error al actualizar el registro.";
                $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
            }

            mysqli_stmt_close($stmt);
            mysqli_close($link);

            header("Location: ../principal.php");
            exit();

        default:
            $_SESSION['MensajeTexto'] = "Advertencia: No se pudo identificar la acción a realizar.";
            $_SESSION['MensajeTipo'] = "is-warning";
            header("Location: ../principal.php");
            exit();
    }
} catch (Exception $e) {
    // Registrar excepciones en el log del servidor para depuración
    error_log("Excepción no controlada: " . $e->getMessage());
    echo "Ha ocurrido un error. Estamos trabajando en corregir esta situación.";
} catch (Error $e) {
    // Registrar errores en el log del servidor
    error_log("Error no controlado: " . $e->getMessage());
    echo "Ha ocurrido un error. Estamos trabajando en corregir esta situación.";
}