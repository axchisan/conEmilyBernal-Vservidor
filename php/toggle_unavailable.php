<?php
include_once 'conexionDB.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';
require_once '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Configurar el encabezado para JSON
header('Content-Type: application/json; charset=utf-8');
mb_internal_encoding('UTF-8');

// Inicializar la respuesta
$response = ['status' => 'error', 'message' => '', 'invalid_emails' => []];

try {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $id_doctor = isset($_POST['id_doctor']) ? (int)$_POST['id_doctor'] : null;

        if (empty($id_doctor)) {
            throw new Exception('ID del doctor no proporcionado.');
        }

        // Obtener nombre del doctor
        $stmtDoctor = $link->prepare("SELECT nombreD, apellido FROM doctor WHERE id_doctor = ?");
        $stmtDoctor->bind_param("i", $id_doctor);
        $stmtDoctor->execute();
        $resultDoctor = $stmtDoctor->get_result();
        $doctor = $resultDoctor->fetch_assoc();
        $doctorName = $doctor ? $doctor['nombreD'] . ' ' . $doctor['apellido'] : 'Desconocido';
        $stmtDoctor->close();

        if ($action === 'add' && isset($_POST['date'])) {
            $date = $_POST['date'];
            if (empty($date)) {
                throw new Exception('Fecha no proporcionada.');
            }

            $stmt = $link->prepare("INSERT INTO unavailable_dates (id_doctor, unavailable_date) VALUES (?, ?)");
            $stmt->bind_param("is", $id_doctor, $date);
            $response['status'] = $stmt->execute() ? 'success' : 'error';
            $response['message'] = $stmt->error ?: '';
            $stmt->close();
        } elseif ($action === 'remove' && isset($_POST['date'])) {
            $date = $_POST['date'];
            if (empty($date)) {
                throw new Exception('Fecha no proporcionada.');
            }

            $stmt = $link->prepare("DELETE FROM unavailable_dates WHERE id_doctor = ? AND unavailable_date = ?");
            $stmt->bind_param("is", $id_doctor, $date);
            $response['status'] = $stmt->execute() ? 'success' : 'error';
            $response['message'] = $stmt->error ?: '';
            $stmt->close();
        } elseif ($action === 'cancel' && isset($_POST['date'])) {
            $date = $_POST['date'];
            if (empty($date)) {
                throw new Exception('Fecha no proporcionada.');
            }

            $stmt = $link->prepare("
                SELECT c.id_cita, p.nombre, p.correo_electronico, co.tipo AS tipo_cita, c.hora_cita 
                FROM citas c 
                JOIN pacientes p ON c.id_paciente = p.id_paciente 
                JOIN consultas co ON c.id_consultas = co.id_consultas 
                WHERE c.id_doctor = ? AND c.fecha_cita = ?
            ");
            $stmt->bind_param("is", $id_doctor, $date);
            $stmt->execute();
            $resultAppointments = $stmt->get_result();

            if ($resultAppointments->num_rows > 0) {
                $appointmentsToDelete = [];
                while ($row = $resultAppointments->fetch_assoc()) {
                    $appointmentsToDelete[] = [
                        'id_cita' => $row['id_cita'],
                        'nombre' => $row['nombre'],
                        'correo_electronico' => $row['correo_electronico'],
                        'tipo_cita' => $row['tipo_cita'],
                        'hora_cita' => $row['hora_cita']
                    ];
                }
                $stmt->close();

                $stmtInsert = $link->prepare("INSERT INTO unavailable_dates (id_doctor, unavailable_date) VALUES (?, ?)");
                $stmtInsert->bind_param("is", $id_doctor, $date);
                if ($stmtInsert->execute()) {
                    $mail = new PHPMailer(true);
                    $allEmailsSent = true;
                    $invalidEmails = [];

                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'arciniegasgerenaduvanyair@gmail.com';
                        $mail->Password = 'yhad jjzz ygxe ignl';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port = 465;
                        $mail->CharSet = 'UTF-8';
                        $mail->setFrom('arciniegasgerenaduvanyair@gmail.com', 'Consultorio Emily Bernal');
                        $mail->isHTML(true);

                        foreach ($appointmentsToDelete as $appointment) {
                            if (!filter_var($appointment['correo_electronico'], FILTER_VALIDATE_EMAIL)) {
                                $invalidEmails[] = $appointment['correo_electronico'];
                                continue;
                            }

                            $mail->addAddress($appointment['correo_electronico']);
                            $mail->Subject = 'Cancelación de Cita - Consultorio Emily Bernal';
                            $mail->Body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                                    <h2 style='color: #6f42c1; text-align: center;'>Cancelación de Cita - Consultorio Emily Bernal</h2>
                                    <p>Estimado/a <strong>" . htmlspecialchars($appointment['nombre'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
                                    <p>Lamentamos informarte que tu cita con el Dr./Dra. <strong>" . htmlspecialchars($doctorName, ENT_QUOTES, 'UTF-8') . "</strong> ha sido cancelada debido a un imprevisto.</p>
                                    <h3 style='color: #333;'>Detalles de la Cita Cancelada:</h3>
                                    <ul style='list-style: none; padding: 0;'>
                                        <li><strong>Fecha:</strong> " . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "</li>
                                        <li><strong>Hora:</strong> " . htmlspecialchars($appointment['hora_cita'], ENT_QUOTES, 'UTF-8') . "</li>
                                        <li><strong>Tipo de Consulta:</strong> " . htmlspecialchars($appointment['tipo_cita'], ENT_QUOTES, 'UTF-8') . "</li>
                                    </ul>
                                    <p>Te invitamos a registrar tu cita en otra fecha lo antes posible. Puedes hacerlo aquí:</p>
                                    <div style='text-align: center; margin: 20px 0;'>
                                        <a href='https://odontologiaemilybernal.com' style='background-color: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Registrar Nueva Cita</a>
                                    </div>
                                    <p>Si tienes preguntas, contáctanos:</p>
                                    <ul style='list-style: none; padding: 0;'>
                                        <li><strong>Teléfono:</strong> +573105547320</li>
                                        <li><strong>WhatsApp:</strong> <a href='https://wa.me/message/WZSLOAVLHOAJB1'>Contactar a la Dra. Emily Bernal</a></li>
                                        <li><strong>Correo:</strong> emilybernal902@gmail.com</li>
                                    </ul>
                                    <p>Gracias por tu comprensión.</p>
                                    <p style='text-align: center; color: #888; font-size: 12px;'>Atentamente,<br>El equipo de <strong>Consultorio Emily Bernal</strong></p>
                                </div>
                            ";
                            $mail->AltBody = "Estimado/a " . htmlspecialchars($appointment['nombre'], ENT_QUOTES, 'UTF-8') . ",\n\n" .
                                            "Lamentamos informarte que tu cita con el Dr./Dra. " . htmlspecialchars($doctorName, ENT_QUOTES, 'UTF-8') . " ha sido cancelada debido a un imprevisto.\n\n" .
                                            "Detalles de la Cita Cancelada:\n" .
                                            "- Fecha: " . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "\n" .
                                            "- Hora: " . htmlspecialchars($appointment['hora_cita'], ENT_QUOTES, 'UTF-8') . "\n" .
                                            "- Tipo de Consulta: " . htmlspecialchars($appointment['tipo_cita'], ENT_QUOTES, 'UTF-8') . "\n\n" .
                                            "Te invitamos a registrar tu cita en otra fecha lo antes posible. Puedes hacerlo aquí: https://odontologiaemilybernal.com\n\n" .
                                            "Si tienes preguntas, contáctanos:\n" .
                                            "- Teléfono: +573105547320\n" .
                                            "- WhatsApp: https://wa.me/message/WZSLOAVLHOAJB1\n" .
                                            "- Correo: emilybernal902@gmail.com\n\n" .
                                            "Gracias por tu comprensión.\n\n" .
                                            "Atentamente,\nEl equipo de Consultorio Emily Bernal";
                            try {
                                if (!$mail->send()) {
                                    $allEmailsSent = false;
                                    $response['message'] .= "Error al enviar correo a " . htmlspecialchars($appointment['correo_electronico'], ENT_QUOTES, 'UTF-8') . ": {$mail->ErrorInfo}\n";
                                }
                            } catch (Exception $e) {
                                $allEmailsSent = false;
                                $response['message'] .= "Error al enviar correo a " . htmlspecialchars($appointment['correo_electronico'], ENT_QUOTES, 'UTF-8') . ": {$e->getMessage()}\n";
                            }
                            $mail->clearAddresses();
                        }

                        $response['invalid_emails'] = $invalidEmails;
                        if (!empty($invalidEmails)) {
                            $response['message'] .= "Los siguientes correos no son válidos y no se enviaron notificaciones: " . implode(", ", $invalidEmails) . "\n";
                        }

                        if ($allEmailsSent || empty($invalidEmails)) {
                            $stmtDelete = $link->prepare("DELETE FROM citas WHERE id_doctor = ? AND fecha_cita = ?");
                            $stmtDelete->bind_param("is", $id_doctor, $date);
                            $response['status'] = $stmtDelete->execute() ? 'success' : 'error';
                            $response['message'] .= $stmtDelete->execute() ? 'Citas canceladas y notificaciones enviadas.' : 'Error al eliminar las citas: ' . $stmtDelete->error;
                            $stmtDelete->close();
                        } else {
                            $response['message'] .= 'No se pudieron enviar todos los correos debido a direcciones inválidas.';
                        }
                    } catch (Exception $e) {
                        $response['message'] = "Error al enviar correos: {$mail->ErrorInfo}";
                    }
                } else {
                    $response['message'] = 'Error al registrar la cancelación: ' . $stmtInsert->error;
                }
                $stmtInsert->close();
            } else {
                $response['message'] = 'No hay citas para cancelar.';
            }
        } elseif ($action === 'cancel_single' && isset($_POST['id_cita'])) {
            $id_cita = (int)$_POST['id_cita'];

            // Obtener los detalles de la cita específica
            $stmt = $link->prepare("
                SELECT c.id_cita, c.fecha_cita, p.nombre, p.correo_electronico, co.tipo AS tipo_cita, c.hora_cita 
                FROM citas c 
                JOIN pacientes p ON c.id_paciente = p.id_paciente 
                JOIN consultas co ON c.id_consultas = co.id_consultas 
                WHERE c.id_cita = ? AND c.id_doctor = ?
            ");
            $stmt->bind_param("ii", $id_cita, $id_doctor);
            $stmt->execute();
            $resultAppointment = $stmt->get_result();

            if ($resultAppointment->num_rows > 0) {
                $appointment = $resultAppointment->fetch_assoc();
                $stmt->close();

                $mail = new PHPMailer(true);
                $allEmailsSent = true;
                $invalidEmails = [];

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'arciniegasgerenaduvanyair@gmail.com';
                    $mail->Password = 'yhad jjzz ygxe ignl';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    $mail->CharSet = 'UTF-8';
                    $mail->setFrom('arciniegasgerenaduvanyair@gmail.com', 'Consultorio Emily Bernal');
                    $mail->isHTML(true);

                    if (!filter_var($appointment['correo_electronico'], FILTER_VALIDATE_EMAIL)) {
                        $invalidEmails[] = $appointment['correo_electronico'];
                    } else {
                        $mail->addAddress($appointment['correo_electronico']);
                        $mail->Subject = 'Cancelación de Cita - Consultorio Emily Bernal';
                        $mail->Body = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                                <h2 style='color: #6f42c1; text-align: center;'>Cancelación de Cita - Consultorio Emily Bernal</h2>
                                <p>Estimado/a <strong>" . htmlspecialchars($appointment['nombre'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
                                <p>Lamentamos informarte que tu cita con el Dr./Dra. <strong>" . htmlspecialchars($doctorName, ENT_QUOTES, 'UTF-8') . "</strong> ha sido cancelada debido a un imprevisto.</p>
                                <h3 style='color: #333;'>Detalles de la Cita Cancelada:</h3>
                                <ul style='list-style: none; padding: 0;'>
                                    <li><strong>Fecha:</strong> " . htmlspecialchars($appointment['fecha_cita'], ENT_QUOTES, 'UTF-8') . "</li>
                                    <li><strong>Hora:</strong> " . htmlspecialchars($appointment['hora_cita'], ENT_QUOTES, 'UTF-8') . "</li>
                                    <li><strong>Tipo de Consulta:</strong> " . htmlspecialchars($appointment['tipo_cita'], ENT_QUOTES, 'UTF-8') . "</li>
                                </ul>
                                <p>Te invitamos a registrar tu cita en otra fecha lo antes posible. Puedes hacerlo aquí:</p>
                                <div style='text-align: center; margin: 20px 0;'>
                                    <a href='https://odontologiaemilybernal.com' style='background-color: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Registrar Nueva Cita</a>
                                </div>
                                <p>Si tienes preguntas, contáctanos:</p>
                                <ul style='list-style: none; padding: 0;'>
                                    <li><strong>Teléfono:</strong> +573105547320</li>
                                    <li><strong>WhatsApp:</strong> <a href='https://wa.me/message/WZSLOAVLHOAJB1'>Contactar a la Dra. Emily Bernal</a></li>
                                    <li><strong>Correo:</strong> emilybernal902@gmail.com</li>
                                </ul>
                                <p>Gracias por tu comprensión.</p>
                                <p style='text-align: center; color: #888; font-size: 12px;'>Atentamente,<br>El equipo de <strong>Consultorio Emily Bernal</strong></p>
                            </div>
                        ";
                        $mail->AltBody = "Estimado/a " . htmlspecialchars($appointment['nombre'], ENT_QUOTES, 'UTF-8') . ",\n\n" .
                                        "Lamentamos informarte que tu cita con el Dr./Dra. " . htmlspecialchars($doctorName, ENT_QUOTES, 'UTF-8') . " ha sido cancelada debido a un imprevisto.\n\n" .
                                        "Detalles de la Cita Cancelada:\n" .
                                        "- Fecha: " . htmlspecialchars($appointment['fecha_cita'], ENT_QUOTES, 'UTF-8') . "\n" .
                                        "- Hora: " . htmlspecialchars($appointment['hora_cita'], ENT_QUOTES, 'UTF-8') . "\n" .
                                        "- Tipo de Consulta: " . htmlspecialchars($appointment['tipo_cita'], ENT_QUOTES, 'UTF-8') . "\n\n" .
                                        "Te invitamos a registrar tu cita en otra fecha lo antes posible. Puedes hacerlo aquí: https://odontologiaemilybernal.com\n\n" .
                                        "Si tienes preguntas, contáctanos:\n" .
                                        "- Teléfono: +573105547320\n" .
                                        "- WhatsApp: https://wa.me/message/WZSLOAVLHOAJB1\n" .
                                        "- Correo: emilybernal902@gmail.com\n\n" .
                                        "Gracias por tu comprensión.\n\n" .
                                        "Atentamente,\nEl equipo de Consultorio Emily Bernal";
                        try {
                            if (!$mail->send()) {
                                $allEmailsSent = false;
                                $response['message'] .= "Error al enviar correo a " . htmlspecialchars($appointment['correo_electronico'], ENT_QUOTES, 'UTF-8') . ": {$mail->ErrorInfo}\n";
                            }
                        } catch (Exception $e) {
                            $allEmailsSent = false;
                            $response['message'] .= "Error al enviar correo a " . htmlspecialchars($appointment['correo_electronico'], ENT_QUOTES, 'UTF-8') . ": {$e->getMessage()}\n";
                        }
                        $mail->clearAddresses();
                    }

                    $response['invalid_emails'] = $invalidEmails;
                    if (!empty($invalidEmails)) {
                        $response['message'] .= "Los siguientes correos no son válidos y no se enviaron notificaciones: " . implode(", ", $invalidEmails) . "\n";
                    }

                    if ($allEmailsSent || empty($invalidEmails)) {
                        $stmtDelete = $link->prepare("DELETE FROM citas WHERE id_cita = ? AND id_doctor = ?");
                        $stmtDelete->bind_param("ii", $id_cita, $id_doctor);
                        $response['status'] = $stmtDelete->execute() ? 'success' : 'error';
                        $response['message'] .= $stmtDelete->execute() ? 'Cita cancelada y notificación enviada.' : 'Error al eliminar la cita: ' . $stmtDelete->error;
                        $stmtDelete->close();
                    } else {
                        $response['message'] .= 'No se pudo enviar el correo debido a una dirección inválida.';
                    }
                } catch (Exception $e) {
                    $response['message'] = "Error al enviar correo: {$mail->ErrorInfo}";
                }
            } else {
                $response['message'] = 'No se encontró la cita para cancelar.';
                $stmt->close();
            }
        } else {
            $response['message'] = 'Acción o datos incompletos.';
        }
    } else {
        $response['message'] = 'Datos incompletos.';
    }
} catch (Exception $e) {
    $response['message'] = 'Error en el servidor: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
mysqli_close($link);
?>