<?php
session_start();
include_once('php/conexionDB.php');
include_once('php/consultas.php');

// Validar la sesión del paciente
if (!isset($_SESSION['id_paciente'])) {
    $_SESSION['MensajeTexto'] = "Error acceso al sistema: Sesión no iniciada.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: index.php");
    exit();
}

$vUsuario = $_SESSION['id_paciente'];
$row = consultarPaciente($link, $vUsuario);

// Obtener citas pendientes e historial
$citas_pendientes = CitasPendientesFPDF($link, $vUsuario);
$citas_realizadas = CitasRealizadasFPDF($link, $vUsuario);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="src/img/logo.png" type="image/png">
    <link rel="stylesheet" href="src/css/lib/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="src/css/tooplate-style.css">
    <link rel="stylesheet" href="src/css/lib/fontawesome/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="src/css/gestionar_citas.css">
    <title>Gestionar Citas - Consultorio Emily Bernal</title>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="row">
                <div class="col-md-4 text-left">
                    <i class="fas fa-phone-alt"></i> 3105547320
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-clock"></i> 8:30 AM - 6:00 PM (Lunes - Sábado)
                </div>
                <div class="col-md-4 text-right">
                    <i class="fas fa-envelope"></i> <a href="mailto:emilybernal902@gmail.com">emilybernal902@gmail.com</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="principal.php">EMILY BERNAL</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"><i class="fas fa-bars"></i></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="principal.php#top">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="principal.php#about">Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="principal.php#team">Dentistas</a></li>
                    <li class="nav-item"><a class="nav-link" href="principal.php#perfil">Perfil</a></li>
                    <li class="nav-item active"><a class="nav-link" href="gestionar_citas.php">Mis Citas</a></li>
                    <li class="nav-item"><a class="nav-link" href="principal.php#google-map">Contacto</a></li>
                    <li class="nav-item appointment-btn"><a href="php/cerrar.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container">
        <h2>Gestionar Mis Citas</h2>

        <!-- Botón de regreso para móviles -->
        <div class="back-to-home d-block d-lg-none mb-4">
            <a href="principal.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
        </div>

        <!-- Mensajes de éxito o error -->
        <?php if (isset($_SESSION['MensajeTexto'])) { ?>
            <div class="alert <?php echo $_SESSION['MensajeTipo']; ?>" role="alert">
                <?php echo $_SESSION['MensajeTexto']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <?php
            $_SESSION['MensajeTexto'] = null;
            $_SESSION['MensajeTipo'] = null;
            ?>
        <?php } ?>

        <!-- Citas Pendientes -->
        <div class="table-container">
            <div class="section-title">Citas Pendientes</div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Consulta</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Doctor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($citas_pendientes) > 0) { ?>
                            <?php while ($cita = mysqli_fetch_assoc($citas_pendientes)) { ?>
                                <tr>
                                    <td data-label="Consulta"><?php echo htmlspecialchars($cita['tipo']); ?></td>
                                    <td data-label="Fecha"><?php echo htmlspecialchars($cita['fecha_cita']); ?></td>
                                    <td data-label="Hora"><?php echo htmlspecialchars($cita['hora_cita']); ?></td>
                                    <td data-label="Doctor"><?php echo htmlspecialchars($cita['nombreD']); ?></td>
                                    <td data-label="Acciones">
                                        <a href="crud/cancelar_cita.php?id=<?php echo $cita['id_cita']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas cancelar esta cita?');">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-center">No tienes citas pendientes.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="text-right p-3">
                <a href="Reportes/reporte.php" target="_blank" class="btn btn-info">
                    <i class="fas fa-download"></i> Descargar PDF
                </a>
            </div>
        </div>

        <!-- Historial de Citas -->
        <div class="table-container">
            <div class="section-title">Historial de Citas</div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Consulta</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Doctor</th>
                            <th>Diagnóstico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($citas_realizadas) > 0) { ?>
                            <?php while ($cita = mysqli_fetch_assoc($citas_realizadas)) { ?>
                                <tr>
                                    <td data-label="Consulta"><?php echo htmlspecialchars($cita['tipo']); ?></td>
                                    <td data-label="Fecha"><?php echo htmlspecialchars($cita['fecha_cita']); ?></td>
                                    <td data-label="Hora"><?php echo htmlspecialchars($cita['hora_cita']); ?></td>
                                    <td data-label="Doctor"><?php echo htmlspecialchars($cita['nombreD']); ?></td>
                                    <td data-label="Diagnóstico"><?php echo htmlspecialchars($cita['descripcion'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-center">No tienes citas realizadas.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="text-right p-3">
                <a href="Reportes/reporteH.php" target="_blank" class="btn btn-info">
                    <i class="fas fa-download"></i> Descargar PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="src/js/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="src/css/lib/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>