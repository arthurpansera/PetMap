<?php
    include('../../../conecta_db.php');

    session_start();

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

    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    $isModerator = false;

    $obj = conecta_db();

    if (!$obj) {
        header("Location: database-error.php");
        exit;
    }
    
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

        $query = "SELECT p.id_publicacao, p.titulo, p.conteudo, p.tipo_publicacao, p.data_criacao, p.data_atualizacao, p.status_publicacao, 
                        p.endereco_rua, p.endereco_bairro, p.endereco_cidade, p.endereco_estado, u.nome 
                FROM publicacao p 
                JOIN usuario u ON p.id_usuario = u.id_usuario 
                JOIN perfil pf ON u.id_usuario = pf.id_usuario
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
                AND p.tipo_publicacao = 'resgate'
                AND pf.status_perfil != 'banido'
                ORDER BY $orderBy";

        $stmt = $obj->prepare($query);
        $stmt->bind_param("ssssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $query = "SELECT p.id_publicacao, p.titulo, p.conteudo, p.tipo_publicacao, p.data_criacao, p.data_atualizacao, p.status_publicacao, 
                        p.endereco_rua, p.endereco_bairro, p.endereco_cidade, p.endereco_estado, u.nome 
                FROM publicacao p 
                JOIN usuario u ON p.id_usuario = u.id_usuario 
                JOIN perfil pf ON u.id_usuario = pf.id_usuario
                WHERE p.tipo_publicacao = 'resgate'
                AND pf.status_perfil != 'banido'
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

        $currentUserId = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : null;
        $obj->set_charset("utf8mb4");
        $likeTerm = "%$term%";

        if ($currentUserId !== null) {
            $stmt = $obj->prepare("SELECT id_usuario, nome FROM usuario WHERE nome LIKE ? AND id_usuario != ? LIMIT 10");
            if ($stmt === false) {
                echo json_encode(['error' => 'Erro no prepare']);
                exit;
            }
            $stmt->bind_param('si', $likeTerm, $currentUserId);
        } else {
            $stmt = $obj->prepare("SELECT id_usuario, nome FROM usuario WHERE nome LIKE ? LIMIT 10");
            if ($stmt === false) {
                echo json_encode(['error' => 'Erro no prepare']);
                exit;
            }
            $stmt->bind_param('s', $likeTerm);
        }

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

                $stmt = $obj->prepare("SELECT id_usuario FROM publicacao WHERE id_publicacao = ?");
                $stmt->bind_param("i", $idPublicacao);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();

                $id_usuario_dono = $res['id_usuario'] ?? null;

                if ($id_usuario_dono && $id_usuario_dono != $userId) {
                    $mensagem = 'Seu post foi impulsionado por ' . htmlspecialchars($userName);

                    $stmtNotif = $obj->prepare("INSERT INTO notificacao (id_usuario_destinatario, id_usuario_acionador, id_publicacao, tipo, mensagem) VALUES (?, ?, ?, 'impulso', ?)");
                    $stmtNotif->bind_param("iiis", $id_usuario_dono, $userId, $idPublicacao, $mensagem);
                    $stmtNotif->execute();
                }
            }
        }

        $redirectUrl = 'rescued-animals.php';
        if (!empty($_GET['pesquisa'])) {
            $redirectUrl .= '?pesquisa=' . urlencode($_GET['pesquisa']);
        }
        header("Location: $redirectUrl");
        exit;
    }

    if (isset($_POST['comentar']) && isset($_POST['id_publicacao']) && isset($_POST['conteudo_comentario'])) {
        if (!$isLoggedIn) {
            header("Location: login.php");
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

            $stmt = $obj->prepare("SELECT id_usuario FROM publicacao WHERE id_publicacao = ?");
            $stmt->bind_param("i", $idPublicacao);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();

            $id_usuario_dono = $res['id_usuario'] ?? null;

            if ($id_usuario_dono && $id_usuario_dono != $userId) {
                $mensagem = 'Você recebeu um comentário de ' . htmlspecialchars($userName);
                
                $stmtNotif = $obj->prepare("INSERT INTO notificacao (id_usuario_destinatario, id_usuario_acionador, id_publicacao, tipo, mensagem) VALUES (?, ?, ?, 'comentario', ?)");
                $stmtNotif->bind_param("iiis", $id_usuario_dono, $userId, $idPublicacao, $mensagem);
                $stmtNotif->execute();
            }
        }

        $redirectUrl = 'rescued-animals.php';
        if (!empty($_GET['pesquisa'])) {
            $redirectUrl .= '?pesquisa=' . urlencode($_GET['pesquisa']);
        }

        if ($stmtComentario->affected_rows > 0) {
            $_SESSION['success_message'] = "Comentário enviado com sucesso.";
        } else {
            $_SESSION['error_message'] = "Erro ao enviar comentário.";
        }

        header("Location: $redirectUrl");
        exit;
    }

    if (isset($_POST['update_comment'])) {
        $idComentario = intval($_POST['id_comentario']);
        $conteudo = trim($_POST['conteudo_comentario']);
        $idUsuarioLogado = $_SESSION['id_usuario'];

        if ($conteudo === '') {
            $_SESSION['error_message'] = "O comentário não pode ficar vazio.";
            header('Location: rescued-animals.php');
            exit;
        }

        $queryCheck = $obj->prepare("SELECT id_usuario FROM comentario WHERE id_comentario = ?");
        $queryCheck->bind_param('i', $idComentario);
        $queryCheck->execute();
        $result = $queryCheck->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['error_message'] = "Comentário não encontrado.";
            header('Location: rescued-animals.php');
            exit;
        }

        $row = $result->fetch_assoc();
        if ($row['id_usuario'] != $idUsuarioLogado) {
            $_SESSION['error_message'] = "Você não tem permissão para editar esse comentário.";
            header('Location: rescued-animals.php');
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

        header('Location: rescued-animals.php');
        exit;
    }

    if (isset($_POST['delete_comment'])) {
        $idComentario = intval($_POST['id_comentario_excluir']);
        $idUsuarioLogado = $_SESSION['id_usuario'];

        $queryCheck = $obj->prepare("SELECT id_usuario FROM comentario WHERE id_comentario = ?");
        $queryCheck->bind_param('i', $idComentario);
        $queryCheck->execute();
        $result = $queryCheck->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['error_message'] = "Comentário não encontrado.";
            header('Location: rescued-animals.php');
            exit;
        }

        $row = $result->fetch_assoc();
        if ($row['id_usuario'] != $idUsuarioLogado) {
            $_SESSION['error_message'] = "Você não tem permissão para excluir esse comentário.";
            header('Location: rescued-animals.php');
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

        header('Location: rescued-animals.php');
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
    <title>PetMap | Animais Resgatados</title>
    <link rel="stylesheet" href="../../styles/pages/rescued-animals/rescued-animals.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <a href="../../../index.php">
                    <img src="../images/logo-petmap/white-logo.png" alt="Logo PetMap">
                </a>
                <form class="search-bar" method="GET" action="rescued-animals.php">
                    <input type="text" name="pesquisa" autocomplete="off" placeholder="Pesquisar..." value="<?php echo isset($_GET['pesquisa']) ? htmlspecialchars($_GET['pesquisa']) : ''; ?>">
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
                                <form action="rescued-animals.php" method="POST">
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
                            <form action="rescued-animals.php" method="POST">
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
            <div class="order-dropdown">
                <button class="order-button" id="orderToggle">⮃ Ordenar</button>
                <div class="order-menu" id="orderMenu">
                    <a href="?ordenar_por=data_desc<?php echo $pesquisa ? '&pesquisa=' . urlencode($pesquisa) : ''; ?>">📅 Mais recentes</a>
                    <a href="?ordenar_por=data_asc<?php echo $pesquisa ? '&pesquisa=' . urlencode($pesquisa) : ''; ?>">🕰️ Mais antigos</a>
                    <a href="?ordenar_por=impulsos_desc<?php echo $pesquisa ? '&pesquisa=' . urlencode($pesquisa) : ''; ?>">🔝 Mais impulsionados</a>
                </div>
            </div>
            <div class="rescued-animal-post">
                <?php if ($result->num_rows > 0): ?>
                    <h2>Animais Resgatados</h2>
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
                            <span>
                                <span class="author-name"><?php echo $post['nome']; ?></span> • 
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
                            </span>

                            <?php if ($post['status_publicacao'] === 'verificado'): ?>
                                <span class="verified-label">✔️ Publicação Verificada</span>
                            <?php endif; ?>
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
                                    if (!empty($post['endereco_cidade']) && !empty($post['endereco_estado'])) {
                                        $enderecoFormatado[] = $post['endereco_cidade'] . ' - ' . strtoupper($post['endereco_estado']);
                                    } elseif (!empty($post['endereco_cidade'])) {
                                        $enderecoFormatado[] = $post['endereco_cidade'];
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

                            $getComentarios = $obj->prepare("SELECT c.id_comentario, c.conteudo, c.data_criacao, c.id_usuario, c.status_comentario, u.nome
                                FROM comentario c
                                JOIN usuario u ON c.id_usuario = u.id_usuario
                                JOIN perfil pf ON u.id_usuario = pf.id_usuario
                                WHERE c.id_publicacao = ?
                                AND pf.status_perfil != 'banido'
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
                                <form method="POST" action="rescued-animals.php<?php echo !empty($pesquisa) ? '?pesquisa=' . urlencode($pesquisa) : ''; ?>" style="display: contents;">
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
                                        <form method="GET" action="login.php" style="display: contents;">
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
                                <div class="comment-form-container comment-form" id="comment-form-<?php echo $idPost; ?>" style="display: none">
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
                                <div class="comments" id="comments-wrapper-<?php echo $idPost; ?>" style="display: none;">
                                    <div class="comments-list" id="comments-<?php echo $idPost; ?>">
                                        <?php foreach ($comentariosArray as $comentario): ?>
                                            <div class="comment" style="margin-bottom: 10px;">
                                                <div class="comment-user-row">
                                                    <div class="comment-user-name">
                                                        <strong><?php echo htmlspecialchars($comentario['nome']); ?></strong> comentou:
                                                    </div>

                                                    <?php if (isset($comentario['status_comentario']) && $comentario['status_comentario'] === 'verificado'): ?>
                                                        <span class="verified-comment-label">✔️ Comentário Verificado</span>
                                                    <?php endif; ?>
                                                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>   
    <script src="../../scripts/pages/rescued-animals/rescued-animals.js"></script>
    <script src="../../scripts/view-comments.js"></script>
    <script src="../../scripts/order-posts.js"></script>
    <script src="../../scripts/user-suggestions.js"></script>
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