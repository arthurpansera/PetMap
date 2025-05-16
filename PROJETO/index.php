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

        $id_publicacao = $stmt->insert_id;

        if (isset($_FILES['foto_publicacao']) && $_FILES['foto_publicacao']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto_publicacao']['tmp_name'];
            $fileName = $_FILES['foto_publicacao']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (getimagesize($fileTmpPath) === false) {
                $_SESSION['error_message'] = "O arquivo enviado não é uma imagem válida.";
                header("Location: index.php");
                exit;
            }

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = uniqid('post_', true) . '.' . $fileExtension;
                $uploadFileDir = __DIR__ . '/src/assets/images/uploads/posts/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                $destPath = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $query_foto = "INSERT INTO imagem (id_publicacao, imagem_url) VALUES (?, ?)";
                    $stmt_foto = $obj->prepare($query_foto);
                    $stmt_foto->bind_param("is", $id_publicacao, $newFileName);
                    $stmt_foto->execute();
                } else {
                    $_SESSION['error_message'] = "Erro ao mover a imagem.";
                }
            } else {
                $_SESSION['error_message'] = "Tipo de arquivo inválido. Apenas JPG, JPEG e PNG são permitidos.";
            }
        }

        header("Location: index.php");
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
                        <?php
                            $nomes = explode(' ', trim($userName));
                            $doisPrimeirosNomes = implode(' ', array_slice($nomes, 0, 2));
                        ?>
                        <li class="user-info">
                            <p class="welcome-message">Bem-vindo, <?php echo htmlspecialchars($doisPrimeirosNomes); ?>!</p>
                            <a class="profile-image" href="src/assets/pages/profile.php">
                                <img src="src/assets/images/perfil-images/profile-icon.png" alt="Ícone de Perfil">
                            </a>
                        </li>
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
                <li><a href="src/assets/pages/lost-animals.php">Animais Perdidos</a></li>
                <li><a href="index.php">Áreas de Maior Abandono</a></li>
                <?php if ($isModerator): ?>
                    <li><a href="index.php">Usuários Cadastrados</a></li>
                <?php endif; ?>
                <li><a href="src/assets/pages/about-us.php">Sobre Nós</a></li>
                <li><a href="src/assets/pages/frequent-questions.php">Perguntas Frequentes</a></li>
                <li><a href="src/assets/pages/support.php">Suporte</a></li>
            </ul>
            <div class="footer">
                <p>&copy;2025 - PetMap.</p>
                <p>Todos os direitos reservados.</p>
            </div>
        </nav> 
        <div class="content">
            <div class="menu-post">
                <?php if ($result->num_rows > 0): ?>
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
                        <img src="src/assets/images/no-posts-image/sem-posts.png" alt="Ícone de Erro" class="no-posts-image">
                    </div>
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
            <form action="index.php" method="POST" enctype="multipart/form-data">
                <div class="post-form-group">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>

                <div class="post-form-group">
                    <label for="conteudo">Conteúdo</label>
                    <textarea id="conteudo" name="conteudo" rows="4" required></textarea>
                </div>

                <div class="post-form-group">
                    <label for="foto_publicacao" class="custom-file-label" id="label_foto_post">📁 Escolher imagem:</label>
                    <input type="file" name="foto_publicacao" id="foto_publicacao">
                </div>

                <div class="post-form-group">
                    <label for="tipo_publicacao">Tipo de Publicação</label>
                    <select id="tipo_publicacao" name="tipo_publicacao" required>
                        <option value="animal">Animal Perdido</option>
                        <option value="resgate">Resgate de Animal</option>
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