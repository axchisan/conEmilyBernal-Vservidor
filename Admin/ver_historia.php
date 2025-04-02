<?php
session_start();
include_once('../php/conexionDB.php');
include_once('../php/consultas.php');

// Validar la sesión
if (!isset($_SESSION['id_doctor'])) {
    $_SESSION['MensajeTexto'] = "Error acceso al sistema: Sesión no iniciada.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ../index.php");
    exit();
}

$vUsuario = $_SESSION['id_doctor'];

// Validar ID de cita y paciente
if (!isset($_GET['id_cita']) || !isset($_GET['id_paciente'])) {
    $_SESSION['MensajeTexto'] = "Error: ID de cita o paciente no proporcionado.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: historia_clinica.php");
    exit();
}

$id_cita = mysqli_real_escape_string($link, $_GET['id_cita']);
$id_paciente = mysqli_real_escape_string($link, $_GET['id_paciente']);

// Obtener datos del paciente
$patient = consultarPaciente($link, $id_paciente);
if (!$patient) {
    $_SESSION['MensajeTexto'] = "Error: No se pudo obtener la información del paciente.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: historia_clinica.php");
    exit();
}

// Calcular la edad del paciente
$birthDate = new DateTime($patient['fecha_nacimiento']);
$currentDate = new DateTime();
$age = $currentDate->diff($birthDate)->y;

// Obtener datos de la cita
$query = "SELECT c.*, con.tipo, d.nombreD 
          FROM citas c 
          LEFT JOIN consultas con ON con.id_consultas = c.id_consultas 
          LEFT JOIN doctor d ON d.id_doctor = c.id_doctor 
          WHERE c.id_cita = ? AND c.id_paciente = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "ii", $id_cita, $id_paciente);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$appointment = mysqli_fetch_assoc($result);
if (!$appointment) {
    $_SESSION['MensajeTexto'] = "Error: No se encontró la cita.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: historia_clinica.php");
    exit();
}

// Obtener el informe médico
$medical_report_query = "SELECT * FROM informe_medico WHERE id_cita = ?";
$stmt = mysqli_prepare($link, $medical_report_query);
mysqli_stmt_bind_param($stmt, "i", $id_cita);
mysqli_stmt_execute($stmt);
$medical_report_result = mysqli_stmt_get_result($stmt);
$medical_report = mysqli_num_rows($medical_report_result) > 0 ? mysqli_fetch_assoc($medical_report_result) : [];
mysqli_stmt_close($stmt);

// Parsear el odontograma si existe
$odontogram_data = [];
if (isset($medical_report['odontogram_data']) && !empty($medical_report['odontogram_data'])) {
    $decoded = json_decode($medical_report['odontogram_data'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $odontogram_data = $decoded;
    } else {
        error_log("Error al decodificar odontogram_data: " . json_last_error_msg());
    }
}

// Obtener datos del doctor
$row = consultarDoctor($link, $vUsuario);

// Calcular el contador de notificaciones no leídas
$contadorNoLeidas = ContarNotificacionesNoLeidas($link, $vUsuario);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Historia Clínica</title>
    <link rel="stylesheet" href="../src/css/lib/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../src/css/lib/fontawesome/css/all.css">
    <link rel="stylesheet" href="../src/css/admin.css?v=3.8">
    <link rel="stylesheet" href="../src/css/informe_paciente.css?v=1.1">
    <style>
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
                <?php if ($row['sexo'] == 'Masculino') { ?>
                    <img src="../src/img/odontologo.png" class="rounded-circle" width="150">
                <?php } elseif ($row['sexo'] == 'Femenino') { ?>
                    <img src="../src/img/odontologa.png" class="rounded-circle" width="150">
                <?php } ?>
                <h3 class="name"><?php echo htmlspecialchars(utf8_decode($row['nombreD'] . ' ' . $row['apellido'])); ?></h3>
                <span class="country">Barbosa Santander</span>
            </div>
            <div class="nav-menu">
                <ul>
                    <li><a href="inicioAdmin.php"><span class="icon-location-arrow mr-3"></span><i class="far fa-calendar-check"></i> Citas</a></li>
                    <li><a href="doctores.php"><span class="icon-location-arrow mr-3"></span><i class="fas fa-user-md"></i> Odontólogos</a></li>
                    <li><a href="calendar.php"><span class="icon-pie-chart mr-3"></span><i class="far fa-calendar-alt"></i> Calendario</a></li>
                    <li class="active"><a href="historia_clinica.php"><span class="icon-pie-chart mr-3"></span><i class="far fa-calendar-alt"></i> Historia Clínica</a></li>
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
                                <li class="breadcrumb-item"><a href="historia_clinica.php">Historia Clínica</a></li>
                                <li class="breadcrumb-item active">Ver Historia</li>
                            </ol>

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
                                            <p><strong>Lugar y Dirección de Residencia:</strong> <?php echo htmlspecialchars($patient['lugar_direccion_residencia'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Datos Adicionales</h5>
                                            <p><strong>Nº de Documento:</strong> <?php echo htmlspecialchars($patient['cedula'] ?? 'N/A'); ?></p>
                                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($patient['telefono'] ?? 'N/A'); ?></p>
                                            <p><strong>EPS:</strong> <?php echo htmlspecialchars($patient['eps'] ?? 'N/A'); ?></p>
                                            <p><strong>Género:</strong> <?php echo htmlspecialchars($patient['sexo'] ?? 'N/A'); ?></p>
                                            <p><strong>Ocupación:</strong> <?php echo htmlspecialchars($patient['ocupacion'] ?? 'N/A'); ?></p>
                                            <p><strong>Estado Civil:</strong> <?php echo htmlspecialchars($patient['estado_civil'] ?? 'N/A'); ?></p>
                                            <p><strong>Emergencia (Nombre):</strong> <?php echo htmlspecialchars($patient['emergencia_nombre'] ?? 'N/A'); ?></p>
                                            <p><strong>Teléfono de Emergencia:</strong> <?php echo htmlspecialchars($patient['emergencia_telefono'] ?? 'N/A'); ?></p>
                                            <?php if ($age < 18) { ?>
                                                <p><strong>Acompañante (Nombre):</strong> <?php echo htmlspecialchars($patient['menor_acompanante'] ?? 'N/A'); ?></p>
                                                <p><strong>Parentesco:</strong> <?php echo htmlspecialchars($patient['menor_parentesco'] ?? 'N/A'); ?></p>
                                                <p><strong>Teléfono Acompañante:</strong> <?php echo htmlspecialchars($patient['menor_telefono'] ?? 'N/A'); ?></p>
                                            <?php } ?>
                                            <p><strong>Tipo de Sangre:</strong> <?php echo htmlspecialchars($patient['tipo_sangre'] ?? 'N/A'); ?></p>
                                            <p><strong>Alertas Médicas:</strong> <?php echo htmlspecialchars($patient['alertas_medicas'] ?? 'N/A'); ?></p>
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
                                                <td><?php echo (isset($patient['historia_cardiovasculares']) && $patient['historia_cardiovasculares'] == 'Sí') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                                <td><?php echo (isset($patient['historia_cardiovasculares']) && $patient['historia_cardiovasculares'] == 'No') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                            </tr>
                                            <tr>
                                                <td>2. Enfermedades Hemorrágicas</td>
                                                <td><?php echo (isset($patient['historia_hemorragicas']) && $patient['historia_hemorragicas'] == 'Sí') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                                <td><?php echo (isset($patient['historia_hemorragicas']) && $patient['historia_hemorragicas'] == 'No') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                            </tr>
                                            <tr>
                                                <td>3. Enfermedades Dermatológicas</td>
                                                <td><?php echo (isset($patient['historia_dermatologicas']) && $patient['historia_dermatologicas'] == 'Sí') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                                <td><?php echo (isset($patient['historia_dermatologicas']) && $patient['historia_dermatologicas'] == 'No') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                            </tr>
                                            <tr>
                                                <td>4. Enfermedades Mentales</td>
                                                <td><?php echo (isset($patient['historia_mentales']) && $patient['historia_mentales'] == 'Sí') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                                <td><?php echo (isset($patient['historia_mentales']) && $patient['historia_mentales'] == 'No') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                            </tr>
                                            <tr>
                                                <td>5. Diabetes</td>
                                                <td><?php echo (isset($patient['historia_diabetes']) && $patient['historia_diabetes'] == 'Sí') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                                <td><?php echo (isset($patient['historia_diabetes']) && $patient['historia_diabetes'] == 'No') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                            </tr>
                                            <tr>
                                                <td>6. Cáncer</td>
                                                <td><?php echo (isset($patient['historia_cancer']) && $patient['historia_cancer'] == 'Sí') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                                <td><?php echo (isset($patient['historia_cancer']) && $patient['historia_cancer'] == 'No') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                            </tr>
                                            <tr>
                                                <td>7. Artritis</td>
                                                <td><?php echo (isset($patient['historia_artritis']) && $patient['historia_artritis'] == 'Sí') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                                <td><?php echo (isset($patient['historia_artritis']) && $patient['historia_artritis'] == 'No') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                            </tr>
                                            <tr>
                                                <td>8. Alergias</td>
                                                <td><?php echo (isset($patient['historia_alergias']) && $patient['historia_alergias'] == 'Sí') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                                <td><?php echo (isset($patient['historia_alergias']) && $patient['historia_alergias'] == 'No') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                            </tr>
                                            <tr>
                                                <td>9. Cirugías</td>
                                                <td><?php echo (isset($patient['historia_cirugias']) && $patient['historia_cirugias'] == 'Sí') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                                <td><?php echo (isset($patient['historia_cirugias']) && $patient['historia_cirugias'] == 'No') ? '<i class="fas fa-check"></i>' : ''; ?></td>
                                            </tr>
                                            <tr>
                                                <td>10. Otros</td>
                                                <td colspan="2"><?php echo htmlspecialchars($patient['historia_otros'] ?? 'N/A'); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Información de la Cita -->
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

                            <!-- Informe Médico -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-notes-medical"></i> Informe Médico
                                </div>
                                <div class="card-body patient-info">
                                    <p><strong>Examen Intraoral:</strong> <?php echo htmlspecialchars($medical_report['examen_intraoral'] ?? 'N/A'); ?></p>
                                    <p><strong>Examen Extraoral:</strong> <?php echo htmlspecialchars($medical_report['examen_extraoral'] ?? 'N/A'); ?></p>
                                    <p><strong>Examen ATM:</strong> <?php echo htmlspecialchars($medical_report['examen_atm'] ?? 'N/A'); ?></p>
                                    <p><strong>Radiografía:</strong></p>
                                    <?php if (!empty($medical_report['radiografia'])) { ?>
                                        <img src="../uploads/radiografias/<?php echo htmlspecialchars($medical_report['radiografia']); ?>" class="uploaded-image" alt="Radiografía" width="200">
                                    <?php } else { ?>
                                        <p>N/A</p>
                                    <?php } ?>
                                    <p><strong>Foto de la Boca:</strong></p>
                                    <?php if (!empty($medical_report['foto_boca'])) { ?>
                                        <img src="../uploads/fotos_boca/<?php echo htmlspecialchars($medical_report['foto_boca']); ?>" class="uploaded-image" alt="Foto de la Boca" width="200">
                                    <?php } else { ?>
                                        <p>N/A</p>
                                    <?php } ?>
                                    <p><strong>Evolución:</strong> <?php echo htmlspecialchars($medical_report['evolucion'] ?? 'N/A'); ?></p>
                                    <p><strong>Diagnóstico:</strong> <?php echo htmlspecialchars($medical_report['diagnostico'] ?? 'N/A'); ?></p>
                                    <p><strong>Plan de Tratamiento:</strong> <?php echo htmlspecialchars($medical_report['plan_tratamiento'] ?? 'N/A'); ?></p>
                                    <p><strong>Costo:</strong> <?php echo nl2br(htmlspecialchars($medical_report['costo'] ?? 'N/A')); ?></p>
                                </div>
                            </div>

                            <!-- Odontograma -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-tooth"></i> Odontograma
                                </div>
                                <div class="card-body patient-info">
                                    <?php if (!empty($odontogram_data)) { ?>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Diente</th>
                                                    <th>Condición</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($odontogram_data as $entry) { ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($entry['tooth']); ?></td>
                                                        <td><?php echo htmlspecialchars($entry['condition']); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    <?php } else { ?>
                                        <p>No hay datos de odontograma disponibles.</p>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-cog"></i> Acciones
                                </div>
                                <div class="card-body patient-info">
                                    <div class="button-container">
                                        <form action="descargar_historia.php" method="POST">
                                            <input type="hidden" name="id_cita" value="<?php echo htmlspecialchars($id_cita); ?>">
                                            <input type="hidden" name="id_paciente" value="<?php echo htmlspecialchars($id_paciente); ?>">
                                            <button type="submit" class="btn btn-custom-secondary">
                                                <i class="fas fa-file-pdf"></i> Descargar Historia Completa
                                            </button>
                                        </form>
                                        <a href="generate_odontogram_pdf.php?patient_id=<?php echo htmlspecialchars($id_paciente); ?>" class="btn btn-custom-secondary">
                                            <i class="fas fa-file-pdf"></i> Descargar Odontograma
                                        </a>
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
    <!-- Script para actualizar el contador de notificaciones -->
    <script>
        // Actualización en tiempo real del contador de notificaciones
        function actualizarContadorNotificaciones() {
            const idDoctor = <?php echo $vUsuario; ?>;
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