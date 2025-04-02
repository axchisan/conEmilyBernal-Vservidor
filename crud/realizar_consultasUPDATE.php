<?php
session_start();
require_once '../php/conexionDB.php';
require_once '../php/consultas.php';

if (!isset($_GET['accion'])) {
    $_SESSION['MensajeTexto'] = "Advertencia: Acción no permitida.";
    $_SESSION['MensajeTipo'] = "is-warning";
    header("Location: ../Admin/inicioAdmin.php");
    exit;
}

$opcion = $_GET['accion'];

try {
    switch ($opcion) {
        case 'UDT':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
                $diagnostico = trim(filter_var($_POST['Diagnostico'], FILTER_SANITIZE_STRING));
                $descripcion = trim(filter_var($_POST['Descripción'], FILTER_SANITIZE_STRING));
                $medicina = trim(filter_var($_POST['Medicina'], FILTER_SANITIZE_STRING));

                $query1 = "INSERT INTO paciente_diagnostico (id_cita, diagnostico, descripcion, medicina) VALUES (?, ?, ?, ?)";
                $query2 = "UPDATE citas SET estado = 'A' WHERE id_cita = ?";

                $stmt1 = mysqli_prepare($link, $query1);
                mysqli_stmt_bind_param($stmt1, "isss", $id, $diagnostico, $descripcion, $medicina);

                $stmt2 = mysqli_prepare($link, $query2);
                mysqli_stmt_bind_param($stmt2, "i", $id);

                $success1 = mysqli_stmt_execute($stmt1);
                $success2 = mysqli_stmt_execute($stmt2);

                if ($success1 && $success2) {
                    $_SESSION['MensajeTexto'] = "Cita actualizada con éxito.";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-info text-white";
                } else {
                    $_SESSION['MensajeTexto'] = "Error actualizando la cita.";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
                    error_log("Error en actualización: " . mysqli_error($link));
                }

                mysqli_stmt_close($stmt1);
                mysqli_stmt_close($stmt2);
                mysqli_close($link);

                header("Location: ../Admin/inicioAdmin.php");
                exit;
            }
            break;

        case 'DLT':
            if (isset($_GET['id'], $_GET['estado'])) {
                $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
                $estado = filter_var($_GET['estado'], FILTER_SANITIZE_STRING);

                if ($estado === "I") {
                    $_SESSION['MensajeTexto'] = "No se puede borrar la cita porque aún no se ha realizado.";
                    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
                } else {
                    $query1 = "DELETE FROM informe_medico WHERE id_cita = ?";
                    $query2 = "DELETE FROM paciente_diagnostico WHERE id_cita = ?";
                    $query3 = "DELETE FROM citas WHERE id_cita = ?";

                    $stmt1 = mysqli_prepare($link, $query1);
                    mysqli_stmt_bind_param($stmt1, "i", $id);

                    $stmt2 = mysqli_prepare($link, $query2);
                    mysqli_stmt_bind_param($stmt2, "i", $id);

                    $stmt3 = mysqli_prepare($link, $query3);
                    mysqli_stmt_bind_param($stmt3, "i", $id);

                    $success1 = mysqli_stmt_execute($stmt1);
                    $success2 = mysqli_stmt_execute($stmt2);
                    $success3 = mysqli_stmt_execute($stmt3);

                    if ($success1 && $success2 && $success3) {
                        $_SESSION['MensajeTexto'] = "Cita borrada con éxito.";
                        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-info text-white";
                    } else {
                        $_SESSION['MensajeTexto'] = "Error borrando la cita.";
                        $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
                        error_log("Error al borrar: " . mysqli_error($link));
                    }

                    mysqli_stmt_close($stmt1);
                    mysqli_stmt_close($stmt2);
                    mysqli_stmt_close($stmt3);
                }

                mysqli_close($link);
                header("Location: ../Admin/inicioAdmin.php");
                exit;
            }
            break;

        default:
            $_SESSION['MensajeTexto'] = "Advertencia: No se pudo identificar la acción a realizar.";
            $_SESSION['MensajeTipo'] = "is-warning";
            header("Location: ../Admin/inicioAdmin.php");
            exit;
    }
} catch (Exception $e) {
    error_log("Excepción no controlada: " . $e->getMessage());
    echo "Ha ocurrido un error. Estamos trabajando en corregir esta situación.";
} catch (Error $e) {
    error_log("Error no controlado: " . $e->getMessage());
    echo "Ha ocurrido un error. Estamos trabajando en corregir esta situación.";
}