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
    <title>PetMap | Sobre Nós</title>
    <link rel="stylesheet" href="../../styles/pages/about-us/about-us.css">
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
                            <form action="about-us.php" method="POST">
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
            <h2>Sobre Nós</h2>
            <div class="about-us-info-container">
                <div class="about-us-info-text-1">
                    <p>O <strong>PetMap</strong> é uma plataforma colaborativa voltada à causa animal. O nosso objetivo é conectar pessoas e ONGs para prevenir o abandono e resgatar animais em situação de risco.<br><br></p>
                    <p>Hoje, mais de <strong>30 milhões de animais</strong> vivem em situação de abandono no Brasil, sendo <strong>75% deles em áreas urbanas</strong>. <br><br></p>
                </div>

                <div class="about-us-image-text">
                    <div class="about-us-info-text-2">
                        <p>Diante disso, o PetMap oferece uma solução tecnológica para mobilizar ações e salvar vidas.</p>

                        <br><p>Pela plataforma, você pode:</p>
                        <br>
                        <ul>
                            <li>Registrar animais perdidos ou avistados</li>
                            <li>Conectar-se com ONGs e voluntários da sua região</li>
                            <li>Mapear áreas críticas de abandono</li>
                            <li>Contribuir com informações relevantes para salvar vidas</li>
                        </ul>
                        <br>
                        <p>Mais do que uma ferramenta, o PetMap é uma <strong>rede de empatia, responsabilidade e ação</strong>. Junte-se a nós e faça a diferença.</p>
                        
                        <div class="btn-container">
                            <?php if (!$isLoggedIn): ?>
                                <a class="btn-about-us" href="login.php">Junte-se a nós</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="about-us-image-wrapper">
                        <img id="dog-icon" src="../images/about-us-page/dog-icon.png" alt="Foto de um Cachorro feliz">
                    </div>
                </div>
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