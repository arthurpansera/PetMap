<?php
    include('../../../conecta_db.php');

    session_start();

    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    $isModerator = false;

    $obj = conecta_db();

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
    <title>PetMap | Suporte</title>
    <link rel="stylesheet" href="../../styles/pages/support/support.css">
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
                                <form action="support.php" method="POST">
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
    <section class="options">
        <nav class="left-menu">
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
            <div class="footer">
                <p>&copy;2025 - PetMap.</p>
                <p>Todos os direitos reservados.</p>
            </div>
        </nav>
        <div class="content">
            <h2>Suporte PetMap</h2>

            <div class="support-img-container">
                <img src="../images/support/dog.jpg" alt="Cão segurando carta de suporte">
            </div>
            
            <div class="form-container">
                <form action="#" method="post" class="contact-form">
                    <h3>Nos mande uma mensagem🐶🐱</h3>
                    <input type="text" name="full_name" placeholder="Nome completo*" required>
                    <input type="email" name="email" placeholder="Email*" required>
                    <input type="text" name="subject" placeholder="Assunto">
                    <textarea name="message" placeholder="Nos diga seu problema*" required></textarea>
                    <button type="submit">Enviar mensagem</button>
                </form>

                <div class="contact-info">
                    <ul class="contact-list">
                        <li>
                            <img src="../images/support/gmail.png" alt="Ícone de Email">
                            <span><strong>:</strong> suportepetmap@gmail.com</span>
                        </li>
                        <li>
                            <img src="../images/support/whatsapp.png" alt="Ícone do WhatsApp">
                            <span><strong>:</strong> (41) 99123-4567</span>
                        </li>
                        <li>
                            <img src="../images/support/clock.png" alt="Ícone de Relógio">
                            <span><strong>:</strong> Segunda a sexta, das 9h às 18h</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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