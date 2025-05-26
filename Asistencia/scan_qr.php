<?php
session_start();
ob_start();
date_default_timezone_set('America/Lima');
require_once '../config.php';

header('Content-Type: application/json');
$response = ['message' => '', 'nombre' => '', 'apellido' => '', 'dni_alumno' => ''];

function sendSMS($phone, $message) {
    error_log("SMS to $phone: $message");
}

if (!isset($_POST['dni_alumno']) || empty($_POST['dni_alumno'])) {
    $response['message'] = 'DNI de alumno no proporcionado.';
    echo json_encode($response);
    ob_end_flush();
    exit;
}

$dni_alumno = $_POST['dni_alumno'];

try {
    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception('Error de conexión a la base de datos.');
    }

    $stmt = $conn->prepare("SELECT dni_alumno, nombre, apellido, email_padre, telefono_padre FROM alumnos WHERE dni_alumno = ?");
    if (!$stmt) {
        throw new Exception('Error en la consulta de alumno: ' . $conn->error);
    }
    $stmt->bind_param("s", $dni_alumno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response['message'] = 'DNI de alumno no válido.';
        echo json_encode($response);
        ob_end_flush();
        exit;
    }

    $alumno = $result->fetch_assoc();
    $current_time = date('H:i:s');
    $current_date = date('Y-m-d');
    $datetime = date('Y-m-d H:i:s');

    $configs = [];
    $stmt = $conn->query("SELECT clave, valor FROM configuraciones");
    if (!$stmt) {
        throw new Exception('Error en la consulta de configuraciones: ' . $conn->error);
    }
    while ($row = $stmt->fetch_assoc()) {
        $configs[$row['clave']] = $row['valor'];
    }

    $stmt = $conn->prepare("SELECT tipo, fecha_hora FROM asistencias WHERE dni_alumno = ? AND DATE(fecha_hora) = ?");
    if (!$stmt) {
        throw new Exception('Error en la consulta de asistencias: ' . $conn->error);
    }
    $stmt->bind_param("ss", $dni_alumno, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $registros = $result->fetch_all(MYSQLI_ASSOC);

    $entrada_registrada = false;
    $salida_registrada = false;
    foreach ($registros as $registro) {
        if ($registro['tipo'] === 'ENTRADA') {
            $entrada_registrada = true;
        }
        if ($registro['tipo'] === 'SALIDA') {
            $salida_registrada = true;
        }
    }

    if ($entrada_registrada && !$salida_registrada && $current_time >= $configs['hora_salida']) {
        $tipo = 'SALIDA';
        $estado = 'A_TIEMPO';
        $message = "El alumno salió del colegio a horas $current_time, nos vemos pronto a otro día de enseñanza en la institución.";
    } elseif (!$entrada_registrada && $current_time <= $configs['hora_limite_falta']) {
        $tipo = 'ENTRADA';
        $estado = ($current_time <= $configs['hora_entrada_fin']) ? 'A_TIEMPO' : 'TARDANZA';

        $stmt = $conn->prepare("SELECT COUNT(*) as tardanzas FROM asistencias WHERE dni_alumno = ? AND estado = 'TARDANZA' AND DATE(fecha_hora) >= DATE_SUB(?, INTERVAL 3 DAY)");
        if (!$stmt) {
            throw new Exception('Error en la consulta de tardanzas: ' . $conn->error);
        }
        $stmt->bind_param("ss", $dni_alumno, $current_date);
        $stmt->execute();
        $tardanzas = $stmt->get_result()->fetch_assoc()['tardanzas'];

        if ($estado === 'TARDANZA') {
            if ($tardanzas >= 2) {
                $message = "TARDANZA: Se le restará puntos en conducta al alumno por las tardanzas consecutivas, por favor acercarse a dirección a justificar.";
            } else {
                $message = "TARDANZA: El alumno llegó a la hora $current_time, si lleva 3 tardanzas consecutivas, se le restará puntos en conducta. Por favor, acercarse al colegio a justificar.";
            }
        } else {
            $message = "El alumno ingresó a la hora $current_time el $current_date.";
        }
    } else {
        $response['message'] = 'El QR ya ha sido registrado anteriormente o no está en el horario permitido.';
        $response['dni_alumno'] = $dni_alumno;
        $response['nombre'] = $alumno['nombre'];
        $response['apellido'] = $alumno['apellido'];
        echo json_encode($response);
        ob_end_flush();
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO asistencias (dni_alumno, tipo, fecha_hora, estado) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception('Error en la inserción de asistencia: ' . $conn->error);
    }
    $stmt->bind_param("ssss", $dni_alumno, $tipo, $datetime, $estado);
    $stmt->execute();

    try {
        sendEmail($alumno['email_padre'], 'Notificación de Asistencia', $message);
        sendSMS($alumno['telefono_padre'], $message);
    } catch (Exception $e) {
        error_log('Error sending notification: ' . $e->getMessage());
    }

    $response['message'] = $message;
    $response['dni_alumno'] = $dni_alumno;
    $response['nombre'] = $alumno['nombre'];
    $response['apellido'] = $alumno['apellido'];
    echo json_encode($response);
    $conn->close();
} catch (Exception $e) {
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
    echo json_encode($response);
}

ob_end_flush();
?>