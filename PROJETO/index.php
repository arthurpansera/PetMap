<?php
    include('conecta_db.php');

    session_start();
    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    $isModerator = false;

    $obj = conecta_db();

    if ($isLoggedIn) {
        $userId = $_SESSION['id_usuario'];
        
        $query = "SELECT descricao FROM perfil WHERE id_usuario = ?";
        $stmt = $obj->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userProfile = $stmt->get_result()->fetch_assoc();

        if ($userProfile && $userProfile['descricao'] === 'Perfil de moderador') {
            $isModerator = true;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetMap | Página Principal</title>
    <link href="src/styles/pages/index/index.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <a href="index.php">
                    <img src="src/assets/images/logo-petmap/white-logo.png" alt="Logo PetMap">
                </a>
                <ul class="ul">
                    <?php if ($isLoggedIn): ?>
                        <a class="profile-image" href="src/assets/pages/profile.php">
                            <img src="src/assets/images/perfil-images/profile-icon.png" alt="Ícone de Perfil">
                        </a>
                    <?php else: ?>
                        <a class="btn" href="src/assets/pages/login.php">Entrar</a>
                    <?php endif; ?>
                </ul>
            </nav>
        </div> 
    </header>
    <section class="options">
        <nav class="left-menu">
            <ul>
                <li><a href="index.php">Página Principal</a></li>
                <li><a href="index.php">Animais Resgatados</a></li>
                <li><a href="index.php">Animais Perdidos</a></li>
                <?php if ($isModerator): ?>
                    <li><a href="index.php">Usuários Cadastrados</a></li>
                <?php endif; ?>
                <li><a href="index.php">Sobre Nós</a></li>
                <li><a href="index.php">Perguntas Frequentes</a></li>
                <li><a href="index.php">Suporte</a></li>
            </ul>
            <div class="footer">
                <p>&copy;2025 - PetMap.</p>
                <p>Todos os direitos reservados.</p>
            </div>
        </nav> 
        <div class="content">
            <div class="menu-content">
                <h1>PetMap</h1>
                <p>Onde tem pet, tem PetMap!</p>
            </div>
            <hr class="line"></hr>
            <div class="menu-post">
                <div class="post-item">
                    <p class="post-info"><span class="author-name">João Silva</span> • <span class="post-time">23 de março de 2025, 14:30</span></p>
                    <p>Hoje, encontramos um cãozinho perdido na rua X. Ele está saudável e pronto para adoção. Acompanhe mais detalhes!</p>
                    <div class="post-actions">
                        <button class="like-button">
                            <i class="like-icon">⬆️</i> Impulsionar
                        </button>
                        <button class="comment-button">
                            <i class="comment-icon">💬</i> Comentar
                        </button>
                    </div>
                </div>
                <div class="post-item">
                    <p class="post-info"><span class="author-name">João Silva</span> • <span class="post-time">23 de março de 2025, 14:30</span></p>
                    <p>Hoje, encontramos um cãozinho perdido na rua X. Ele está saudável e pronto para adoção. Acompanhe mais detalhes!</p>
                    <img src="src/assets/images/example-images/imagem-cao-teste.png" alt="Logo PetMap">
                    <div class="post-actions">
                        <button class="like-button">
                            <i class="like-icon">⬆️</i> Impulsionar
                        </button>
                        <button class="comment-button">
                            <i class="comment-icon">💬</i> Comentar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php if ($isLoggedIn && ($userProfile['descricao'] == 'Perfil de ONG' || $userProfile['descricao'] == 'Perfil de cidadão')): ?>
        <button class="floating-button" title="Nova Publicação" onclick="">
            +
        </button>
    <?php endif; ?>
</body>
</html>