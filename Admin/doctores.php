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
$row = consultarDoctor($link, $vUsuario);
$resultado = MostrarEspecialidad($link); // Obtener lista de especialidades

// Calcular el contador de notificaciones no leídas
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
    <link href="https://code.jquery.com/ui/1.10.3/themes/redmond/jquery-ui.css" rel="stylesheet" media="screen">
    <title>Odontólogos</title>
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
                <h3 class="name"><?php echo utf8_decode($row['nombreD'] . ' ' . $row['apellido']); ?></h3>
                <span class="country">Barbosa Santander</span>
            </div>
            <div class="nav-menu">
                <ul>
                    <li><a href="inicioAdmin.php"><span class="icon-location-arrow mr-3"></span><i class="far fa-calendar-check"></i> Citas</a></li>
                    <li class="active"><a href="doctores.php"><span class="icon-location-arrow mr-3"></span><i class="fas fa-user-md"></i> Odontólogos</a></li>
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
                                <li class="breadcrumb-item"><a href="./inicioAdmin.php">Inicio</a></li>
                                <li class="breadcrumb-item active">Odontólogos</li>
                            </ol>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="container">
                                            <!-- Move the message display logic here, just before the blue bar -->
                                            <?php if (isset($_SESSION['MensajeTexto']) && isset($_SESSION['MensajeTipo'])): ?>
                                                <div class="alert <?php echo $_SESSION['MensajeTipo']; ?>" role="alert" id="mensaje">
                                                    <?php 
                                                    echo $_SESSION['MensajeTexto'];
                                                    // Limpiar mensajes después de mostrarlos
                                                    unset($_SESSION['MensajeTexto']);
                                                    unset($_SESSION['MensajeTipo']);
                                                    ?>
                                                    <button class="delete"><i class="fa fa-times"></i></button>
                                                </div>
                                                <script>
                                                    setTimeout(function() {
                                                        document.getElementById('mensaje').style.display = 'none';
                                                    }, 5000);
                                                </script>
                                            <?php endif; ?>

                                            <form action="../crud/registro_INSERT.php?opciones=INSDOCT" method="POST" enctype="multipart/form-data" autocomplete="off">
                                                <div class="p-3 mb-2 bg-primary text-white text-center">Agregar un nuevo odontólogo</div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label for="nombres">Nombres</label>
                                                            <input class="form-control" type="text" name="name" placeholder="Nombres" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="apellidos">Apellidos</label>
                                                            <input class="form-control" type="text" name="apellido" placeholder="Apellidos" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="nacimiento">Fecha de nacimiento</label>
                                                            <input class="form-control" type="date" name="nacimiento" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label for="correo">Correo electrónico</label>
                                                            <input class="form-control" type="email" name="correo" placeholder="Correo Electrónico" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="clave">Contraseña</label>
                                                            <input class="form-control" type="password" name="clave" id="clave" placeholder="Contraseña" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="clave2">Confirmar Contraseña</label>
                                                            <input class="form-control" type="password" name="clave2" id="clave2" placeholder="Contraseña" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label for="sexo">Sexo</label>
                                                            <select class="form-control" name="sexo" required>
                                                                <option value="Masculino">Masculino</option>
                                                                <option value="Femenino">Femenino</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="especialidad">Especialidad</label>
                                                            <select class="form-control" name="especialidad" id="especialidad" required>
                                                                <?php while ($row = mysqli_fetch_array($resultado, MYSQLI_ASSOC)) { ?>
                                                                    <option value="<?php echo $row['id_especialidad']; ?>"><?php echo $row['tipo']; ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="cell">Teléfono</label>
                                                            <input class="form-control" type="text" name="cell" placeholder="Teléfono">
                                                        </div>
                                                    </div>
                                                </div>
                                                <button class="btn btn-success btn-lg" type="submit" name="guardar" value="Guardar">
                                                    <i class="far fa-save"></i> Guardar
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
        </div>
    </main>

    <script src="../src/js/jquery.js"></script>
    <script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script src="../src/css/lib/bootstrap/js/bootstrap.min.js"></script>
    <script src="../src/js/admin.js"></script>
    <!-- Script para cerrar alertas dinámicamente -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            (document.querySelectorAll('.alert .delete') || []).forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
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