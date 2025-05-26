<?php
session_start();
require_once '../config.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_alumno = $_POST['id_alumno'];
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT codigo_alumno FROM alumnos WHERE id = ?");
    $stmt->bind_param("i", $id_alumno);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $alumno = $result->fetch_assoc();
        $codigo_alumno = $alumno['codigo_alumno'];
        
        $qr_path = 'qrcodes/' . $codigo_alumno . '.png';
        if (!file_exists('qrcodes')) {
            mkdir('qrcodes', 0777, true);
        }
        
        $options = new QROptions([
            'eccLevel' => QRCode::ECC_L,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => false,
        ]);
        
        $qrcode = new QRCode($options);
        $qrcode->render($codigo_alumno, $qr_path);
        
        $_SESSION['qr_path'] = $qr_path;
        header('Location: ../index.php');
    } else {
        echo "Alumno no encontrado.";
    }
    
    $stmt->close();
    $conn->close();
}
?>