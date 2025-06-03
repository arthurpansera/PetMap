<?php
    include('../../../conecta_db.php');

    session_start();

    if (isset($_SESSION['error_message'])) {
        $mensagem = addslashes($_SESSION['error_message']);
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Erro!',
                    text: '{$mensagem}',
                    icon: 'error',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#7A00CC',
                    allowOutsideClick: true,
                    heightAuto: false
                });
            });
        </script>";
        unset($_SESSION['error_message']);
    }

    if (isset($_SESSION['success_message'])) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Sucesso!',
                    text: '{$_SESSION['success_message']}',
                    icon: 'success',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#7A00CC',
                    allowOutsideClick: true,
                    heightAuto: false
                });
            });
        </script>";
        unset($_SESSION['success_message']);
    }

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
                            <form action="support.php" method="POST">
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

        <div class="menu-overlay" id="menuOverlay"></div>

        <div class="content">
            <h2>Suporte PetMap</h2>
            
            <div class="support-container">

                <p class="form-intro">
                    O <strong>PetMap</strong> é uma plataforma colaborativa voltada à causa animal. Nosso objetivo é conectar pessoas e ONGs para prevenir o abandono e resgatar animais em situação de risco. 💜<br>
                    Caso tenha dúvidas, sugestões ou precise de ajuda, preencha o formulário abaixo
                </p>

                <?php if ($isLoggedIn): ?>
                    <button class="toggle-button" onclick="toggleForm()">Nos mande uma mensagem 🐶🐱</button>
                <?php else: ?>
                    <button class="toggle-button" onclick="showLoginAlert()">Nos mande uma mensagem 🐶🐱</button>
                <?php endif; ?>

                <div id="form-wrapper">
                    <div class="form-wrapper">
                        <form action="send-message-support.php" method="post" class="contact-form">
                            <input type="text" name="full_name" placeholder="Insira seu nome completo*" required value="<?= htmlspecialchars($userName ?? '') ?>">
                            <input type="email" name="email" placeholder="exemplo@gmail.com*" required>
                            <select name="subject" required>
                                <option value="" disabled selected>Selecione um assunto*</option>
                                <option value="problema-login">Problema com login</option>
                                <option value="bug-site">Bug no site</option>
                                <option value="duvidas-uso">Dúvidas sobre uso</option>
                                <option value="sugestoes">Sugestões</option>
                                <option value="outros">Outros</option>
                            </select>
                            <textarea name="message" placeholder="Nos diga seu problema*" required></textarea>
                            <button type="submit">Enviar mensagem</button>
                        </form>
                        
                    </div>
                </div>

                <div class="container-bottom">
                    <div class="contact-info">
                        <ul class="contact-list">
                            <li>
                                <img src="../images/support/gmail.png" alt="Ícone de Email">
                                <span><strong></strong>petmap0328@gmail.com</span>
                            </li>
                            <li>
                                <img src="../images/support/whatsapp.png" alt="Ícone do WhatsApp">
                                <span><strong></strong> (41) 99123-4567</span>
                            </li>
                            <li>
                                <img src="../images/support/clock.png" alt="Ícone de Relógio">
                                <span><strong></strong> Segunda a sexta, das 9h às 18h</span>
                            </li>
                        </ul>
                    </div>

                    <div class="support-img-container">
                        <img src="../images/support/support-dog-icon.png" alt="Cão segurando carta de suporte">
                    </div>
                </div>
            </div>  
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../scripts/pages/support/support.js"></script>
    <script src="../../scripts/left-menu.js"></script>

    <?php if ($isLoggedIn): ?>
        <script>

            let tempoInatividade = 15 * 60 * 1000;
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

    <script>
        function toggleForm() {
            const form = document.getElementById('form-wrapper');
            form.classList.toggle('show');
        }

        function showLoginAlert() {
            Swal.fire({
                title: 'Atenção!',
                text: 'Você precisa estar logado para enviar uma mensagem ao suporte.',
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#7A00CC',
                allowOutsideClick: true,
                heightAuto: false
            });
        }

    </script>
</body>
</html>