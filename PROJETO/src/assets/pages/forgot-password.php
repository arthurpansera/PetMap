<?php
    $msg = $_GET['msg'] ?? null;
    $showForm = $msg !== 'success';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Recuperar senha</title>
    <link rel="stylesheet" href="../../styles/pages/forgot-password/section.css" />
</head>
<body>
    <?php if ($msg): ?>

        <div class="message <?= $msg === 'success' ? 'success' : 'error' ?>">
            <?php if ($msg === 'success'): ?>
                <p>Verifique seu e-mail para redefinir a senha.</p>
            <?php elseif ($msg === 'email_not_found'): ?>
                <p>E-mail não encontrado. Verifique e tente novamente.</p>
            <?php else: ?>
                <p>Erro ao enviar o e-mail. Tente novamente.</p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

    <?php if ($showForm): ?>
        <form method="POST" action="send-email.php">
            <label>Digite seu e-mail:</label>
            <input type="email" name="email" required />
            <button type="submit">Enviar link de recuperação</button>
        </form>
    <?php endif; ?>

</body>
</html>