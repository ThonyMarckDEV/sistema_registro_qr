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
    <title>Escanear Código QR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        #reader {
            width: 100%;
            max-width: 640px;
            border: 2px solid #000;
            background: #000;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Escanear Código QR</h1>

        <!-- Camera View -->
        <div class="mb-4">
            <div id="reader"></div>
            <p id="scan-result" class="mt-2 text-red-500"></p>
            <div id="attendance-details" class="mt-4 hidden bg-white p-4 rounded shadow"></div>
        </div>

        <!-- Manual Input Fallback -->
        <div class="mb-4">
            <label for="manual_codigo" class="block text-sm font-medium">Ingresar Código Manualmente</label>
            <input type="text" id="manual_codigo" class="mt-1 block w-full border rounded p-2" placeholder="Ej: AL001">
            <button onclick="manualScan()" class="bg-blue-500 text-white px-4 py-2 rounded mt-2">Registrar</button>
        </div>
        
        <!-- Back Button -->
        <a href="../index.php" class="bg-gray-500 text-white px-4 py-2 rounded inline-block">Volver</a>
    </div>

    <script>
        const scanResult = document.getElementById('scan-result');
        const attendanceDetails = document.getElementById('attendance-details');
        const html5QrCode = new Html5Qrcode("reader");
        let isScanning = false;

        function startScanner() {
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    if (!isScanning) {
                        processScan(decodedText);
                    }
                },
                (errorMessage) => {
                    // Ignore continuous scanning errors
                }
            ).catch(err => {
                let errorMessage = 'Error al iniciar la cámara: ' + err;
                if (err.name === 'NotAllowedError') {
                    errorMessage = 'Permiso de cámara denegado. Por favor, permite el acceso a la cámara.';
                } else if (err.name === 'NotFoundError') {
                    errorMessage = 'No se encontraron cámaras.';
                }
                scanResult.innerText = errorMessage;
                scanResult.classList.add('text-red-500');
                console.error('Camera error:', err);
            });
        }

        function stopScanner() {
            html5QrCode.stop().catch(err => {
                console.error('Error stopping scanner:', err);
            });
        }

        function processScan(decodedText) {
            if (isScanning) return;
            isScanning = true;
            scanResult.innerText = 'Procesando escaneo...';
            scanResult.classList.remove('text-red-500', 'text-green-500');
            scanResult.classList.add('text-blue-500');

            stopScanner();

            const url = './scan_qr.php';
            console.log('Fetching URL:', url, 'with codigo_alumno:', decodedText);
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'codigo_alumno=' + encodeURIComponent(decodedText)
            })
            .then(response => {
                console.log('Response status:', response.status, 'URL:', response.url);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    scanResult.innerText = data.message.includes('registrada correctamente') ? 'Asistencia registrada correctamente' : data.message;
                    scanResult.classList.remove('text-red-500', 'text-green-500', 'text-blue-500');
                    scanResult.classList.add(data.message.includes('registrada correctamente') ? 'text-green-500' : 'text-red-500');
                    attendanceDetails.classList.remove('hidden');
                    attendanceDetails.innerHTML = `
                        <p><strong>Código Alumno:</strong> ${data.codigo_alumno || decodedText}</p>
                        <p><strong>Alumno:</strong> ${data.nombre || 'No disponible'} ${data.apellido || ''}</p>
                        <p><strong>Mensaje:</strong> ${data.message}</p>
                    `;
                } catch (e) {
                    throw new Error('Respuesta no válida del servidor: ' + e.message + ' (Raw: ' + text.substring(0, 100) + ')');
                }
            })
            .catch(err => {
                scanResult.innerText = 'Error al procesar el escaneo: ' + err.message;
                scanResult.classList.remove('text-blue-500');
                scanResult.classList.add('text-red-500');
                console.error('Fetch error:', err);
            })
            .finally(() => {
                isScanning = false;
                setTimeout(startScanner, 1000); // Restart scanner after 1 second
            });
        }

        startScanner();

        function manualScan() {
            const codigo = document.getElementById('manual_codigo').value;
            if (codigo && !isScanning) {
                processScan(codigo);
            } else if (isScanning) {
                scanResult.innerText = 'Espere a que termine el escaneo actual.';
                scanResult.classList.add('text-red-500');
            } else {
                scanResult.innerText = 'Por favor, ingresa un código válido.';
                scanResult.classList.add('text-red-500');
            }
        }
    </script>
</body>
</html>