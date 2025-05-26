<?php
session_start();
date_default_timezone_set('America/Lima');
require_once '../config.php';

// Get and validate input parameters
$dni_alumno = isset($_GET['dni_alumno']) ? trim($_GET['dni_alumno']) : null;
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;

// Build the SQL query
$conn = getDBConnection();
$conn->query("SET time_zone = '-05:00'");
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

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa del Reporte</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="data:,">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Vista Previa del Reporte</h1>
        <a href="report.php" class="text-blue-500 hover:underline mb-4 inline-block">← Volver al Formulario</a>
        
        <div class="mb-4">
            <h2 class="text-xl font-semibold">
                Reporte de Asistencias
                <?php if ($dni_alumno) echo " - DNI Alumno: " . htmlspecialchars($dni_alumno); ?>
                <?php if ($start_date) echo " - Desde: " . htmlspecialchars($start_date); ?>
                <?php if ($end_date) echo " - Hasta: " . htmlspecialchars($end_date); ?>
            </h2>
            <a href="report.php?format=pdf<?php 
                echo $dni_alumno ? '&dni_alumno=' . urlencode($dni_alumno) : '';
                echo $start_date ? '&start_date=' . urlencode($start_date) : '';
                echo $end_date ? '&end_date=' . urlencode($end_date) : '';
            ?>" class="bg-green-500 text-white px-4 py-2 rounded inline-block mt-2">Descargar PDF</a>
        </div>

        <?php if (empty($records)): ?>
            <p class="text-red-500">No se encontraron registros para los filtros especificados.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="py-2 px-4 border">DNI</th>
                            <th class="py-2 px-4 border">Nombre</th>
                            <th class="py-2 px-4 border">Tipo</th>
                            <th class="py-2 px-4 border">Fecha/Hora</th>
                            <th class="py-2 px-4 border">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td class="py-2 px-4 border"><?php echo htmlspecialchars($row['dni_alumno']); ?></td>
                                <td class="py-2 px-4 border"><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                <td class="py-2 px-4 border"><?php echo htmlspecialchars($row['tipo']); ?></td>
                                <td class="py-2 px-4 border"><?php echo htmlspecialchars($row['fecha_hora']); ?></td>
                                <td class="py-2 px-4 border"><?php echo htmlspecialchars($row['estado']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>