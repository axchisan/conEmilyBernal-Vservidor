<?php
session_start();
include_once './php/conexionDB.php';
include_once './php/consultas.php';

if (isset($_SESSION['id_paciente'])) {
    $vUsuario = $_SESSION['id_paciente'];
    $row = consultarPaciente($link, $vUsuario);
} else {
    $_SESSION['MensajeTexto'] = "Error: acceso al sistema no registrado.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ./index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Consulorio Emily Bernal</title>
    <link rel="icon" href="./src/img/logo.png" type="image/png" />
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="./src/css/editar_form.css">
    <link rel="stylesheet" href="src/css/lib/fontawesome/css/all.css">
</head>

<body id="top" data-spy="scroll" data-target=".navbar-collapse" data-offset="50">
    <!-- Menu -->
    <section class="navbar navbar-default navbar-static-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="icon icon-bar"></span>
                    <span class="icon icon-bar"></span>
                    <span class="icon icon-bar"></span>
                </button>
                <a href="./principal.php" class="navbar-brand"><img src="src/img/logo.png" width="20px" height="20px" alt="Logo"></a>
                <a href="./principal.php" class="navbar-brand">Consultorio Emily Bernal</a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="./principal.php#top" class="smoothScroll">Inicio</a></li>
                    <li><a href="./principal.php#about" class="smoothScroll">Nosotros</a></li>
                    <li><a href="./principal.php#team" class="smoothScroll">Dentistas</a></li>
                    <li><a href="./principal.php#perfil" class="smoothScroll">Perfil</a></li>
                    <li><a href="./principal.php#google-map" class="smoothScroll">Contacto</a></li>
                    <li class="appointment-btn"><a href="./principal.php#appointment">Realizar una Cita</a></li>
                </ul>
            </div>
        </div>
    </section>

    <div class="container">
        <div id="advanced-search-form">
            <form action="./crud/actualizar_paciente.php?accion=UDT" method="POST" enctype="multipart/form-data" autocomplete="off" class="form-horizontal">
                <input type="hidden" name="id" value="<?php echo $row['id_paciente']; ?>">
                <div class="form-group">
                    <label for="first-name">Nombre</label>
                    <input type="text" class="form-control" name="name" placeholder="Nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>" id="first-name">
                </div>especialidad
                <div class="form-group">
                    <label for="last-name">Apellido</label>
                    <input type="text" class="form-control" name="apellido" placeholder="Apellido" value="<?php echo htmlspecialchars($row['apellido']); ?>" id="last-name">
                </div>
                <div class="form-group">
                    <label for="number">Teléfono</label>
                    <input type="text" class="form-control" name="cell" placeholder="Teléfono" value="<?php echo htmlspecialchars($row['telefono']); ?>" id="number">
                </div>
                <div class="form-group">
                    <label for="age">Fecha de nacimiento</label>
                    <input type="text" class="form-control" name="nacimiento" placeholder="Fecha de nacimiento" value="<?php echo htmlspecialchars($row['fecha_nacimiento']); ?>" id="age">
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" class="form-control" name="correo" placeholder="Correo Electrónico" value="<?php echo htmlspecialchars($row['correo_electronico']); ?>" id="email">
                </div>
                <div class="form-group">
                    <label for="category">Contraseña</label>
                    <input type="password" class="form-control" name="clave" placeholder="Ingrese nueva contraseña (opcional)" id="category">
                </div>
                <div class="form-group">
                    <label for="sexo" class="font-weight-bold">Sexo</label>
                    <select class="form-control" name="sexo" required>
                        <option value="Masculino" <?php echo $row['sexo'] === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                        <option value="Femenino" <?php echo $row['sexo'] === 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
                    </select>
                </div>
                <br>
                <button class="btn btn-success btn-lg btn-responsive" name="actualizar" id="search">
                    <i class="fas fa-sign-in-alt"></i> Actualizar
                </button>
                <div class="form-group">
                    <a href="principal.php"><i class="fas fa-history"></i> Atrás</a>
                </div>
            </form>
        </div>
    </div>

    <script src="src/js/jquery.js"></script>
    <script src="src/js/bootstrap.min.js"></script>
</body>
</html>