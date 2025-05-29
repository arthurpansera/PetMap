<?php
session_start();
include('../../../conecta_db.php');
$obj = conecta_db();

if (!isset($_GET['token']) || !isset($_SESSION['token_recuperacao']) || $_GET['token'] !== $_SESSION['token_recuperacao']) {
    die("Token inválido ou expirado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['senha'];
    $email = $_SESSION['email_recuperacao'];

    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    $query = "UPDATE usuario u
        JOIN contato c ON u.id_usuario = c.id_usuario
        SET u.senha = ?
        WHERE c.email = ?
    ";

    $stmt = $obj->prepare($query);
    $stmt->bind_param("ss", $senha_hash, $email);

    if ($stmt->execute()) {
        unset($_SESSION['token_recuperacao']);
        unset($_SESSION['email_recuperacao']);
        header("Location: login.php?reset=success");
    } else {
        echo "Erro ao redefinir a senha.";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recuperar senha</title>
    <link rel="stylesheet" href="../../styles/pages/forgot-password/section.css" />
</head>
<body>
    <form method="POST" action="">
        <label>Nova senha:</label>
        <input type="password" name="senha" required>
        <button type="submit">Redefinir senha</button>
    </form>
</body>
</html>

