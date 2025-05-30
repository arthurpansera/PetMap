<?php
    session_start();
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    include('../../../conecta_db.php');
    $obj = conecta_db();

    if (!$obj) {
        header("Location: database-error.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];

        $query = "SELECT u.id_usuario 
                FROM usuario u 
                JOIN contato c ON u.id_usuario = c.id_usuario
                WHERE c.email = ?
                LIMIT 1";

        $stmt = $obj->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $token = bin2hex(random_bytes(16));
            $_SESSION['token_recuperacao'] = $token;
            $_SESSION['email_recuperacao'] = $email;

            $link = "http://localhost/PetMap/PROJETO/src/assets/pages/password-reset.php?token=$token";

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'petmap0328@gmail.com';
                $mail->Password = 'zjyd ntit xlsk vjur';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('petmap0328@gmail.com', 'PetMap');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Redefina sua senha';
                $mail->Body    = "Clique no link para redefinir sua senha: <a href='$link'>$link</a>";

                if ($mail->send()) {
                    header("Location: forgot-password.php?msg=success");
                    exit;
                } else {
                    header("Location: forgot-password.php?msg=error");
                    exit;
                }

            } catch (Exception $e) {
                header("Location: forgot-password.php?msg=error");
                exit;
            }

        } else {
            header("Location: forgot-password.php?msg=email_not_found");
            exit;
        }
    }
?>