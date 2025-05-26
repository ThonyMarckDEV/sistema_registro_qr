<?php
ob_start(); // Start output buffering
session_start();
date_default_timezone_set('America/Lima');
require_once '../config.php';
require_once '../vendor/autoload.php';

// Ensure MySQL uses the same timezone as PHP
$conn = getDBConnection();
$conn->query("SET time_zone = '-05:00'"); // Set MySQL to America/Lima (UTC-5)

// Get and validate input parameters
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';
$dni_alumno = isset($_GET['dni_alumno']) ? trim($_GET['dni_alumno']) : (isset($_POST['dni_alumno']) ? trim($_POST['dni_alumno']) : null);
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

// Validate dates
if ($start_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
    ob_end_clean();
    echo json_encode(['message' => 'Formato de fecha de inicio inválido.']);
    $conn->close();
    exit;
}
if ($end_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    ob_end_clean();
    echo json_encode(['message' => 'Formato de fecha de fin inválido.']);
    $conn->close();
    exit;
}
if ($start_date && $end_date && $start_date > $end_date) {
    ob_end_clean();
    echo json_encode(['message' => 'La fecha de inicio no puede ser posterior a la fecha de fin.']);
    $conn->close();
    exit;
}

// Validate dni_alumno format
if ($dni_alumno && !preg_match('/^\d{8}$/', $dni_alumno)) {
    ob_end_clean();
    echo json_encode(['message' => 'El DNI del alumno debe contener exactamente 8 dígitos numéricos.']);
    $conn->close();
    exit;
}

if ($format === 'pdf') {
    $pdf = new \TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);

    // Set the title based on filters
    $title = 'Reporte de Asistencias';
    if ($dni_alumno) {
        $title .= ' - DNI Alumno: ' . htmlspecialchars($dni_alumno);
    }
    if ($start_date && $end_date) {
        $title .= ' - Desde: ' . htmlspecialchars($start_date) . ' Hasta: ' . htmlspecialchars($end_date);
    } elseif ($start_date) {
        $title .= ' - Desde: ' . htmlspecialchars($start_date);
    } elseif ($end_date) {
        $title .= ' - Hasta: ' . htmlspecialchars($end_date);
    } else {
        $title .= ' - Todos los Alumnos';
    }
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('helvetica', '', 12);

    // Build the SQL query
    $sql = "SELECT a.dni_alumno, al.nombre, al.apellido, a.tipo, a.fecha_hora, a.estado 
            FROM asistencias a 
            JOIN alumnos al ON a.dni_alumno = al.dni_alumno";
    $params = [];
    $types = '';
    $where_clauses = [];

    if ($dni_alumno) {
        $where_clauses[] = "al.dni_alumno = ?";
        $params[] = $dni_alumno;
        $types .= 's';
    }
    if ($start_date) {
        $where_clauses[] = "DATE(a.fecha_hora) >= ?";
        $params[] = $start_date;
        $types .= 's';
    }
    if ($end_date) {
        $where_clauses[] = "DATE(a.fecha_hora) <= ?";
        $params[] = $end_date;
        $types .= 's';
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    $sql .= " ORDER BY a.fecha_hora DESC";

    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        ob_end_clean();
        echo json_encode(['message' => 'Error en la consulta: ' . $conn->error]);
        $conn->close();
        exit;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $pdf->Cell(0, 10, 'No se encontraron registros para los filtros especificados.', 0, 1);
    } else {
        // Add table header
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(40, 10, 'DNI', 1, 0, 'C');
        $pdf->Cell(60, 10, 'Nombre', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Tipo', 1, 0, 'C');
        $pdf->Cell(50, 10, 'Fecha/Hora', 1, 0, 'C');
        $pdf->Cell(30, 10, 'Estado', 1, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);

        // Add table rows
        while ($row = $result->fetch_assoc()) {
            $pdf->Cell(40, 10, $row['dni_alumno'], 1, 0);
            $pdf->Cell(60, 10, "{$row['nombre']} {$row['apellido']}", 1, 0);
            $pdf->Cell(30, 10, $row['tipo'], 1, 0);
            $pdf->Cell(50, 10, $row['fecha_hora'], 1, 0);
            $pdf->Cell(30, 10, $row['estado'], 1, 1);
        }
    }

    $stmt->close();
    $conn->close();
    ob_end_clean();
    $pdf->Output('reporte_asistencias.pdf', 'D');
} else {
    ob_end_clean();
    echo json_encode(['message' => 'Formato no soportado. Use PDF.']);
}
?>