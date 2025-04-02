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
$resultadoCitas = MostrarCitas($link, $vUsuario);
$contadorNoLeidas = ContarNotificacionesNoLeidas($link, $vUsuario);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../src/img/logo.png" type="image/png">
    <link rel="stylesheet" href="../src/css/lib/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../src/css/admin.css?v=3.8">
    <link rel="stylesheet" href="../src/css/custom_styles.css">
    <link rel="stylesheet" href="../src/css/lib/fontawesome/css/all.css">
    <link rel="stylesheet" href="../src/js/lib/datatable/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../src/js/lib/datatable/css/responsive.dataTables.min.css">
    <title>Citas</title>
    <script type="text/javascript">
        function confirmation() {
            return confirm("¿Realmente desea eliminar esta cita?");
        }
    </script>
    <style>
        .action-buttons {
            display: flex;
            gap: 3px;
            white-space: nowrap;
        }
        .action-buttons a, .action-buttons button {
            font-size: 0.85rem;
            padding: 2px;
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
        @media (max-width: 768px) {
            .btn-add-appointment {
                font-size: 0.8rem;
                padding: 5px 10px;
            }
            .action-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
            .action-buttons a, .action-buttons button {
                font-size: 1rem;
                padding: 5px;
                line-height: 1;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 30px;
                min-height: 30px;
                border-radius: 5px;
            }
            .action-buttons .cancel-appointment {
                cursor: pointer;
                touch-action: manipulation;
            }
        }
        .loading-container {
            text-align: center;
        }
        .spinner {
            display: inline-block;
            position: relative;
            width: 40px;
            height: 40px;
        }
        .spinner .circle {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid transparent;
            border-top-color: #6f42c1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        .spinner .circle-1 {
            animation-delay: 0s;
        }
        .spinner .circle-2 {
            animation-delay: -0.3s;
        }
        .spinner .circle-3 {
            animation-delay: -0.6s;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loading-text {
            margin-top: 10px;
            color: #6f42c1;
        }
        .modal {
            z-index: 1055 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }
    </style>
</head>

<body>
    <!-- Modal para confirmación de cancelación -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #6f42c1; color: white;">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirmación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="confirmationMessage">
                    ¿Desea cancelar esta cita y notificar al paciente?
                </div>
                <div class="modal-body" id="loadingIndicator" style="display: none;">
                    <div class="loading-container">
                        <div class="spinner">
                            <div class="circle circle-1"></div>
                            <div class="circle circle-2"></div>
                            <div class="circle circle-3"></div>
                        </div>
                        <p class="loading-text">Procesando, por favor espera...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmAction">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para alertas (éxito/error) -->
    <div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #6f42c1; color: white;">
                    <h5 class="modal-title" id="alertModalLabel">Notificación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body" id="alertMessage">
                    Cita cancelada y notificación enviada.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

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
                    <li class="active"><a href="inicioAdmin.php"><span class="icon-location-arrow mr-3"></span><i class="far fa-calendar-check"></i> Citas</a></li>
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
                                    <div class="col-md-12 text-info">
                                        <div class="p-3 mb-2 bg-primary text-white text-center">Citas</div>
                                        <div class="mb-3 text-right">
                                            <a href="agregar_cita.php" class="btn btn-success btn-add-appointment">
                                                <i class="fas fa-plus"></i> Agregar Nueva Cita
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
                                                    <th>Nombre completo</th>
                                                    <th>Edad</th>
                                                    <th>Consulta</th>
                                                    <th>Fecha</th>
                                                    <th>Hora</th>
                                                    <th>Estado</th>
                                                    <th>Diagnóstico</th>
                                                    <th>Editar</th>
                                                    <th>Informe</th>
                                                    <th>Eliminar</th>
                                                    <th>Cancelar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = mysqli_fetch_array($resultadoCitas, MYSQLI_ASSOC)) { ?>
                                                    <tr>
                                                        <td><?php echo $row['nombre'] . ' ' . $row['apellido']; ?></td>
                                                        <td><?php echo $row['años']; ?></td>
                                                        <td><?php echo $row['tipo']; ?></td>
                                                        <td><?php echo $row['fecha_cita']; ?></td>
                                                        <td><?php echo $row['hora_cita']; ?></td>
                                                        <td><?php echo $row['estado'] == 'A' ? "Realizada" : "Pendiente"; ?></td>
                                                        <td><?php echo $row['descripcion']; ?></td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <a class="button is-info" data-toggle="tooltip" data-placement="top" title="Editar" href="./realizar_consulta.php?accion=UDT&id=<?php echo $row['id_cita']; ?>">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <a class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Ver Informe" href="./informe.php?id=<?php echo $row['id_paciente']; ?>">
                                                                    <i class="fas fa-file-alt"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <a class="button text-danger" data-toggle="tooltip" data-placement="top" title="Anular" href="../crud/realizar_consultasUPDATE.php?accion=DLT&id=<?php echo $row['id_cita']; ?>&estado=<?php echo $row['estado']; ?>" onclick="return confirmation()">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <?php if ($row['estado'] != 'A') { ?>
                                                                    <button class="button text-warning cancel-appointment" data-id="<?php echo $row['id_cita']; ?>" data-toggle="tooltip" data-placement="top" title="Cancelar y Notificar">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                <?php } ?>
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
    <script src="../src/js/lib/datatable/datatable.js"></script>
    <script src="../src/js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Cerrar alertas
            (document.querySelectorAll('.alert .delete') || []).forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
            });

            // Variable para evitar eventos duplicados
            let isProcessing = false;

            // Detectar si es un dispositivo móvil
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const eventType = isMobile ? 'touchstart' : 'click';

            // Usar delegación de eventos para manejar clics y toques en botones generados dinámicamente
            $(document).on(eventType, '.cancel-appointment', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Evitar que el evento se dispare múltiples veces
                if (isProcessing) {
                    return;
                }
                isProcessing = true;

                const idCita = $(this).data('id');

                // Mostrar el modal de confirmación con un pequeño retraso para estabilizar
                setTimeout(function() {
                    $('#confirmationModal').modal('show');
                }, 100);

                // Manejar el evento de confirmación
                $('#confirmAction').off('click').on('click', function() {
                    $('#loadingIndicator').css('display', 'block').addClass('show');
                    $('#confirmationMessage').hide();
                    $('#confirmAction').prop('disabled', true);

                    let startTime = Date.now();
                    $.ajax({
                        url: '../php/toggle_unavailable.php',
                        type: 'POST',
                        data: {
                            id_cita: idCita,
                            id_doctor: <?php echo $vUsuario; ?>,
                            action: 'cancel_single'
                        },
                        success: function(response) {
                            $('#confirmationModal').modal('hide');
                            try {
                                var res = typeof response === 'string' ? JSON.parse(response) : response;
                                if (res.status === 'success') {
                                    // Eliminar la fila de la tabla
                                    $(`button[data-id="${idCita}"]`).closest('tr').remove();
                                    let message = 'Cita cancelada y notificación enviada.';
                                    if (res.invalid_emails && res.invalid_emails.length > 0) {
                                        message += '\nAdvertencia: No se pudo enviar la notificación al siguiente correo inválido: ' + res.invalid_emails.join(', ');
                                    }
                                    $('#alertMessage').text(message);
                                    $('#alertModal').modal('show');
                                } else {
                                    $('#alertMessage').text(res.message || 'Error al cancelar la cita.');
                                    $('#alertModal').modal('show');
                                }
                            } catch (e) {
                                $('#alertMessage').text('Error al procesar la respuesta del servidor: ' + e.message);
                                $('#alertModal').modal('show');
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#alertMessage').text('Error en la conexión: ' + error);
                            $('#alertModal').modal('show');
                        },
                        complete: function() {
                            let elapsedTime = Date.now() - startTime;
                            let delay = elapsedTime < 1000 ? 1000 - elapsedTime : 0;
                            setTimeout(function() {
                                $('#loadingIndicator').removeClass('show').css('display', 'none');
                                $('#confirmationMessage').show();
                                $('#confirmAction').prop('disabled', false);
                                isProcessing = false;
                            }, delay);
                        }
                    });
                });

                // Resetear la bandera si el modal se cierra sin confirmar
                $('#confirmationModal').on('hidden.bs.modal', function() {
                    isProcessing = false;
                });
            });

            // Evitar que el modal se cierre al tocar fuera rápidamente
            $('#confirmationModal').on('show.bs.modal', function() {
                $(this).data('bs.modal')._config.backdrop = 'static';
                $(this).data('bs.modal')._config.keyboard = false;
            });

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
                            return;
                        }
                        const badge = $('.badge');
                        if (data.no_leidas > 0) {
                            badge.text(data.no_leidas).show();
                        } else {
                            badge.hide();
                        }
                    },
                    error: function(xhr, status, error) {}
                });
            }

            // Actualizar cada 30 segundos
            setInterval(actualizarContadorNotificaciones, 30000);
            actualizarContadorNotificaciones();
        });
    </script>
</body>

</html>