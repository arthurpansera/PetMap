<?php
    include('../../../conecta_db.php');

    session_start();

    $tempoInatividade = 900;

    if (!isset($_SESSION['id_usuario'])) {
        $_SESSION['error_message'] = 'Sua sessão expirou. Faça login novamente.';
        header("Location: login.php?erro=expirado");
        exit();
    }

    if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso']) > $tempoInatividade) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['error_message'] = 'Sua sessão expirou por inatividade.';
        header("Location: login.php");
        exit();
    }

    $_SESSION['ultimo_acesso'] = time();

    if (isset($_SESSION['error_message'])) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Erro!',
                    text: '{$_SESSION['error_message']}',
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

    $obj = conecta_db();
    $obj->query("SET lc_time_names = 'pt_BR'");
    date_default_timezone_set('America/Sao_Paulo');
    setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil');

    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    $isModerator = false;

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

    $idUsuario = (int) $_GET['id'];

    $queryUser = "SELECT u.id_usuario, u.nome, p.descricao AS tipo_conta, p.foto,
            c.email, c.telefone,
            o.endereco_rua AS ong_endereco_rua, o.endereco_numero AS ong_endereco_numero,
            o.endereco_complemento AS ong_endereco_complemento, o.endereco_bairro AS ong_endereco_bairro,
            o.endereco_cidade AS ong_endereco_cidade, o.endereco_estado AS ong_endereco_estado,
            o.endereco_pais AS ong_endereco_pais, o.endereco_cep AS ong_endereco_cep,
            cida.endereco_rua AS cidadao_endereco_rua, cida.endereco_numero AS cidadao_endereco_numero,
            cida.endereco_complemento AS cidadao_endereco_complemento, cida.endereco_bairro AS cidadao_endereco_bairro,
            cida.endereco_cidade AS cidadao_endereco_cidade, cida.endereco_estado AS cidadao_endereco_estado,
            cida.endereco_pais AS cidadao_endereco_pais, cida.endereco_cep AS cidadao_endereco_cep
        FROM usuario u
        LEFT JOIN perfil p ON u.id_usuario = p.id_usuario
        LEFT JOIN contato c ON u.id_usuario = c.id_usuario
        LEFT JOIN ong o ON u.id_usuario = o.id_usuario
        LEFT JOIN cidadao cida ON u.id_usuario = cida.id_usuario
        WHERE u.id_usuario = ?
    ";

    $stmtUser = $obj->prepare($queryUser);
    $stmtUser->bind_param("i", $idUsuario);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();

    $user = $resultUser->fetch_assoc();

    $queryPosts = "SELECT p.id_publicacao, p.titulo, p.conteudo, p.tipo_publicacao, p.data_criacao, p.data_atualizacao,
            p.endereco_rua, p.endereco_bairro, p.endereco_cidade, p.endereco_estado
        FROM publicacao p
        WHERE p.id_usuario = ?
        ORDER BY p.data_criacao DESC
    ";

    $stmtPosts = $obj->prepare($queryPosts);
    $stmtPosts->bind_param("i", $idUsuario);
    $stmtPosts->execute();
    $result_posts = $stmtPosts->get_result();

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

        $redirectUrl = 'view-profile.php?id=' . intval($_GET['id']);
        header("Location: $redirectUrl");
        exit;
    }

    if (isset($_POST['comentar']) && isset($_POST['id_publicacao']) && isset($_POST['conteudo_comentario'])) {
        if (!$isLoggedIn) {
            header("Location: src/assets/pages/login.php");
            exit;
        }

        $idPublicacao = intval($_POST['id_publicacao']);
        $conteudoComentario = trim($_POST['conteudo_comentario']);

        if (!empty($conteudoComentario)) {
            $insertComentario = "INSERT INTO comentario (id_usuario, id_publicacao, conteudo) VALUES (?, ?, ?)";
            $stmtComentario = $obj->prepare($insertComentario);
            $stmtComentario->bind_param("iis", $userId, $idPublicacao, $conteudoComentario);
            $stmtComentario->execute();

            $updateTotal = "UPDATE publicacao SET total_comentarios = total_comentarios + 1 WHERE id_publicacao = ?";
            $stmtUpdate = $obj->prepare($updateTotal);
            $stmtUpdate->bind_param("i", $idPublicacao);
            $stmtUpdate->execute();
        }


        if ($stmtComentario->affected_rows > 0) {
            $_SESSION['success_message'] = "Comentário enviado com sucesso.";
        } else {
            $_SESSION['error_message'] = "Erro ao enviar comentário.";
        }

        $redirectUrl = 'view-profile.php?id=' . intval($_GET['id']);
        header("Location: $redirectUrl");
        exit;
    }

    if (isset($_POST['update_comment'])) {
        $idComentario = intval($_POST['id_comentario']);
        $conteudo = trim($_POST['conteudo_comentario']);
        $idUsuarioLogado = $_SESSION['id_usuario'];
        $redirectUrl = 'view-profile.php?id=' . intval($_GET['id']);


        if ($conteudo === '') {
            $_SESSION['error_message'] = "O comentário não pode ficar vazio.";
            header("Location: $redirectUrl");
            exit;
        }

        $queryCheck = $obj->prepare("SELECT id_usuario FROM comentario WHERE id_comentario = ?");
        $queryCheck->bind_param('i', $idComentario);
        $queryCheck->execute();
        $result = $queryCheck->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['error_message'] = "Comentário não encontrado.";
            header("Location: $redirectUrl");
            exit;
        }

        $row = $result->fetch_assoc();
        if ($row['id_usuario'] != $idUsuarioLogado) {
            $_SESSION['error_message'] = "Você não tem permissão para editar esse comentário.";
            header("Location: $redirectUrl");
            exit;
        }

        $updateQuery = $obj->prepare("UPDATE comentario SET conteudo = ?, data_atualizacao = NOW() WHERE id_comentario = ?");
        $updateQuery->bind_param('si', $conteudo, $idComentario);
        $updateQuery->execute();

        if ($updateQuery->affected_rows > 0) {
            $_SESSION['success_message'] = "Comentário atualizado com sucesso.";
        } else {
            $_SESSION['error_message'] = "Nenhuma alteração feita ou erro na atualização.";
        }

        header("Location: $redirectUrl");
        exit;
    }

    if (isset($_POST['delete_comment'])) {
        $idComentario = intval($_POST['id_comentario_excluir']);
        $idUsuarioLogado = $_SESSION['id_usuario'];
        $redirectUrl = 'view-profile.php?id=' . intval($_GET['id']);

        $queryCheck = $obj->prepare("SELECT id_usuario FROM comentario WHERE id_comentario = ?");
        $queryCheck->bind_param('i', $idComentario);
        $queryCheck->execute();
        $result = $queryCheck->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['error_message'] = "Comentário não encontrado.";
            header("Location: $redirectUrl");
            exit;
        }

        $row = $result->fetch_assoc();
        if ($row['id_usuario'] != $idUsuarioLogado) {
            $_SESSION['error_message'] = "Você não tem permissão para excluir esse comentário.";
            header("Location: $redirectUrl");
            exit;
        }

        $deleteQuery = $obj->prepare("DELETE FROM comentario WHERE id_comentario = ?");
        $deleteQuery->bind_param('i', $idComentario);
        $deleteQuery->execute();

        if ($deleteQuery->affected_rows > 0) {
            $_SESSION['success_message'] = "Comentário excluído com sucesso.";
        } else {
            $_SESSION['error_message'] = "Erro ao excluir comentário.";
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
    <title>PetMap | Perfil</title>
    <link href="../../styles/pages/view-profile/view-profile.css" rel="stylesheet">
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
    </section>
    <section class="profile">
        <div class="profile-content">
            <div class="profile-header">
                <div>
                    <?php if (!empty($user['foto'])): ?>
                        <img src="/PetMap/PROJETO/src/assets/images/uploads/profile/<?php echo htmlspecialchars($user['foto']) . '?v=' . time(); ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <img src="../images/perfil-images/default-profile-photo.png" alt="Foto padrão" class="profile-picture">
                    <?php endif; ?>
                </div>

                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['nome']); ?></h2>

                    <div class="profile-details">
                        <p><span class="label">Tipo de Conta:</span> <?php echo htmlspecialchars($user['tipo_conta']); ?></p>
                        <p><span class="label">E-mail:</span> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><span class="label">Telefone:</span> <?php echo htmlspecialchars($user['telefone']); ?></p>

                        <?php if ($user['tipo_conta'] == 'Perfil de ONG' || $user['tipo_conta'] == 'Perfil de cidadão'): ?>
                            <div class="address-grid">
                                <div>
                                    <p><span class="label">Endereço:</span>
                                        <?php
                                            $rua = ($user['tipo_conta'] == 'Perfil de ONG') ? $user['ong_endereco_rua'] : $user['cidadao_endereco_rua'];
                                            $numero = ($user['tipo_conta'] == 'Perfil de ONG') ? $user['ong_endereco_numero'] : $user['cidadao_endereco_numero'];
                                            echo (stripos($rua, 'Rua ') !== 0 ? 'Rua ' : '') . htmlspecialchars($rua) . ', ' . htmlspecialchars($numero);
                                        ?>
                                    </p>
                                    <?php if (!empty($user['ong_endereco_complemento']) || !empty($user['cidadao_endereco_complemento'])): ?>
                                        <p><span class="label">Complemento:</span> <?php echo htmlspecialchars(($user['tipo_conta'] == 'Perfil de ONG') ? $user['ong_endereco_complemento'] : $user['cidadao_endereco_complemento']); ?></p>
                                    <?php endif; ?>
                                    <p><span class="label">Bairro:</span> <?php echo htmlspecialchars(($user['tipo_conta'] == 'Perfil de ONG') ? $user['ong_endereco_bairro'] : $user['cidadao_endereco_bairro']); ?></p>
                                </div>
                                <div>
                                    <p><span class="label">Cidade:</span> <?php echo htmlspecialchars(($user['tipo_conta'] == 'Perfil de ONG') ? $user['ong_endereco_cidade'] : $user['cidadao_endereco_cidade']); ?></p>
                                    <p><span class="label">Estado:</span> <?php echo htmlspecialchars(($user['tipo_conta'] == 'Perfil de ONG') ? $user['ong_endereco_estado'] : $user['cidadao_endereco_estado']); ?></p>
                                    <p><span class="label">País:</span> <?php echo htmlspecialchars(($user['tipo_conta'] == 'Perfil de ONG') ? $user['ong_endereco_pais'] : $user['cidadao_endereco_pais']); ?></p>
                                    <p><span class="label">CEP:</span> <?php echo htmlspecialchars(($user['tipo_conta'] == 'Perfil de ONG') ? $user['ong_endereco_cep'] : $user['cidadao_endereco_cep']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
    </section>
    
    <div class="content-area">
        <div class="tabs">
            <button onclick="showSection('publicacoes-section')">📄 Publicações</button>
            <button onclick="showSection('comentarios-section')">💬 Comentários</button>
        </div>
    </div>

    <section id="publicacoes-section" style="display: block;" class="content">
        <div class="user-posts">
            <h2>Publicações</h2>
            <?php if ($result_posts->num_rows > 0): ?>
                <?php while ($post = $result_posts->fetch_assoc()): ?>

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

                        $getComentarios = $obj->prepare("SELECT c.conteudo, c.data_criacao, u.nome
                                FROM comentario c
                                JOIN usuario u ON c.id_usuario = u.id_usuario
                                WHERE c.id_publicacao = ?
                                ORDER BY c.data_criacao DESC
                            ");
                            $getComentarios->bind_param("i", $idPost);
                            $getComentarios->execute();
                            $comentarios = $getComentarios->get_result();
                    ?>

                    <div class="post-item">
                        <p class="post-info">
                            <span class="author-name"><?php echo htmlspecialchars($user['nome']); ?></span> • 
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

                    <h3 class="post-title"><?php echo htmlspecialchars($post['titulo']); ?></h3>
                    <p><?php echo $post['conteudo']; ?></p>

                    <?php if (!empty($post['endereco_rua']) || !empty($post['endereco_bairro']) || !empty($post['endereco_cidade']) || !empty($post['endereco_estado'])): ?>
                    
                        <p class="post-address" style="margin-top: 8px; color: #555; font-size: 0.95rem;">
                            📍
                            <?php
                                $enderecoFormatado = [];

                                if (!empty($post['endereco_rua'])) {
                                    $enderecoFormatado[] = $post['endereco_rua'];
                                }
                                if (!empty($post['endereco_bairro'])) {
                                    $enderecoFormatado[] = 'Bairro ' . $post['endereco_bairro'];
                                }
                                if (!empty($post['endereco_cidade'])) {
                                    $cidadeEstado = $post['endereco_cidade'];
                                    if (!empty($post['endereco_estado'])) {
                                        $cidadeEstado .= ' - ' . strtoupper($post['endereco_estado']);
                                    }
                                    $enderecoFormatado[] = $cidadeEstado;
                                } elseif (!empty($post['endereco_estado'])) {
                                    $enderecoFormatado[] = strtoupper($post['endereco_estado']);
                                }
                                echo implode(', ', $enderecoFormatado);
                            ?>
                        </p>

                    <?php else: ?>

                        <p class="post-address" style="margin-top: 8px; color: #555; font-size: 0.95rem; font-style: italic;">
                            Endereço não informado
                        </p>

                    <?php endif; ?>

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

                    <?php
                        $idPost = $post['id_publicacao'];

                        $getComentarios = $obj->prepare("SELECT c.id_comentario, c.conteudo, c.data_criacao, c.id_usuario, u.nome
                            FROM comentario c
                            JOIN usuario u ON c.id_usuario = u.id_usuario
                            WHERE c.id_publicacao = ?
                            ORDER BY c.data_criacao DESC
                        ");
                        $getComentarios->bind_param("i", $idPost);
                        $getComentarios->execute();
                        $comentarios = $getComentarios->get_result();

                        $comentariosArray = [];
                        while ($row = $comentarios->fetch_assoc()) {
                            $comentariosArray[] = $row;
                        }
                        $totalComentarios = count($comentariosArray);

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

                    <div class="post-actions">
                        <div class="posts-buttons">
                            <form method="POST" action="view-profile.php?id=<?php echo $idUsuario; ?>" style="display: contents;">
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

                            <?php if ($isLoggedIn): ?>
                                    <button class="comment-button" onclick="toggleCommentForm(<?php echo $idPost; ?>)">💬 Comentar</button>
                                <?php else: ?>
                                    <form method="GET" action="src/assets/pages/login.php" style="display: contents;">
                                        <button type="submit" class="comment-button">💬 Comentar</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($totalComentarios > 0): ?>
                                    <button class="toggle-comments-button comment-button" onclick="toggleComments(<?php echo $idPost; ?>)">
                                        💬 Ver comentários (<?php echo $totalComentarios; ?>)
                                    </button>
                            <?php endif; ?>
                        </div>

                        <?php if ($isLoggedIn): ?>
                            <div class="comment-form-containe comment-form" id="comment-form-<?php echo $idPost; ?>" style="display: none">
                                <div id="comment-form-container-<?php echo $idPost; ?>" style="display:none;">
                                    <form method="POST" class="comment-form" id="comment-form-<?php echo $idPost; ?>">
                                        <input type="hidden" name="id_publicacao" value="<?php echo $idPost; ?>">
                                        <input type="hidden" name="id_comentario" id="id_comentario_<?php echo $idPost; ?>" value="">
                                        <textarea name="conteudo_comentario" id="textarea_comentario_<?php echo $idPost; ?>" rows="2" placeholder="Escreva um comentário..." required></textarea>

                                        <button type="submit" id="submit-button-<?php echo $idPost; ?>" name="comentar">Enviar</button>
                                        <button type="button" onclick="closeCommentForm(<?php echo $idPost; ?>)">Cancelar</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($totalComentarios > 0): ?>
                            <div class="comments-profile" id="comments-wrapper-<?php echo $idPost; ?>" style="display: none;">
                                <div class="comments-list-profile" id="comments-<?php echo $idPost; ?>">
                                    <?php foreach ($comentariosArray as $comentario): ?>
                                        <div class="comment-profile" style="margin-bottom: 10px;">
                                            <p class="comment-user"><strong><?php echo htmlspecialchars($comentario['nome']); ?></strong> comentou:</p>
                                            <p class="comment-content"><?php echo nl2br(htmlspecialchars($comentario['conteudo'])); ?></p>
                                            <p class="comment-date">
                                                <small><?php echo utf8_encode(strftime('%d de %B de %Y, %Hh%M', strtotime($comentario['data_criacao']))); ?></small>
                                            </p>

                                            <?php if ($isLoggedIn && $comentario['id_usuario'] == $_SESSION['id_usuario']): ?>

                                                <div class="comment-actions">
                                                    <button class="edit-comment-btn"
                                                        onclick="editarComentario(
                                                            <?php echo $idPost; ?>,
                                                            <?php echo $comentario['id_comentario']; ?>,
                                                            '<?php echo htmlspecialchars(addslashes($comentario['conteudo'])); ?>'
                                                        )">✏️ Editar
                                                    </button>

                                                    <form method="POST" id="form-excluir-<?= $comentario['id_comentario']; ?>">
                                                        <input type="hidden" name="id_comentario_excluir" value="<?= $comentario['id_comentario']; ?>">
                                                        <button type="button" onclick="confirmDelete(this)" name="delete_comment" class="delete-comment-btn">
                                                            🗑️ Excluir
                                                        </button>
                                                    </form>

                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php elseif ($user['tipo_conta'] === 'Perfil de moderador'): ?>
                <p style="font-size: 1.2rem; ">Este é um perfil de moderador. Moderadores não podem realizar publicações, mas podem comentar em publicações de outros usuários.</p><br><br>
            <?php else: ?>
                <p style="font-size: 1.2rem; ">Este usuário ainda não realizou nenhuma publicação.</p><br><br>
            <?php endif; ?>
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

    <section id="comentarios-section" style="display: none;" class="content">
        <?php
            $id_usuario = $idUsuario;

            $getComentarios = $obj->prepare(" SELECT 
                    c.id_comentario, 
                    c.conteudo, 
                    c.data_criacao, 
                    c.id_usuario,
                    u.nome,
                    p.id_publicacao,
                    p.titulo AS titulo_publicacao
                FROM comentario c
                JOIN usuario u ON c.id_usuario = u.id_usuario
                JOIN publicacao p ON c.id_publicacao = p.id_publicacao
                WHERE c.id_usuario = ?
                ORDER BY c.data_criacao DESC
                ");

            $getComentarios->bind_param("i", $id_usuario);
            $getComentarios->execute();
            $comentarios = $getComentarios->get_result();

            $comentariosDoUsuario = [];
            while ($row = $comentarios->fetch_assoc()) {
                $comentariosDoUsuario[] = $row;
            }
        ?>

        <div class="comentarios-perfil" id="comentarios-perfil">
            <h2>Comentários</h2>
            <?php if (count($comentariosDoUsuario) > 0): ?>
                
                <div class="comments-list">
                    <?php foreach ($comentariosDoUsuario as $comentario): ?>
                        <div class="comment">
                            <p class="comment-on-post">
                                Você comentou em: <strong ><?php echo htmlspecialchars($comentario['titulo_publicacao']); ?></strong>
                                às <strong><?php echo strftime('%Hh%M, %d de %B de %Y', strtotime($comentario['data_criacao'])); ?></strong>
                            </p>

                             <p class="comment-content"><?php echo nl2br(htmlspecialchars($comentario['conteudo'])); ?></p>
                        </div>
                    <?php endforeach; ?>

                    <div id="floating-edit-form" class="comment-form" style="display: none;">
                        <form method="POST" id="comment-form-perfil">
                            <input type="hidden" name="id_comentario" id="id_comentario_perfil" value="">
                            <textarea name="conteudo_comentario" id="textarea_comentario_perfil" rows="3" placeholder="Edite seu comentário..." required></textarea>

                            <button type="submit" id="submit-button-perfil" name="update_comment">Enviar</button>
                            <button type="button" onclick="closeCommentFormPerfil()">Cancelar</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <p style="font-size: 1.2rem; ">Este usuário ainda não comentou em nenhuma publicação.</p><br><br>
            <?php endif; ?>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../scripts/pages/view-profile/view-profile.js"></script>
    <script src="../../scripts/view-comments.js"></script>


</body>
</html>