<?php
session_start();
date_default_timezone_set('America/Lima');
require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Código QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="data:,">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Generar Código QR</h1>
        <a href="../index.php" class="text-blue-500 hover:underline mb-4 inline-block">← Volver al Dashboard</a>
        
        <form action="generate_qr.php" method="POST" class="space-y-4">
            <div>
                <label for="id_alumno" class="block text-sm font-medium">ID del Alumno</label>
                <input type="text" name="id_alumno" id="id_alumno" class="mt-1 block w-full border rounded p-2" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Generar QR</button>
        </form>
        
        <?php if (isset($_SESSION['qr_path'])): ?>
            <div class="mt-4">
                <h3 class="text-lg font-medium">Código QR Generado</h3>
                <img src="<?php echo $_SESSION['qr_path']; ?>" alt="Código QR" class="mt-2">
                <a href="<?php echo $_SESSION['qr_path']; ?>" download class="bg-green-500 text-white px-4 py-2 rounded mt-2 inline-block">Descargar QR</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>