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
                <li><a href="areas.php">Áreas de Maior Abandono</a></li>
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
            <h2>Suporte PetMap</h2>
            <div class="support-info">
                <div class="support-info-1">
                    <p>O PetMap é uma plataforma colaborativa dedicada à proteção e ao bem-estar dos animais. Nosso objetivo é conectar pessoas, ONGs e comunidades para facilitar o resgate de animais perdidos ou abandonados.</p>
                    <p>Se você tiver dúvidas, encontrar algum problema na plataforma ou quiser enviar sugestões, nossa equipe de suporte está pronta para ajudar.</p>
                </div>
                <div class="support-image">
                    <img src="../images/example-images/imagem-gato-suporte.jpg" alt="Foto de um Gato">
                </div>
                <div class="support-info-2">
                    <p>Entre em contato com a gente:</p>
                    <ul>
                        <li><strong>Email:</strong> suportepetmap@gmail.com</li>
                        <li><strong>WhatsApp:</strong> (41) 99123-4567</li>
                        <li><strong>Horário de atendimento:</strong> Segunda a sexta, das 9h às 18h</li>
                    </ul>
                </div>
                <div class="support-info-3">
                    <p>Obrigado por fazer parte dessa rede de cuidado e solidariedade com os animais.</p>
                    <p>Com você, o PetMap vai mais longe!</p>
                </div>
            </div>
        </div>
    </section>
</body>
</html>