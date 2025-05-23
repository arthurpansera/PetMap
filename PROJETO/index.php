<?php
    include('conecta_db.php');
    
    session_start();

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

        $query = "SELECT p.id_publicacao, p.titulo, p.conteudo, p.tipo_publicacao, p.data_criacao, p.data_atualizacao, p.endereco_rua, p.endereco_bairro, p.endereco_cidade, p.endereco_estado, u.nome 
                FROM publicacao p 
                JOIN usuario u ON p.id_usuario = u.id_usuario 
                WHERE p.titulo LIKE ? 
                    OR p.conteudo LIKE ? 
                    OR u.nome LIKE ? 
                    OR DATE_FORMAT(p.data_criacao, '%d/%m/%Y') LIKE ? 
                    OR DATE_FORMAT(p.data_criacao, '%d/%m/%Y %H:%i') LIKE ?
                    OR DATE_FORMAT(p.data_criacao, '%d de %M de %Y') LIKE ?
                    OR DATE_FORMAT(p.data_criacao, '%Hh%i') LIKE ?
                    OR DATE_FORMAT(p.data_criacao, '%H:%i') LIKE ?
                ORDER BY $orderBy";

        $stmt = $obj->prepare($query);
        $stmt->bind_param("ssssssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $query = "SELECT p.id_publicacao, p.titulo, p.conteudo, p.tipo_publicacao, p.data_criacao,p.data_atualizacao,p.endereco_rua, p.endereco_bairro, p.endereco_cidade, p.endereco_estado, u.nome 
                FROM publicacao p 
                JOIN usuario u ON p.id_usuario = u.id_usuario 
                ORDER BY $orderBy";
        $result = $obj->query($query);
    }

    if (isset($_POST['make_post'])) {
        $titulo = $_POST['titulo'];
        $conteudo = $_POST['conteudo'];
        $tipoPublicacao = $_POST['tipo_publicacao'];
        $rua = $_POST['endereco_rua'];
        $bairro = $_POST['endereco_bairro'];
        $cidade = $_POST['endereco_cidade'];
        $estado = $_POST['state'];

        date_default_timezone_set('America/Sao_Paulo');
        $dataCriacao = date('Y-m-d H:i:s');


        $insertQuery = "INSERT INTO publicacao ( titulo, conteudo, tipo_publicacao, id_usuario, data_criacao, endereco_rua, endereco_bairro, endereco_cidade, endereco_estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $obj->prepare($insertQuery);
        $stmt->bind_param("sssisssss", $titulo, $conteudo, $tipoPublicacao, $userId, $dataCriacao, $rua, $bairro, $cidade, $estado);
        $stmt->execute();

        $id_publicacao = $stmt->insert_id;

        $fileCount = is_array($_FILES['foto_publicacao']['name']) 
            ? count($_FILES['foto_publicacao']['name']) 
            : 0;

        $maxImages = 8;
        if ($fileCount > $maxImages) {
            $fileCount = $maxImages;
        }
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['foto_publicacao']['error'][$i] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['foto_publicacao']['tmp_name'][$i];
                $fileName = $_FILES['foto_publicacao']['name'][$i];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (getimagesize($fileTmpPath) === false) {
                    continue;
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

                        if ($stmt_foto) {
                            $stmt_foto->bind_param("is", $id_publicacao, $newFileName);
                            $stmt_foto->execute();
                        }
                    }
                }
            }
        }
    
        $_SESSION['success_message'] = "Publicação realizada com sucesso!";

        header('Location: index.php');
        exit;
    }

    if (isset($_POST['impulsionar']) && isset($_POST['id_publicacao'])) {
        if (!$isLoggedIn) {
            header("Location: src/assets/pages/login.php");
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

        $redirectUrl = 'index.php';
        if (!empty($_GET['pesquisa'])) {
            $redirectUrl .= '?pesquisa=' . urlencode($_GET['pesquisa']);
        }
        header("Location: $redirectUrl");
        exit;
    }

    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: src/assets/pages/login.php");
        exit();
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
                <form class="search-bar" method="GET" action="index.php">
                    <input type="text" name="pesquisa" placeholder="Pesquisar..." value="<?php echo isset($_GET['pesquisa']) ? htmlspecialchars($_GET['pesquisa']) : ''; ?>">
                    <button type="submit">🔍</button>
                </form>
                <ul class="ul">
                    <?php if ($isLoggedIn): ?>
                        <?php
                            $nome = explode(' ', trim($userName));
                            $prmeiroNome = implode(' ', array_slice($nome, 0, 1));
                        ?>
                        <li class="user-info">
                            <p class="welcome-message">Bem-vindo, <?php echo htmlspecialchars($prmeiroNome); ?>!</p>
                            <a class="profile-image" href="src/assets/pages/profile.php">
                                <img src="src/assets/images/perfil-images/profile-icon.png" alt="Ícone de Perfil">
                            </a>
                            <div class="logout-button">
                                <form action="index.php" method="POST">
                                    <button type="submit" name="logout">
                                        <img src="src/assets/images/perfil-images/icone-sair-branco.png" alt="Sair da Conta">
                                    </button>
                                </form>
                            </div>
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
                <li><a href="src/assets/pages/rescued-animals.php">Animais Resgatados</a></li>
                <li><a href="src/assets/pages/lost-animals.php">Animais Perdidos</a></li>
                <li><a href="src/assets/pages/areas.php">Áreas de Maior Abandono</a></li>
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
            <div class="order-dropdown">
                <button class="order-button" id="orderToggle">⮃ Ordenar</button>
                <div class="order-menu" id="orderMenu">
                    <a href="?ordenar_por=data_desc<?php echo $pesquisa ? '&pesquisa=' . urlencode($pesquisa) : ''; ?>">📅 Mais recentes</a>
                    <a href="?ordenar_por=data_asc<?php echo $pesquisa ? '&pesquisa=' . urlencode($pesquisa) : ''; ?>">🕰️ Mais antigos</a>
                    <a href="?ordenar_por=impulsos_desc<?php echo $pesquisa ? '&pesquisa=' . urlencode($pesquisa) : ''; ?>">🔝 Mais impulsionados</a>
                </div>
            </div>
            <div class="menu-post">
                <?php if ($result->num_rows > 0): ?>
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
                                        <img src="src/assets/images/uploads/posts/<?php echo htmlspecialchars($imagem); ?>" alt="Imagem da publicação">
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
                                <form method="POST" action="index.php<?php echo !empty($pesquisa) ? '?pesquisa=' . urlencode($pesquisa) : ''; ?>" style="display: contents;">
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
                        <img src="src/assets/images/no-posts-image/sem-posts.png" alt="Ícone de Erro" class="no-posts-image">
                    </div>
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
            
            <form id="postForm" action="index.php" method="POST" enctype="multipart/form-data">
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
                    <input type="file" name="foto_publicacao[]" id="foto_publicacao" multiple accept="image/*">
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

                <br><br>
                <h3>Endereço</h3>

                <div class="row-style">
                    <div class="row-style-content">
                        <div class="form-group">
                            <label for="endereco_rua">Rua:</label>
                            <input type="text" id="endereco_rua" name="endereco_rua" class="required campo-endereco" data-type="rua" data-required="true" placeholder="Insira o nome da rua">
                            <span class="span-required"> Rua não pode conter caracteres especias.</span>
                        </div>
                        <div class="form-group">
                            <label for="endereco_bairro">Bairro:</label>
                            <input type="text" id="endereco_bairro" name="endereco_bairro" class="required campo-endereco" data-type="bairro" data-required="true" placeholder="Insira o bairro">
                            <span class="span-required">Bairro não pode conter números ou caracteres especiais.</span>
                        </div>
                    </div>

                    <div class="row-style-content">
                        <div class="form-group">
                            <label for="endereco_cidade">Cidade:</label>
                            <input type="text" id="endereco_cidade" name="endereco_cidade" class="required campo-endereco" data-type="cidade" data-required="true" placeholder="Insira a cidade">
                            <span class="span-required">Cidade não pode conter números ou caracteres especiais.</span>
                        </div>

                        <div class="form-group">
                            <label for="state"><b>Estado: *</b></label>
                            <select name="state" id="state" class="mid-inputUser required campo-endereco" data-type="estado" data-required="true">
                                <option value="">Selecione um estado</option>
                                <option value="AC" <?php echo (isset($_POST['state']) && $_POST['state'] === 'AC') ? 'selected' : ''; ?>>Acre</option>
                                <option value="AL" <?php echo (isset($_POST['state']) && $_POST['state'] === 'AL') ? 'selected' : ''; ?>>Alagoas</option>
                                <option value="AP" <?php echo (isset($_POST['state']) && $_POST['state'] === 'AP') ? 'selected' : ''; ?>>Amapá</option>
                                <option value="AM" <?php echo (isset($_POST['state']) && $_POST['state'] === 'AM') ? 'selected' : ''; ?>>Amazonas</option>
                                <option value="BA" <?php echo (isset($_POST['state']) && $_POST['state'] === 'BA') ? 'selected' : ''; ?>>Bahia</option>
                                <option value="CE" <?php echo (isset($_POST['state']) && $_POST['state'] === 'CE') ? 'selected' : ''; ?>>Ceará</option>
                                <option value="DF" <?php echo (isset($_POST['state']) && $_POST['state'] === 'DF') ? 'selected' : ''; ?>>Distrito Federal</option>
                                <option value="ES" <?php echo (isset($_POST['state']) && $_POST['state'] === 'ES') ? 'selected' : ''; ?>>Espírito Santo</option>
                                <option value="GO" <?php echo (isset($_POST['state']) && $_POST['state'] === 'GO') ? 'selected' : ''; ?>>Goiás</option>
                                <option value="MA" <?php echo (isset($_POST['state']) && $_POST['state'] === 'MA') ? 'selected' : ''; ?>>Maranhão</option>
                                <option value="MT" <?php echo (isset($_POST['state']) && $_POST['state'] === 'MT') ? 'selected' : ''; ?>>Mato Grosso</option>
                                <option value="MS" <?php echo (isset($_POST['state']) && $_POST['state'] === 'MS') ? 'selected' : ''; ?>>Mato Grosso do Sul</option>
                                <option value="MG" <?php echo (isset($_POST['state']) && $_POST['state'] === 'MG') ? 'selected' : ''; ?>>Minas Gerais</option>
                                <option value="PA" <?php echo (isset($_POST['state']) && $_POST['state'] === 'PA') ? 'selected' : ''; ?>>Pará</option>
                                <option value="PB" <?php echo (isset($_POST['state']) && $_POST['state'] === 'PB') ? 'selected' : ''; ?>>Paraíba</option>
                                <option value="PR" <?php echo (isset($_POST['state']) && $_POST['state'] === 'PR') ? 'selected' : ''; ?>>Paraná</option>
                                <option value="PE" <?php echo (isset($_POST['state']) && $_POST['state'] === 'PE') ? 'selected' : ''; ?>>Pernambuco</option>
                                <option value="PI" <?php echo (isset($_POST['state']) && $_POST['state'] === 'PI') ? 'selected' : ''; ?>>Piauí</option>
                                <option value="RJ" <?php echo (isset($_POST['state']) && $_POST['state'] === 'RJ') ? 'selected' : ''; ?>>Rio de Janeiro</option>
                                <option value="RN" <?php echo (isset($_POST['state']) && $_POST['state'] === 'RN') ? 'selected' : ''; ?>>Rio Grande do Norte</option>
                                <option value="RS" <?php echo (isset($_POST['state']) && $_POST['state'] === 'RS') ? 'selected' : ''; ?>>Rio Grande do Sul</option>
                                <option value="RO" <?php echo (isset($_POST['state']) && $_POST['state'] === 'RO') ? 'selected' : ''; ?>>Rondônia</option>
                                <option value="RR" <?php echo (isset($_POST['state']) && $_POST['state'] === 'RR') ? 'selected' : ''; ?>>Roraima</option>
                                <option value="SC" <?php echo (isset($_POST['state']) && $_POST['state'] === 'SC') ? 'selected' : ''; ?>>Santa Catarina</option>
                                <option value="SP" <?php echo (isset($_POST['state']) && $_POST['state'] === 'SP') ? 'selected' : ''; ?>>São Paulo</option>
                                <option value="SE" <?php echo (isset($_POST['state']) && $_POST['state'] === 'SE') ? 'selected' : ''; ?>>Sergipe</option>
                                <option value="TO" <?php echo (isset($_POST['state']) && $_POST['state'] === 'TO') ? 'selected' : ''; ?>>Tocantins</option>
                            </select>
                            <span class="span-required">Selecione um estado válido.</span>
                        </div>
                    </div>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" id="nao_sei_endereco" name="nao_sei_endereco" onclick="desabilitarCamposEndereco()">
                    <label for="nao_sei_endereco">Não sei informar o endereço</label>
                </div>

                <button type="submit" name="make_post" class="create-post" onclick="btnRegisterOnClick(event, this.form)">Publicar</button>
            </form>
        </div>
    </div>

    <script src="src/scripts/pages/index/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="src/scripts/order-posts.js"></script>
    <script src="src/scripts/register-validation.js"></script>
    
</body>
</html>