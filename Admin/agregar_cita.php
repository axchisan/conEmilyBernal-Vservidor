<?php
session_start();
include_once('../php/conexionDB.php');
include_once('../php/consultas.php');

// Validar la sesión del doctor
if (!isset($_SESSION['id_doctor'])) {
    $_SESSION['MensajeTexto'] = "Error: Acceso al sistema no registrado.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ../index.php");
    exit();
}

$doctor_id = $_SESSION['id_doctor'];

// Obtener lista de consultas (tipos de consulta)
$query_consultas = "SELECT id_consultas, tipo FROM consultas ORDER BY tipo";
$resultado_consultas = mysqli_query($link, $query_consultas);

// Obtener lista de pacientes para el <select>
$resultado_pacientes = MostrarPacientes($link);

// Obtener datos del doctor
$doctor = consultarDoctor($link, $doctor_id);

// Función para generar un correo único
function generarCorreoUnico($link, $nombre, $apellido)
{
    $base_email = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nombre) . '.' . preg_replace('/[^a-zA-Z0-9]/', '', $apellido));
    $domain = '@consultorioemilybernal.com';
    $email = $base_email . $domain;
    $counter = 1;

    while (true) {
        $query = "SELECT COUNT(*) as total FROM pacientes WHERE correo_electronico = ?";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($row['total'] == 0) {
            break; // Correo único encontrado
        }
        $email = $base_email . '.' . $counter . $domain;
        $counter++;
    }
    return $email;
}

// Variables para los placeholders
$generated_email = '';
$default_password = 'default123';

// Manejo del registro de un nuevo paciente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_patient'])) {
    $nombre = mysqli_real_escape_string($link, trim($_POST['nombre']));
    $apellido = mysqli_real_escape_string($link, trim($_POST['apellido']));
    $telefono = mysqli_real_escape_string($link, trim($_POST['telefono']));
    $sexo = mysqli_real_escape_string($link, trim($_POST['sexo']));
    $fecha_nacimiento = mysqli_real_escape_string($link, trim($_POST['fecha_nacimiento']));

    // Correo electrónico
    $correo_electronico = !empty($_POST['correo_electronico']) ? mysqli_real_escape_string($link, trim($_POST['correo_electronico'])) : generarCorreoUnico($link, $nombre, $apellido);

    // Validar si el correo ya existe (en caso de que el doctor ingrese uno manualmente)
    $query_check = "SELECT COUNT(*) as total FROM pacientes WHERE correo_electronico = ?";
    $stmt_check = mysqli_prepare($link, $query_check);
    mysqli_stmt_bind_param($stmt_check, "s", $correo_electronico);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $row_check = mysqli_fetch_assoc($result_check);
    mysqli_stmt_close($stmt_check);

    if ($row_check['total'] > 0) {
        $_SESSION['MensajeTexto'] = "Error: El correo ya está registrado.";
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
        // Guardar los datos ingresados para repoblar el formulario
        $_SESSION['form_data'] = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'telefono' => $telefono,
            'sexo' => $sexo,
            'fecha_nacimiento' => $fecha_nacimiento
        ];
        header("Location: agregar_cita.php");
        exit;
    }

    // Contraseña
    $clave_input = !empty($_POST['clave']) ? trim($_POST['clave']) : 'default123';
    $clave = password_hash($clave_input, PASSWORD_BCRYPT);

    $query = "INSERT INTO pacientes (nombre, apellido, telefono, sexo, fecha_nacimiento, correo_electronico, clave) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "sssssss", $nombre, $apellido, $telefono, $sexo, $fecha_nacimiento, $correo_electronico, $clave);

    if (mysqli_stmt_execute($stmt)) {
        $new_patient_id = mysqli_insert_id($link);
        $_SESSION['MensajeTexto'] = "Paciente registrado correctamente.";
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-success text-white";
        $_SESSION['new_patient_id'] = $new_patient_id; // Guardar el ID del nuevo paciente para seleccionarlo automáticamente
    } else {
        $_SESSION['MensajeTexto'] = "Error al registrar el paciente: " . mysqli_error($link);
        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    }
    mysqli_stmt_close($stmt);
    header("Location: agregar_cita.php");
    exit();
} else {
    // Generar el correo para el placeholder si se está cargando el formulario
    $form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
    $nombre = $form_data['nombre'] ?? '';
    $apellido = $form_data['apellido'] ?? '';
    if ($nombre && $apellido) {
        $generated_email = generarCorreoUnico($link, $nombre, $apellido);
    }
}
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
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <title>Agregar Nueva Cita</title>
    <style>
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
            display: none;
        }

        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000 !important;
        }

        .toggle-button {
            cursor: pointer;
            font-size: 0.8em;
            margin-left: 5px;
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
                <?php if ($doctor['sexo'] == 'Masculino') { ?>
                    <img src="../src/img/odontologo.png" class="rounded-circle" width="150">
                <?php } elseif ($doctor['sexo'] == 'Femenino') { ?>
                    <img src="../src/img/odontologa.png" class="rounded-circle" width="150">
                <?php } ?>
                <h3 class="name"><?php echo utf8_decode($doctor['nombreD'] . ' ' . $doctor['apellido']); ?></h3>
                <span class="country">Barbosa Santander</span>
            </div>
            <div class="nav-menu">
                <ul>
                    <li><a href="inicioAdmin.php"><span class="icon-location-arrow mr-3"></span><i class="far fa-calendar-check"></i> Citas</a></li>
                    <li><a href="doctores.php"><span class="icon-location-arrow mr-3"></span><i class="fas fa-user-md"></i> Odontólogos</a></li>
                    <li><a href="calendar.php"><span class="icon-pie-chart mr-3"></span><i class="far fa-calendar-alt"></i> Calendario</a></li>
                    <li><a href="historia_clinica.php"><span class="icon-pie-chart mr-3"></span><i class="far fa-calendar-alt"></i> Historia Clínica</a></li>
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
                                <li class="breadcrumb-item active">Agregar Nueva Cita</li>
                            </ol>

                            <?php if (isset($_SESSION['MensajeTexto'])) { ?>
                                <div class="alert <?php echo $_SESSION['MensajeTipo']; ?>" role="alert">
                                    <?php echo $_SESSION['MensajeTexto']; ?>
                                    <button class="delete"><i class="fa fa-times"></i></button>
                                </div>
                                <?php
                                $_SESSION['MensajeTexto'] = null;
                                $_SESSION['MensajeTipo'] = null;
                                ?>
                            <?php } ?>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="far fa-calendar-plus"></i> Agregar Nueva Cita
                                </div>
                                <div class="card-body">
                                    <form action="../crud/cita_INSERT.php?opciones=INS" method="POST" id="appointment-form">
                                        <div class="form-group">
                                            <label for="paciente">Paciente:</label>
                                            <div class="input-group">
                                                <!-- Campo de autocompletado -->
                                                <div id="autocomplete-container" style="display: block;">
                                                    <input type="text" class="form-control" id="paciente_search" placeholder="Buscar paciente por nombre o apellido">
                                                    <input type="hidden" id="paciente_id" name="paciente">
                                                </div>
                                                <!-- Campo de select -->
                                                <div id="select-container" style="display: none;">
                                                    <select class="form-control" id="paciente_select" name="paciente_select">
                                                        <option value="">Seleccione un paciente</option>
                                                        <?php while ($paciente = mysqli_fetch_assoc($resultado_pacientes)) { ?>
                                                            <option value="<?php echo $paciente['id_paciente']; ?>">
                                                                <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="input-group-append">
                                                    <span class="input-group-text toggle-button" id="toggle-patient-search" title="Cambiar modo de búsqueda">
                                                        <i class="fas fa-caret-down"></i>
                                                    </span>
                                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#registerPatientModal">
                                                        <i class="fas fa-user-plus"></i> Registrar Nuevo Paciente
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">Busca o selecciona un paciente existente.</small>
                                            <div id="paciente-error" class="error-message"></div>
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_cita">Fecha de la Cita:</label>
                                            <input type="text" class="form-control" id="fecha_cita" name="fecha_cita" required>
                                            <div id="fecha-error" class="error-message"></div>
                                        </div>
                                        <div class="form-group">
                                            <label for="hora">Hora de la Cita:</label>
                                            <select class="form-control" id="hora" name="hora" required>
                                                <option value="">Seleccione una hora</option>
                                                <!-- Horarios de la mañana -->
                                                <option value="09:00 AM - 10:00 AM">09:00 AM - 10:00 AM</option>
                                                <option value="10:00 AM - 11:00 AM">10:00 AM - 11:00 AM</option>
                                                <option value="11:00 AM - 12:00 PM">11:00 AM - 12:00 PM</option>
                                                <!-- Horarios de la tarde -->
                                                <option value="02:00 PM - 03:00 PM">02:00 PM - 03:00 PM</option>
                                                <option value="03:00 PM - 04:00 PM">03:00 PM - 04:00 PM</option>
                                                <option value="04:00 PM - 05:00 PM">04:00 PM - 05:00 PM</option>
                                                <option value="05:00 PM - 06:00 PM">05:00 PM - 06:00 PM</option>
                                            </select>
                                            <div id="hora-error" class="error-message"></div>
                                        </div>
                                        <div class="form-group">
                                            <label for="consultas">Tipo de Consulta:</label>
                                            <select class="form-control" id="consultas" name="consultas" required>
                                                <option value="">Seleccione un tipo de consulta</option>
                                                <?php while ($consulta = mysqli_fetch_assoc($resultado_consultas)) { ?>
                                                    <option value="<?php echo $consulta['id_consultas']; ?>">
                                                        <?php echo htmlspecialchars($consulta['tipo']); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <div id="consultas-error" class="error-message"></div>
                                        </div>
                                        <div class="button-container custom-button-container">
                                            <button type="submit" name="enviar" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Guardar Cita
                                            </button>
                                            <a href="inicioAdmin.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Cancelar
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para registrar un nuevo paciente -->
    <div class="modal fade" id="registerPatientModal" tabindex="-1" role="dialog" aria-labelledby="registerPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerPatientModalLabel">Registrar Nuevo Paciente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="agregar_cita.php" method="POST" id="register-patient-form">
                        <div class="form-group">
                            <label for="nombre">Nombre:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($form_data['nombre'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="apellido">Apellido:</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required value="<?php echo htmlspecialchars($form_data['apellido'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required value="<?php echo htmlspecialchars($form_data['telefono'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="sexo">Género:</label>
                            <select class="form-control" id="sexo" name="sexo" required>
                                <option value="Masculino" <?php echo (isset($form_data['sexo']) && $form_data['sexo'] == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="Femenino" <?php echo (isset($form_data['sexo']) && $form_data['sexo'] == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required value="<?php echo htmlspecialchars($form_data['fecha_nacimiento'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="correo_electronico">Correo Electrónico (opcional):</label>
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" placeholder="<?php echo htmlspecialchars($generated_email ?: 'Se generará automáticamente'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="clave">Contraseña (opcional):</label>
                            <input type="text" class="form-control" id="clave" name="clave" placeholder="<?php echo htmlspecialchars($default_password); ?>">
                        </div>
                        <button type="submit" name="register_patient" class="btn btn-primary">Registrar Paciente</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../src/js/jquery.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="../src/css/lib/bootstrap/js/bootstrap.min.js"></script>
    <script src="../src/js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Generar correo dinámicamente al escribir nombre y apellido
            $("#nombre, #apellido").on("input", function() {
                var nombre = $("#nombre").val().trim();
                var apellido = $("#apellido").val().trim();
                if (nombre && apellido) {
                    $.ajax({
                        url: "../php/generate_email.php",
                        type: "POST",
                        data: {
                            nombre: nombre,
                            apellido: apellido
                        },
                        dataType: "json",
                        success: function(data) {
                            if (data.email) {
                                $("#correo_electronico").attr("placeholder", data.email);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al generar correo:", error);
                        }
                    });
                } else {
                    $("#correo_electronico").attr("placeholder", "Se generará automáticamente");
                }
            });

            // Alternar entre autocompletado y select
            $("#toggle-patient-search").click(function() {
                var $autocompleteContainer = $("#autocomplete-container");
                var $selectContainer = $("#select-container");
                var $icon = $(this).find("i");

                if ($autocompleteContainer.is(":visible")) {
                    $autocompleteContainer.hide();
                    $selectContainer.show();
                    $icon.removeClass("fa-caret-down").addClass("fa-caret-up");
                    $("#paciente_id").val(""); // Limpiar el campo oculto si se cambia al select
                    // Ajustar el atributo required
                    $("#paciente_search").removeAttr("required");
                    $("#paciente_id").removeAttr("required");
                    $("#paciente_select").attr("required", true);
                } else {
                    $selectContainer.hide();
                    $autocompleteContainer.show();
                    $icon.removeClass("fa-caret-up").addClass("fa-caret-down");
                    $("#paciente_select").val(""); // Limpiar el select si se cambia al autocompletado
                    // Ajustar el atributo required
                    $("#paciente_select").removeAttr("required");
                    $("#paciente_search").attr("required", true);
                    $("#paciente_id").attr("required", true);
                }
            });

            // Sincronizar el select con el campo oculto
            $("#paciente_select").change(function() {
                $("#paciente_id").val($(this).val());
                $("#paciente-error").hide();
            });

            // Autocompletado para buscar pacientes
            $("#paciente_search").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: "../php/search_patients.php",
                        type: "POST",
                        dataType: "json",
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    label: item.nombre + " " + item.apellido,
                                    value: item.nombre + " " + item.apellido,
                                    id: item.id_paciente
                                };
                            }));
                        },
                        error: function(xhr, status, error) {
                            console.error("Error en autocompletado:", error);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    $("#paciente_id").val(ui.item.id);
                    $("#paciente_search").val(ui.item.label);
                    $("#paciente-error").hide();
                    return false; // Evitar que el valor del input se reescriba automáticamente
                }
            });

            // Seleccionar automáticamente el paciente recién registrado
            <?php if (isset($_SESSION['new_patient_id'])) { ?>
                $.ajax({
                    url: "../php/get_patient.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        id_paciente: <?php echo $_SESSION['new_patient_id']; ?>
                    },
                    success: function(data) {
                        if (data) {
                            $("#paciente_search").val(data.nombre + " " + data.apellido);
                            $("#paciente_id").val(data.id_paciente);
                            $("#paciente_select").val(data.id_paciente);
                            $("#paciente-error").hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al obtener paciente recién registrado:", error);
                    }
                });
                <?php unset($_SESSION['new_patient_id']); ?>
            <?php } ?>

            // Configurar datepicker para deshabilitar domingos y fechas no disponibles
            $("#fecha_cita").datepicker({
                beforeShowDay: function(date) {
                    return [date.getDay() !== 0];
                },
                minDate: 0,
                dateFormat: "yy-mm-dd",
                onSelect: function(dateText) {
                    validateDate(dateText);
                    updateTimeSlots();
                    $("#fecha-error").hide();
                }
            });

            // Validar fecha al cambiar la fecha
            function validateDate(selectedDate) {
                var selectedDoctor = <?php echo json_encode($doctor_id); ?>;
                $.ajax({
                    url: "../php/get_unavailable_dates.php",
                    type: "POST",
                    data: {
                        id_doctor: selectedDoctor
                    },
                    dataType: "json",
                    success: function(unavailableDates) {
                        var $fechaError = $("#fecha-error");
                        if (unavailableDates.includes(selectedDate)) {
                            $fechaError.text("El doctor no está disponible en esta fecha. Por favor, elija otra fecha.").show();
                            $("#fecha_cita").val("");
                        } else {
                            $fechaError.hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#fecha-error").text("Error al verificar la disponibilidad del doctor.").show();
                        console.error("Error al validar fecha:", error);
                    }
                });
            }

            // Actualizar horarios disponibles
            var $fechaInput = $("#fecha_cita");
            var $horaSelect = $("#hora");
            var $errorDiv = $("#hora-error");

            function updateTimeSlots() {
                var selectedDate = $fechaInput.val();
                var selectedDoctor = <?php echo json_encode($doctor_id); ?>;

                if (!selectedDate) {
                    $horaSelect.find("option").each(function() {
                        if ($(this).val() !== "") {
                            $(this).prop("disabled", true).css("color", "#999");
                        }
                    });
                    return;
                }

                $.ajax({
                    url: "../php/get_unavailable_times.php",
                    type: "POST",
                    data: {
                        id_doctor: selectedDoctor,
                        fecha_cita: selectedDate
                    },
                    dataType: "json",
                    success: function(data) {
                        if (data.error) {
                            $errorDiv.text(data.error).show();
                            $horaSelect.find("option").each(function() {
                                if ($(this).val() !== "") {
                                    $(this).prop("disabled", true).css("color", "#999");
                                }
                            });
                            return;
                        }

                        var unavailableTimes = data;
                        $horaSelect.find("option").each(function() {
                            if ($(this).val() === "") return;
                            if (unavailableTimes.includes($(this).val())) {
                                $(this).prop("disabled", true).css("color", "#999");
                            } else {
                                $(this).prop("disabled", false).css("color", "#000");
                            }
                        });

                        if ($horaSelect.val() && unavailableTimes.includes($horaSelect.val())) {
                            $horaSelect.val("");
                            $errorDiv.text("La hora seleccionada ya no está disponible. Por favor, elija otra.").show();
                        } else {
                            $errorDiv.hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        $errorDiv.text("Error al verificar la disponibilidad de horarios.").show();
                        $horaSelect.find("option").each(function() {
                            if ($(this).val() !== "") {
                                $(this).prop("disabled", true).css("color", "#999");
                            }
                        });
                        console.error("Error al actualizar horarios:", error);
                    }
                });
            }

            $fechaInput.on("change", updateTimeSlots);

            // Validar el formulario al enviarlo
            $("#appointment-form").on("submit", function(e) {
                var pacienteId = $("#paciente_id").val();
                var hora = $("#hora").val();
                var fecha = $("#fecha_cita").val();
                var consulta = $("#consultas").val();
                var isAutocompleteVisible = $("#autocomplete-container").is(":visible");

                // Depuración
                console.log("Paciente ID:", pacienteId);
                console.log("Hora:", hora);
                console.log("Fecha:", fecha);
                console.log("Consulta:", consulta);
                console.log("Autocompletado visible:", isAutocompleteVisible);

                var hasError = false;

                // Validar paciente
                if (!pacienteId) {
                    $("#paciente-error").text("Por favor, seleccione un paciente.").show();
                    hasError = true;
                } else {
                    $("#paciente-error").hide();
                }

                // Validar fecha
                if (!fecha) {
                    $("#fecha-error").text("Por favor, seleccione una fecha para la cita.").show();
                    hasError = true;
                } else {
                    $("#fecha-error").hide();
                }

                // Validar hora
                if (!hora) {
                    $("#hora-error").text("Por favor, seleccione una hora para la cita.").show();
                    hasError = true;
                } else {
                    $("#hora-error").hide();
                }

                // Validar tipo de consulta
                if (!consulta) {
                    $("#consultas-error").text("Por favor, seleccione un tipo de consulta.").show();
                    hasError = true;
                } else {
                    $("#consultas-error").hide();
                }

                if (hasError) {
                    e.preventDefault();
                    console.log("Formulario no enviado debido a errores de validación.");
                } else {
                    console.log("Formulario válido, enviando...");
                }
            });

            // Cerrar alertas
            (document.querySelectorAll(".alert .delete") || []).forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener("click", () => {
                    $notification.parentNode.removeChild($notification);
                });
            });

            // Limpiar datos del formulario después de un error
            <?php unset($_SESSION['form_data']); ?>
        });
    </script>
</body>

</html>