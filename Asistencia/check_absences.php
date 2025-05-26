<?php
require_once '../config.php';

$conn = getDBConnection();
$current_date = date('Y-m-d');
$configs = [];
$stmt = $conn->query("SELECT clave, valor FROM configuraciones");
while ($row = $stmt->fetch_assoc()) {
    $configs[$row['clave']] = $row['valor'];
}

$stmt = $conn->query("SELECT dni_alumno, email_padre FROM alumnos");
$alumnos = $stmt->fetch_all(MYSQLI_ASSOC);

foreach ($alumnos as $alumno) {
    $dni_alumno = $alumno['dni_alumno'];
    $stmt = $conn->prepare("SELECT COUNT(*) as entradas FROM asistencias WHERE dni_alumno = ? AND tipo = 'ENTRADA' AND DATE(fecha_hora) = ?");
    $stmt->bind_param("ss", $dni_alumno, $current_date);
    $stmt->execute();
    $entradas = $stmt->get_result()->fetch_assoc()['entradas'];
    
    if ($entradas == 0) {
        $message = "FALTA: El alumno faltó a clases hoy, por favor acercarse a dirección a justificar, caso contrario, se le restará puntos en conducta.";
        sendEmail($alumno['email_padre'], 'Notificación de Falta', $message);
        
        $estado = 'FALTA';
        $datetime = "$current_date {$configs['hora_limite_falta']}";
        $stmt = $conn->prepare("INSERT INTO asistencias (dni_alumno, tipo, fecha_hora, estado) VALUES (?, 'ENTRADA', ?, ?)");
        $stmt->bind_param("sss", $dni_alumno, $datetime, $estado);
        $stmt->execute();
    }
}

$conn->close();
?>