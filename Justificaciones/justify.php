<?php
session_start();
date_default_timezone_set('America/Lima');
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_alumno = $_POST['codigo_alumno'];
    $fecha = $_POST['fecha'];
    $motivo = $_POST['motivo'];
    $tipo = $_POST['tipo'];
    
    $conn = getDBConnection();
    
    // Validate codigo_alumno
    $stmt = $conn->prepare("SELECT codigo_alumno FROM alumnos WHERE codigo_alumno = ?");
    $stmt->bind_param("s", $codigo_alumno);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['justify_message'] = 'Error: El código de alumno no existe.';
        header('Location: ../index.php');
        $conn->close();
        exit;
    }
    
    // Insert justification
    $stmt = $conn->prepare("INSERT INTO justificaciones (codigo_alumno, fecha, motivo, tipo) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['justify_message'] = 'Error en la preparación de la consulta: ' . $conn->error;
        header('Location: ../index.php');
        $conn->close();
        exit;
    }
    $stmt->bind_param("ssss", $codigo_alumno, $fecha, $motivo, $tipo);
    
    if ($stmt->execute()) {
        $_SESSION['justify_message'] = 'Justificación registrada con éxito.';
    } else {
        $_SESSION['justify_message'] = 'Error al registrar la justificación: ' . $stmt->error;
    }
    
    $conn->close();
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Justificación</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Registrar Justificación</h1>
        <?php if (isset($_SESSION['justify_message'])): ?>
            <p class="<?php echo strpos($_SESSION['justify_message'], 'Error') !== false ? 'text-red-500' : 'text-green-500'; ?>">
                <?php echo $_SESSION['justify_message']; unset($_SESSION['justify_message']); ?>
            </p>
        <?php endif; ?>
        <form action="justify.php" method="POST" class="space-y-4">
            <div>
                <label for="codigo_alumno" class="block text-sm font-medium">Código del Alumno</label>
                <input type="text" name="codigo_alumno" id="codigo_alumno" class="mt-1 block w-full border rounded p-2" required>
            </div>
            <div>
                <label for="fecha" class="block text-sm font-medium">Fecha</label>
                <input type="date" name="fecha" id="fecha" class="mt-1 block w-full border rounded p-2" required>
            </div>
            <div>
                <label for="motivo" class="block text-sm font-medium">Motivo</label>
                <textarea name="motivo" id="motivo" class="mt-1 block w-full border rounded p-2" required></textarea>
            </div>
            <div>
                <label for="tipo" class="block text-sm font-medium">Tipo</label>
                <select name="tipo" id="tipo" class="mt-1 block w-full border rounded p-2" required>
                    <option value="FALTA">Falta</option>
                    <option value="TARDANZA">Tardanza</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Registrar</button>
        </form>
        <a href="../index.php" class="bg-gray-500 text-white px-4 py-2 rounded inline-block mt-4">Volver</a>
    </div>
</body>
</html>