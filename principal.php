<?php
session_start();
include_once './php/conexionDB.php';
include_once './php/consultas.php';

$resultado = MostrarConsultas($link);
$resultadoDentistas = MostrarDentistas($link);

// Validar sesión
if (!isset($_SESSION['id_paciente'])) {
    $_SESSION['MensajeTexto'] = "Por favor inicia sesión para acceder a esta página.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-warning text-white";
    header("Location: ./index.php");
    exit();
}

$vUsuario = $_SESSION['id_paciente'];
$row = consultarPaciente($link, $vUsuario);
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="Odontología Dra. Emily Bernal en Barbosa, Santander. Servicios dentales de calidad: ortodoncia, odontología biológica, estética dental y más. Agenda tu cita hoy.">
    <title>DOCTORA EMILY BERNAL</title>
    <link rel="icon" href="./src/img/logo.png" type="image/png" />
    <link rel="stylesheet" href="src/css/bootstrap.min.css">
    <link rel="stylesheet" href="src/css/font-awesome.min.css">
    <link rel="stylesheet" href="src/css/animate.css">
    <link rel="stylesheet" href="src/css/owl.carousel.css">
    <link rel="stylesheet" href="src/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="src/css/tooplate-style.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="./src/js/Datepicker.js"></script>
    <script src="src/js/appointmentValidation.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body id="top" data-spy="scroll" data-target=".navbar-collapse" data-offset="50">
    <!-- Preloader -->
    <section class="preloader">
        <div class="spinner">
            <span class="spinner-rotate"></span>
        </div>
    </section>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-sm-10">
                    <p><?php echo ($row['sexo'] == 'Masculino') ? "Bienvenido {$row['nombre']} {$row['apellido']}" : "Bienvenida {$row['nombre']} {$row['apellido']}"; ?></p>
                </div>

                <div class="col-md-8 col-sm-10">
                    <span class="phone-icon"><i class="fa fa-phone"></i> 3105547320</span>
                    <span class="date-icon"><i class="fa fa-calendar-plus-o"></i> 8:30 AM - 6:00 PM (Lunes-Sabado)</span>
                    <span class="email-icon"><i class="fa fa-envelope-o"></i> <a href="mailto:emilybernal902@gmail.com">emilybernal902@gmail.com</a></span>
                    <span><i class="fa fa-sign-out"></i> <a href="./php/cerrar.php">Cerrar Sesión</a></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Menu -->
    <section class="navbar navbar-default navbar-static-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="icon icon-bar"></span>
                    <span class="icon icon-bar"></span>
                    <span class="icon icon-bar"></span>
                </button>
                <a href="principal.php" class="navbar-brand"><img src="src/img/logo.png" width="20px" height="20px" alt="Logo"></a>
                <a href="principal.php" class="navbar-brand">EMILY BERNAL</a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#top" class="smoothScroll">Inicio</a></li>
                    <li><a href="#about" class="smoothScroll">Nosotros</a></li>
                    <li><a href="#team" class="smoothScroll">Dentistas</a></li>
                    <li><a href="#perfil" class="smoothScroll">Perfil</a></li>
                    <li><a href="#perfil" class="smoothScroll">Mis citas</a></li>
                    <li><a href="#google-map" class="smoothScroll">Contacto</a></li>
                    <li class="appointment-btn"><a href="#appointment">Realizar una Cita</a></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Mostrar mensajes si existen -->
    <?php if (isset($_SESSION['MensajeTexto']) && isset($_SESSION['MensajeTipo'])): ?>
        <div class="<?php echo $_SESSION['MensajeTipo']; ?>" id="mensaje">
            <?php
            echo $_SESSION['MensajeTexto'];
            // Limpiar mensajes después de mostrarlos
            unset($_SESSION['MensajeTexto']);
            unset($_SESSION['MensajeTipo']);
            ?>
        </div>
        <script>
            setTimeout(function() {
                document.getElementById('mensaje').style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>

    <!-- Mensaje de alerta -->
    <div class="row">
        <div class="col-md-3 col-md-offset-5">
            <?php if (isset($_SESSION['MensajeTexto'])) { ?>
                <div class="alert <?php echo $_SESSION['MensajeTipo'] ?>" role="alert">
                    <?php echo $_SESSION['MensajeTexto'] ?>
                    <button class="delete"><i class="fa fa-times"></i></button>
                </div>
            <?php
                $_SESSION['MensajeTexto'] = null;
                $_SESSION['MensajeTipo'] = null;
            } ?>
        </div>
    </div>

    <!-- Home -->
    <section id="home" class="slider" data-stellar-background-ratio="0.5">
        <div class="container">
            <div class="row">
                <div class="owl-carousel owl-theme">
                    <div class="item item-first">
                        <div class="caption">
                            <div class="col-md-offset-1 col-md-10">
                                <h3>Soy un dentista. Yo creo sonrisas. ¿Cuál es tu súper poder?</h3>
                                <h1>Vida saludable</h1>
                                <a href="#team" class="section-btn btn btn-default smoothScroll">Conoce a nuestros dentistas</a>
                            </div>
                        </div>
                    </div>
                    <div class="item item-second">
                        <div class="caption">
                            <div class="col-md-offset-1 col-md-10">
                                <h3>Vamos a hacer tu vida más feliz</h3>
                                <h1>Nuevo estilo de vida</h1>
                                <a href="#about" class="section-btn btn btn-default btn-gray smoothScroll">Más sobre nosotros</a>
                            </div>
                        </div>
                    </div>
                    <div class="item item-third">
                        <div class="caption">
                            <div class="col-md-offset-1 col-md-10">
                                <h3>La odontología no es cara, lo que es caro es el descuido.</h3>
                                <h1>Información personal</h1>
                                <a href="#perfil" class="section-btn btn btn-default btn-blue smoothScroll">Perfil</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About -->
    <section id="about">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="about-info">
                        <h2 class="wow fadeInUp" data-wow-delay="0.6s">Bienvenido</h2>
                        <div class="wow fadeInUp" data-wow-delay="0.8s">
                            <p>En esta clínica se ofrecen servicios odontológicos a niños y adultos en diferentes ramas: Diagnóstico, Emergencias, Radiología, Periodoncia, Operatoria Dental, Odontopediatría, Endodoncia, Prótesis (Fija, Parcial Removible y Total), Cirugía y Ortodoncia.</p>
                            <h5>¿Por qué elegirnos?</h5>
                            <p>Somos un consultorio odontológico enfocados en mantener su salud oral y su estética dental. Brindamos acceso a un modelo de odontología de calidad con especialistas en cada rama, materiales dentales de excelente calidad y tecnología adecuada para mejorar la experiencia durante el tratamiento.</p>
                            <p>-Agenda tu valoración odontológica SIN COSTO</p>
                            <p>-Selecciona la forma de pago que más se ajuste a tu bolsillo, con facilidad de pago en nuestros tratamientos y diferentes alternativas.</p>
                            <h5>Visión</h5>
                            <p>Servicios odontológicos en Santander y Boyacá, logrando expansión a otros municipios, mejora continua de procesos y garantizando calidad y profesionalidad.</p>
                            <h5>Misión</h5>
                            <p>Brindar un servicio de excelencia en salud oral, basado en conocimientos, alta tecnología y calidez humana que cubran las necesidades y expectativas de nuestros pacientes.</p>
                        </div>
                        <figure class="profile wow fadeInUp" data-wow-delay="1s">
                            <img src="src/img/emily-perfil.png" class="img-responsive" alt="">
                            <figcaption>
                                <h3>Dra. EMILY BERNAL</h3>
                                <p>Odontóloga general con énfasis clínico en Odontología Biológica y Quirúrgica</p>
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team -->
    <section id="team" data-stellar-background-ratio="1">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="about-info">
                        <h2 class="wow fadeInUp" data-wow-delay="0.1s">Nuestros dentistas</h2>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-4 col-sm-6">
                    <div class="team-thumb wow fadeInUp" data-wow-delay="0.2s">
                        <img src="src/img/team-image1.jpg" class="img-responsive" alt="">
                        <div class="team-info">
                            <h3>Dr. Jaime Rolón</h3>
                            <p>Odontólogo general con énfasis en Odontología Biológica y Quirúrgica</p>
                            <p>Diplomados en Alta Estética</p>
                            <div class="team-contact-info">
                                <p><i class="fa fa-phone"></i> Contacto vía consultorio</p>
                                <p><i class="fa fa-envelope-o"></i> <a href="mailto:emilybernal902@gmail.com" target="_blank">emilybernal902@gmail.com</a></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="team-thumb wow fadeInUp" data-wow-delay="0.4s">
                        <img src="src/img/team-image2.jpg" class="img-responsive" alt="">
                        <div class="team-info">
                            <h3>Dr. Kaleth Quuaz</h3>
                            <p>Odontologo especialista en ortodoncia y ortopedia maxilar </p>

                            <div class="team-contact-info">
                                <p><i class="fa fa-phone"></i> Contacto vía consultorio</p>
                                <p><i class="fa fa-envelope-o"></i> <a href="mailto:emilybernal902@gmail.com" target="_blank">emilybernal902@gmail.com</a></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="team-thumb wow fadeInUp" data-wow-delay="0.6s">
                        <img src="src/img/team-image3.jpg" class="img-responsive" alt="">
                        <div class="team-info">
                            <h3>Dr. Emily Bernal</h3>
                            <p>Odontologa general con énfasis clínico en odontología biológica y quirúrgica</p>
                            <p>Diplomados en Alta Estética</p>
                            <div class="team-contact-info">
                                <p><i class="fa fa-phone"></i> 3105547320</p>
                                <p><i class="fa fa-envelope-o"></i> <a href="mailto:emilybernal902@gmail.com" target="_blank">emilybernal902@gmail.com</a></p>
                                <p><i class="fa fa-instagram"></i> <a href="https://instagram.com/dra.emilybernal?igshid=MzNlNGNkZWQ4Mg==" target="_blank">Instagram</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Appointment -->
    <section id="appointment" data-stellar-background-ratio="3">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <img src="src/img/appointment-image.jpg" class="img-responsive" alt="">
                </div>
                <div class="col-md-6 col-sm-6">
                    <form action="./crud/cita_INSERT.php?opciones=INS" method="POST" enctype="multipart/form-data" autocomplete="off" id="appointment-form" onsubmit="return validateForm()">
                        <div class="section-title wow fadeInUp" data-wow-delay="0.4s">
                            <h2>Realizar una Cita</h2>
                        </div>
                        <div class="wow fadeInUp" data-wow-delay="0.8s">
                            <div class="col-md-6 col-sm-6">
                                <label for="name">Nombre</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Nombre" required value="<?php echo isset($_SESSION['FormData']['name']) ? htmlspecialchars($_SESSION['FormData']['name']) : $row['nombre']; ?>">
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <label for="apellido">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido" required value="<?php echo isset($_SESSION['FormData']['name']) ? htmlspecialchars($_SESSION['FormData']['name']) : $row['apellido']; ?>">
                            </div>
                            <div class="col-md-12 col-sm-6">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Correo Electrónico" required value="<?php echo isset($_SESSION['FormData']['email']) ? htmlspecialchars($_SESSION['FormData']['email']) : $row['correo_electronico']; ?>">
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <label for="fecha_cita">Fecha de la cita</label>
                                <input type="text" class="form-control" name="fecha_cita" id="fecha_cita" required value="<?php echo isset($_SESSION['FormData']['fecha_cita']) ? htmlspecialchars($_SESSION['FormData']['fecha_cita']) : ''; ?>">
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <label for="hora">Hora de la cita</label>
                                <select class="form-control" name="hora" id="hora" required>
                                    <option value="">Seleccione una hora</option>
                                    <!-- Horarios de la mañana -->
                                    <option value="09:00 AM - 10:00 AM" <?php echo isset($_SESSION['FormData']['hora']) && $_SESSION['FormData']['hora'] == '09:00 AM - 10:00 AM' ? 'selected' : ''; ?>>09:00 AM - 10:00 AM</option>
                                    <option value="10:00 AM - 11:00 AM" <?php echo isset($_SESSION['FormData']['hora']) && $_SESSION['FormData']['hora'] == '10:00 AM - 11:00 AM' ? 'selected' : ''; ?>>10:00 AM - 11:00 AM</option>
                                    <option value="11:00 AM - 12:00 PM" <?php echo isset($_SESSION['FormData']['hora']) && $_SESSION['FormData']['hora'] == '11:00 AM - 12:00 PM' ? 'selected' : ''; ?>>11:00 AM - 12:00 PM</option>
                                    <!-- Horarios de la tarde -->
                                    <option value="02:00 PM - 03:00 PM" <?php echo isset($_SESSION['FormData']['hora']) && $_SESSION['FormData']['hora'] == '02:00 PM - 03:00 PM' ? 'selected' : ''; ?>>02:00 PM - 03:00 PM</option>
                                    <option value="03:00 PM - 04:00 PM" <?php echo isset($_SESSION['FormData']['hora']) && $_SESSION['FormData']['hora'] == '03:00 PM - 04:00 PM' ? 'selected' : ''; ?>>03:00 PM - 04:00 PM</option>
                                    <option value="04:00 PM - 05:00 PM" <?php echo isset($_SESSION['FormData']['hora']) && $_SESSION['FormData']['hora'] == '04:00 PM - 05:00 PM' ? 'selected' : ''; ?>>04:00 PM - 05:00 PM</option>
                                    <option value="05:00 PM - 06:00 PM" <?php echo isset($_SESSION['FormData']['hora']) && $_SESSION['FormData']['hora'] == '05:00 PM - 06:00 PM' ? 'selected' : ''; ?>>05:00 PM - 06:00 PM</option>
                                </select>
                                <div id="hora-error" class="error-message" style="display: none;"></div>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <label for="consultas">Consultas</label><br>
                                <select name="consultas" id="consultas" required>
                                    <?php while ($row1 = mysqli_fetch_array($resultado, MYSQLI_ASSOC)) { ?>
                                        <option value="<?php echo $row1['id_consultas']; ?>" <?php echo isset($_SESSION['FormData']['consultas']) && $_SESSION['FormData']['consultas'] == $row1['id_consultas'] ? 'selected' : ''; ?>>
                                            <?php echo $row1['tipo']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <label for="dentistas">Dentistas</label><br>
                                <select name="dentistas" id="dentistas" required>
                                    <?php while ($row2 = mysqli_fetch_array($resultadoDentistas, MYSQLI_ASSOC)) { ?>
                                        <option value="<?php echo $row2['id_doctor']; ?>" <?php echo isset($_SESSION['FormData']['dentistas']) && $_SESSION['FormData']['dentistas'] == $row2['id_doctor'] ? 'selected' : ''; ?>>
                                            <?php echo $row2['nombreD']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <br><label for="phone">Teléfono</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Teléfono" required value="<?php echo isset($_SESSION['FormData']['phone']) ? htmlspecialchars($_SESSION['FormData']['phone']) : $row['telefono']; ?>">
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <div class="g-recaptcha" data-sitekey="6LezIuwqAAAAABE2_UWVOaHe9DamIwxKhyXLffyO"></div>
                                <?php if (isset($_SESSION['CaptchaError'])) { ?>
                                    <p style="color: red; margin-top: 10px;"><?php echo $_SESSION['CaptchaError'];
                                                                                unset($_SESSION['CaptchaError']); ?></p>
                                <?php } ?>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <br><button type="submit" name="enviar" value="enviar" class="form-control" id="cf-submit">Enviar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfil -->
    <section id="perfil" style="margin-top: 10%;">
        <div class="container">
            <div class="main-body">
                <div class="row gutters-sm">
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center text-center">
                                    <img src="<?php echo $row['sexo'] == 'Masculino' ? './src/img/iconoH.jpg' : './src/img/iconoM.jpg'; ?>" class="rounded-circle" width="150" alt="">
                                    <div class="mt-3">
                                        <h3 class="name"><?php echo "{$row['nombre']} {$row['apellido']}"; ?></h3>
                                        <p class="text-secondary mb-1">Consultorio Odontológico Emily Bernal</p>
                                        <p class="text-muted font-size-sm"><?php echo $row['correo_electronico']; ?></p>
                                        <div class="card bg-light" style="margin-top: 20%;">
                                            <div class="card-header">
                                                <h4><strong>Acciones</strong></h4>
                                            </div>
                                            <div class="card-body">
                                                <label>Editar perfil</label>
                                                <a class="btn btn-primary" href="./editar_paciente.php"><i class="fa fa-edit"></i></a><br>
                                                <label>Gestionar mis Citas</label>
                                                <a class="btn btn-success" target="_blank" href="gestionar_citas.php"><i class="fa fa-eye"></i></a><br>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="mb-0">Nombre</h5>
                                    </div>
                                    <div class="col-sm-9 text-secondary"><?php echo $row['nombre']; ?></div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="mb-0">Apellido</h5>
                                    </div>
                                    <div class="col-sm-9 text-secondary"><?php echo $row['apellido']; ?></div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="mb-0">Sexo</h5>
                                    </div>
                                    <div class="col-sm-9 text-secondary"><?php echo $row['sexo']; ?></div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="mb-0">Correo electrónico</h5>
                                    </div>
                                    <div class="col-sm-9 text-secondary"><?php echo $row['correo_electronico']; ?></div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="mb-0">Teléfono</h5>
                                    </div>
                                    <div class="col-sm-9 text-secondary"><?php echo $row['telefono']; ?></div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <h5 class="mb-0">Fecha de nacimiento</h5>
                                    </div>
                                    <div class="col-sm-9 text-secondary"><?php echo $row['fecha_nacimiento']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Google Map -->
    <section id="google-map">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3968.44774838783!2d-73.61959973934803!3d5.932847479720224!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e41e51b70759ac5%3A0xf0a976a60eeb1784!2sOdontolog%C3%ADa%20Dra.Emily%20Bernal!5e0!3m2!1ses-419!2sco!4v1740097163838!5m2!1ses-419!2sco" width="100%" height="350" frameborder="0" style="border:0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </section>

    <!-- Footer -->
    <footer data-stellar-background-ratio="5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <div class="footer-thumb">
                        <h4 class="wow fadeInUp" data-wow-delay="0.4s">Dirección</h4>
                        <p>Barbosa, Santander</p>
                        <div class="contact-info">
                            <p><i class="fa fa-phone"></i> 3105547320</p>
                            <p><i class="fa fa-envelope-o"></i> <a href="mailto:emilybernal902@gmail.com">correo</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="footer-thumb">
                        <h4 class="wow fadeInUp" data-wow-delay="0.4s">Últimas Noticias</h4>
                        <div class="latest-stories">
                            <div class="stories-image">
                                <a href="" target="_blank"><img src="src/img/blanqueamiento.jpg" class="img-responsive" alt=""></a>
                            </div>
                            <div class="stories-info">
                                <h5>Últimas Tecnologías</h5>
                                </a>
                                <span>en el proceso dental</span>
                            </div>
                        </div>
                        <div class="latest-stories">
                            <div class="stories-image">
                                <a href="" target="_blank"><img src="src/img/evolucion.jpg" class="img-responsive" alt=""></a>
                            </div>
                            <div class="stories-info">
                                <h5>Valoración Gratis</h5>
                                </a>
                                <span>Emily Bernal</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="footer-thumb">
                        <div class="opening-hours">
                            <h4 class="wow fadeInUp" data-wow-delay="0.4s">Horario de Atención</h4>
                            <p>Lunes - Sabado <span>08:30 AM - 6:00 PM</span></p>

                        </div>
                        <ul class="social-icon">
                            <li><a href="https://wa.me/message/WZSLOAVLHOAJB1" target="_blank" class="fa fa-whatsapp" attr="whatsapp icon"></a></li>
                            <li><a href="https://instagram.com/dra.emilybernal?igshid=MzNlNGNkZWQ4Mg==" target="_blank" class="fa fa-instagram" attr="instagram icon"></a></li>


                        </ul>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 border-top">
                    <div class="col-md-4 col-sm-6">
                        <div class="copyright-text">
                            <p>ODONTOLOGÍA © Dra. Emily Bernal | Barbosa, Santander</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="footer-link">
                            <a>Política de privacidad</a>
                            <a>Cookies</a>
                            <a>Avisos legales</a>


                        </div>
                    </div>
                    <div class="col-md-2 col-sm-2 text-align-center">
                        <div class="angle-up-btn">
                            <a href="#top" class="smoothScroll wow fadeInUp" data-wow-delay="1.2s"><i class="fa fa-angle-up"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="src/js/bootstrap.min.js"></script>
    <script src="src/js/jquery.sticky.js"></script>
    <script src="src/js/jquery.stellar.min.js"></script>
    <script src="src/js/wow.min.js"></script>
    <script src="src/js/smoothscroll.js"></script>
    <script src="src/js/owl.carousel.min.js"></script>
    <script type="module" src="src/js/auth.js?v=1.0"></script>
    <script src="src/js/custom.js?v=1.0"></script>
    <script>
        // Manejo de cierre de notificaciones
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.alert .delete').forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
            });
        });
    </script>
</body>

</html>