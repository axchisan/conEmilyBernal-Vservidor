<?php
function validarLogin($link, $user, $pass, $tipo)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $tabla = ($tipo == "Paciente") ? "pacientes" : "doctor";
    $campoCorreo = ($tipo == "Paciente") ? "correo_electronico" : "correo_eletronico";
    $campoId = ($tipo == "Paciente") ? "id_paciente" : "id_doctor";
    $redirect = ($tipo == "Paciente") ? "principal.php" : "Admin/inicioAdmin.php";

    $query = "SELECT * FROM $tabla WHERE $campoCorreo = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "s", $user);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) == 1) {
        $row = mysqli_fetch_assoc($resultado);
        if (password_verify($pass, $row['clave'])) {
            // No generamos ni actualizamos session_token
            $_SESSION[$campoId] = $row[$campoId];
            $_SESSION['MensajeTexto'] = null;
            $_SESSION['MensajeTipo'] = null;
            header("Location: $redirect");
            exit;
        } else {
            $_SESSION['MensajeTexto'] = "Error validando datos: Contraseña incorrecta";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        }
    } else {
        $_SESSION['MensajeTexto'] = "Error validando datos: Usuario no encontrado";
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    }

    mysqli_stmt_close($stmt);
}

function consultarPaciente($link, $id)
{
    $query = "SELECT * FROM pacientes WHERE id_paciente = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) == 1) {
        $row = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
        return $row;
    } else {
        $_SESSION['MensajeTexto'] = "Error validando datos de usuario";
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        header("Location: ./index.php");
        exit;
    }
}

function consultarDoctor($link, $id)
{
    $query = "SELECT * FROM doctor WHERE id_doctor = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) == 1) {
        $row = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
        return $row;
    } else {
        $_SESSION['MensajeTexto'] = "Error validando datos de usuario";
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        header("Location: ../index.php");
        exit;
    }
}


function MostrarConsultas($link)
{
    $query = "SELECT * FROM consultas";
    return mysqli_query($link, $query);
}

function MostrarEspecialidad($link)
{
    $query = "SELECT * FROM especialidad";
    return mysqli_query($link, $query);
}

function MostrarDentistas($link)
{
    $query = "SELECT * FROM doctor";
    return mysqli_query($link, $query);
}

function MostrarPacientes($link)
{
    $query = "SELECT * FROM pacientes";
    return mysqli_query($link, $query);
}

function MostrarCitas1($link)
{
    
    $query = "SELECT * FROM citas";
    return mysqli_query($link, $query);
}

function MostrarCitasCompletadas($link, $id_doctor)
{
    $query = "
        SELECT 
            c.id_cita, p.id_paciente, p.nombre, p.apellido, d.nombreD, 
            p.fecha_nacimiento, c.fecha_cita, c.hora_cita, con.tipo, c.estado,
            YEAR(CURDATE()) - YEAR(p.fecha_nacimiento) AS años, pd.descripcion
        FROM citas AS c
        LEFT JOIN pacientes AS p ON p.id_paciente = c.id_paciente
        LEFT JOIN doctor AS d ON d.id_doctor = c.id_doctor
        LEFT JOIN consultas AS con ON con.id_consultas = c.id_consultas
        LEFT JOIN paciente_diagnostico AS pd ON pd.id_cita = c.id_cita
        WHERE d.id_doctor = ? AND c.estado = 'A'";
    
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_doctor);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function MostrarCitas($link, $id)
{
    $query = "
        SELECT 
            c.id_cita, p.id_paciente, p.nombre, p.apellido, d.nombreD, 
            p.fecha_nacimiento, c.fecha_cita, c.hora_cita, con.tipo, c.estado,
            YEAR(CURDATE()) - YEAR(p.fecha_nacimiento) AS años, pd.descripcion
        FROM citas AS c
        LEFT JOIN pacientes AS p ON p.id_paciente = c.id_paciente
        LEFT JOIN doctor AS d ON d.id_doctor = c.id_doctor
        LEFT JOIN consultas AS con ON con.id_consultas = c.id_consultas
        LEFT JOIN paciente_diagnostico AS pd ON pd.id_cita = c.id_cita
        WHERE d.id_doctor = ?";
    
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function ConsultarCitas($link, $id)
{
    $query = "SELECT * FROM citas WHERE id_cita = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) == 1) {
        $row = mysqli_fetch_array($resultado);
        mysqli_stmt_close($stmt);
        return $row;
    } else {
        $_SESSION['MensajeTexto'] = "Error consultando datos";
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        mysqli_stmt_close($stmt);
    }
}

function CitasPendientesFPDF($link, $id)
{
    $query = "
        SELECT 
            c.id_cita, c.estado, p.nombre, p.apellido, d.nombreD, 
            p.fecha_nacimiento, c.fecha_cita, c.hora_cita, con.tipo,
            YEAR(CURDATE()) - YEAR(p.fecha_nacimiento) AS años, pd.descripcion
        FROM citas AS c
        LEFT JOIN pacientes AS p ON p.id_paciente = c.id_paciente
        LEFT JOIN doctor AS d ON d.id_doctor = c.id_doctor
        LEFT JOIN consultas AS con ON con.id_consultas = c.id_consultas
        LEFT JOIN paciente_diagnostico AS pd ON pd.id_cita = c.id_cita
        WHERE c.estado = 'I' AND p.id_paciente = ?";
    
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function CitasRealizadasFPDF($link, $id)
{
    $query = "
        SELECT 
            c.id_cita, c.estado, p.nombre, p.apellido, d.nombreD, 
            p.fecha_nacimiento, c.fecha_cita, c.hora_cita, con.tipo,
            YEAR(CURDATE()) - YEAR(p.fecha_nacimiento) AS años, pd.descripcion, pd.medicina
        FROM citas AS c
        LEFT JOIN pacientes AS p ON p.id_paciente = c.id_paciente
        LEFT JOIN doctor AS d ON d.id_doctor = c.id_doctor
        LEFT JOIN consultas AS con ON con.id_consultas = c.id_consultas
        LEFT JOIN paciente_diagnostico AS pd ON pd.id_cita = c.id_cita
        WHERE c.estado = 'A' AND p.id_paciente = ?";
    
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}
function GenerarNotificacion($link, $id_doctor, $tipo, $mensaje) {
    $leida = 0; // Por defecto, no leída
    $fecha = date('Y-m-d H:i:s'); // Fecha actual en la zona horaria de PHP (America/Bogota)
    $query = "INSERT INTO notificaciones (id_doctor, tipo, mensaje, fecha, leida) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "isssi", $id_doctor, $tipo, $mensaje, $fecha, $leida);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}
function ObtenerNotificaciones($link, $id_doctor, $mostrar_todas = false) {
    $query = "SELECT id_notificacion, tipo, mensaje, fecha, leida 
              FROM notificaciones 
              WHERE id_doctor = ?" . ($mostrar_todas ? "" : " AND leida = 0") . " 
              ORDER BY fecha DESC";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_doctor);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function ContarNotificacionesNoLeidas($link, $id_doctor) {
    $query = "SELECT COUNT(*) as total FROM notificaciones WHERE id_doctor = ? AND leida = 0";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_doctor);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row['total'];
}

function EliminarNotificacion($link, $id_notificacion, $id_doctor) {
    $query = "DELETE FROM notificaciones WHERE id_notificacion = ? AND id_doctor = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id_notificacion, $id_doctor);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}
function MarcarNotificacionLeida($link, $id_notificacion, $id_doctor) {
    $query = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = ? AND id_doctor = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id_notificacion, $id_doctor);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function EliminarNotificacionesLeidas($link, $id_doctor) {
    $query = "DELETE FROM notificaciones WHERE id_doctor = ? AND leida = 1";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_doctor);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function EliminarTodasNotificaciones($link, $id_doctor) {
    $query = "DELETE FROM notificaciones WHERE id_doctor = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_doctor);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}
?>