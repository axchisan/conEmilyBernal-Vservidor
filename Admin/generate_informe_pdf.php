<?php
// Iniciar buffer de salida
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

// Verificar conexión a la base de datos
if (!$link) {
    die("Error: No se pudo conectar a la base de datos: " . mysqli_connect_error());
}

// Verificar si se recibió el ID del paciente
if (!isset($_POST['patient_id']) || empty($_POST['patient_id'])) {
    die("Error: ID de paciente no proporcionado.");
}

$patient_id = mysqli_real_escape_string($link, $_POST['patient_id']);
$doctor_id = $_SESSION['id_doctor'];

// Verificar si la sesión del doctor está activa
if (!isset($_SESSION['id_doctor'])) {
    die("Error: Sesión de doctor no encontrada.");
}

// Obtener datos del paciente
$patient = consultarPaciente($link, $patient_id);
if (!$patient) {
    die("Error: No se pudo obtener la información del paciente.");
}

// Calcular la edad del paciente
if (isset($patient['fecha_nacimiento']) && !empty($patient['fecha_nacimiento'])) {
    $birthDate = new DateTime($patient['fecha_nacimiento']);
    $currentDate = new DateTime();
    $age = $currentDate->diff($birthDate)->y;
} else {
    $age = 'N/A';
}

// Obtener datos de la cita más reciente
$query = "SELECT c.*, con.tipo, d.nombreD 
          FROM citas c 
          LEFT JOIN consultas con ON con.id_consultas = c.id_consultas 
          LEFT JOIN doctor d ON d.id_doctor = c.id_doctor 
          WHERE c.id_paciente = ? AND c.id_doctor = ? 
          ORDER BY c.fecha_cita DESC LIMIT 1";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "ii", $patient_id, $doctor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$result || mysqli_num_rows($result) == 0) {
    die("Error: No se encontraron citas para este paciente.");
}
$appointment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Obtener el informe médico existente
$medical_report_query = "SELECT * FROM informe_medico WHERE id_cita = ?";
$stmt = mysqli_prepare($link, $medical_report_query);
mysqli_stmt_bind_param($stmt, "i", $appointment['id_cita']);
mysqli_stmt_execute($stmt);
$medical_report_result = mysqli_stmt_get_result($stmt);
$medical_report = mysqli_num_rows($medical_report_result) > 0 ? mysqli_fetch_assoc($medical_report_result) : [];
mysqli_stmt_close($stmt);

// Parsear el odontograma si existe
$odontogram_data = isset($medical_report['odontogram_data']) ? json_decode($medical_report['odontogram_data'], true) : [];

// Verificar TCPDF
if (!file_exists('../Reportes/tcpdf/tcpdf.php')) {
    die("Error: No se encontró el archivo TCPDF en '../Reportes/tcpdf/tcpdf.php'");
}
require_once '../Reportes/tcpdf/tcpdf.php';

// Crear instancia de TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuración del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema Odontológico');
$pdf->SetTitle('Informe Médico');
$pdf->SetSubject('Informe Médico del Paciente');
$pdf->SetKeywords('Informe, Médico, Paciente, Odontología');

$pdf->SetMargins(15, 30, 15);
$pdf->SetHeaderMargin(15);
$pdf->SetFooterMargin(15);

$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);

// Configurar encabezado con logo
if (file_exists('../src/img/logo.png')) {
    $pdf->SetHeaderData('../src/img/logo.png', 30, 'Informe Médico', '');
} else {
    $pdf->SetHeaderData('', 0, 'Informe Médico', 'Logo no encontrado');
}
$pdf->SetFooterData();

// Añadir una página
$pdf->AddPage();

// Forzar el logo en la primera página
if (file_exists('../src/img/logo.png')) {
    $pdf->Image('../src/img/logo.png', 15, 15, 30, 0);
} else {
    $pdf->SetXY(15, 15);
    $pdf->Cell(0, 10, 'Logo no encontrado', 0, 1);
}

// Título
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Ln(20);
$pdf->Cell(0, 15, 'Informe Médico', 0, 1, 'C');
$pdf->Ln(10);

// Línea separadora
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(10);

// Información del paciente
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Información del Paciente', 0, 1, 'L');
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 10);

$pdf->Cell(40, 7, 'Nombre:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['nombre'] . ' ' . $patient['apellido'], 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Edad:', 0, 0, 'L');
$pdf->Cell(60, 7, $age . ($age !== 'N/A' ? ' años' : ''), 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Fecha de Nacimiento:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['fecha_nacimiento'], 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Correo Electrónico:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['correo_electronico'], 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Teléfono:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['telefono'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'EPS:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['eps'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Ocupación:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['ocupacion'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Estado Civil:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['estado_civil'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Cédula:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['cedula'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Género:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['sexo'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Emergencia (Nombre):', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['emergencia_nombre'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Teléfono de Emergencia:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['emergencia_telefono'] ?? 'N/A', 0, 1, 'L');
if ($age !== 'N/A' && $age < 18) {
    $pdf->Ln(3);
    $pdf->Cell(40, 7, 'Acompañante (Nombre):', 0, 0, 'L');
    $pdf->Cell(60, 7, $patient['menor_acompanante'] ?? 'N/A', 0, 1, 'L');
    $pdf->Ln(3);
    $pdf->Cell(40, 7, 'Parentesco:', 0, 0, 'L');
    $pdf->Cell(60, 7, $patient['menor_parentesco'] ?? 'N/A', 0, 1, 'L');
    $pdf->Ln(3);
    $pdf->Cell(40, 7, 'Teléfono Acompañante:', 0, 0, 'L');
    $pdf->Cell(60, 7, $patient['menor_telefono'] ?? 'N/A', 0, 1, 'L');
}
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Tipo de Sangre:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['tipo_sangre'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Alertas Médicas:', 0, 0, 'L');
$pdf->MultiCell(150, 7, $patient['alertas_medicas'] ?? 'N/A', 0, 'L');
$pdf->Ln(10);

// Anamnesis (Historia Familiar o Personal)
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Anamnesis (Historia Familiar o Personal)', 0, 1, 'L');
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 10);

$labelWidth = 60;
$valueWidth = 40;

$pdf->Cell($labelWidth, 7, 'Enfermedades Cardiovasculares:', 0, 0, 'L');
$pdf->Cell($valueWidth, 7, $patient['historia_cardiovasculares'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell($labelWidth, 7, 'Enfermedades Hemorrágicas:', 0, 0, 'L');
$pdf->Cell($valueWidth, 7, $patient['historia_hemorragicas'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell($labelWidth, 7, 'Enfermedades Dermatológicas:', 0, 0, 'L');
$pdf->Cell($valueWidth, 7, $patient['historia_dermatologicas'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell($labelWidth, 7, 'Enfermedades Mentales:', 0, 0, 'L');
$pdf->Cell($valueWidth, 7, $patient['historia_mentales'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell($labelWidth, 7, 'Diabetes:', 0, 0, 'L');
$pdf->Cell($valueWidth, 7, $patient['historia_diabetes'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell($labelWidth, 7, 'Cáncer:', 0, 0, 'L');
$pdf->Cell($valueWidth, 7, $patient['historia_cancer'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell($labelWidth, 7, 'Artritis:', 0, 0, 'L');
$pdf->Cell($valueWidth, 7, $patient['historia_artritis'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell($labelWidth, 7, 'Alergias:', 0, 0, 'L');
$pdf->Cell($valueWidth, 7, $patient['historia_alergias'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell($labelWidth, 7, 'Cirugías:', 0, 0, 'L');
$pdf->Cell($valueWidth, 7, $patient['historia_cirugias'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell($labelWidth, 7, 'Otros:', 0, 0, 'L');
$pdf->MultiCell(150, 7, $patient['historia_otros'] ?? 'N/A', 0, 'L');
$pdf->Ln(10);

// Información de la cita
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Información de la Cita', 0, 1, 'L');
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 10);

$pdf->Cell(40, 7, 'Fecha:', 0, 0, 'L');
$pdf->Cell(60, 7, $appointment['fecha_cita'], 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Hora:', 0, 0, 'L');
$pdf->Cell(60, 7, $appointment['hora_cita'], 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Doctor:', 0, 0, 'L');
$pdf->Cell(60, 7, $appointment['nombreD'], 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Motivo de Consulta:', 0, 0, 'L');
$pdf->Cell(60, 7, $appointment['tipo'], 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Estado:', 0, 0, 'L');
$pdf->Cell(60, 7, $appointment['estado'] == 'A' ? 'Realizada' : 'Pendiente', 0, 1, 'L');
$pdf->Ln(10);

// Informe médico
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Informe Médico', 0, 1, 'L');
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 10);

$labelWidthMedical = 55;
$valueWidthMedical = 135;
$labelWidthMedical2 = 85;
$valueWidthMedical2 = 105;

$pdf->Cell($labelWidthMedical, 7, 'Examen Intraoral:', 0, 0, 'L');
$pdf->MultiCell($valueWidthMedical, 7, $medical_report['examen_intraoral'] ?? 'N/A', 0, 'L');
$pdf->Ln(5); 

$pdf->Cell($labelWidthMedical, 7, 'Examen Extraoral:', 0, 0, 'L');
$pdf->MultiCell($valueWidthMedical, 7, $medical_report['examen_extraoral'] ?? 'N/A', 0, 'L');
$pdf->Ln(5); 

$pdf->Cell($labelWidthMedical, 7, 'Examen ATM:', 0, 0, 'L');
$pdf->MultiCell($valueWidthMedical, 7, $medical_report['examen_atm'] ?? 'N/A', 0, 'L');
$pdf->Ln(5); 

$pdf->Cell($labelWidthMedical, 7, 'Observación, Palpación Intraoral:', 0, 0, 'L');
$pdf->MultiCell($valueWidthMedical, 7, $medical_report['observacion_intraoral'] ?? 'N/A', 0, 'L');
$pdf->Ln(5); 

$pdf->Cell($labelWidthMedical2, 7, 'Observación, Palpación Extraoral (ATM y Músculos):', 0, 0, 'L');
$pdf->MultiCell($valueWidthMedical2, 7, $medical_report['observacion_extraoral_atm'] ?? 'N/A', 0, 'L');
$pdf->Ln(5); 

$pdf->Cell($labelWidthMedical, 7, 'Descripción Radiográfica:', 0, 0, 'L');
$pdf->MultiCell($valueWidthMedical, 7, $medical_report['descripcion_radiografica'] ?? 'N/A', 0, 'L');
$pdf->Ln(5); 

$pdf->Cell($labelWidthMedical, 7, 'Diagnóstico Periodontal:', 0, 0, 'L');
$pdf->MultiCell($valueWidthMedical, 7, $medical_report['diagnostico_periodontal'] ?? 'N/A', 0, 'L');
$pdf->Ln(5); 

$pdf->Cell($labelWidthMedical, 7, 'Plan de Tratamiento:', 0, 0, 'L');
$pdf->MultiCell($valueWidthMedical, 7, $medical_report['plan_tratamiento'] ?? 'N/A', 0, 'L');
$pdf->Ln(5); 

$pdf->Cell($labelWidthMedical, 7, 'Pronóstico:', 0, 0, 'L');
$pdf->MultiCell($valueWidthMedical, 7, $medical_report['pronostico'] ?? 'N/A', 0, 'L');
$pdf->Ln(5); 

// Imágenes
if (isset($medical_report['radiografia']) && !empty($medical_report['radiografia'])) {
    $radiografia_path = '../uploads/radiografias/' . $medical_report['radiografia'];
    if (!file_exists($radiografia_path)) {
        error_log("Error: No se encontró la radiografía en '$radiografia_path'");
    } else {
        $pdf->Cell(40, 7, 'Radiografía:', 0, 1, 'L');
        $pdf->Image($radiografia_path, 50, $pdf->GetY(), 100);
        $pdf->Ln(90);
    }
}

if (isset($medical_report['foto_boca']) && !empty($medical_report['foto_boca'])) {
    $foto_boca_path = '../uploads/fotos_boca/' . $medical_report['foto_boca'];
    if (!file_exists($foto_boca_path)) {
        error_log("Error: No se encontró la foto de la boca en '$foto_boca_path'");
    } else {
        $pdf->Cell(40, 7, 'Foto de la Boca:', 0, 1, 'L');
        $pdf->Image($foto_boca_path, 50, $pdf->GetY(), 100);
        $pdf->Ln(90);
    }
}

$pdf->Cell(40, 7, 'Evolución:', 0, 0, 'L');
$pdf->MultiCell(150, 7, $medical_report['evolucion'] ?? 'N/A', 0, 'L');
$pdf->Ln(5);
$pdf->Cell(40, 7, 'Diagnóstico:', 0, 0, 'L');
$pdf->MultiCell(150, 7, $medical_report['diagnostico'] ?? 'N/A', 0, 'L');
$pdf->Ln(5);
$pdf->Cell(40, 7, 'Costo:', 0, 0, 'L');
$pdf->Cell(60, 7, '$' . ($medical_report['costo'] ?? 'N/A'), 0, 1, 'L');
$pdf->Ln(10);

// Nueva página para el odontograma
if (!empty($odontogram_data)) {
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Odontograma', 0, 1, 'L');
    $pdf->Ln(5);

    // Tabla del odontograma
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(230, 240, 255);
    $pdf->Cell(30, 7, 'Diente', 1, 0, 'C', 1);
    $pdf->Cell(150, 7, 'Condición', 1, 1, 'C', 1);

    $pdf->SetFillColor(245, 245, 245);
    foreach ($odontogram_data as $entry) {
        $pdf->Cell(30, 7, $entry['tooth'], 1, 0, 'C', 0);
        $pdf->Cell(150, 7, $entry['condition'], 1, 1, 'L', 0);
    }
}

// Limpiar buffer y generar PDF
ob_end_clean();

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Informe_Medico_' . $patient_id . '.pdf"');
$pdf->Output('Informe_Medico_' . $patient_id . '.pdf', 'D');
exit();