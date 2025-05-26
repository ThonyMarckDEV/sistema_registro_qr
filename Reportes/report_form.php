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
    <title>Generar Reportes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="data:,">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Generar Reportes</h1>
        <a href="../index.php" class="text-blue-500 hover:underline mb-4 inline-block">← Volver al Dashboard</a>
        
        <form action="report_output.php" method="GET" class="space-y-4">
            <div>
                <label for="report_id_alumno" class="block text-sm font-medium">ID del Alumno (opcional)</label>
                <input type="text" name="id_alumno" id="report_id_alumno" class="mt-1 block w-full border rounded p-2" placeholder="Ej: 1">
            </div>
            <div>
                <label for="start_date" class="block text-sm font-medium">Fecha de Inicio</label>
                <input type="date" name="start_date" id="start_date" class="mt-1 block w-full border rounded p-2">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium">Fecha de Fin</label>
                <input type="date" name="end_date" id="end_date" class="mt-1 block w-full border rounded p-2">
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Ver Reporte</button>
                <a href="report.php?format=pdf" class="bg-green-500 text-white px-4 py-2 rounded inline-block">Exportar PDF (Todos)</a>
            </div>
        </form>
    </div>

    <script>
        document.querySelector('form[action="report_output.php"]').addEventListener('submit', function(e) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            if (startDate && endDate && startDate > endDate) {
                e.preventDefault();
                alert('La fecha de inicio no puede ser posterior a la fecha de fin.');
            }
        });
    </script>
</body>
</html>