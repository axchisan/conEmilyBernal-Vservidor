<?php
session_start();

// Validar la sesión
if (!isset($_SESSION['id_doctor'])) {
    $_SESSION['MensajeTexto'] = "Error acceso al sistema: Sesión no iniciada.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ../index.php");
    exit();
}

// Verificar si se recibió el ID y cargar datos necesarios
if (!empty($_GET['id'])) {
    include_once('../php/conexionDB.php');
    include_once('../php/consultas.php');
    $id = $_GET['id'];
    $row = ConsultarCitas($link, $id);
    $vUsuario = $_SESSION['id_doctor'];
    $row1 = consultarDoctor($link, $vUsuario);
    
    // Calcular el contador de notificaciones no leídas
    $contadorNoLeidas = ContarNotificacionesNoLeidas($link, $vUsuario);
} else {
    // No se maneja el caso de ID vacío..
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../src/img/logo.png" type="image/png">
    <link rel="stylesheet" href="../src/css/lib/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../src/css/admin.css?v=3.8">
    <link rel="stylesheet" href="../src/realizar_consulta.css">
    <link rel="stylesheet" href="../src/css/lib/fontawesome/css/all.css">
    <link rel="stylesheet" href="../src/css/custom_styles.css">
    <link rel="stylesheet" href="../src/js/lib/datatable/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../src/js/lib/datatable/css/responsive.dataTables.min.css">
    <title>Realizar Cita</title>
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
                <?php if ($row1['sexo'] == 'Masculino') { ?>
                    <img src="../src/img/odontologo.png" class="rounded-circle" width="150">
                <?php } elseif ($row1['sexo'] == 'Femenino') { ?>
                    <img src="../src/img/odontologa.png" class="rounded-circle" width="150">
                <?php } ?>
                <h3 class="name"><?php echo utf8_decode($row1['nombreD'] . ' ' . $row1['apellido']); ?></h3>
                <span class="country">Barbosa Santander</span>
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
                                <li class="breadcrumb-item active">Inicio</li>
                            </ol>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="container">
                                        <div class="p-3 mb-2 bg-info text-white text-center">Realizar diagnóstico sobre la cita</div>
                                        <form action="../crud/realizar_consultasUPDATE.php?accion=UDT" method="POST" enctype="multipart/form-data" autocomplete="off" class="form-horizontal">
                                            <input type="hidden" name="id" id="id" value="<?php echo $row['id_cita']; ?>">

                                            <div class="card">
                                                <div class="card-body">
                                                    <h5 class="card-title">Diagnóstico</h5>
                                                    <textarea class="form-control" name="Diagnostico" placeholder="Escribe el diagnóstico aquí..." rows="5" required></textarea>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="row" style="margin-top: 5%;">
                                                    <div class="col-md-4">
                                                        <label for="descripcion">Descripción</label>
                                                        <textarea class="form-control" name="Descripción" placeholder="Escribe la descripción aquí..." rows="5" required></textarea>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="medicina">Medicina</label>
                                                        <textarea class="form-control" name="Medicina" placeholder="Escribe la medicina opcional aquí..." rows="5"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-4" style="margin-top: 5%;">
                                                    <button class="btn btn-success btn-lg" type="submit" name="guardar" value="Guardar">
                                                        <i class="far fa-save"></i> Guardar
                                                    </button>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-2 col-md-offset-5">
                                                        <a href="./inicioAdmin.php"><i class="fas fa-history"></i> Atrás</a>
                                                    </div>
                                                </div>
                                            </div>
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