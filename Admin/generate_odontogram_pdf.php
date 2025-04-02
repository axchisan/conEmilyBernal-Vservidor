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

// Verificar conexión a la base de datos
if (!$link) {
    die("Error: No se pudo conectar a la base de datos: " . mysqli_connect_error());
}

// Verificar si se recibió el ID del paciente
if (!isset($_GET['patient_id']) || empty($_GET['patient_id'])) {
    die("Error: ID de paciente no proporcionado.");
}

$patient_id = mysqli_real_escape_string($link, $_GET['patient_id']);
$doctor_id = $_SESSION['id_doctor'];

// Obtener datos del paciente
$patient = consultarPaciente($link, $patient_id);
if (!$patient) {
    die("Error: No se pudo obtener la información del paciente.");
}

// Obtener datos de la cita más reciente
$query = "SELECT c.* 
          FROM citas c 
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
class MYPDF extends TCPDF {
    public function Header() {
        // Fondo del encabezado
        $this->SetFillColor(230, 240, 255);
        $this->Rect(0, 0, 210, 35, 'F');

        // Logo
        if (file_exists('../src/img/logo.png')) {
            $this->Image('../src/img/logo.png', 15, 10, 30, 0);
        }

        // Información del consultorio
        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColor(33, 37, 41);
        $this->SetXY(50, 10);
        $this->Cell(0, 5, 'Consultorio Odontológico Dra. Emily Valeria Bernal Jaimes', 0, 1, 'L');
        $this->SetXY(50, 15);
        $this->Cell(0, 5, 'Odontóloga UNICOC T.P. 1007363847', 0, 1, 'L');

        // Título
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(0, 102, 204);
        $this->SetY(25);
        $this->Cell(0, 10, 'ODONTOGRAMA', 0, 1, 'C');

        // Línea separadora
        $this->SetLineStyle(array('width' => 0.3, 'color' => array(0, 102, 204)));
        $this->Line(15, 35, 195, 35);
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuración del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema Odontológico');
$pdf->SetTitle('Odontograma');
$pdf->SetSubject('Odontograma del Paciente');
$pdf->SetKeywords('Odontograma, Paciente, Odontología');

$pdf->SetMargins(15, 40, 15); // Ajustar el margen superior para el encabezado
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(true, 15); // Ajustar el margen inferior (sin firma)

$pdf->setPrintHeader(true);
$pdf->setPrintFooter(false); // Desactivar el footer para no mostrar la firma

// Añadir una página
$pdf->AddPage();

// Información del paciente (breve)
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 10, 'Información del Paciente', 0, 1, 'L');
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(33, 37, 41);

$pdf->Cell(40, 7, 'Nombre:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['nombre'] . ' ' . $patient['apellido'], 0, 1, 'L');
$pdf->Ln(3);
$pdf->Cell(40, 7, 'Nº de documento:', 0, 0, 'L');
$pdf->Cell(60, 7, $patient['cedula'] ?? 'N/A', 0, 1, 'L');
$pdf->Ln(10);

// Tabla del odontograma
if (!empty($odontogram_data)) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 102, 204);
    $pdf->Cell(0, 10, 'Datos del Odontograma', 0, 1, 'L');
    $pdf->Ln(5);

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
} else {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'No hay datos de odontograma disponibles.', 0, 1, 'L');
}

// Limpiar buffer y generar PDF
ob_end_clean();

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Odontograma_' . $patient_id . '.pdf"');
$pdf->Output('Odontograma_' . $patient_id . '.pdf', 'D');
exit();