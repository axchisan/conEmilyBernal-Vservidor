<?php
session_start();
date_default_timezone_set('America/Bogota');
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
$row = consultarDoctor($link, $vUsuario);

// Determinar si mostrar todas las notificaciones o solo las no leídas
$mostrar_todas = isset($_GET['mostrar']) && $_GET['mostrar'] == 'todas';
$resultadoNotificaciones = ObtenerNotificaciones($link, $vUsuario, $mostrar_todas);

// Manejar acciones
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id_notificacion = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    if ($_GET['accion'] == 'DLT') {
        if (EliminarNotificacion($link, $id_notificacion, $vUsuario)) {
            $_SESSION['MensajeTexto'] = "Notificación eliminada con éxito.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-success text-white";
        } else {
            $_SESSION['MensajeTexto'] = "Error al eliminar la notificación.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        }
    } elseif ($_GET['accion'] == 'READ') {
        if (MarcarNotificacionLeida($link, $id_notificacion, $vUsuario)) {
            $_SESSION['MensajeTexto'] = "Notificación marcada como leída.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-info text-white";
        } else {
            $_SESSION['MensajeTexto'] = "Error al marcar la notificación como leída.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        }
    }
    header("Location: notificaciones.php" . ($mostrar_todas ? "?mostrar=todas" : ""));
    exit();
}

// Eliminar todas las notificaciones leídas o todas
if (isset($_GET['accion'])) {
    if ($_GET['accion'] == 'DLT_LEIDAS') {
        if (EliminarNotificacionesLeidas($link, $vUsuario)) {
            $_SESSION['MensajeTexto'] = "Notificaciones leídas eliminadas con éxito.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-success text-white";
        } else {
            $_SESSION['MensajeTexto'] = "Error al eliminar las notificaciones leídas.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        }
    } elseif ($_GET['accion'] == 'DLT_TODAS') {
        if (EliminarTodasNotificaciones($link, $vUsuario)) {
            $_SESSION['MensajeTexto'] = "Todas las notificaciones eliminadas con éxito.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-success text-white";
        } else {
            $_SESSION['MensajeTexto'] = "Error al eliminar todas las notificaciones.";
            $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        }
    }
    header("Location: notificaciones.php" . ($mostrar_todas ? "?mostrar=todas" : ""));
    exit();
}

$contadorNoLeidas = ContarNotificacionesNoLeidas($link, $vUsuario);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../src/img/logo.png" type="image/png">
    <link rel="stylesheet" href="../src/css/lib/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../src/css/admin.css?v=3.9">
    <link rel="stylesheet" href="../src/css/custom_styles.css">
    <link rel="stylesheet" href="../src/css/lib/fontawesome/css/all.css">
    <link rel="stylesheet" href="../src/js/lib/datatable/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../src/js/lib/datatable/css/responsive.dataTables.min.css">
    <title>Notificaciones</title>
    <script type="text/javascript">
        function confirmation() {
            return confirm("¿Realmente desea eliminar esta notificación?");
        }

        function confirmDeleteLeidas() {
            return confirm("¿Realmente desea eliminar todas las notificaciones leídas?");
        }

        function confirmDeleteTodas() {
            return confirm("¿Realmente desea eliminar todas las notificaciones?");
        }
    </script>
    <style>
        .action-buttons {
            display: flex;
            gap: 3px;
            white-space: nowrap;
        }

        .action-buttons a {
            font-size: 0.85rem;
            padding: 2px;
        }

        .btn-action {
            font-size: 0.9rem;
            padding: 5px 10px;
            margin-left: 5px;
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

        /* Ajustes para pantallas pequeñas */
        @media (max-width: 768px) {
            .action-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }

            .action-buttons a {
                font-size: 1rem;
                padding: 5px;
            }

            .btn-action {
                font-size: 0.8rem;
                padding: 4px 8px;
                margin-left: 0;
                margin-bottom: 5px;
                width: 100%;
                text-align: center;
            }

            .mb-3.text-right {
                text-align: center !important;
            }

            .mb-3.text-right .btn-action {
                display: block;
            }

            /* Ajustar el diseño de la tabla para que sea más responsive */
            #example {
                width: 100% !important;
            }

            #example thead {
                display: none;
            }

            #example tbody tr {
                display: block;
                margin-bottom: 15px;
                border-bottom: 2px solid #e0e0e0;
            }

            #example tbody td {
                display: block;
                text-align: left;
                font-size: 0.8rem;
                padding: 8px 15px;
                border-bottom: 1px solid #e0e0e0;
                position: relative;
                word-wrap: break-word;
                white-space: normal;
            }

            #example tbody td:before {
                content: attr(data-label);
                font-weight: bold;
                display: inline-block;
                width: 40%;
                padding-right: 10px;
                color: #333;
            }

            #example tbody td:last-child {
                border-bottom: 0;
            }

            /* Ajustar específicamente la celda "Mensaje" */
            #example tbody td[data-label="Mensaje"] {
                display: flex;
                flex-direction: column;
                max-width: 100%;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: normal;
            }

            #example tbody td[data-label="Mensaje"]:before {
                width: auto;
                padding-right: 0;
                margin-bottom: 5px;
            }

            #example tbody td[data-label="Mensaje"] span.message-content {
                margin-left: 0;
                display: block;
            }
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
                <h3 class="name"><?php echo utf8_decode($row['nombreD'] . ' ' . $row['apellido']); ?></h3>
                <span class="country">Barbosa Santander</span>
            </div>
            <div class="nav-menu">
                <ul>
                    <li><a href="inicioAdmin.php"><span class="icon-location-arrow mr-3"></span><i class="far fa-calendar-check"></i> Citas</a></li>
                    <li><a href="doctores.php"><span class="icon-location-arrow mr-3"></span><i class="fas fa-user-md"></i> Odontólogos</a></li>
                    <li><a href="calendar.php"><span class="icon-pie-chart mr-3"></span><i class="far fa-calendar-alt"></i> Calendario</a></li>
                    <li><a href="historia_clinica.php"><span class="icon-pie-chart mr-3"></span><i class="far fa-calendar-alt"></i> Historia Clínica</a></li>
                    <li class="active"><a href="notificaciones.php"><span class="icon-bell mr-3"></span><i class="fas fa-bell"></i> Notificaciones <?php if ($contadorNoLeidas > 0) { ?><span class="badge"><?php echo $contadorNoLeidas; ?></span><?php } ?></a></li>
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
                                <li class="breadcrumb-item active">Notificaciones</li>
                            </ol>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12 text-info">
                                        <div class="p-3 mb-2 bg-primary text-white text-center">Notificaciones</div>
                                        <div class="mb-3 text-right">
                                            <a href="notificaciones.php<?php echo $mostrar_todas ? '' : '?mostrar=todas'; ?>" class="btn btn-info btn-action">
                                                <i class="fas fa-eye"></i> <?php echo $mostrar_todas ? 'Ver No Leídas' : 'Ver Todas'; ?>
                                            </a>
                                            <a href="notificaciones.php?accion=DLT_LEIDAS<?php echo $mostrar_todas ? '&mostrar=todas' : ''; ?>" class="btn btn-warning btn-action" onclick="return confirmDeleteLeidas()">
                                                <i class="fas fa-trash-alt"></i> Eliminar Leídas
                                            </a>
                                            <a href="notificaciones.php?accion=DLT_TODAS<?php echo $mostrar_todas ? '&mostrar=todas' : ''; ?>" class="btn btn-danger btn-action" onclick="return confirmDeleteTodas()">
                                                <i class="fas fa-trash"></i> Eliminar Todas
                                            </a>
                                        </div>
                                        <div class="col-md-7">
                                            <?php if (isset($_SESSION['MensajeTexto'])) { ?>
                                                <div class="alert <?php echo $_SESSION['MensajeTipo']; ?>" role="alert">
                                                    <?php echo $_SESSION['MensajeTexto']; ?>
                                                    <button class="delete"><i class="fa fa-times"></i></button>
                                                </div>
                                            <?php
                                                $_SESSION['MensajeTexto'] = null;
                                                $_SESSION['MensajeTipo'] = null;
                                            } ?>
                                        </div>
                                        <table id="example" class="table table-striped nowrap responsive">
                                            <thead>
                                                <tr>
                                                    <th>Fecha/Hora</th>
                                                    <th>Tipo</th>
                                                    <th>Mensaje</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="notificaciones-body">
                                                <?php while ($notificacion = mysqli_fetch_assoc($resultadoNotificaciones)) { ?>
                                                    <tr data-id="<?php echo $notificacion['id_notificacion']; ?>">
                                                        <td data-label="Fecha/Hora"><?php echo date('d/m/Y h:i A', strtotime($notificacion['fecha'])); ?></td>
                                                        <td data-label="Tipo"><?php echo htmlspecialchars($notificacion['tipo']); ?></td>
                                                        <td data-label="Mensaje">
                                                            <span class="message-content"><?php echo htmlspecialchars($notificacion['mensaje']); ?></span>
                                                        </td>
                                                        <td data-label="Estado"><?php echo $notificacion['leida'] ? 'Leída' : 'No Leída'; ?></td>
                                                        <td data-label="Acciones">
                                                            <div class="action-buttons">
                                                                <?php if (!$notificacion['leida']) { ?>
                                                                    <a class="button text-primary" data-toggle="tooltip" data-placement="top" title="Marcar como Leída" href="notificaciones.php?accion=READ&id=<?php echo $notificacion['id_notificacion']; ?><?php echo $mostrar_todas ? '&mostrar=todas' : ''; ?>">
                                                                        <i class="fas fa-check"></i>
                                                                    </a>
                                                                <?php } ?>
                                                                <a class="button text-danger" data-toggle="tooltip" data-placement="top" title="Eliminar" href="notificaciones.php?accion=DLT&id=<?php echo $notificacion['id_notificacion']; ?><?php echo $mostrar_todas ? '&mostrar=todas' : ''; ?>" onclick="return confirmation()">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Cargar scripts en el orden correcto -->
    <script src="../src/js/lib/datatable/js/jquery-3.5.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="../src/css/lib/bootstrap/js/bootstrap.min.js"></script>
    <script src="../src/js/lib/datatable/js/jquery.dataTables.min.js"></script>
    <script src="../src/js/lib/datatable/js/dataTables.responsive.min.js"></script>
    <!-- Comentamos datatable.js para evitar conflictos -->
    <!-- <script src="../src/js/lib/datatable/datatable.js"></script> -->
    <script src="../src/js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Cerrar alertas dinámicamente
            (document.querySelectorAll('.alert .delete') || []).forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
            });

            // Inicializar DataTables una sola vez
            let table = $('#example').DataTable({
                responsive: false, // Desactivar el responsive de DataTables porque usamos nuestro propio diseño
                ordering: false,
                searching: false,
                paging: false,
                info: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                }
            });

            // Actualización en tiempo real de notificaciones
            function actualizarNotificaciones() {
                const idDoctor = <?php echo $vUsuario; ?>;
                const mostrarTodas = <?php echo $mostrar_todas ? 'true' : 'false'; ?>;
                $.ajax({
                    url: '../php/get_notificaciones.php',
                    type: 'POST',
                    data: {
                        id_doctor: idDoctor,
                        mostrar_todas: mostrarTodas
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.error) {
                            console.error('Error al obtener notificaciones:', data.error);
                            return;
                        }

                        // Destruir la instancia de DataTables antes de actualizar
                        if ($.fn.DataTable.isDataTable('#example')) {
                            table.destroy();
                        }

                        // Actualizar el cuerpo de la tabla
                        const tbody = $('#notificaciones-body');
                        tbody.empty();
                        data.notificaciones.forEach(notificacion => {
                            const row = `
                                <tr data-id="${notificacion.id_notificacion}">
                                    <td data-label="Fecha/Hora">${notificacion.fecha}</td>
                                    <td data-label="Tipo">${notificacion.tipo}</td>
                                    <td data-label="Mensaje">
                                        <span class="message-content">${notificacion.mensaje}</span>
                                    </td>
                                    <td data-label="Estado">${notificacion.leida == 1 ? 'Leída' : 'No Leída'}</td>
                                    <td data-label="Acciones">
                                        <div class="action-buttons">
                                            ${notificacion.leida == 0 ? `
                                                <a class="button text-primary" data-toggle="tooltip" data-placement="top" title="Marcar como Leída" href="notificaciones.php?accion=READ&id=${notificacion.id_notificacion}${mostrarTodas ? '&mostrar=todas' : ''}">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            ` : ''}
                                            <a class="button text-danger" data-toggle="tooltip" data-placement="top" title="Eliminar" href="notificaciones.php?accion=DLT&id=${notificacion.id_notificacion}${mostrarTodas ? '&mostrar=todas' : ''}" onclick="return confirmation()">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            tbody.append(row);
                        });

                        // Reinicializar DataTables después de actualizar
                        table = $('#example').DataTable({
                            responsive: false,
                            ordering: false,
                            searching: false,
                            paging: false,
                            info: false,
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                            }
                        });

                        // Actualizar el contador de notificaciones no leídas
                        const badge = $('.badge');
                        if (data.no_leidas > 0) {
                            badge.text(data.no_leidas).show();
                        } else {
                            badge.hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al actualizar notificaciones:', error);
                    }
                });
            }

            // Actualizar cada 30 segundos
            setInterval(actualizarNotificaciones, 30000);
            // Llamada inicial
            actualizarNotificaciones();
        });
    </script>
</body>

</html>