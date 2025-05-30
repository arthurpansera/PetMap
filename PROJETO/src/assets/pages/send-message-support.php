<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$petmapEmail = 'petmap0328@gmail.com';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($fullName) || empty($email) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: support.php?msg=invalid');
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'petmap0328@gmail.com'; 
        $mail->Password = 'zjyd ntit xlsk vjur';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($email, $fullName);
        $mail->addAddress($petmapEmail, 'PetMap Suporte');

        $mail->isHTML(true);
        $mail->Subject = "Contato via Suporte - Assunto: $subject";

        $mailBody = "
            <h2>Mensagem enviada pelo formulário de suporte PetMap</h2>
            <p><strong>Nome:</strong> " . htmlspecialchars($fullName) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Assunto:</strong> " . htmlspecialchars($subject) . "</p>
            <p><strong>Mensagem:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
        ";

        $mail->Body = $mailBody;

        $mail->send();

        $_SESSION['success_message'] = "Mensagem enviada com sucesso!";
        header('Location: support.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erro ao enviar mensagem: " . $mail->ErrorInfo;
        header('Location: support.php');
        exit;
    }

} else {
    header('Location: support.php');
    exit;
}
