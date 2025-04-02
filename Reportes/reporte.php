<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
require_once 'tcpdf/tcpdf.php'; // Ajusta la ruta según tu proyecto
require_once '../php/conexionDB.php';
require_once '../php/consultas.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_paciente'])) {
    $_SESSION['MensajeTexto'] = "Error: Acceso al sistema no registrado.";
    $_SESSION['MensajeTipo'] = "p-3 mb-2 bg-danger text-white";
    header("Location: ../index.php");
    exit();
}

$Usuario = $_SESSION['nombre'] ?? 'Usuario Desconocido';
$vUsuario = $_SESSION['id_paciente'] ?? null;

if (!$vUsuario) {
    die("Error: No se pudo obtener el ID del paciente.");
}

// Consultar datos del paciente
$row1 = consultarPaciente($link, $vUsuario);
$resultado = CitasPendientesFPDF($link, $vUsuario);

class MYPDF extends TCPDF
{
    public function Header()
    {
        // Verificar si el archivo de imagen existe antes de incluirlo
        $logoPath = __DIR__ . '/../src/img/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 10, 25, 25, '', '', '', false, 300, '', false, false, 0);
        } else {
            $this->SetFont('helvetica', 'I', 10);
            $this->Cell(0, 10, 'Logo no encontrado', 0, 1, 'L');
        }

        $this->SetFont('helvetica', 'B', 18);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(0, 15, 'Historial Clínico', 0, 1, 'C', false, '', 0, false, 'T', 'M');
        $this->SetDrawColor(100, 100, 100);
        $this->Line(10, 35, 200, 35);
        // Asegurar espacio suficiente después del encabezado
        $this->SetY(40); // Mover el cursor a una posición segura debajo del encabezado
    }

    public function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Fecha de generación: ' . date("d/m/Y H:i"), 0, 0, 'L');
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'R');
    }

    public function DatosPaciente($row1)
    {
        // Mover a una posición debajo del encabezado
        $this->SetY(50); // Ajustar este valor según las necesidades de tu diseño
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, "Información del Paciente", 0, 1, 'L', false, '', 0, false, 'T', 'M');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(40, 7, "Nombre:", 0, 0, 'L');
        $this->Cell(60, 7, $row1['nombre'] ?? 'N/A', 0, 1, 'L');
        $this->Cell(40, 7, "Apellido:", 0, 0, 'L');
        $this->Cell(60, 7, $row1['apellido'] ?? 'N/A', 0, 1, 'L');
        $this->Cell(40, 7, "Sexo:", 0, 0, 'L');
        $this->Cell(60, 7, $row1['sexo'] ?? 'N/A', 0, 1, 'L');
        $this->Cell(40, 7, "Fecha de nacimiento:", 0, 0, 'L');
        $this->Cell(60, 7, $row1['fecha_nacimiento'] ?? 'N/A', 0, 1, 'L');
        $this->Ln(5);
    }

    public function GenerarTabla($resultado)
    {
        // Mover a una posición después de los datos del paciente
        $this->SetY($this->GetY() + 10); // Ajustar según la posición actual
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, "Citas Pendientes", 0, 1, 'L', false, '', 0, false, 'T', 'M');

        // Verificar si hay citas pendientes
        if ($resultado->num_rows == 0) {
            $this->SetFont('helvetica', 'I', 10);
            $this->Cell(0, 10, "No hay citas pendientes para este paciente.", 0, 1, 'C');
            return;
        }

        // Encabezado de la tabla
        $this->SetFont('helvetica', 'B', 10);
        $this->SetFillColor(30, 144, 255);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(60, 10, 'Consulta', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Fecha', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Hora', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Doctor', 1, 1, 'C', true);

        // Datos de la tabla
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(0, 0, 0);
        while ($row = $resultado->fetch_assoc()) {
            $this->Cell(60, 8, $row['tipo'] ?? 'N/A', 1, 0, 'C');
            $this->Cell(30, 8, $row['fecha_cita'] ?? 'N/A', 1, 0, 'C');
            $this->Cell(30, 8, $row['hora_cita'] ?? 'N/A', 1, 0, 'C');
            $this->Cell(50, 8, $row['nombreD'] ?? 'N/A', 1, 1, 'C');
        }
    }
}

// Generar PDF
try {
    $pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Sistema de Citas');
    $pdf->SetTitle('Historial Clínico');
    $pdf->SetSubject('Reporte de Citas');
    $pdf->SetKeywords('TCPDF, PDF, Historial, Citas');
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);

    // Agregar datos del paciente
    $pdf->DatosPaciente($row1);

    // Generar tabla con citas pendientes
    $pdf->GenerarTabla($resultado);

    // Salida del PDF
    ob_end_clean();
    $pdf->Output('historial_clinico.pdf', 'I');
} catch (Exception $e) {
    ob_end_clean();
    die("Error al generar el PDF: " . $e->getMessage());
}
?>