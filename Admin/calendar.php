<?php
session_start();
include_once('../php/conexionDB.php');
include_once('../php/consultas.php');

if (!isset($_SESSION['id_doctor'])) {
    $_SESSION['MensajeTexto'] = "Error acceso al sistema no registrado.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ../index.php");
    exit;
}

$vUsuario = $_SESSION['id_doctor'];

$row = consultarDoctor($link, $vUsuario);
if (!$row) {
    die("No se encontraron datos del doctor con id_doctor = $vUsuario.");
}

// Calcular el contador de notificaciones no leídas
$contadorNoLeidas = ContarNotificacionesNoLeidas($link, $vUsuario);

// Obtener fechas no disponibles para el doctor
$stmtUnavailable = $link->prepare("SELECT unavailable_date FROM unavailable_dates WHERE id_doctor = ?");
$stmtUnavailable->bind_param("i", $vUsuario);
$stmtUnavailable->execute();
$resultadoUnavailable = $stmtUnavailable->get_result();
$unavailableDates = [];
while ($rowUnavailable = $resultadoUnavailable->fetch_assoc()) {
    $unavailableDates[] = $rowUnavailable['unavailable_date'];
}
$stmtUnavailable->close();

$resultadoDentistas = MostrarCitas($link, $vUsuario);

$stmtAppointments = $link->prepare("
    SELECT c.id_cita, c.fecha_cita, p.nombre AS paciente_nombre, co.tipo AS tipo_cita, c.hora_cita, d.nombreD AS doctor_nombre, c.id_doctor 
    FROM citas c 
    JOIN pacientes p ON c.id_paciente = p.id_paciente 
    JOIN doctor d ON c.id_doctor = d.id_doctor 
    JOIN consultas co ON c.id_consultas = co.id_consultas 
    WHERE c.id_doctor = ?
");
$stmtAppointments->bind_param("i", $vUsuario);
$stmtAppointments->execute();
$resultadoAppointments = $stmtAppointments->get_result();
$appointmentsData = [];
while ($rowAppointment = $resultadoAppointments->fetch_assoc()) {
    $appointmentsData[] = [
        'id_cita' => $rowAppointment['id_cita'],
        'fecha_cita' => $rowAppointment['fecha_cita'],
        'nombre' => $rowAppointment['paciente_nombre'],
        'tipo' => $rowAppointment['tipo_cita'],
        'hora_cita' => $rowAppointment['hora_cita'],
        'nombreD' => $rowAppointment['doctor_nombre'],
        'id_doctor' => $rowAppointment['id_doctor']
    ];
}
$stmtAppointments->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="../src/img/logo.png" type="image/png" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../src/css/admin.css?v=3.8">
    <link rel="stylesheet" href="../src/css/lib/fontawesome/css/all.css">
    <link rel='stylesheet' type='text/css' href='../src/css/fullcalendar.css' />
    <link rel="stylesheet" href="../src/css/custom_styles.css">

    <script src="https://code.jquery.com/jquery-3.2.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script type='text/javascript' src='../src/js/lib/FullCalendar/moment.min.js'></script>
    <script type='text/javascript' src='../src/js/lib/FullCalendar/fullcalendar.min.js'></script>
    <script type='text/javascript' src='../src/js/lib/FullCalendar/locale/es.js'></script>

    <title>Calendario</title>
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
    <!-- Modal para confirmación (marcar/desmarcar día) -->
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
                    ¿Desea marcar este día como no disponible?
                </div>
                <!-- Indicador de carga -->
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
                    Día marcado como no disponible.
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
                <?php
                if ($row['sexo'] == 'Masculino') {
                ?>
                    <img src="../src/img/odontologo.png" class="rounded-circle" width="150">
                <?php
                } elseif ($row['sexo'] == 'Femenino') {
                ?>
                    <img src="../src/img/odontologa.png" class="rounded-circle" width="150">
                <?php
                }
                ?>
                <h3 class="name"><?php echo htmlspecialchars($row['nombreD'] . ' ' . $row['apellido'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <span class="country">Barbosa Santander</span>
            </div>
            <div class="nav-menu">
                <ul>
                    <li><a href="inicioAdmin.php"><span class="icon-location-arrow mr-3"></span> <i class="far fa-calendar-check"></i> Citas</a></li>
                    <li><a href="doctores.php"><span class="icon-location-arrow mr-3"></span><i class="fas fa-user-md"></i> Odontólogos</a></li>
                    <li class="active"><a href="calendar.php"><span class="icon-pie-chart mr-3"></span> <i class="far fa-calendar-alt"></i> Calendario</a></li>
                    <li><a href="historia_clinica.php"><span class="icon-pie-chart mr-3"></span> <i class="far fa-calendar-alt"></i> Historia Clínica</a></li>
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
                            <div>
                                <ol class="breadcrumb bg-white">
                                    <li class="breadcrumb-item">
                                        <a href="./inicioAdmin.php">Inicio</a>
                                    </li>
                                    <li class="breadcrumb-item active">
                                        Calendario
                                    </li>
                                </ol>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12 text-info">
                                        <div class="p-3 mb-2 bg-primary text-white text-center">Calendario de Citas</div>
                                        <div class="row">
                                            <div id="content" class="col-lg-12">
                                                <div class="row">
                                                    <div id="content" class="col-lg-12">
                                                        <div id="calendar"></div>
                                                        <div class="modal fade" id="modal-event" tabindex="-1" role="dialog" aria-labelledby="modal-eventLabel" aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title text-primary" id="event-title"></h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">×</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body text-dark">
                                                                        <div id="event-description"></div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../src/js/admin.js"></script>

    <script>
        function addZero(i) {
            if (i < 10) {
                i = '0' + i;
            }
            return i;
        }

        var hoy = new Date();
        var dd = hoy.getDate();
        var mm = hoy.getMonth() + 1;
        var yyyy = hoy.getFullYear();

        dd = addZero(dd);
        mm = addZero(mm);

        $(document).ready(function() {
            // Array de fechas no disponibles desde PHP
            var unavailableDates = <?php echo json_encode($unavailableDates, JSON_UNESCAPED_UNICODE); ?>;
            var appointments = <?php echo json_encode($appointmentsData, JSON_UNESCAPED_UNICODE); ?>;

            // Establecemos la vista predeterminada como 'month' para todos los dispositivos
            var defaultView = 'month';

            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                defaultView: defaultView,
                defaultDate: yyyy + '-' + mm + '-' + dd,
                buttonIcons: true,
                weekNumbers: false,
                editable: true,
                eventLimit: true,
                events: [
                    <?php
                    while ($row1 = mysqli_fetch_array($resultadoDentistas)) {
                    ?> {
                            id: '<?php echo $row1['id_cita']; ?>',
                            title: '<?php echo htmlspecialchars($row1['tipo'], ENT_QUOTES, 'UTF-8'); ?>',
                            description: '<?php echo 'El paciente ' . htmlspecialchars($row1['nombre'], ENT_QUOTES, 'UTF-8') . ' ha realizado una consulta de ' . htmlspecialchars($row1['tipo'], ENT_QUOTES, 'UTF-8') . ' con el doctor ' . htmlspecialchars($row1['nombreD'], ENT_QUOTES, 'UTF-8') . '.' . '<br>' . 'Fecha de la cita: ' . htmlspecialchars($row1['fecha_cita'], ENT_QUOTES, 'UTF-8') . '<br>' . 'Hora de la cita: ' . htmlspecialchars($row1['hora_cita'], ENT_QUOTES, 'UTF-8'); ?>',
                            start: '<?php echo $row1['fecha_cita']; ?>',
                            textColor: 'White',
                            display: 'background'
                        },
                    <?php
                    }
                    ?>
                ],
                eventClick: function(calEvent, jsEvent, view) {
                    // Prevenimos la propagación del evento para evitar conflictos
                    jsEvent.preventDefault();
                    jsEvent.stopPropagation();

                    // Aseguramos que el modal esté completamente cerrado antes de abrirlo
                    $('#modal-event').modal('hide');

                    // Limpiamos cualquier .modal-backdrop residual
                    $('.modal-backdrop').remove();

                    // Actualizamos el contenido del modal
                    $('#event-title').text(calEvent.title);
                    $('#event-description').html(calEvent.description);

                    // Abrimos el modal con opciones específicas
                    $('#modal-event').modal({
                        backdrop: true,
                        keyboard: true,
                        show: true
                    });

                    // Aseguramos que el .modal-backdrop se elimine al cerrar el modal
                    $('#modal-event').on('hidden.bs.modal', function() {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                    });
                },
                dayRender: function(date, cell) {
                    var dateStr = $.fullCalendar.formatDate(date, 'YYYY-MM-DD');
                    if (unavailableDates.indexOf(dateStr) !== -1) {
                        cell.css('background-color', '#ffcccc');
                    }
                },
                dayClick: function(date, jsEvent, view) {
                    var dateStr = $.fullCalendar.formatDate(date, 'YYYY-MM-DD');
                    var isUnavailable = unavailableDates.indexOf(dateStr) !== -1;
                    var hasAppointments = appointments.some(function(app) {
                        return app.fecha_cita === dateStr && app.id_doctor === <?php echo $vUsuario; ?>;
                    });

                    $('#confirmationMessage').text(isUnavailable ?
                        'Este día está marcado como no disponible. ¿Desea desmarcarlo?' :
                        (hasAppointments ?
                            'Este día tiene citas registradas. ¿Desea cancelar el día y notificar a los pacientes?' :
                            '¿Desea marcar este día como no disponible?'
                        )
                    );
                    $('#confirmationModal').modal('show');

                    $('#confirmAction').off('click').on('click', function() {
                        var action = isUnavailable ? 'remove' : (hasAppointments ? 'cancel' : 'add');
                        $('#loadingIndicator').css('display', 'block').addClass('show');
                        $('#confirmationMessage').hide();
                        $('#confirmAction').prop('disabled', true);

                        let startTime = Date.now();
                        $.ajax({
                            url: '../php/toggle_unavailable.php',
                            type: 'POST',
                            data: {
                                date: dateStr,
                                id_doctor: <?php echo $vUsuario; ?>,
                                action: action
                            },
                            success: function(response) {
                                $('#confirmationModal').modal('hide');
                                try {
                                    var res = typeof response === 'string' ? JSON.parse(response) : response;
                                    if (res.status === 'success') {
                                        if (action === 'remove') {
                                            unavailableDates.splice(unavailableDates.indexOf(dateStr), 1);
                                            $(jsEvent.target).css('background-color', '');
                                            $('#alertMessage').text('Día desmarcado como disponible.');
                                        } else if (action === 'cancel') {
                                            appointments = appointments.filter(function(app) {
                                                return app.fecha_cita !== dateStr || app.id_doctor !== <?php echo $vUsuario; ?>;
                                            });
                                            unavailableDates.push(dateStr);
                                            $(jsEvent.target).css('background-color', '#ffcccc');
                                            $('#calendar').fullCalendar('removeEvents', function(event) {
                                                return event.start.format('YYYY-MM-DD') === dateStr;
                                            });
                                            // Mostrar mensaje de éxito con advertencia si hay correos inválidos
                                            let message = 'Día cancelado y notificaciones enviadas.';
                                            if (res.invalid_emails && res.invalid_emails.length > 0) {
                                                message += '\nAdvertencia: No se pudieron enviar notificaciones a los siguientes correos inválidos: ' + res.invalid_emails.join(', ');
                                            }
                                            $('#alertMessage').text(message);
                                        } else {
                                            unavailableDates.push(dateStr);
                                            $(jsEvent.target).css('background-color', '#ffcccc');
                                            $('#alertMessage').text('Día marcado como no disponible.');
                                        }
                                        $('#alertModal').modal('show');
                                    } else {
                                        $('#alertMessage').text(res.message || 'Error al procesar la acción.');
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
                                }, delay);
                            }
                        });
                    });
                }
            });

            // Aseguramos que los botones de cierre del modal funcionen
            $('#modal-event .close, #modal-event .btn[data-dismiss="modal"]').on('click touchstart', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#modal-event').modal('hide');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            });
        });
    </script>

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
<?php
mysqli_close($link);
?>