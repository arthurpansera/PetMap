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
                            $nomes = explode(' ', trim($userName));
                            $doisPrimeirosNomes = implode(' ', array_slice($nomes, 0, 2));
                        ?>
                        <li class="user-info">
                            <p class="welcome-message">Bem-vindo, <?php echo htmlspecialchars($doisPrimeirosNomes); ?>!</p>
                            <a class="profile-image" href="profile.php">
                                <img src="../images/perfil-images/profile-icon.png" alt="Ícone de Perfil">
                            </a>
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
                <li><a href="../../../index.php">Áreas de Maior Abandono</a></li>
                <?php if ($isModerator): ?>
                    <li><a href="../../../index.php">Usuários Cadastrados</a></li>
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
            <h2>Sobre Nós</h2>
            <div class="about-us-info-container">
                <div class="about-us-info">
                    <div class="about-us-info-text">
                        <p>O <strong>PetMap</strong> é uma plataforma colaborativa voltada à causa animal. Conectamos pessoas, ONGs e protetores para prevenir o abandono e resgatar animais em situação de risco.<br><br></p>

                        <p>Hoje, mais de <strong>30 milhões de animais</strong> vivem em situação de abandono no Brasil, sendo <strong>75% deles em áreas urbanas</strong>. <br><br>Diante disso, o PetMap oferece uma solução tecnológica para mobilizar ações e salvar vidas.</p>

                        <br><p>Pela plataforma, você pode:</p>

                        <br>
                        <ul>
                            <li>Registrar animais perdidos ou avistados</li>
                            <li>Conectar-se com ONGs e voluntários da sua região</li>
                            <li>Mapear áreas críticas de abandono</li>
                            <li>Contribuir com informações relevantes para salvar vidas</li>
                        </ul>

                        <br>

                        <p>Mais do que uma ferramenta, o PetMap é uma <strong>rede de empatia, responsabilidade e ação</strong>. Junte-se a nós e faça parte dessa transformação.</p>
                    </div>
                    <div class="about-us-image">
                        <img id="logo-petmap" src="../images/logo-petmap/purple-logo.png" alt="Logo Petmap">
                        <img id="dog-icon" src="../images/about-us-page/dog-icon.png" alt="Foto de um Cachorro feliz">
                    </div>

                </div>

                <div class="btn-container">
                    <?php if (!$isLoggedIn): ?>
                        <a class="btn-about-us btn" href="login.php">Junte-se a nós</a>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
        
    </section>
</body>
</html>