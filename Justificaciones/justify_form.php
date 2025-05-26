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
    <title>Registrar Justificación</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="data:,">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Registrar Justificación</h1>
        <a href="../index.php" class="text-blue-500 hover:underline mb-4 inline-block">← Volver al Dashboard</a>
        
        <?php if (isset($_SESSION['justify_message'])): ?>
            <p class="<?php echo strpos($_SESSION['justify_message'], 'Error') !== false ? 'text-red-500' : 'text-green-500'; ?>">
                <?php echo $_SESSION['justify_message']; unset($_SESSION['justify_message']); ?>
            </p>
        <?php endif; ?>
        
        <form action="justify.php" method="POST" class="space-y-4" onsubmit="return validateJustificationForm()">
            <div>
                <label for="justify_codigo" class="block text-sm font-medium">Código del Alumno</label>
                <input type="text" name="codigo_alumno" id="justify_codigo" class="mt-1 block w-full border rounded p-2" required>
            </div>
            <div>
                <label for="fecha" class="block text-sm font-medium">Fecha</label>
                <input type="date" name="fecha" id="fecha" class="mt-1 block w-full border rounded p-2" required>
            </div>
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
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Registrar Justificación</button>
        </form>
    </div>

    <script>
        function validateJustificationForm() {
            const codigo = document.getElementById('justify_codigo').value;
            const fecha = document.getElementById('fecha').value;
            const motivo = document.getElementById('motivo').value;
            const tipo = document.getElementById('tipo').value;

            if (!codigo || !fecha || !motivo || !tipo) {
                alert('Por favor, complete todos los campos.');
                return false;
            }
            if (!/^[A-Z0-9]+$/.test(codigo)) {
                alert('El código de alumno debe contener solo letras mayúsculas y números.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>