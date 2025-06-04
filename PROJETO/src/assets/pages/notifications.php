<?php
    include('../../../conecta_db.php');

    session_start();

    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    $isModerator = false;

    $obj = conecta_db();

    if (!$obj) {
        header("Location: database-error.php");
        exit;
    }

    if ($isLoggedIn) {
        $userId = $_SESSION['id_usuario'];
        
        $query = "SELECT nome, descricao FROM usuario u 
                  JOIN perfil p ON u.id_usuario = p.id_usuario 
                  WHERE u.id_usuario = ?";
        $stmt = $obj->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userProfile = $stmt->get_result()->fetch_assoc();
    
        if ($userProfile) {
            $userName = $userProfile['nome'];
            if ($userProfile['descricao'] === 'Perfil de moderador') {
                $isModerator = true;
            }
        }
    }

    $notifications = [];
    if ($isLoggedIn) {
        $query = "SELECT id_notificacao, mensagem, data_criacao, status FROM notificacao WHERE id_usuario_destinatario = ? ORDER BY data_criacao DESC";
        $stmt = $obj->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }

    if (isset($_POST['mark_as_read'])) {
        $notifId = $_POST['notif_id'] ?? 0;
        if ($notifId && $isLoggedIn) {
            $updateQuery = "UPDATE notificacao SET status = 'lida' WHERE id_notificacao = ? AND id_usuario_destinatario = ?";
            $stmt = $obj->prepare($updateQuery);
            $stmt->bind_param("ii", $notifId, $userId);
            $stmt->execute();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetMap | Notificações</title>
    <link rel="stylesheet" href="../../styles/pages/notifications/notifications.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <a href="../../../index.php">
                    <img src="../images/logo-petmap/white-logo.png" alt="Logo PetMap">
                </a>
                <ul class="ul">
                    <?php if ($isLoggedIn): ?>
                        <?php
                            $nome = explode(' ', trim($userName));
                            $prmeiroNome = implode(' ', array_slice($nome, 0, 1));
                        ?>
                        <li class="user-info">
                            <p class="welcome-message">Bem-vindo, <?php echo htmlspecialchars($prmeiroNome); ?>!</p>
                            <a class="profile-image" href="profile.php">
                                <img src="../images/perfil-images/profile-icon.png" alt="Ícone de Perfil">
                            </a>
                            <div class="logout-button">
                                <form action="about-us.php" method="POST">
                                    <button type="submit" name="logout">
                                        <img src="../images/perfil-images/icone-sair-branco.png" alt="Sair da Conta">
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php else: ?>
                        <a class="btn" href="login.php">Entrar</a>
                    <?php endif; ?>
                </ul>
            </nav>
        </div> 
    </header>
    <section class="info">
        <div class="menu-toggle" id="menuToggle" aria-label="Abrir menu" aria-expanded="false" role="button" tabindex="0">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <nav class="left-menu" id="leftMenu">
            <ul>
                <li><a href="../../../index.php">Página Principal</a></li>
                <li><a href="rescued-animals.php">Animais Resgatados</a></li>
                <li><a href="lost-animals.php">Animais Perdidos</a></li>
                <li><a href="areas.php">Áreas de Maior Abandono</a></li>
                <?php if ($isModerator): ?>
                    <li><a href="registered-users.php">Usuários Cadastrados</a></li>
                <?php endif; ?>
                <li><a href="about-us.php">Sobre Nós</a></li>
                <li><a href="frequent-questions.php">Perguntas Frequentes</a></li>
                <li><a href="support.php">Suporte</a></li>
            </ul>
            <?php if ($isLoggedIn): ?>
                <div class="mobile-user-options">
                    <ul>
                        <li><a href="profile.php">Meu Perfil</a></li>
                        <li>
                            <form action="areas.php" method="POST">
                                <button type="submit" name="logout">Sair</button>
                            </form>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="footer">
                <p>&copy;2025 - PetMap.</p>
                <p>Todos os direitos reservados.</p>
            </div>
        </nav>

        <div class="content">
            <h2>Suas Notificações</h2>
            <div class="notifications">
                <?php if (empty($notifications)): ?>
                    <p>Você não tem notificações.</p>
                <?php else: ?>
                    <ul class="notifications-list">
                        <?php foreach ($notifications as $notif): ?>
                            <li class="notification-item <?php echo $notif['status'] === 'nao_lida' ? 'unread' : 'read'; ?>">
                                <p><?php echo htmlspecialchars($notif['mensagem']); ?></p>
                                <small><?php echo date('d/m/Y H:i', strtotime($notif['data_criacao'])); ?></small>

                                <?php if ($notif['status'] === 'nao_lida'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="notif_id" value="<?php echo $notif['id_notificacao']; ?>">
                                        <button type="submit" name="mark_as_read">Marcar como lido</button>
                                    </form>
                                <?php else: ?>
                                    <span class="read-label">Lida</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../scripts/left-menu.js"></script>

    <?php if ($isLoggedIn): ?>
    <script>
    let tempoInatividade = 15 * 60 * 1000; // 15 minutos
    let timer;

    function resetTimer() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            window.location.href = "logout-inactivity.php";
        }, tempoInatividade);
    }

    ['mousemove', 'keydown', 'scroll', 'click'].forEach(evt =>
        document.addEventListener(evt, resetTimer)
    );

    resetTimer();
    </script>
    <?php endif; ?>
    
</body>
</html>