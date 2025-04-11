<?php
    include('conecta_db.php');

    session_start();
    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    $isModerator = false;

    $obj = conecta_db();
    date_default_timezone_set('America/Sao_Paulo');
    setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil');

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

    $query = "SELECT p.titulo, p.conteudo, p.tipo_publicacao, p.data_criacao, u.nome 
          FROM publicacao p 
          JOIN usuario u ON p.id_usuario = u.id_usuario 
          ORDER BY p.data_criacao DESC";

    $result = $obj->query($query);

    if (isset($_POST['make_post'])) {
        $titulo = $_POST['titulo'];
        $conteudo = $_POST['conteudo'];
        $tipoPublicacao = $_POST['tipo_publicacao'];
        $dataCriacao = date('Y-m-d H:i:s');

        $insertQuery = "INSERT INTO publicacao (titulo, conteudo, tipo_publicacao, id_usuario, data_criacao) VALUES (?, ?, ?, ?, ?)";
        $stmt = $obj->prepare($insertQuery);
        $stmt->bind_param("sssis", $titulo, $conteudo, $tipoPublicacao, $userId, $dataCriacao);
        $stmt->execute();

        header('Location: index.php');
        exit;
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
                    <p class="post-info"><span class="author-name">João Silva</span> • <span class="post-time">23 de março de 2025, 14h30</span></p>
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
                    <p class="post-info"><span class="author-name">João Silva</span> • <span class="post-time">23 de março de 2025, 14h30</span></p>
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
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($post = $result->fetch_assoc()): ?>
                        <div class="post-item">
                            <p class="post-info">
                                <span class="author-name"><?php echo $post['nome']; ?></span> • 
                                <span class="post-time"><?php echo utf8_encode(strftime('%d de %B de %Y, %Hh%M', strtotime($post['data_criacao']))); ?></span>
                            </p>
                            <?php
                            $tiposFormatados = [
                                'animal' => 'Animal',
                                'resgate' => 'Resgate',
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
                    <p>Não há publicações disponíveis.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <?php if ($isLoggedIn && ($userProfile['descricao'] == 'Perfil de ONG' || $userProfile['descricao'] == 'Perfil de cidadão')): ?>
        <button class="floating-button" title="Nova Publicação" onclick="openPostModal()">
            +
        </button>
    <?php endif; ?>

    <div id="postModal" class="post-modal">
        <div class="post-modal-content">
            <span class="post-modal-close" onclick="closePostModal()">&times;</span>
            <h2>Criar Nova Publicação</h2>
            <form action="index.php" method="POST">
                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>
                <div class="form-group">
                    <label for="conteudo">Conteúdo</label>
                    <textarea id="conteudo" name="conteudo" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="tipo_publicacao">Tipo de Publicação</label>
                    <select id="tipo_publicacao" name="tipo_publicacao" required>
                        <option value="animal">Animal</option>
                        <option value="resgate">Resgate</option>
                        <option value="informacao">Informação</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <button type="submit" name="make_post" class="create-post" onclick="">Publicar</button>
            </form>
        </div>
    </div>

    <script src="src/scripts/pages/index/index.js"></script>

</body>
</html>