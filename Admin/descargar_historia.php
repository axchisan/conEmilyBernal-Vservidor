<?php
ob_start();
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

// Verificar IDs de cita y paciente
if (!isset($_POST['id_cita']) || !isset($_POST['id_paciente'])) {
    $_SESSION['MensajeTexto'] = "Error: ID de cita o paciente no proporcionado.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: historia_clinica.php");
    exit();
}

$id_cita = mysqli_real_escape_string($link, $_POST['id_cita']);
$id_paciente = mysqli_real_escape_string($link, $_POST['id_paciente']);
$doctor_id = $_SESSION['id_doctor'];

// Obtener datos del paciente
$patient = consultarPaciente($link, $id_paciente);
if (!$patient) {
    $_SESSION['MensajeTexto'] = "Error: No se pudo obtener la información del paciente.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: historia_clinica.php");
    exit();
}

// Calcular edad del paciente
$age = 'N/A';
if (isset($patient['fecha_nacimiento']) && !empty($patient['fecha_nacimiento'])) {
    $birthDate = new DateTime($patient['fecha_nacimiento']);
    $currentDate = new DateTime();
    $age = $currentDate->diff($birthDate)->y;
}

// Obtener datos de la cita
$query = "SELECT c.*, con.tipo, d.nombreD 
          FROM citas c 
          LEFT JOIN consultas con ON con.id_consultas = c.id_consultas 
          LEFT JOIN doctor d ON d.id_doctor = c.id_doctor 
          WHERE c.id_cita = ? AND c.id_paciente = ? AND c.id_doctor = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "iii", $id_cita, $id_paciente, $doctor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['MensajeTexto'] = "Error: No se encontró la cita.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: historia_clinica.php");
    exit();
}
$appointment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Obtener informe médico
$medical_report_query = "SELECT * FROM informe_medico WHERE id_cita = ?";
$stmt = mysqli_prepare($link, $medical_report_query);
mysqli_stmt_bind_param($stmt, "i", $id_cita);
mysqli_stmt_execute($stmt);
$medical_report_result = mysqli_stmt_get_result($stmt);
$medical_report = mysqli_num_rows($medical_report_result) > 0 ? mysqli_fetch_assoc($medical_report_result) : [];
mysqli_stmt_close($stmt);

// Parsear el odontograma si existe
$odontogram_data = isset($medical_report['odontogram_data']) ? json_decode($medical_report['odontogram_data'], true) : [];

// Verificar y cargar TCPDF
if (!file_exists('../Reportes/tcpdf/tcpdf.php')) {
    die("Error: No se encontró el archivo TCPDF en '../Reportes/tcpdf/tcpdf.php'");
}
require_once '../Reportes/tcpdf/tcpdf.php';

// Crear instancia de TCPDF
class MYPDF extends TCPDF {
    // No necesitamos el método Footer() ya que la firma se agregará manualmente
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuración básica del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema Odontológico');
$pdf->SetTitle('Historia Clínica');
$pdf->SetSubject('Historia Clínica del Paciente');
$pdf->SetKeywords('Historia, Clínica, Paciente, Odontología');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false); // Desactivar el footer para evitar que se muestre en todas las páginas
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15); // Reducir el margen inferior ya que no usamos el footer

// Añadir página para Información General y Anamnesis
$pdf->AddPage();

// Fondo del encabezado
$pdf->SetFillColor(230, 240, 255);
$pdf->Rect(0, 0, 210, 35, 'F');

// Logo
if (file_exists('../src/img/logo.png')) {
    $pdf->Image('../src/img/logo.png', 15, 10, 30, 0);
}

// Información del consultorio
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetXY(50, 10);
$pdf->Cell(0, 5, 'Consultorio Odontológico Dra. Emily Valeria Bernal Jaimes', 0, 1, 'L');
$pdf->SetXY(50, 15);
$pdf->Cell(0, 5, 'Odontóloga UNICOC T.P. 1007363847', 0, 1, 'L');

// Título y número de historia
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(0, 102, 204);
$pdf->SetY(25);
$pdf->Cell(150, 10, 'HISTORIA CLÍNICA', 0, 0, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetLineStyle(array('width' => 0.2, 'color' => array(0, 102, 204)));
$pdf->Cell(30, 10, 'Nº ' . $id_cita, 1, 1, 'R', 1);

// Línea separadora
$pdf->SetLineStyle(array('width' => 0.3, 'color' => array(0, 102, 204)));
$pdf->Line(15, 35, 195, 35);
$pdf->Ln(10);

// Sección "Información General"
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 10, 'INFORMACIÓN GENERAL', 0, 1, 'L');
$pdf->Ln(5);

// Tabla de Información General
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetFillColor(245, 245, 245);
$pdf->SetDrawColor(150, 150, 150);

// Definir anchos de columnas
$col1_label_width = 50;
$col1_value_width = 40;
$col2_label_width = 50;
$col2_value_width = 40;

$pdf->Cell($col1_label_width, 7, 'Nombre y apellidos:', 1, 0, 'L', 1);
$pdf->Cell($col1_value_width, 7, $patient['nombre'] . ' ' . $patient['apellido'], 1, 0, 'L', 0);
$pdf->Cell($col2_label_width, 7, 'Fecha y lugar de nacimiento:', 1, 0, 'L', 1);
$pdf->Cell($col2_value_width, 7, $patient['fecha_nacimiento'], 1, 1, 'L', 0);

$pdf->Cell($col1_label_width, 7, 'Edad:', 1, 0, 'L', 1);
$pdf->Cell($col1_value_width, 7, $age . ($age !== 'N/A' ? ' años' : ''), 1, 0, 'L', 0);
$pdf->Cell($col2_label_width, 7, 'Nº documento:', 1, 0, 'L', 1);
$pdf->Cell($col2_value_width, 7, $patient['cedula'] ?? 'N/A', 1, 1, 'L', 0);

$pdf->Cell($col1_label_width, 7, 'Dirección de residencia:', 1, 0, 'L', 1);
$pdf->Cell($col1_value_width, 7, $patient['lugar_direccion_residencia'] ?? 'N/A', 1, 0, 'L', 0);
$pdf->Cell($col2_label_width, 7, 'Teléfono:', 1, 0, 'L', 1);
$pdf->Cell($col2_value_width, 7, $patient['telefono'] ?? 'N/A', 1, 1, 'L', 0);

$pdf->Cell($col1_label_width, 7, 'EPS:', 1, 0, 'L', 1);
$pdf->Cell($col1_value_width, 7, $patient['eps'] ?? 'N/A', 1, 0, 'L', 0);
$pdf->Cell($col2_label_width, 7, 'Género:', 1, 0, 'L', 1);
$pdf->Cell($col2_value_width, 7, $patient['sexo'] ?? 'N/A', 1, 1, 'L', 0);

$pdf->Cell($col1_label_width, 7, 'Estado civil:', 1, 0, 'L', 1);
$pdf->Cell($col1_value_width, 7, $patient['estado_civil'] ?? 'N/A', 1, 0, 'L', 0);
$pdf->Cell($col2_label_width, 7, 'Ocupación:', 1, 0, 'L', 1);
$pdf->Cell($col2_value_width, 7, $patient['ocupacion'] ?? 'N/A', 1, 1, 'L', 0);

$pdf->Cell($col1_label_width, 7, 'Acompañante:', 1, 0, 'L', 1);
$pdf->Cell($col1_value_width, 7, $patient['menor_acompanante'] ?? 'N/A', 1, 0, 'L', 0);
$pdf->Cell($col2_label_width, 7, 'Emergencia: llamar a:', 1, 0, 'L', 1);
$pdf->Cell($col2_value_width, 7, $patient['emergencia_nombre'] ?? 'N/A', 1, 1, 'L', 0);

$pdf->Cell($col1_label_width, 7, 'Parentesco:', 1, 0, 'L', 1);
$pdf->Cell($col1_value_width, 7, $patient['menor_parentesco'] ?? 'N/A', 1, 0, 'L', 0);
$pdf->Cell($col2_label_width, 7, 'Teléfono:', 1, 0, 'L', 1);
$pdf->Cell($col2_value_width, 7, $patient['emergencia_telefono'] ?? 'N/A', 1, 1, 'L', 0);

$pdf->Cell($col1_label_width, 7, 'Teléfono:', 1, 0, 'L', 1);
$pdf->Cell($col1_value_width, 7, $patient['menor_telefono'] ?? 'N/A', 1, 0, 'L', 0);
$pdf->Cell($col2_label_width, 7, 'Motivo de consulta:', 1, 0, 'L', 1);
$pdf->Cell($col2_value_width, 7, $appointment['tipo'], 1, 1, 'L', 0);

$pdf->Ln(15);

// Sección "Anamnesis"
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 10, 'ANAMNESIS', 0, 1, 'L');
$pdf->Ln(5);

// Tabla de Anamnesis
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(33, 37, 41);
$anamnesis_width = 100;
$anamnesis_label_width = 70;
$anamnesis_check_width = 15;

// Encabezados de la tabla
$pdf->SetFillColor(230, 240, 255);
$pdf->Cell($anamnesis_label_width, 7, 'Historia Familiar o Personal', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width, 7, 'Sí', 1, 0, 'C', 1);
$pdf->Cell($anamnesis_check_width, 7, 'No', 1, 0, 'C', 1);

// Cuadro de Alertas Médicas
$alertas_x = 15 + $anamnesis_width + 10;
$alertas_y = $pdf->GetY();
$pdf->SetXY($alertas_x, $alertas_y);
$pdf->SetFillColor(230, 240, 255);
$pdf->Cell(70, 7, 'Alertas Médicas', 1, 1, 'C', 1);
$alertas_y += 7;
$pdf->SetXY($alertas_x, $alertas_y);
$pdf->SetFillColor(245, 245, 245);
$pdf->MultiCell(70, 35, $patient['alertas_medicas'] ?? 'N/A', 1, 'L', 1);

// Continuar tabla de Anamnesis
$pdf->SetXY(15, $alertas_y);
$pdf->SetFillColor(245, 245, 245);

$pdf->Cell($anamnesis_label_width, 7, '1. Enf. Cardiovasculares', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_cardiovasculares'] == 'Sí' ? 'X' : '', 1, 0, 'C', 0);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_cardiovasculares'] == 'No' ? 'X' : '', 1, 1, 'C', 0);

$pdf->Cell($anamnesis_label_width, 7, '2. Enf. Hemorrágicas', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_hemorragicas'] == 'Sí' ? 'X' : '', 1, 0, 'C', 0);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_hemorragicas'] == 'No' ? 'X' : '', 1, 1, 'C', 0);

$pdf->Cell($anamnesis_label_width, 7, '3. Enf. Dermatológicas', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_dermatologicas'] == 'Sí' ? 'X' : '', 1, 0, 'C', 0);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_dermatologicas'] == 'No' ? 'X' : '', 1, 1, 'C', 0);

$pdf->Cell($anamnesis_label_width, 7, '4. Enf. Mentales', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_mentales'] == 'Sí' ? 'X' : '', 1, 0, 'C', 0);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_mentales'] == 'No' ? 'X' : '', 1, 1, 'C', 0);

$pdf->Cell($anamnesis_label_width, 7, '5. Diabetes', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_diabetes'] == 'Sí' ? 'X' : '', 1, 0, 'C', 0);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_diabetes'] == 'No' ? 'X' : '', 1, 1, 'C', 0);

$pdf->Cell($anamnesis_label_width, 7, '6. Cáncer', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_cancer'] == 'Sí' ? 'X' : '', 1, 0, 'C', 0);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_cancer'] == 'No' ? 'X' : '', 1, 1, 'C', 0);

$pdf->Cell($anamnesis_label_width, 7, '7. Artritis', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_artritis'] == 'Sí' ? 'X' : '', 1, 0, 'C', 0);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_artritis'] == 'No' ? 'X' : '', 1, 1, 'C', 0);

$pdf->Cell($anamnesis_label_width, 7, '8. Alergias', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_alergias'] == 'Sí' ? 'X' : '', 1, 0, 'C', 0);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_alergias'] == 'No' ? 'X' : '', 1, 1, 'C', 0);

$pdf->Cell($anamnesis_label_width, 7, '9. Cirugías', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_cirugias'] == 'Sí' ? 'X' : '', 1, 0, 'C', 0);
$pdf->Cell($anamnesis_check_width, 7, $patient['historia_cirugias'] == 'No' ? 'X' : '', 1, 1, 'C', 0);

$pdf->Cell($anamnesis_label_width, 7, '10. Otros', 1, 0, 'L', 1);
$pdf->Cell($anamnesis_check_width * 2, 7, $patient['historia_otros'] ?? 'N/A', 1, 1, 'L', 0);

// Ajustar posición final
$end_y = max($pdf->GetY(), $alertas_y + 35);
$pdf->SetY($end_y);

// Agregar la firma manualmente al final de la primera página, cerca del borde inferior
$pdf->SetY(267); // Posicionar a 267 mm desde la parte superior (297 mm - 10 mm de margen inferior - 20 mm de altura del rectángulo)
$pdf->SetFont('helvetica', 'I', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(150, 7, 'La información es suministrada por el paciente y en constancia firma:', 0, 0, 'L');
$pdf->SetLineStyle(array('width' => 0.3, 'color' => array(0, 0, 0)));
$pdf->Line(15, $pdf->GetY() + 2, 80, $pdf->GetY() + 2);
$pdf->SetXY(165, $pdf->GetY() - 5);
$pdf->SetFillColor(245, 245, 245);
$pdf->SetDrawColor(0, 0, 0);
$pdf->Rect(165, $pdf->GetY(), 30, 20, 'DF');

// Nueva página para el odontograma
if (!empty($odontogram_data)) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 102, 204);
    $pdf->Cell(0, 10, 'ODONTOGRAMA', 0, 1, 'L');
    $pdf->Ln(5);

    // Tabla del odontograma
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->SetFillColor(230, 240, 255);
    $pdf->Cell(30, 7, 'Diente', 1, 0, 'C', 1);
    $pdf->Cell(150, 7, 'Condición', 1, 1, 'C', 1);

    $pdf->SetFillColor(245, 245, 245);
    foreach ($odontogram_data as $entry) {
        $pdf->Cell(30, 7, $entry['tooth'], 1, 0, 'C', 0);
        $pdf->Cell(150, 7, $entry['condition'], 1, 1, 'L', 0);
    }
}

// Generar y descargar PDF
ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Historia_Clinica_' . $id_paciente . '_Cita_' . $id_cita . '.pdf"');
$pdf->Output('Historia_Clinica_' . $id_paciente . '_Cita_' . $id_cita . '.pdf', 'D');
exit();