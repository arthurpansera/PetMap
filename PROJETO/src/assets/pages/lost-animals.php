<?php
    include('../../../conecta_db.php');

    session_start();
    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    $isModerator = false;

    $obj = conecta_db();
    $obj->query("SET lc_time_names = 'pt_BR'");
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

    $pesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';

    $ordenarPor = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'impulsos_desc';

    switch ($ordenarPor) {
        case 'data_asc':
            $orderBy = 'p.data_criacao ASC';
            break;
        case 'impulsos_desc':
            $orderBy = 'p.total_impulsos DESC';
            break;
        case 'data_desc':
        default:
            $orderBy = 'p.data_criacao DESC';
            break;
    }

    if (!empty($pesquisa)) {
        $searchTerm = '%' . $pesquisa . '%';

        $query = "SELECT p.id_publicacao, p.titulo, p.conteudo, p.tipo_publicacao, p.data_criacao, p.data_atualizacao, u.nome 
                FROM publicacao p 
                JOIN usuario u ON p.id_usuario = u.id_usuario 
                WHERE (
                    p.titulo LIKE ? 
                    OR p.conteudo LIKE ? 
                    OR u.nome LIKE ? 
                    OR DATE_FORMAT(p.data_criacao, '%d/%m/%Y') LIKE ? 
                    OR DATE_FORMAT(p.data_criacao, '%d/%m/%Y %H:%i') LIKE ?
                    OR DATE_FORMAT(p.data_criacao, '%d de %M de %Y') LIKE ?
                    OR DATE_FORMAT(p.data_criacao, '%Hh%i') LIKE ?
                    OR DATE_FORMAT(p.data_criacao, '%H:%i') LIKE ?
                )
                AND p.tipo_publicacao = 'animal'
                ORDER BY $orderBy";

        $stmt = $obj->prepare($query);
        $stmt->bind_param("ssssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $query = "SELECT p.id_publicacao, p.titulo, p.conteudo, p.tipo_publicacao, p.data_criacao, p.data_atualizacao, u.nome 
                FROM publicacao p 
                JOIN usuario u ON p.id_usuario = u.id_usuario 
                WHERE p.tipo_publicacao = 'animal'
                ORDER BY $orderBy";
        $result = $obj->query($query);
    }

    if (isset($_GET['ajax_search']) && $_GET['ajax_search'] == 1) {
        header('Content-Type: application/json; charset=utf-8');
        $term = isset($_GET['term']) ? trim($_GET['term']) : '';

        if (strlen($term) < 1) {
            echo json_encode([]);
            exit;
        }

        $obj->set_charset("utf8mb4");
        $likeTerm = "%$term%";
        $stmt = $obj->prepare("SELECT id_usuario, nome FROM usuario WHERE nome LIKE ? LIMIT 10");

        if ($stmt === false) {
            echo json_encode(['error' => 'Erro no prepare']);
            exit;
        }

        $stmt->bind_param('s', $likeTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        echo json_encode($users);
        exit;
    }

    if (isset($_POST['impulsionar']) && isset($_POST['id_publicacao'])) {
        if (!$isLoggedIn) {
            header("Location: login.php");
            exit;
        }

        $idPublicacao = intval($_POST['id_publicacao']);

        if ($isLoggedIn) {
            $checkQuery = "SELECT 1 FROM impulso_publicacao WHERE id_usuario = ? AND id_publicacao = ?";
            $stmt = $obj->prepare($checkQuery);
            $stmt->bind_param("ii", $userId, $idPublicacao);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $deleteQuery = "DELETE FROM impulso_publicacao WHERE id_usuario = ? AND id_publicacao = ?";
                $stmtDel = $obj->prepare($deleteQuery);
                $stmtDel->bind_param("ii", $userId, $idPublicacao);
                $stmtDel->execute();

                $updateQuery = "UPDATE publicacao SET total_impulsos = GREATEST(total_impulsos - 1, 0) WHERE id_publicacao = ?";
                $stmtUpd = $obj->prepare($updateQuery);
                $stmtUpd->bind_param("i", $idPublicacao);
                $stmtUpd->execute();

                unset($_SESSION['impulsionado_' . $idPublicacao]);
            } else {
                $insertQuery = "INSERT INTO impulso_publicacao (id_usuario, id_publicacao) VALUES (?, ?)";
                $stmtIns = $obj->prepare($insertQuery);
                $stmtIns->bind_param("ii", $userId, $idPublicacao);
                $stmtIns->execute();

                $updateQuery = "UPDATE publicacao SET total_impulsos = total_impulsos + 1 WHERE id_publicacao = ?";
                $stmtUpd = $obj->prepare($updateQuery);
                $stmtUpd->bind_param("i", $idPublicacao);
                $stmtUpd->execute();

                $_SESSION['impulsionado_' . $idPublicacao] = true;
            }
        }

        $redirectUrl = 'lost-animals.php';
        if (!empty($_GET['pesquisa'])) {
            $redirectUrl .= '?pesquisa=' . urlencode($_GET['pesquisa']);
        }
        header("Location: $redirectUrl");
        exit;
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
                <form class="search-bar" method="GET" action="lost-animals.php">
                    <input type="text" name="pesquisa" placeholder="Pesquisar..." value="<?php echo isset($_GET['pesquisa']) ? htmlspecialchars($_GET['pesquisa']) : ''; ?>">
                    <button type="submit">🔍</button>
                </form>
                <div id="user-suggestions" class="user-suggestions"></div>
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
                                <form action="lost-animals.php" method="POST">
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
            <div class="order-dropdown">
                <button class="order-button" id="orderToggle">⮃ Ordenar</button>
                <div class="order-menu" id="orderMenu">
                    <a href="?ordenar_por=data_desc<?php echo $pesquisa ? '&pesquisa=' . urlencode($pesquisa) : ''; ?>">📅 Mais recentes</a>
                    <a href="?ordenar_por=data_asc<?php echo $pesquisa ? '&pesquisa=' . urlencode($pesquisa) : ''; ?>">🕰️ Mais antigos</a>
                    <a href="?ordenar_por=impulsos_desc<?php echo $pesquisa ? '&pesquisa=' . urlencode($pesquisa) : ''; ?>">🔝 Mais impulsionados</a>
                </div>
            </div>
            <div class="lost-animal-post">
                <?php if ($result->num_rows > 0): ?>
                    <h2>Animais Perdidos</h2>
                    <?php while ($post = $result->fetch_assoc()): ?>

                    <?php
                        $idPost = $post['id_publicacao'];
                        $images = [];

                        $imgQuery = "SELECT imagem_url FROM imagem WHERE id_publicacao = ?";
                        $stmtImg = $obj->prepare($imgQuery);
                        $stmtImg->bind_param("i", $idPost);
                        $stmtImg->execute();
                        $imgResult = $stmtImg->get_result();

                        while ($row = $imgResult->fetch_assoc()) {
                            $images[] = $row['imagem_url'];
                        }
                    ?>

                    <div class="post-item">
                        <p class="post-info">
                            <span class="author-name"><?php echo $post['nome']; ?></span>
                            <span class="post-time">
                                <?php 
                                    setlocale(LC_TIME, 'pt_BR.UTF-8');
                                    echo utf8_encode(strftime('%d de %B de %Y, %Hh%M', strtotime($post['data_criacao'])));
                                ?>

                                <?php if (!empty($post['data_atualizacao']) && $post['data_criacao'] != $post['data_atualizacao']): ?>
                                    <em style="font-size: 0.85em; color: #777;">
                                        (editado às <?php echo utf8_encode(strftime('%d de %B de %Y, %Hh%M', strtotime($post['data_atualizacao']))); ?>)
                                    </em>
                                <?php endif; ?>
                            </span>
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
                        
                        <?php
                            $images = $images ?? [];
                            $totalImages = count($images);
                            $maxVisible = 3;

                            $galleryClass = 'multiple-images';
                            if ($totalImages == 1) {
                                $galleryClass = 'single-image';
                            } elseif ($totalImages == 2) {
                                $galleryClass = 'two-images';
                            }

                            $visibleImages = array_slice($images, 0, $maxVisible);
                            $moreCount = max(0, $totalImages - $maxVisible);
                        ?>

                        <div class="image-gallery <?php echo $galleryClass; ?>">
                            <?php foreach ($visibleImages as $index => $imagem): ?>
                                <?php 
                                    $isLastVisibleWithMore = ($index === $maxVisible - 1 && $moreCount > 0);
                                ?>
                                <div 
                                    class="image-wrapper<?php echo $isLastVisibleWithMore ? ' more-images-posts' : ''; ?>" 
                                    <?php if ($isLastVisibleWithMore): ?>
                                        data-images='<?php echo json_encode($images); ?>'
                                    <?php endif; ?>
                                >
                                    <?php if ($isLastVisibleWithMore): ?>
                                        <div class="image-overlay">+<?php echo $moreCount; ?></div>
                                    <?php endif; ?>
                                    <img src="../images/uploads/posts/<?php echo htmlspecialchars($imagem); ?>" alt="Imagem da publicação">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="post-actions">
                            <?php
                                $jaImpulsionou = false;
                                $impulsos = 0;

                                if ($isLoggedIn) {
                                    $checkQuery = "SELECT 1 FROM impulso_publicacao WHERE id_usuario = ? AND id_publicacao = ?";
                                    $stmt = $obj->prepare($checkQuery);
                                    $stmt->bind_param("ii", $userId, $idPost);
                                    $stmt->execute();
                                    $stmt->store_result();
                                    $jaImpulsionou = $stmt->num_rows > 0;
                                }

                                $q = $obj->prepare("SELECT total_impulsos FROM publicacao WHERE id_publicacao = ?");
                                $q->bind_param("i", $idPost);
                                $q->execute();
                                $r = $q->get_result()->fetch_assoc();
                                $impulsos = $r ? intval($r['total_impulsos']) : 0;

                                if ($isLoggedIn && $jaImpulsionou) {
                                    $labelBotao = '✅ Impulsionado' . ($impulsos > 0 ? " ($impulsos)" : '');
                                } else {
                                    $labelBotao = '⬆️ Impulsionar' . ($impulsos > 0 ? " ($impulsos)" : '');
                                }

                                $btnClass = 'like-button';
                                if ($isLoggedIn && $jaImpulsionou) {
                                    $btnClass .= ' impulsionado';
                                }
                            ?>
                            <form method="POST" action="lost-animals.php<?php echo !empty($pesquisa) ? '?pesquisa=' . urlencode($pesquisa) : ''; ?>" style="display: contents;">
                                <?php if (!empty($pesquisa)): ?>
                                    <input type="hidden" name="pesquisa" value="<?php echo htmlspecialchars($pesquisa); ?>">
                                <?php endif; ?>
                                <input type="hidden" name="id_publicacao" value="<?php echo $idPost; ?>">
                                <button 
                                    type="submit" 
                                    name="impulsionar" 
                                    class="<?php echo $btnClass; ?>"
                                >
                                    <?php echo $labelBotao; ?>
                                </button>
                            </form>

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

        <div id="modal-images-posts" class="modal-images-posts">
            <div class="modal-content-images-posts">
                <span class="close-images-posts">&times;</span>
                <button id="prevImage" class="modal-nav-button" aria-label="Imagem anterior">&#10094;</button>
                <div class="modal-gallery-images-posts">
                <img id="modalImage" src="" alt="Imagem Modal">
                </div>
                <button id="nextImage" class="modal-nav-button" aria-label="Próxima Imagem">&#10095;</button>
            </div>
        </div>
    </section>

    <script src="../../scripts/pages/lost-animals/lost-animals.js"></script>
    <script src="../../scripts/order-posts.js"></script>
    <script src="../../scripts/user-suggestions.js"></script>

</body>
</html>