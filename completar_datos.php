<?php
session_start();
include_once 'php/conexionDB.php';

if (!isset($_SESSION['id_paciente']) || !isset($_SESSION['google_new_user'])) {
    header("Location: index.php");
    exit;
}

// Obtener datos actuales del usuario
$query = "SELECT correo_electronico, nombre FROM pacientes WHERE id_paciente = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['id_paciente']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Completar Datos - Consultorio Odontológico</title>
    <link rel="icon" href="./src/img/logo.png" type="image/png" />
    <link rel="stylesheet" href="src/css/login.css" />
    <link href="src/css/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="src/css/lib/fontawesome/css/all.css">
</head>
<body>
    <div class="container login-container">
        <div class="row">
            <div class="col-md-6 ads">
                <h1><span id="fl">Consultorio</span><span id="sl">Odontológico</span></h1>
            </div>
            <div class="col-md-6 login-form">
                <div class="profile-img">
                    <img src="src/img/logo.png" alt="profile_img" height="100px" width="100px;">
                </div>
                <h3>Completar Datos</h3>
                <form action="crud/completar_datos_INSERT.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="name" class="font-weight-bold">Nombre</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="apellido" class="font-weight-bold">Apellido</label>
                                <input type="text" class="form-control" name="apellido" placeholder="Apellido" required>
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
                                <input class="form-control" type="date" name="nacimiento" required>
                            </div>
                            <div class="col-md-6">
                                <label for="cell" class="font-weight-bold">Teléfono</label>
                                <input type="text" class="form-control" name="cell" placeholder="Teléfono" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="correo" class="font-weight-bold">Correo Electrónico</label>
                        <input type="email" class="form-control" name="correo" value="<?php echo htmlspecialchars($user['correo_electronico']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary btn-lg btn-block" type="submit" name="completar" value="completar">
                            <i class="fas fa-save"></i> Guardar y Continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($link); ?>