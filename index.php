<?php
session_start();
date_default_timezone_set('America/Lima');
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Asistencia Escolar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-button {
            background-color: #3b82f6;
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
            text-align: center;
            height: 150px;
            width: 150px;
        }
        .dashboard-button:hover {
            background-color: #2563eb;
        }
        .dashboard-button i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .dashboard-button span {
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4 text-center">Sistema de Asistencia Escolar</h1>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <a href="./GenerarQR/generate_qr_form.php" class="dashboard-button">
                    <i class="fas fa-qrcode"></i>
                    <span>Generar Código QR</span>
                </a>
            </div>
            <div>
                <a href="./Asistencia/scan.php" class="dashboard-button">
                    <i class="fas fa-camera"></i>
                    <span>Escanear Código QR</span>
                </a>
            </div>
            <div>
                <a href="./Justificaciones/justify_form.php" class="dashboard-button">
                    <i class="fas fa-file-alt"></i>
                    <span>Registrar Justificación</span>
                </a>
            </div>
            <div>
                <a href="./Reportes/report_form.php" class="dashboard-button">
                    <i class="fas fa-chart-bar"></i>
                    <span>Generar Reportes</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>