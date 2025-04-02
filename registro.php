<?php
include_once 'php/conexionDB.php';
include_once 'php/consultas.php';

// Redirigir si el usuario ya está logueado
if (isset($_SESSION['id_paciente'])) {
    header("Location: ./principal.php");
    exit();
} elseif (isset($_SESSION['id_doctor'])) {
    header("Location: ./Admin/inicioAdmin.php");
    exit();
}

// Solo iniciar sesión si hay datos de Google
$google_email = '';
$google_name = '';
$google_apellido = '';
if (isset($_SESSION['google_email']) || isset($_SESSION['google_name'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $google_email = $_SESSION['google_email'] ?? '';
    $full_name = $_SESSION['google_name'] ?? '';
    $name_parts = explode(' ', trim($full_name));
    $half = (int) ceil(count($name_parts) / 2);
    $google_name = implode(' ', array_slice($name_parts, 0, $half));
    $google_apellido = implode(' ', array_slice($name_parts, $half));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Consultorio Odontológico EMILY BERNAL</title>
    <link rel="icon" href="./src/img/logo.png" type="image/png" />
    <link rel="stylesheet" href="src/css/login.css" />
    <link href="src/css/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css" />
    <link rel="stylesheet" href="src/css/lib/fontawesome/css/all.css">
</head>

<body>
    <div class="container login-container">
        <div class="row">
            <div class="col-12 col-md-6 ads">
                <h1><span id="fl">Consultorio</span><span id="sl">Odontológico</span></h1>
            </div>
            <div class="col-12 col-md-6 login-form">
                <div class="profile-img">
                    <img src="src/img/logo.png" alt="profile_img" height="100px" width="100px;">
                </div>
                <h3>Registrarse</h3>

                <!-- Mostrar mensajes de éxito o error -->
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

                <form action="crud/registro_INSERT.php?opciones=INS" method="POST" enctype="multipart/form-data" autocomplete="off">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="name" class="font-weight-bold">Nombre</label>
                                <input type="text" class="form-control" name="name" placeholder="Nombre" value="<?php echo htmlspecialchars($google_name); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="apellido" class="font-weight-bold">Apellido</label>
                                <input type="text" class="form-control" name="apellido" placeholder="Apellido" value="<?php echo htmlspecialchars($google_apellido); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="sexo" class="font-weight-bold">Sexo</label>
                                <select class="form-control" name="sexo" required>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nacimiento" class="font-weight-bold">Fecha de nacimiento</label>
                                <input class="form-control" type="date" name="nacimiento" placeholder="Fecha de nacimiento" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cell" class="font-weight-bold">Teléfono</label>
                                <input type="text" class="form-control" name="cell" placeholder="Teléfono" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="correo" class="font-weight-bold">Correo Electrónico</label>
                        <input type="email" class="form-control" name="correo" placeholder="Correo electrónico" value="<?php echo htmlspecialchars($google_email); ?>" <?php echo empty($google_email) ? '' : 'readonly'; ?> required>
                    </div>
                    <div class="form-group">
                        <label for="password" class="font-weight-bold">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                        <button type="button" class="toggle-password" data-target="password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block" type="submit" name="ingresar" value="ingresar">
                            <i class="fas fa-sign-in-alt"></i> Registrarse
                        </button>
                    </div>
                    <div class="form-group">
                        <a href="index.php"><i class="fas fa-history"></i> Atrás</a>
                    </div>
                </form>

                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <h3>¡Registro Exitoso!</h3>
                    <p>Tu cuenta ha sido creada correctamente.</p>
                    <a href="principal.php" class="btn btn-primary">Aceptar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="src/js/tooglePassword.js"></script>
    <script>
        // Manejo de cierre de notificaciones
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.notification .delete, .alert .delete').forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
            });
        });
    </script>
</body>
</html>

<?php
// Limpiar variables de sesión solo si existen
if (isset($_SESSION['google_email']) || isset($_SESSION['google_name'])) {
    unset($_SESSION['google_email']);
    unset($_SESSION['google_name']);
}
?>