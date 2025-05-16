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

    $query = "SELECT p.id_publicacao, p.titulo, p.conteudo, p.tipo_publicacao, p.data_criacao, u.nome 
            FROM publicacao p 
            JOIN usuario u ON p.id_usuario = u.id_usuario
            WHERE p.tipo_publicacao = 'animal'
            ORDER BY p.data_criacao DESC";
    $result = $obj->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetMap | Animais Perdidos</title>
    <link rel="stylesheet" href="../../styles/pages/lost-animals/lost-animals.css">
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
                <li><a href="../../../index.php">Animais Resgatados</a></li>
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
            <div class="lost-animal-post">
                <?php if ($result->num_rows > 0): ?>
                    <h2>Animais Perdidos</h2>
                    <?php while ($post = $result->fetch_assoc()): ?>

                        <?php
                            $idPost = $post['id_publicacao'];
                            $img = null;

                            $imgQuery = "SELECT imagem_url FROM imagem WHERE id_publicacao = ?";
                            $stmtImg = $obj->prepare($imgQuery);
                            $stmtImg->bind_param("i", $idPost);
                            $stmtImg->execute();
                            $imgResult = $stmtImg->get_result();
                            $img = $imgResult->fetch_assoc();
                        ?>

                        <div class="post-item">
                            <p class="post-info">
                                <span class="author-name"><?php echo $post['nome']; ?></span> • 
                                <span class="post-time"><?php echo utf8_encode(strftime('%d de %B de %Y, %Hh%M', strtotime($post['data_criacao']))); ?></span>
                            </p>
                            <?php
                                $tiposFormatados = [
                                    'animal' => 'Animal Perdido',
                                    'resgate' => 'Resgate de Animal',
                                    'informacao' => 'Informação',
                                    'cidadao' => 'Cidadão',
                                    'outro' => 'Outro'
                                ];
                            ?>
                            <p class="post-type">
                                <span class="badge">Tipo da publicação: <?php echo $tiposFormatados[$post['tipo_publicacao']] ?? ucfirst($post['tipo_publicacao']); ?></span>
                            </p>
                            <h3 class="post-title"><?php echo $post['titulo']; ?></h3>

                            <p><?php echo $post['conteudo']; ?></p>

                            <?php if (!empty($img['imagem_url'])): ?>
                                <div class="imagem-publicacao-container">
                                    <img src="src/assets/images/uploads/posts/<?php echo htmlspecialchars($img['imagem_url']); ?>" alt="Imagem da publicação">
                                </div>
                            <?php endif; ?>


                            <div class="post-actions">
                                <button class="like-button">
                                    <i class="like-icon">⬆️</i> Impulsionar
                                </button>
                                <button class="comment-button">
                                    <i class="comment-icon">💬</i> Comentar
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-posts-error">
                        <p class="no-posts-message">Não há publicações disponíveis.</p>
                        <img src="../images/no-posts-image/sem-posts.png" alt="Ícone de Erro" class="no-posts-image">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
</html>