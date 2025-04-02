<?php
// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once('../php/conexionDB.php');
include_once('../php/consultas.php');

// Validar sesión del doctor
if (!isset($_SESSION['id_doctor'])) {
    $_SESSION['MensajeTexto'] = "Error: Acceso al sistema no registrado.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ../index.php");
    exit();
}

// Validar ID del paciente
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['MensajeTexto'] = "Error: ID de paciente no proporcionado.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: inicioAdmin.php");
    exit();
}

$patient_id = mysqli_real_escape_string($link, $_GET['id']);
$doctor_id = $_SESSION['id_doctor'];

// Obtener datos del paciente
$patient = consultarPaciente($link, $patient_id);
if (!$patient) {
    $_SESSION['MensajeTexto'] = "Error: No se pudo obtener la información del paciente con ID $patient_id.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: inicioAdmin.php");
    exit();
}

// Calcular la edad del paciente
$age = 'N/A';
if (isset($patient['fecha_nacimiento']) && !empty($patient['fecha_nacimiento'])) {
    $birthDate = new DateTime($patient['fecha_nacimiento']);
    $currentDate = new DateTime();
    $age = $currentDate->diff($birthDate)->y;
}

// Obtener datos de la cita más reciente del paciente con este doctor
$query = "SELECT c.*, con.tipo, d.nombreD 
          FROM citas c 
          LEFT JOIN consultas con ON con.id_consultas = c.id_consultas 
          LEFT JOIN doctor d ON d.id_doctor = c.id_doctor 
          WHERE c.id_paciente = ? AND c.id_doctor = ? 
          ORDER BY c.fecha_cita DESC LIMIT 1";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "ii", $patient_id, $doctor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['MensajeTexto'] = "Error: No se encontraron citas para este paciente con el doctor actual.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: inicioAdmin.php");
    exit();
}
$appointment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Calcular el contador de notificaciones no leídas
$contadorNoLeidas = ContarNotificacionesNoLeidas($link, $doctor_id);

// Manejo del formulario para actualizar datos del paciente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_patient'])) {
    $telefono = isset($_POST['telefono']) ? mysqli_real_escape_string($link, $_POST['telefono']) : '';
    $eps = isset($_POST['eps']) ? mysqli_real_escape_string($link, $_POST['eps']) : '';
    $ocupacion = isset($_POST['ocupacion']) ? mysqli_real_escape_string($link, $_POST['ocupacion']) : '';
    $estado_civil = isset($_POST['estado_civil']) ? mysqli_real_escape_string($link, $_POST['estado_civil']) : '';
    $cedula = isset($_POST['cedula']) ? mysqli_real_escape_string($link, $_POST['cedula']) : '';
    $genero = isset($_POST['genero']) ? mysqli_real_escape_string($link, $_POST['genero']) : '';
    $emergencia_nombre = isset($_POST['emergencia_nombre']) ? mysqli_real_escape_string($link, $_POST['emergencia_nombre']) : '';
    $emergencia_telefono = isset($_POST['emergencia_telefono']) ? mysqli_real_escape_string($link, $_POST['emergencia_telefono']) : '';
    $menor_acompanante = isset($_POST['menor_acompanante']) ? mysqli_real_escape_string($link, $_POST['menor_acompanante']) : '';
    $menor_parentesco = isset($_POST['menor_parentesco']) ? mysqli_real_escape_string($link, $_POST['menor_parentesco']) : '';
    $menor_telefono = isset($_POST['menor_telefono']) ? mysqli_real_escape_string($link, $_POST['menor_telefono']) : '';
    $tipo_sangre = isset($_POST['tipo_sangre']) ? mysqli_real_escape_string($link, $_POST['tipo_sangre']) : '';
    $alertas_medicas = isset($_POST['alertas_medicas']) ? mysqli_real_escape_string($link, $_POST['alertas_medicas']) : '';
    $lugar_direccion_residencia = isset($_POST['lugar_direccion_residencia']) ? mysqli_real_escape_string($link, $_POST['lugar_direccion_residencia']) : '';

    // Validar campos ENUM para aceptar solo 'Sí' o 'No'
    $historia_cardiovasculares = isset($_POST['historia_cardiovasculares']) && in_array($_POST['historia_cardiovasculares'], ['Sí', 'No']) ? $_POST['historia_cardiovasculares'] : 'No';
    $historia_hemorragicas = isset($_POST['historia_hemorragicas']) && in_array($_POST['historia_hemorragicas'], ['Sí', 'No']) ? $_POST['historia_hemorragicas'] : 'No';
    $historia_dermatologicas = isset($_POST['historia_dermatologicas']) && in_array($_POST['historia_dermatologicas'], ['Sí', 'No']) ? $_POST['historia_dermatologicas'] : 'No';
    $historia_mentales = isset($_POST['historia_mentales']) && in_array($_POST['historia_mentales'], ['Sí', 'No']) ? $_POST['historia_mentales'] : 'No';
    $historia_diabetes = isset($_POST['historia_diabetes']) && in_array($_POST['historia_diabetes'], ['Sí', 'No']) ? $_POST['historia_diabetes'] : 'No';
    $historia_cancer = isset($_POST['historia_cancer']) && in_array($_POST['historia_cancer'], ['Sí', 'No']) ? $_POST['historia_cancer'] : 'No';
    $historia_artritis = isset($_POST['historia_artritis']) && in_array($_POST['historia_artritis'], ['Sí', 'No']) ? $_POST['historia_artritis'] : 'No';
    $historia_alergias = isset($_POST['historia_alergias']) && in_array($_POST['historia_alergias'], ['Sí', 'No']) ? $_POST['historia_alergias'] : 'No';
    $historia_cirugias = isset($_POST['historia_cirugias']) && in_array($_POST['historia_cirugias'], ['Sí', 'No']) ? $_POST['historia_cirugias'] : 'No';
    $historia_otros = isset($_POST['historia_otros']) ? mysqli_real_escape_string($link, $_POST['historia_otros']) : '';

    $update_query = "UPDATE pacientes SET 
                     telefono = ?, 
                     eps = ?, 
                     ocupacion = ?, 
                     estado_civil = ?, 
                     cedula = ?, 
                     sexo = ?, 
                     emergencia_nombre = ?, 
                     emergencia_telefono = ?, 
                     menor_acompanante = ?, 
                     menor_parentesco = ?, 
                     menor_telefono = ?, 
                     tipo_sangre = ?, 
                     alertas_medicas = ?,
                     lugar_direccion_residencia = ?,
                     historia_cardiovasculares = ?,
                     historia_hemorragicas = ?,
                     historia_dermatologicas = ?,
                     historia_mentales = ?,
                     historia_diabetes = ?,
                     historia_cancer = ?,
                     historia_artritis = ?,
                     historia_alergias = ?,
                     historia_cirugias = ?,
                     historia_otros = ?
                     WHERE id_paciente = ?";
    $stmt = mysqli_prepare($link, $update_query);
    if (!$stmt) {
        $_SESSION['MensajeTexto'] = "Error al preparar la consulta: " . mysqli_error($link);
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        header("Location: informe.php?id=$patient_id");
        exit();
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssssssssssssssssss",
        $telefono,
        $eps,
        $ocupacion,
        $estado_civil,
        $cedula,
        $genero,
        $emergencia_nombre,
        $emergencia_telefono,
        $menor_acompanante,
        $menor_parentesco,
        $menor_telefono,
        $tipo_sangre,
        $alertas_medicas,
        $lugar_direccion_residencia,
        $historia_cardiovasculares,
        $historia_hemorragicas,
        $historia_dermatologicas,
        $historia_mentales,
        $historia_diabetes,
        $historia_cancer,
        $historia_artritis,
        $historia_alergias,
        $historia_cirugias,
        $historia_otros,
        $patient_id
    );

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['MensajeTexto'] = "Datos del paciente actualizados correctamente.";
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-success text-white";
        $patient = consultarPaciente($link, $patient_id);
    } else {
        $_SESSION['MensajeTexto'] = "Error al actualizar los datos del paciente: " . mysqli_error($link);
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    }
    mysqli_stmt_close($stmt);
    header("Location: informe.php?id=$patient_id");
    exit();
}

// Manejo del formulario para el informe médico
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_medical'])) {
    $examen_intraoral = isset($_POST['examen_intraoral']) ? mysqli_real_escape_string($link, trim($_POST['examen_intraoral'])) : '';
    $examen_extraoral = isset($_POST['examen_extraoral']) ? mysqli_real_escape_string($link, trim($_POST['examen_extraoral'])) : '';
    $examen_atm = isset($_POST['examen_atm']) ? mysqli_real_escape_string($link, trim($_POST['examen_atm'])) : '';
    $observacion_intraoral = isset($_POST['observacion_intraoral']) ? mysqli_real_escape_string($link, trim($_POST['observacion_intraoral'])) : '';
    $observacion_extraoral_atm = isset($_POST['observacion_extraoral_atm']) ? mysqli_real_escape_string($link, trim($_POST['observacion_extraoral_atm'])) : '';
    $descripcion_radiografica = isset($_POST['descripcion_radiografica']) ? mysqli_real_escape_string($link, trim($_POST['descripcion_radiografica'])) : '';
    $diagnostico_periodontal = isset($_POST['diagnostico_periodontal']) ? mysqli_real_escape_string($link, trim($_POST['diagnostico_periodontal'])) : '';
    $plan_tratamiento = isset($_POST['plan_tratamiento']) ? mysqli_real_escape_string($link, trim($_POST['plan_tratamiento'])) : '';
    $pronostico = isset($_POST['pronostico']) ? mysqli_real_escape_string($link, trim($_POST['pronostico'])) : '';
    $evolucion = isset($_POST['evolucion']) ? mysqli_real_escape_string($link, trim($_POST['evolucion'])) : '';
    $diagnostico = isset($_POST['diagnostico']) ? mysqli_real_escape_string($link, trim($_POST['diagnostico'])) : '';
    $costo = isset($_POST['costo']) && $_POST['costo'] !== '' ? mysqli_real_escape_string($link, trim($_POST['costo'])) : '';

    // Manejo del odontograma
    $odontogram_data = [];
    if (isset($_POST['odontogram_tooth']) && isset($_POST['odontogram_condition'])) {
        $teeth = $_POST['odontogram_tooth'];
        $conditions = $_POST['odontogram_condition'];
        for ($i = 0; $i < count($teeth); $i++) {
            if (!empty($teeth[$i]) && !empty($conditions[$i])) {
                $odontogram_data[] = [
                    'tooth' => (int)$teeth[$i],
                    'condition' => mysqli_real_escape_string($link, trim($conditions[$i]))
                ];
            }
        }
    }
    $odontogram_json = json_encode($odontogram_data);

    $radiografia = '';
    $foto_boca = '';

    // Manejo de la radiografía
    if (isset($_FILES['radiografia']) && $_FILES['radiografia']['error'] === UPLOAD_ERR_OK) {
        $radiografia_path = "../uploads/radiografias/";
        if (!is_dir($radiografia_path) && !mkdir($radiografia_path, 0777, true)) {
            $_SESSION['MensajeTexto'] = "Error: No se pudo crear el directorio para radiografías.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        } else {
            $radiografia_name = $patient_id . "_radiografia_" . time() . "." . pathinfo($_FILES['radiografia']['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($_FILES['radiografia']['tmp_name'], $radiografia_path . $radiografia_name)) {
                $_SESSION['MensajeTexto'] = "Error al subir la radiografía.";
                $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
            } else {
                $radiografia = $radiografia_name;
            }
        }
    }

    // Manejo de la foto de la boca
    if (isset($_FILES['foto_boca']) && $_FILES['foto_boca']['error'] === UPLOAD_ERR_OK) {
        $foto_boca_path = "../uploads/fotos_boca/";
        if (!is_dir($foto_boca_path) && !mkdir($foto_boca_path, 0777, true)) {
            $_SESSION['MensajeTexto'] = "Error: No se pudo crear el directorio para fotos de la boca.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        } else {
            $foto_boca_name = $patient_id . "_boca_" . time() . "." . pathinfo($_FILES['foto_boca']['name'], PATHINFO_EXTENSION);
            if (!move_uploaded_file($_FILES['foto_boca']['tmp_name'], $foto_boca_path . $foto_boca_name)) {
                $_SESSION['MensajeTexto'] = "Error al subir la foto de la boca.";
                $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
            } else {
                $foto_boca = $foto_boca_name;
            }
        }
    }

    $check_query = "SELECT * FROM informe_medico WHERE id_cita = ?";
    $stmt = mysqli_prepare($link, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $appointment['id_cita']);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);

    if (!$check_result) {
        $_SESSION['MensajeTexto'] = "Error al verificar el informe médico: " . mysqli_error($link);
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    } elseif (mysqli_num_rows($check_result) > 0) {
        // Actualizar informe existente
        // Determinar qué campos actualizar según si se subieron radiografía y/o foto_boca
        if ($radiografia && $foto_boca) {
            $update_medical_query = "UPDATE informe_medico SET 
                                    examen_intraoral = ?, 
                                    examen_extraoral = ?, 
                                    examen_atm = ?, 
                                    observacion_intraoral = ?, 
                                    observacion_extraoral_atm = ?, 
                                    descripcion_radiografica = ?, 
                                    diagnostico_periodontal = ?, 
                                    plan_tratamiento = ?, 
                                    pronostico = ?, 
                                    evolucion = ?, 
                                    diagnostico = ?, 
                                    costo = ?, 
                                    radiografia = ?, 
                                    foto_boca = ?, 
                                    odontogram_data = ?
                                    WHERE id_cita = ?";
            $stmt = mysqli_prepare($link, $update_medical_query);
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssssssssssi",
                $examen_intraoral,
                $examen_extraoral,
                $examen_atm,
                $observacion_intraoral, 
                $observacion_extraoral_atm,
                $descripcion_radiografica,
                $diagnostico_periodontal,
                $plan_tratamiento,
                $pronostico,
                $evolucion,
                $diagnostico,
                $costo,
                $radiografia,
                $foto_boca,
                $odontogram_json,
                $appointment['id_cita']
            );
        } elseif ($radiografia) {
            $update_medical_query = "UPDATE informe_medico SET 
                                    examen_intraoral = ?, 
                                    examen_extraoral = ?, 
                                    examen_atm = ?, 
                                    observacion_intraoral = ?, 
                                    observacion_extraoral_atm = ?, 
                                    descripcion_radiografica = ?, 
                                    diagnostico_periodontal = ?, 
                                    plan_tratamiento = ?, 
                                    pronostico = ?, 
                                    evolucion = ?, 
                                    diagnostico = ?, 
                                    costo = ?, 
                                    radiografia = ?, 
                                    odontogram_data = ?
                                    WHERE id_cita = ?";
            $stmt = mysqli_prepare($link, $update_medical_query);
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssssssssi",
                $examen_intraoral,
                $examen_extraoral,
                $examen_atm,
                $observacion_intraoral,
                $observacion_extraoral_atm,
                $descripcion_radiografica,
                $diagnostico_periodontal,
                $plan_tratamiento,
                $pronostico,
                $evolucion,
                $diagnostico,
                $costo,
                $radiografia,
                $odontogram_json,
                $appointment['id_cita']
            );
        } elseif ($foto_boca) {
            $update_medical_query = "UPDATE informe_medico SET 
                                    examen_intraoral = ?, 
                                    examen_extraoral = ?, 
                                    examen_atm = ?, 
                                    observacion_intraoral = ?, 
                                    observacion_extraoral_atm = ?, 
                                    descripcion_radiografica = ?, 
                                    diagnostico_periodontal = ?, 
                                    plan_tratamiento = ?, 
                                    pronostico = ?, 
                                    evolucion = ?, 
                                    diagnostico = ?, 
                                    costo = ?, 
                                    foto_boca = ?, 
                                    odontogram_data = ?
                                    WHERE id_cita = ?";
            $stmt = mysqli_prepare($link, $update_medical_query);
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssssssssi",
                $examen_intraoral,
                $examen_extraoral,
                $examen_atm,
                $observacion_intraoral,
                $observacion_extraoral_atm,
                $descripcion_radiografica,
                $diagnostico_periodontal,
                $plan_tratamiento,
                $pronostico,
                $evolucion,
                $diagnostico,
                $costo,
                $foto_boca,
                $odontogram_json,
                $appointment['id_cita']
            );
        } else {
            $update_medical_query = "UPDATE informe_medico SET 
                                    examen_intraoral = ?, 
                                    examen_extraoral = ?, 
                                    examen_atm = ?, 
                                    observacion_intraoral = ?, 
                                    observacion_extraoral_atm = ?, 
                                    descripcion_radiografica = ?, 
                                    diagnostico_periodontal = ?, 
                                    plan_tratamiento = ?, 
                                    pronostico = ?, 
                                    evolucion = ?, 
                                    diagnostico = ?, 
                                    costo = ?, 
                                    odontogram_data = ?
                                    WHERE id_cita = ?";
            $stmt = mysqli_prepare($link, $update_medical_query);
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssssssssi",
                $examen_intraoral,
                $examen_extraoral,
                $examen_atm,
                $observacion_intraoral,
                $observacion_extraoral_atm,
                $descripcion_radiografica,
                $diagnostico_periodontal,
                $plan_tratamiento,
                $pronostico,
                $evolucion,
                $diagnostico,
                $costo,
                $odontogram_json,
                $appointment['id_cita']
            );
        }

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['MensajeTexto'] = "Informe médico actualizado correctamente.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-success text-white";
        } else {
            $_SESSION['MensajeTexto'] = "Error al actualizar el informe médico: " . mysqli_error($link);
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        }
        mysqli_stmt_close($stmt);
    } else {
        // Insertar nuevo informe
        $insert_medical_query = "INSERT INTO informe_medico (id_cita, id_paciente, examen_intraoral, examen_extraoral, examen_atm, observacion_intraoral, observacion_extraoral_atm, descripcion_radiografica, diagnostico_periodontal, plan_tratamiento, pronostico, evolucion, diagnostico, costo, radiografia, foto_boca, odontogram_data) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $insert_medical_query);
        mysqli_stmt_bind_param(
            $stmt,
            "iisssssssssssssss",
            $appointment['id_cita'],
            $patient_id,
            $examen_intraoral,
            $examen_extraoral,
            $examen_atm,
            $observacion_intraoral,
            $observacion_extraoral_atm,
            $descripcion_radiografica,
            $diagnostico_periodontal,
            $plan_tratamiento,
            $pronostico,
            $evolucion,
            $diagnostico,
            $costo,
            $radiografia,
            $foto_boca,
            $odontogram_json
        );

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['MensajeTexto'] = "Informe médico creado correctamente.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-success text-white";
        } else {
            $_SESSION['MensajeTexto'] = "Error al crear el informe médico: " . mysqli_error($link);
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        }
        mysqli_stmt_close($stmt);
    }

    header("Location: informe.php?id=$patient_id");
    exit();
}

// Obtener el informe médico actual
$medical_report_query = "SELECT * FROM informe_medico WHERE id_cita = ?";
$stmt = mysqli_prepare($link, $medical_report_query);
mysqli_stmt_bind_param($stmt, "i", $appointment['id_cita']);
mysqli_stmt_execute($stmt);
$medical_report_result = mysqli_stmt_get_result($stmt);
$medical_report = mysqli_num_rows($medical_report_result) > 0 ? mysqli_fetch_assoc($medical_report_result) : [];
mysqli_stmt_close($stmt);

// Parsear el odontograma si existe
$odontogram_data = isset($medical_report['odontogram_data']) ? json_decode($medical_report['odontogram_data'], true) : [];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Informe del Paciente</title>
    <link rel="stylesheet" href="../src/css/lib/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../src/css/lib/fontawesome/css/all.css">
    <link rel="stylesheet" href="../src/css/admin.css?v=3.8">
    <link rel="stylesheet" href="../src/css/informe_paciente.css?v=1.0">
    <style>
        .odontogram-table th, .odontogram-table td {
            text-align: center;
            vertical-align: middle;
        }
        .odontogram-table .btn {
            margin: 2px;
        }
        .odontogram-table input, .odontogram-table select {
            width: 100%;
            box-sizing: border-box;
        }
        .odontogram-table .tooth-icon {
            margin-right: 5px;
            color: #6f42c1; /* Morado para que combine con el tema */
        }
        .odontogram-table .condition-icon {
            margin-left: 5px;
            font-size: 0.9em;
        }
        .btn-discreet {
            background-color: #e2e6ea; /* Gris claro */
            color: #495057; /* Texto gris oscuro */
            border: none;
            padding: 5px 10px;
            font-size: 0.9em;
            transition: background-color 0.3s;
        }
        .btn-discreet:hover {
            background-color: #d6d9dc; /* Un gris un poco más oscuro al pasar el mouse */
        }
        .btn-discreet i {
            margin-right: 5px;
        }
        .badge {
            position: relative;
            top: -10px;
            left: -10px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="toggle">
            <a href="#" class="burger js-menu-toggle" data-toggle="collapse" data-target="#main-navbar">
                <span></span>
            </a>
        </div>
        <div class="side-inner">
            <div class="profile">
                <?php
                $doctor = consultarDoctor($link, $doctor_id);
                if (!$doctor) {
                    echo '<p>Error: No se pudo obtener la información del doctor.</p>';
                } else {
                    if ($doctor['sexo'] == 'Masculino') {
                        echo '<img src="../src/img/odontologo.png" class="rounded-circle" width="150">';
                    } elseif ($doctor['sexo'] == 'Femenino') {
                        echo '<img src="../src/img/odontologa.png" class="rounded-circle" width="150">';
                    }
                    echo '<h3 class="name">' . htmlspecialchars(utf8_decode($doctor['nombreD'] . ' ' . $doctor['apellido'])) . '</h3>';
                    echo '<span class="country">Barbosa Santander</span>';
                }
                ?>
            </div>
            <div class="nav-menu">
                <ul>
                    <li><a href="inicioAdmin.php"><span class="icon-location-arrow mr-3"></span><i class="far fa-calendar-check"></i> Citas</a></li>
                    <li><a href="doctores.php"><span class="icon-location-arrow mr-3"></span><i class="fas fa-user-md"></i> Odontólogos</a></li>
                    <li><a href="calendar.php"><span class="icon-pie-chart mr-3"></span><i class="far fa-calendar-alt"></i> Calendario</a></li>
                    <li><a href="historia_clinica.php"><span class="icon-pie-chart mr-3"></span><i class="far fa-calendar-alt"></i> Historia Clínica</a></li>
                    <li><a href="notificaciones.php"><span class="icon-bell mr-3"></span><i class="fas fa-bell"></i> Notificaciones <?php if ($contadorNoLeidas > 0) { ?><span class="badge"><?php echo $contadorNoLeidas; ?></span><?php } ?></a></li>
                    <li><a href="../php/cerrar.php"><span class="icon-sign-out mr-3"></span><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </aside>

    <main class="bg bg-white">
        <div class="site-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="content-box-large">
                            <ol class="breadcrumb bg-white">
                                <li class="breadcrumb-item"><a href="inicioAdmin.php">Inicio</a></li>
                                <li class="breadcrumb-item active">Informe del Paciente</li>
                            </ol>

                            <?php if (isset($_SESSION['MensajeTexto'])) { ?>
                                <div class="alert <?php echo htmlspecialchars($_SESSION['MensajeTipo']); ?>" role="alert">
                                    <?php echo htmlspecialchars($_SESSION['MensajeTexto']); ?>
                                    <button class="delete"><i class="fa fa-times"></i></button>
                                </div>
                                <?php
                                $_SESSION['MensajeTexto'] = null;
                                $_SESSION['MensajeTipo'] = null;
                                ?>
                            <?php } ?>

                            <form method="POST" action="">
                                <!-- Información General -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-user"></i> Información General
                                    </div>
                                    <div class="card-body patient-info">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5>Datos Principales</h5>
                                                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($patient['nombre'] . ' ' . $patient['apellido']); ?></p>
                                                <p><strong>Edad:</strong> <?php echo htmlspecialchars($age); ?> años</p>
                                                <p><strong>Fecha de Nacimiento:</strong> <?php echo htmlspecialchars($patient['fecha_nacimiento']); ?></p>
                                                <p><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($patient['correo_electronico']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Datos Médicos</h5>
                                                <div class="form-group">
                                                    <label for="cedula">Nº de Documento:</label>
                                                    <input type="text" class="form-control" id="cedula" name="cedula" value="<?php echo htmlspecialchars($patient['cedula'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="telefono">Teléfono:</label>
                                                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($patient['telefono'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="lugar_direccion_residencia">Lugar y Dirección de Residencia:</label>
                                                    <input type="text" class="form-control" id="lugar_direccion_residencia" name="lugar_direccion_residencia" value="<?php echo htmlspecialchars($patient['lugar_direccion_residencia'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="eps">EPS:</label>
                                                    <input type="text" class="form-control" id="eps" name="eps" value="<?php echo htmlspecialchars($patient['eps'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="genero">Género:</label>
                                                    <select class="form-control" id="genero" name="genero">
                                                        <option value="Masculino" <?php echo (isset($patient['sexo']) && $patient['sexo'] == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                                                        <option value="Femenino" <?php echo (isset($patient['sexo']) && $patient['sexo'] == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="ocupacion">Ocupación:</label>
                                                    <input type="text" class="form-control" id="ocupacion" name="ocupacion" value="<?php echo htmlspecialchars($patient['ocupacion'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="estado_civil">Estado Civil:</label>
                                                    <input type="text" class="form-control" id="estado_civil" name="estado_civil" value="<?php echo htmlspecialchars($patient['estado_civil'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="emergencia_nombre">En caso de emergencia, llamar a:</label>
                                                    <input type="text" class="form-control" id="emergencia_nombre" name="emergencia_nombre" value="<?php echo htmlspecialchars($patient['emergencia_nombre'] ?? ''); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="emergencia_telefono">Teléfono de Emergencia:</label>
                                                    <input type="text" class="form-control" id="emergencia_telefono" name="emergencia_telefono" value="<?php echo htmlspecialchars($patient['emergencia_telefono'] ?? ''); ?>">
                                                </div>
                                                <?php if ($age !== 'N/A' && $age < 18) { ?>
                                                    <div class="form-group">
                                                        <label for="menor_acompanante">Nombre del Acompañante (Menor de Edad):</label>
                                                        <input type="text" class="form-control" id="menor_acompanante" name="menor_acompanante" value="<?php echo htmlspecialchars($patient['menor_acompanante'] ?? ''); ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="menor_parentesco">Parentesco:</label>
                                                        <input type="text" class="form-control" id="menor_parentesco" name="menor_parentesco" value="<?php echo htmlspecialchars($patient['menor_parentesco'] ?? ''); ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="menor_telefono">Teléfono del Acompañante:</label>
                                                        <input type="text" class="form-control" id="menor_telefono" name="menor_telefono" value="<?php echo htmlspecialchars($patient['menor_telefono'] ?? ''); ?>">
                                                    </div>
                                                <?php } ?>
                                                <div class="form-group">
                                                    <label for="tipo_sangre">Tipo de Sangre:</label>
                                                    <input type="text" class="form-control" id="tipo_sangre" name="tipo_sangre" value="<?php echo htmlspecialchars($patient['tipo_sangre'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Anamnesis -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-heartbeat"></i> Anamnesis (Historia Familiar o Personal)
                                    </div>
                                    <div class="card-body patient-info">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Historia Familiar o Personal</th>
                                                    <th>Sí</th>
                                                    <th>No</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>1. Enfermedades Cardiovasculares</td>
                                                    <td><input type="radio" name="historia_cardiovasculares" value="Sí" <?php echo (isset($patient['historia_cardiovasculares']) && $patient['historia_cardiovasculares'] == 'Sí') ? 'checked' : ''; ?> required></td>
                                                    <td><input type="radio" name="historia_cardiovasculares" value="No" <?php echo (isset($patient['historia_cardiovasculares']) && $patient['historia_cardiovasculares'] == 'No') ? 'checked' : ''; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td>2. Enfermedades Hemorrágicas</td>
                                                    <td><input type="radio" name="historia_hemorragicas" value="Sí" <?php echo (isset($patient['historia_hemorragicas']) && $patient['historia_hemorragicas'] == 'Sí') ? 'checked' : ''; ?> required></td>
                                                    <td><input type="radio" name="historia_hemorragicas" value="No" <?php echo (isset($patient['historia_hemorragicas']) && $patient['historia_hemorragicas'] == 'No') ? 'checked' : ''; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td>3. Enfermedades Dermatológicas</td>
                                                    <td><input type="radio" name="historia_dermatologicas" value="Sí" <?php echo (isset($patient['historia_dermatologicas']) && $patient['historia_dermatologicas'] == 'Sí') ? 'checked' : ''; ?> required></td>
                                                    <td><input type="radio" name="historia_dermatologicas" value="No" <?php echo (isset($patient['historia_dermatologicas']) && $patient['historia_dermatologicas'] == 'No') ? 'checked' : ''; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td>4. Enfermedades Mentales</td>
                                                    <td><input type="radio" name="historia_mentales" value="Sí" <?php echo (isset($patient['historia_mentales']) && $patient['historia_mentales'] == 'Sí') ? 'checked' : ''; ?> required></td>
                                                    <td><input type="radio" name="historia_mentales" value="No" <?php echo (isset($patient['historia_mentales']) && $patient['historia_mentales'] == 'No') ? 'checked' : ''; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td>5. Diabetes</td>
                                                    <td><input type="radio" name="historia_diabetes" value="Sí" <?php echo (isset($patient['historia_diabetes']) && $patient['historia_diabetes'] == 'Sí') ? 'checked' : ''; ?> required></td>
                                                    <td><input type="radio" name="historia_diabetes" value="No" <?php echo (isset($patient['historia_diabetes']) && $patient['historia_diabetes'] == 'No') ? 'checked' : ''; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td>6. Cáncer</td>
                                                    <td><input type="radio" name="historia_cancer" value="Sí" <?php echo (isset($patient['historia_cancer']) && $patient['historia_cancer'] == 'Sí') ? 'checked' : ''; ?> required></td>
                                                    <td><input type="radio" name="historia_cancer" value="No" <?php echo (isset($patient['historia_cancer']) && $patient['historia_cancer'] == 'No') ? 'checked' : ''; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td>7. Artritis</td>
                                                    <td><input type="radio" name="historia_artritis" value="Sí" <?php echo (isset($patient['historia_artritis']) && $patient['historia_artritis'] == 'Sí') ? 'checked' : ''; ?> required></td>
                                                    <td><input type="radio" name="historia_artritis" value="No" <?php echo (isset($patient['historia_artritis']) && $patient['historia_artritis'] == 'No') ? 'checked' : ''; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td>8. Alergias</td>
                                                    <td><input type="radio" name="historia_alergias" value="Sí" <?php echo (isset($patient['historia_alergias']) && $patient['historia_alergias'] == 'Sí') ? 'checked' : ''; ?> required></td>
                                                    <td><input type="radio" name="historia_alergias" value="No" <?php echo (isset($patient['historia_alergias']) && $patient['historia_alergias'] == 'No') ? 'checked' : ''; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td>9. Cirugías</td>
                                                    <td><input type="radio" name="historia_cirugias" value="Sí" <?php echo (isset($patient['historia_cirugias']) && $patient['historia_cirugias'] == 'Sí') ? 'checked' : ''; ?> required></td>
                                                    <td><input type="radio" name="historia_cirugias" value="No" <?php echo (isset($patient['historia_cirugias']) && $patient['historia_cirugias'] == 'No') ? 'checked' : ''; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td>10. Otros</td>
                                                    <td colspan="2" class="position-relative">
                                                        <textarea class="form-control compact-textarea" name="historia_otros" id="historia_otros" maxlength="17"><?php echo htmlspecialchars($patient['historia_otros'] ?? ''); ?></textarea>
                                                        <span id="char_count" class="char-counter"></span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="form-group">
                                            <label for="alertas_medicas">Alertas Médicas:</label>
                                            <textarea class="form-control" id="alertas_medicas" name="alertas_medicas"><?php echo htmlspecialchars($patient['alertas_medicas'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="button-container custom-button-container">
                                    <button type="submit" name="update_patient" class="btn btn-update">
                                        <i class="fas fa-sync-alt"></i> Actualizar Datos
                                    </button>
                                </div>
                            </form>

                            <!-- Información de la cita -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="far fa-calendar-check"></i> Información de la Cita
                                </div>
                                <div class="card-body patient-info">
                                    <p><strong>Fecha:</strong> <?php echo htmlspecialchars($appointment['fecha_cita']); ?></p>
                                    <p><strong>Hora:</strong> <?php echo htmlspecialchars($appointment['hora_cita']); ?></p>
                                    <p><strong>Doctor:</strong> <?php echo htmlspecialchars($appointment['nombreD']); ?></p>
                                    <p><strong>Motivo de Consulta:</strong> <?php echo htmlspecialchars($appointment['tipo']); ?></p>
                                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($appointment['estado'] == 'A' ? 'Realizada' : 'Pendiente'); ?></p>
                                </div>
                            </div>

                            <!-- Informe médico -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-notes-medical"></i> Informe Médico
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="examen_intraoral">Examen Clínico Intraoral:</label>
                                            <textarea class="form-control" id="examen_intraoral" name="examen_intraoral"><?php echo htmlspecialchars($medical_report['examen_intraoral'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="examen_extraoral">Examen Clínico Extraoral:</label>
                                            <textarea class="form-control" id="examen_extraoral" name="examen_extraoral"><?php echo htmlspecialchars($medical_report['examen_extraoral'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="examen_atm">Examen ATM:</label>
                                            <textarea class="form-control" id="examen_atm" name="examen_atm"><?php echo htmlspecialchars($medical_report['examen_atm'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="observacion_intraoral">Observación, Palpación Intraoral:</label>
                                            <textarea class="form-control" id="observacion_intraoral" name="observacion_intraoral"><?php echo htmlspecialchars($medical_report['observacion_intraoral'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="observacion_extraoral_atm">Observación, Palpación Extraoral (ATM y Músculos Masticación):</label>
                                            <textarea class="form-control" id="observacion_extraoral_atm" name="observacion_extraoral_atm"><?php echo htmlspecialchars($medical_report['observacion_extraoral_atm'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="descripcion_radiografica">Descripción Radiográfica:</label>
                                            <textarea class="form-control" id="descripcion_radiografica" name="descripcion_radiografica"><?php echo htmlspecialchars($medical_report['descripcion_radiografica'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="diagnostico_periodontal">Diagnóstico Periodontal:</label>
                                            <textarea class="form-control" id="diagnostico_periodontal" name="diagnostico_periodontal"><?php echo htmlspecialchars($medical_report['diagnostico_periodontal'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="plan_tratamiento">Plan de Tratamiento:</label>
                                            <textarea class="form-control" id="plan_tratamiento" name="plan_tratamiento"><?php echo htmlspecialchars($medical_report['plan_tratamiento'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="pronostico">Pronóstico:</label>
                                            <textarea class="form-control" id="pronostico" name="pronostico"><?php echo htmlspecialchars($medical_report['pronostico'] ?? ''); ?></textarea>
                                        </div>
                                        <!-- Sección de Odontograma -->
                                        <div class="form-group">
                                            <label for="odontogram">Odontograma:</label>
                                            <table class="table table-bordered odontogram-table" id="odontogram-table">
                                                <thead>
                                                    <tr>
                                                        <th>Diente</th>
                                                        <th>Condición</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($odontogram_data)) : ?>
                                                        <?php foreach ($odontogram_data as $index => $entry) : ?>
                                                            <tr>
                                                                <td>
                                                                    <i class="fas fa-tooth tooth-icon"></i>
                                                                    <input type="number" class="form-control d-inline-block" style="width: 80px;" name="odontogram_tooth[]" value="<?php echo htmlspecialchars($entry['tooth']); ?>" min="1" max="48" required>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <input type="text" class="form-control odontogram-condition" name="odontogram_condition[]" value="<?php echo htmlspecialchars($entry['condition']); ?>" required>
                                                                        <span class="condition-icon">
                                                                            <?php
                                                                            $condition = strtolower($entry['condition']);
                                                                            if ($condition === 'ausente') {
                                                                                echo '<i class="fas fa-times text-danger"></i>';
                                                                            } elseif ($condition === 'sano') {
                                                                                echo '<i class="fas fa-check text-success"></i>';
                                                                            } elseif (strpos($condition, 'caries') !== false) {
                                                                                echo '<i class="fas fa-exclamation-triangle text-warning"></i>';
                                                                            }
                                                                            ?>
                                                                        </span>
                                                                    </div>
                                                                </td>
                                                                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <tr>
                                                            <td>
                                                                <i class="fas fa-tooth tooth-icon"></i>
                                                                <input type="number" class="form-control d-inline-block" style="width: 80px;" name="odontogram_tooth[]" min="1" max="48" required>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <input type="text" class="form-control odontogram-condition" name="odontogram_condition[]" required>
                                                                    <span class="condition-icon"></span>
                                                                </div>
                                                            </td>
                                                            <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                            <div class="d-flex justify-content-between mt-2">
                                                <button type="button" class="btn btn-primary btn-sm" id="add-odontogram-row"><i class="fas fa-plus"></i> Agregar Diente</button>
                                                <a href="generate_odontogram_pdf.php?patient_id=<?php echo htmlspecialchars($patient_id); ?>" class="btn btn-discreet">
                                                    <i class="fas fa-file-pdf"></i> Descargar Odontograma
                                                </a>
                                            </div>
                                        </div>
                                        <div class="form-group custom-file-upload">
                                            <label for="radiografia">Radiografía:</label>
                                            <div class="file-upload-wrapper">
                                                <input type="file" class="custom-file-input" id="radiografia" name="radiografia" accept="image/*">
                                                <span class="file-upload-text">Selecciona una radiografía...</span>
                                                <button type="button" class="file-upload-btn"><i class="fas fa-upload"></i> Subir</button>
                                            </div>
                                            <?php if (isset($medical_report['radiografia']) && $medical_report['radiografia']) { ?>
                                                <div class="image-preview">
                                                    <img src="../uploads/radiografias/<?php echo htmlspecialchars($medical_report['radiografia']); ?>" class="uploaded-image" alt="Radiografía">
                                                    <button type="button" class="remove-image" data-type="radiografia"><i class="fas fa-trash-alt"></i> Eliminar</button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="form-group custom-file-upload">
                                            <label for="foto_boca">Foto de la Boca:</label>
                                            <div class="file-upload-wrapper">
                                                <input type="file" class="custom-file-input" id="foto_boca" name="foto_boca" accept="image/*">
                                                <span class="file-upload-text">Selecciona una foto de la boca...</span>
                                                <button type="button" class="file-upload-btn"><i class="fas fa-upload"></i> Subir</button>
                                            </div>
                                            <?php if (isset($medical_report['foto_boca']) && $medical_report['foto_boca']) { ?>
                                                <div class="image-preview">
                                                    <img src="../uploads/fotos_boca/<?php echo htmlspecialchars($medical_report['foto_boca']); ?>" class="uploaded-image" alt="Foto de la Boca">
                                                    <button type="button" class="remove-image" data-type="foto_boca"><i class="fas fa-trash-alt"></i> Eliminar</button>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="form-group">
                                            <label for="evolucion">Evolución:</label>
                                            <textarea class="form-control" id="evolucion" name="evolucion"><?php echo htmlspecialchars($medical_report['evolucion'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="diagnostico">Diagnóstico:</label>
                                            <textarea class="form-control" id="diagnostico" name="diagnostico"><?php echo htmlspecialchars($medical_report['diagnostico'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="costo">Costo:</label>
                                            <textarea class="form-control" id="costo" name="costo"><?php echo htmlspecialchars($medical_report['costo'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="button-container custom-button-container">
                                            <button type="submit" name="update_medical" class="btn btn-save">
                                                <i class="fas fa-save"></i> Guardar Informe Médico
                                            </button>
                                        </div>
                                    </form>
                                    <div class="button-container custom-button-container">
                                        <form action="generate_informe_pdf.php" method="post" style="display: inline;">
                                            <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">
                                            <button type="submit" class="btn btn-pdf">
                                                <i class="fas fa-file-pdf"></i> Guardar Informe en PDF
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../src/js/jquery.js"></script>
    <script src="../src/css/lib/bootstrap/js/bootstrap.min.js"></script>
    <script src="../src/js/admin.js"></script>
    <script>
        // Contador de caracteres para el campo "Otros"
        document.addEventListener('DOMContentLoaded', () => {
            const historiaOtros = document.getElementById('historia_otros');
            const charCount = document.getElementById('char_count');
            const maxLength = parseInt(historiaOtros.getAttribute('maxlength')); // Obtener el maxlength (17)

            // Función para actualizar el contador
            const updateCharCount = () => {
                const remaining = maxLength - historiaOtros.value.length;
                charCount.textContent = remaining;
                charCount.classList.toggle('low', remaining < 5); // Añadir clase 'low' si quedan menos de 5 caracteres
            };

            // Actualizar al cargar la página
            updateCharCount();

            // Actualizar al escribir
            historiaOtros.addEventListener('input', updateCharCount);

            // Cerrar alertas
            (document.querySelectorAll('.alert .delete') || []).forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
            });

            // Mostrar nombre del archivo seleccionado
            document.querySelectorAll('.custom-file-input').forEach(input => {
                input.addEventListener('change', function() {
                    const fileName = this.files[0]?.name || 'Selecciona un archivo...';
                    this.parentElement.querySelector('.file-upload-text').textContent = fileName;
                });
            });

            // Eliminar imágenes con AJAX
            document.querySelectorAll('.remove-image').forEach(button => {
                button.addEventListener('click', function() {
                    const imagePreview = this.parentElement;
                    const type = this.getAttribute('data-type');
                    const fileName = imagePreview.querySelector('img').src.split('/').pop();
                    const idCita = <?php echo json_encode($appointment['id_cita']); ?>;

                    if (confirm('¿Estás seguro de que deseas eliminar esta imagen?')) {
                        fetch('delete_image.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `type=${type}&file_name=${encodeURIComponent(fileName)}&id_cita=${idCita}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    imagePreview.remove();
                                    alert(data.message);
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Ocurrió un error al eliminar la imagen');
                            });
                    }
                });
            });

            // Manejo dinámico del odontograma
            const odontogramTable = document.getElementById('odontogram-table').getElementsByTagName('tbody')[0];
            document.getElementById('add-odontogram-row').addEventListener('click', () => {
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>
                        <i class="fas fa-tooth tooth-icon"></i>
                        <input type="number" class="form-control d-inline-block" style="width: 80px;" name="odontogram_tooth[]" min="1" max="48" required>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <input type="text" class="form-control odontogram-condition" name="odontogram_condition[]" required>
                            <span class="condition-icon"></span>
                        </div>
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>
                `;
                odontogramTable.appendChild(newRow);
            });

            odontogramTable.addEventListener('click', (e) => {
                if (e.target.closest('.remove-row')) {
                    const row = e.target.closest('tr');
                    if (odontogramTable.rows.length > 1) {
                        row.remove();
                    } else {
                        alert('Debe haber al menos una fila en el odontograma.');
                    }
                }
            });

            // Actualizar íconos de condición dinámicamente
            odontogramTable.addEventListener('input', (e) => {
                if (e.target.classList.contains('odontogram-condition')) {
                    const conditionInput = e.target;
                    const conditionIcon = conditionInput.nextElementSibling;
                    const condition = conditionInput.value.toLowerCase();

                    conditionIcon.innerHTML = '';
                    if (condition === 'ausente') {
                        conditionIcon.innerHTML = '<i class="fas fa-times text-danger"></i>';
                    } else if (condition === 'sano') {
                        conditionIcon.innerHTML = '<i class="fas fa-check text-success"></i>';
                    } else if (condition.includes('caries')) {
                        conditionIcon.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i>';
                    }
                }
            });
        });

        // Actualización en tiempo real del contador de notificaciones
        function actualizarContadorNotificaciones() {
            const idDoctor = <?php echo $doctor_id; ?>;
            $.ajax({
                url: '../php/get_notificaciones.php',
                type: 'POST',
                data: { id_doctor: idDoctor, mostrar_todas: true },
                dataType: 'json',
                success: function(data) {
                    if (data.error) {
                        console.error('Error al obtener notificaciones:', data.error);
                        return;
                    }

                    // Actualizar el contador de notificaciones no leídas
                    const badge = $('.badge');
                    if (data.no_leidas > 0) {
                        badge.text(data.no_leidas).show();
                    } else {
                        badge.hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al actualizar el contador de notificaciones:', error);
                }
            });
        }

        // Actualizar cada 30 segundos
        setInterval(actualizarContadorNotificaciones, 30000);
        // Llamada inicial
        actualizarContadorNotificaciones();
    </script>
</body>

</html>