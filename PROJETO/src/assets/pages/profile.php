<?php
    session_start();

    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
    $isModerator = false;

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
    
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        header("Location: ../../assets/pages/login.php");
        exit();
    }

    if (!isset($_SESSION['id_usuario'])) {
        echo "Erro: ID do usuário não encontrado.";
        exit();
    }

    include('../../../conecta_db.php');

    $id_usuario = $_SESSION['id_usuario'];

    $obj = conecta_db();

    if (!$obj) {
        header("Location: database-error.php");
        exit;
    }

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

    $query = "SELECT u.id_usuario, u.nome, c.email, c.telefone, p.foto, p.descricao AS tipo_conta, p.status_perfil,
                o.endereco_rua AS ong_endereco_rua, o.endereco_numero AS ong_endereco_numero, o.endereco_complemento AS ong_endereco_complemento, o.endereco_bairro AS ong_endereco_bairro, 
                o.endereco_cidade AS ong_endereco_cidade, o.endereco_estado AS ong_endereco_estado, o.endereco_pais AS ong_endereco_pais, o.endereco_cep AS ong_endereco_cep,
                ci.endereco_rua AS cidadao_endereco_rua, ci.endereco_numero AS cidadao_endereco_numero, ci.endereco_complemento AS cidadao_endereco_complemento, ci.endereco_bairro AS cidadao_endereco_bairro, 
                ci.endereco_cidade AS cidadao_endereco_cidade, ci.endereco_estado AS cidadao_endereco_estado, ci.endereco_pais AS cidadao_endereco_pais, ci.endereco_cep AS cidadao_endereco_cep
            FROM usuario u
            JOIN perfil p ON u.id_usuario = p.id_usuario
            JOIN contato c ON u.id_usuario = c.id_usuario
            LEFT JOIN ong o ON u.id_usuario = o.id_usuario
            LEFT JOIN cidadao ci ON u.id_usuario = ci.id_usuario
            WHERE u.id_usuario = ?";
    $stmt = $obj->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    $query_posts = "SELECT id_publicacao, titulo, conteudo, tipo_publicacao, data_criacao, data_atualizacao, endereco_rua, endereco_bairro, endereco_cidade, endereco_estado, status_publicacao 
                    FROM publicacao WHERE id_usuario = ? ORDER BY data_criacao DESC";
    $stmt_posts = $obj->prepare($query_posts);
    $stmt_posts->bind_param("i", $id_usuario);
    $stmt_posts->execute();
    $result_posts = $stmt_posts->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "Erro ao buscar os dados do usuário.";
        exit();
    }

    if (isset($_POST['update_profile'])) {
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto_perfil']['tmp_name'];
            $fileName = $_FILES['foto_perfil']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = uniqid('profile_', true) . '.' . $fileExtension;
                $uploadFileDir = __DIR__ . '/../../assets/images/uploads/profile/';
                $destPath = $uploadFileDir . $newFileName;
                
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $query_foto = "UPDATE perfil SET foto = ? WHERE id_usuario = ?";
                    $stmt_foto = $obj->prepare($query_foto);
                    $stmt_foto->bind_param("si", $newFileName, $user['id_usuario']);
                    $stmt_foto->execute();
                } else {
                    $_SESSION['error_message'] = "Erro ao mover a imagem.";
                }
            } else {
                $_SESSION['error_message'] = "Tipo de arquivo inválido. Apenas imagens são permitidas.";
            }
        }
    }

    if (isset($_POST['remove_foto'])) {
        if (!empty($user['foto'])) {
            $uploadFileDir = __DIR__ . '/../../assets/images/uploads/profile/';
            $fotoPath = $uploadFileDir . $user['foto'];
    
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }

            $query_remover = "UPDATE perfil SET foto = NULL WHERE id_usuario = ?";
            $stmt_remover = $obj->prepare($query_remover);
            $stmt_remover->bind_param("i", $user['id_usuario']);
            $stmt_remover->execute();

            $query_user = "SELECT * FROM perfil WHERE id_usuario = ?";
            $stmt_user = $obj->prepare($query_user);
            $stmt_user->bind_param("i", $user['id_usuario']);
            $stmt_user->execute();
            $result = $stmt_user->get_result();
            $_SESSION['user'] = $result->fetch_assoc();
            $_SESSION['success_message'] = "Foto de perfil removida com sucesso.";
        }

        header("Location: profile.php");
        exit();
    }

    function excluir_fotos_orfas($conn) {
        $query = "SELECT foto FROM perfil WHERE foto IS NOT NULL";
        $result = $conn->query($query);
    
        $fotos_vinculadas = [];
        while ($row = $result->fetch_assoc()) {
            $fotos_vinculadas[] = $row['foto'];
        }
    
        $uploadFileDir = realpath(__DIR__ . '/../../assets/images/uploads/profile/');
    
        if ($uploadFileDir && is_dir($uploadFileDir)) {
            $files = scandir($uploadFileDir);
    
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
    
                if (!in_array($file, $fotos_vinculadas)) {
                    $filePath = $uploadFileDir . DIRECTORY_SEPARATOR . $file;
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
        }
    }

    excluir_fotos_orfas($obj);
    
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: ../../assets/pages/login.php");
        exit();
    }

    if (isset($_POST['delete_account'])) {
        $query_perfil = "DELETE FROM perfil WHERE id_usuario = ?";
        $stmt_perfil = $obj->prepare($query_perfil);
        $stmt_perfil->bind_param("i", $user['id_usuario']);
        $stmt_perfil->execute();

        $query_moderador = "DELETE FROM moderador WHERE id_usuario = ?";
        $stmt_moderador = $obj->prepare($query_moderador);
        $stmt_moderador->bind_param("i", $user['id_usuario']);
        $stmt_moderador->execute();

        $query_contato = "DELETE FROM contato WHERE id_usuario = ?";
        $stmt_contato = $obj->prepare($query_contato);
        $stmt_contato->bind_param("i", $user['id_usuario']);
        $stmt_contato->execute();

        $query_usuario = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmt_usuario = $obj->prepare($query_usuario);
        $stmt_usuario->bind_param("i", $user['id_usuario']);
        $stmt_usuario->execute();

        session_destroy();
        header("Location: ../../assets/pages/login.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];
        $senha = $_POST['senha'];
        $confirmar_senha = $_POST['confirmar_senha'];
        $endereco_rua = $_POST['endereco_rua'];
        $endereco_numero = $_POST['endereco_numero'];
        $endereco_complemento = $_POST['endereco_complemento'];
        $endereco_bairro = $_POST['endereco_bairro'];
        $endereco_cidade = $_POST['endereco_cidade'];
        $endereco_estado = $_POST['endereco_estado'];
        $endereco_pais = $_POST['endereco_pais'];
        $endereco_cep = $_POST['endereco_cep'];

        if ($senha !== $confirmar_senha) {
            $_SESSION['error_message'] = "As senhas não coincidem!";
            header("Location: profile.php");
            exit();
        }

        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $query_usuario = "UPDATE usuario SET nome = ?, senha = ? WHERE id_usuario = ?";
            $query_contato = "UPDATE contato SET telefone = ?, email = ? WHERE id_usuario = ?";
            $stmt_usuario = $obj->prepare($query_usuario);
            $stmt_usuario->bind_param("ssi", $nome, $senha_hash, $user['id_usuario']);
            $stmt_contato = $obj->prepare($query_contato);
            $stmt_contato->bind_param("ssi", $telefone, $email, $user['id_usuario']);
        } else {
            $query_usuario = "UPDATE usuario SET nome = ? WHERE id_usuario = ?";
            $query_contato = "UPDATE contato SET telefone = ?, email = ? WHERE id_usuario = ?";
            $stmt_usuario = $obj->prepare($query_usuario);
            $stmt_usuario->bind_param("si", $nome, $user['id_usuario']);
            $stmt_contato = $obj->prepare($query_contato);
            $stmt_contato->bind_param("ssi", $telefone, $email, $user['id_usuario']);
        }

        if ($user['tipo_conta'] == 'Perfil de ONG') {
            $query_endereco = "UPDATE ong SET endereco_rua = ?, endereco_numero = ?, endereco_complemento = ?, endereco_bairro = ?, endereco_cidade = ?, endereco_estado = ?, endereco_pais = ?, endereco_cep = ? WHERE id_usuario = ?";
            $stmt_endereco = $obj->prepare($query_endereco);
            $stmt_endereco->bind_param("ssssssssi", $endereco_rua, $endereco_numero, $endereco_complemento, $endereco_bairro, $endereco_cidade, $endereco_estado, $endereco_pais, $endereco_cep, $user['id_usuario']);
        } elseif ($user['tipo_conta'] == 'Perfil de cidadão') {
            $query_endereco = "UPDATE cidadao SET endereco_rua = ?, endereco_numero = ?, endereco_complemento = ?, endereco_bairro = ?, endereco_cidade = ?, endereco_estado = ?, endereco_pais = ?, endereco_cep = ? WHERE id_usuario = ?";
            $stmt_endereco = $obj->prepare($query_endereco);
            $stmt_endereco->bind_param("ssssssssi", $endereco_rua, $endereco_numero, $endereco_complemento, $endereco_bairro, $endereco_cidade, $endereco_estado, $endereco_pais, $endereco_cep, $user['id_usuario']);
        }

        $stmt_usuario->execute();
        $stmt_contato->execute();

        if ($user['tipo_conta'] == 'Perfil de ONG' || $user['tipo_conta'] == 'Perfil de cidadão') {
            $stmt_endereco->execute();
        }

        $_SESSION['success_message'] = "Informações salvas.";

        header("Location: profile.php");
        exit();
    }

    if (isset($_POST['make_post'])) {
        $titulo = $_POST['titulo'];
        $conteudo = $_POST['conteudo'];
        $tipoPublicacao = $_POST['tipo_publicacao'];
        $rua = $_POST['endereco_rua'];
        $bairro = $_POST['endereco_bairro'];
        $cidade = $_POST['endereco_cidade'];
        $estado = $_POST['state'];
        $id_usuario = $_SESSION['id_usuario'];

        date_default_timezone_set('America/Sao_Paulo');
        $dataCriacao = date('Y-m-d H:i:s');

        $insertQuery = "INSERT INTO publicacao (titulo, conteudo, tipo_publicacao, id_usuario, data_criacao, endereco_rua, endereco_bairro, endereco_cidade, endereco_estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $obj->prepare($insertQuery);
        $stmt->bind_param("sssisssss", $titulo, $conteudo, $tipoPublicacao, $id_usuario, $dataCriacao, $rua, $bairro, $cidade, $estado);
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

                    $rootPath = realpath(__DIR__ . '/../../..');
                    $uploadFileDir = $rootPath . '/src/assets/images/uploads/posts/';
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

        header('Location: profile.php');
        exit;
    }

    if (isset($_POST['update_post'])) {
        $postId = intval($_POST['post_id']);
        $titulo = $_POST['titulo'];
        $conteudo = $_POST['conteudo'];
        $tipoPublicacao = $_POST['tipo_publicacao'];
        $rua = $_POST['endereco_rua'];
        $bairro = $_POST['endereco_bairro'];
        $cidade = $_POST['endereco_cidade'];
        $estado = $_POST['endereco_estado'];

        date_default_timezone_set('America/Sao_Paulo');
        $dataAtualizacao = date('Y-m-d H:i:s'); 

        $updateQuery = "UPDATE publicacao SET titulo = ?, conteudo = ?, tipo_publicacao = ?, data_atualizacao = ?, endereco_rua = ?, endereco_bairro = ?, endereco_cidade = ?, endereco_estado = ? WHERE id_publicacao = ?";
        $stmt = $obj->prepare($updateQuery);
        $stmt->bind_param("ssssssssi", $titulo, $conteudo, $tipoPublicacao, $dataAtualizacao, $rua, $bairro, $cidade, $estado, $postId);
        $stmt->execute();

        if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
            $uploadDir = realpath(__DIR__ . '/../../..') . '/src/assets/images/uploads/posts/';
            
            foreach ($_POST['delete_images'] as $imageName) {
                $safeImageName = basename($imageName);
                $filePath = $uploadDir . '/' . $safeImageName;

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                $deleteImage = $obj->prepare("DELETE FROM imagem WHERE id_publicacao = ? AND imagem_url = ?");
                $deleteImage->bind_param("is", $postId, $safeImageName);
                $deleteImage->execute();
            }
        }

        $hasNewImages = isset($_FILES['foto_publicacao']) 
            && isset($_FILES['foto_publicacao']['error'][0])
            && $_FILES['foto_publicacao']['error'][0] !== 4;

        if ($hasNewImages) {
            $uploadDir = realpath(__DIR__ . '/../../..') . '/src/assets/images/uploads/posts/';

            $selectImgs = $obj->prepare("SELECT imagem_url FROM imagem WHERE id_publicacao = ?");
            $selectImgs->bind_param("i", $postId);
            $selectImgs->execute();
            $resultImgs = $selectImgs->get_result();

            while ($row = $resultImgs->fetch_assoc()) {
                $filePath = $uploadDir . '/' . $row['imagem_url'];
                if (file_exists($filePath)) {
                    unlink($filePath); 
                }
            }

            $deleteImgs = $obj->prepare("DELETE FROM imagem WHERE id_publicacao = ?");
            $deleteImgs->bind_param("i", $postId);
            $deleteImgs->execute();

            $countQuery = $obj->prepare("SELECT COUNT(*) AS total FROM imagem WHERE id_publicacao = ?");
            $countQuery->bind_param("i", $postId);
            $countQuery->execute();
            $countResult = $countQuery->get_result();
            $countRow = $countResult->fetch_assoc();
            $currentImageCount = $countRow['total'] ?? 0;

            $deleteCount = isset($_POST['delete_images']) ? count($_POST['delete_images']) : 0;

            $maxAllowed = 8;
            $availableSlots = $maxAllowed - ($currentImageCount - $deleteCount);

            $newImages = $_FILES['foto_publicacao'];
            $newImageCount = isset($newImages['name']) ? count($newImages['name']) : 0;

            $fileCount = min($newImageCount, $availableSlots);

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

                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $destPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            $query_foto = "INSERT INTO imagem (id_publicacao, imagem_url) VALUES (?, ?)";
                            $stmt_foto = $obj->prepare($query_foto);

                            if ($stmt_foto) {
                                $stmt_foto->bind_param("is", $postId, $newFileName);
                                $stmt_foto->execute();
                            }
                        }
                    }
                }
            }
        }

        $_SESSION['success_message'] = "Publicação editada com sucesso";

        header('Location: profile.php');
        exit;
    }

    if (isset($_POST['post_id'])) {
        $postId = intval($_POST['post_id']);

        $selectImgs = $obj->prepare("SELECT imagem_url FROM imagem WHERE id_publicacao = ?");
        $selectImgs->bind_param("i", $postId);
        $selectImgs->execute();
        $resultImgs = $selectImgs->get_result();

        $uploadDir = realpath(__DIR__ . '/../../..') . '/src/assets/images/uploads/posts/';
        while ($row = $resultImgs->fetch_assoc()) {
            $filePath = $uploadDir . '/' . $row['imagem_url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $deleteImgs = $obj->prepare("DELETE FROM imagem WHERE id_publicacao = ?");
        $deleteImgs->bind_param("i", $postId);
        $deleteImgs->execute();

        $deletePost = $obj->prepare("DELETE FROM publicacao WHERE id_publicacao = ?");
        $deletePost->bind_param("i", $postId);
        $deletePost->execute();

        header('Location: profile.php');
        exit;
    }

    if (isset($_POST['update_comment'])) {
        $idComentario = intval($_POST['id_comentario']);
        $conteudo = trim($_POST['conteudo_comentario']);
        $id_usuario = $_SESSION['id_usuario'];

        if ($conteudo === '') {
            $_SESSION['error_message'] = "O comentário não pode ficar vazio.";
            header('Location: index.php');
            exit;
        }

        $queryCheck = $obj->prepare("SELECT id_usuario FROM comentario WHERE id_comentario = ?");
        $queryCheck->bind_param('i', $idComentario);
        $queryCheck->execute();
        $result = $queryCheck->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['error_message'] = "Comentário não encontrado.";
            header('Location: index.php');
            exit;
        }

        $row = $result->fetch_assoc();
        if ($row['id_usuario'] != $id_usuario) {
            $_SESSION['error_message'] = "Você não tem permissão para editar esse comentário.";
            header('Location: index.php');
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

        header("Location: profile.php");
        exit;
    }

    if (isset($_POST['delete_comment'])) {
        $idComentario = intval($_POST['id_comentario_excluir']);
        $id_usuario = $_SESSION['id_usuario'];

        $queryCheck = $obj->prepare("SELECT id_usuario FROM comentario WHERE id_comentario = ?");
        $queryCheck->bind_param('i', $idComentario);
        $queryCheck->execute();
        $result = $queryCheck->get_result();

        if ($result->num_rows === 0) {
            $_SESSION['error_message'] = "Comentário não encontrado.";
            header('Location: index.php');
            exit;
        }

        $row = $result->fetch_assoc();

        if ($row['id_usuario'] != $id_usuario) {
            $_SESSION['error_message'] = "Você não tem permissão para excluir esse comentário.";
            header('Location: index.php');
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

        header('Location: profile.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetMap | Perfil</title>
    <link href="../../styles/pages/profile/profile.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <a href="../../../index.php">
                    <img src="../images/logo-petmap/white-logo.png" alt="Logo PetMap">
                </a>
                <a href="profile.php" class="profile-image">
                    <img src="../images/perfil-images/profile-icon.png" alt="Ícone de Perfil">
                </a>
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
                    </ul>
                </div>
            <?php endif; ?>
            <div class="footer">
                <p>&copy;2025 - PetMap.</p>
                <p>Todos os direitos reservados.</p>
            </div>
        </nav>

        <div class="menu-overlay" id="menuOverlay"></div>
    </section>

    <section class="profile">
        <div class="logout-button">
            <form action="profile.php" method="POST" class="tooltip-wrapper">
                <button type="submit" name="logout">
                    <img src="../images/perfil-images/sair.png" alt="Sair da Conta">
                </button>
                <span class="tooltip-text">Sair da conta</span>
            </form>
        </div>

        <div class="profile-content">
            <div class="profile-header">
                <div class="profile-left">
                    <?php if (!empty($user['foto'])): ?>
                        <img src="/PetMap/PROJETO/src/assets/images/uploads/profile/<?php echo htmlspecialchars($user['foto']) . '?v=' . time(); ?>" alt="Foto de perfil">
                        <form method="post">
                            <button type="submit" name="remove_foto" class="btn-remove-photo">
                                Remover Foto de Perfil
                            </button>
                        </form>
                    <?php else: ?>
                        <img src="../images/perfil-images/default-profile-photo.png" alt="Foto padrão" class="profile-picture">
                    <?php endif; ?>
                </div>

                <div class="profile-info">
                    <h2>
                        <?php echo htmlspecialchars($user['nome']); ?>

                        <?php
                            $status = $user['status_perfil'];
                            $tipoConta = $user['tipo_conta'];

                            if ($tipoConta === 'Perfil de moderador') {
                                echo '<img src="../images/perfil-images/moderador.png" alt="Moderador" class="status-icon" title="Moderador">';
                            } elseif ($status === 'verificado') {
                                echo '<img src="../images/perfil-images/verificado.png" alt="Verificado" class="status-icon" title="Conta verificada">';
                            } elseif ($status === 'nao_verificado') {
                                echo '<img src="../images/perfil-images/nao-verificado.png" alt="Não verificado" class="status-icon" title="Conta não verificada">';
                            } elseif ($status === 'banido') {
                                echo '<img src="../images/perfil-images/banido.png" alt="Banido" class="status-icon" title="Conta banida">';
                            }
                        ?>
                    </h2>

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

                    <div class="profile-buttons">
                        <div class="functions-buttons">
                            <a href="#" class="profile-edit" onclick="openModal()">Editar Informações</a>
                            <form action="profile.php" method="POST" id="deleteForm">
                                <button type="submit" id="deleteBtn" class="profile-delete">Excluir Conta</button>
                                <input type="hidden" name="delete_account" value="1">
                            </form>
                        </div>
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
            <h2>Minhas Publicações</h2>
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
                    ?>

                    <div class="post-item">
                        <p class="post-info">
                            <span>
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

                            $getComentarios = $obj->prepare("SELECT c.id_comentario, c.conteudo, c.data_criacao, c.id_usuario, c.status_comentario, u.nome
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
                        ?>

                        <div class="post-actions">
                            <div class="posts-buttons">
                                <form method="POST" action="profile.php">

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

                                    <?php
                                        $naoSeiEndereco = (
                                            empty($post['endereco_rua']) &&
                                            empty($post['endereco_bairro']) &&
                                            empty($post['endereco_cidade']) &&
                                            empty($post['endereco_estado'])
                                        ) ? '1' : '0';
                                    ?>

                                    <button 
                                        type="button" 
                                        class="edit-button" 
                                        onclick="openEditPostModal(this);"
                                        data-id="<?= $post['id_publicacao']; ?>"
                                        data-titulo="<?= htmlspecialchars($post['titulo']); ?>"
                                        data-conteudo="<?= htmlspecialchars($post['conteudo']); ?>"
                                        data-tipo="<?= $post['tipo_publicacao']; ?>"
                                        data-endereco_rua="<?= htmlspecialchars($post['endereco_rua']); ?>"
                                        data-endereco_bairro="<?= htmlspecialchars($post['endereco_bairro']); ?>"
                                        data-endereco_cidade="<?= htmlspecialchars($post['endereco_cidade']); ?>"
                                        data-endereco_estado="<?= htmlspecialchars($post['endereco_estado']); ?>"
                                        data-nao_sei_endereco="<?= $naoSeiEndereco ?>"
                                        data-images='<?= htmlspecialchars(json_encode($images), ENT_QUOTES, 'UTF-8'); ?>'
                                    >
                                        ✏️ Editar
                                    </button>

                                </form>

                                <form method="POST" action="profile.php">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id_publicacao']; ?>">
                                    <button 
                                        type="button" 
                                        class="delete-button" 
                                        onclick="confirmDeletePost(this)"
                                    >🗑️ Excluir</button>
                                </form>

                                <?php if ($totalComentarios > 0): ?>
                                    <button class="toggle-comments-button comment-button" onclick="toggleComments(<?php echo $idPost; ?>)">
                                        💬 Ver comentários (<?php echo $totalComentarios; ?>)
                                    </button>
                                <?php endif; ?>

                            </div>

                            <?php if ($id_usuario): ?>
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
                                <div class="comments-profile" id="comments-wrapper-<?php echo $idPost; ?>" style="display: none;">
                                    <div class="comments-list-profile" id="comments-<?php echo $idPost; ?>">
                                        <?php foreach ($comentariosArray as $comentario): ?>
                                            <div class="comment-profile" style="margin-bottom: 10px;">
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

                                                <?php if ($id_usuario && $comentario['id_usuario'] == $_SESSION['id_usuario']): ?>

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
                </div>
                <?php endwhile; ?>
            <?php elseif ($user['tipo_conta'] === 'Perfil de moderador'): ?>
                <p style="font-size: 1.2rem; ">O seu perfil é de moderador. Portanto, você não pode realizar publicações.</p>
                <p style="font-size: 1.2rem; ">No entanto, você pode comentar nas publicações de outros usuários.</p><br><br>
            <?php else: ?>
                <p style="font-size: 1.2rem; ">Ainda não há publicações suas por aqui. Que tal compartilhar algo?</p><br><br>
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

    <?php if ($user['tipo_conta'] == 'Perfil de ONG' || $user['tipo_conta'] == 'Perfil de cidadão'): ?>
        <button class="floating-button" title="Nova Publicação" onclick="openPostModal()">
            +
        </button>
    <?php endif; ?>

    <section id="comentarios-section" style="display: none;" class="content">

        <?php
            $id_usuario = $_SESSION['id_usuario'];

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
            <h2>Meus Comentários</h2>
            <?php if (count($comentariosDoUsuario) > 0): ?>
                
                <div class="comments-list">
                    <?php foreach ($comentariosDoUsuario as $comentario): ?>
                        <div class="comment">
                            <div class="comment-meta-row">
                                <div class="comment-meta-left">
                                    Você comentou em: <strong ><?php echo htmlspecialchars($comentario['titulo_publicacao']); ?></strong>
                                    às <strong><?php echo strftime('%Hh%M, %d de %B de %Y', strtotime($comentario['data_criacao'])); ?></strong>
                                </div>

                                <?php
                                    $stmtStatus = $obj->prepare("SELECT status_comentario FROM comentario WHERE id_comentario = ?");
                                    $stmtStatus->bind_param("i", $comentario['id_comentario']);
                                    $stmtStatus->execute();
                                    $resultStatus = $stmtStatus->get_result();
                                    $statusComentario = $resultStatus->fetch_assoc()['status_comentario'];
                                ?>

                                <?php if ($statusComentario === 'verificado'): ?>
                                    <div class="comment-meta-right">
                                        ✔️ Comentário Verificado
                                    </div>
                                <?php endif; ?>
                            </div>

                            <p class="comment-content"><?php echo nl2br(htmlspecialchars($comentario['conteudo'])); ?></p>

                            <?php if ($id_usuario && $comentario['id_usuario'] == $_SESSION['id_usuario']): ?>
                                <div class="comment-actions">
                                    <button class="edit-comment-btn"
                                        onclick="editarComentarioPerfil(
                                            <?php echo $comentario['id_publicacao']; ?>,
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

                            <div id="edit-form-<?= $comentario['id_comentario']; ?>" class="comment-form" style="display: none;">
                                <form method="POST" id="comment-form-perfil">
                                    <input type="hidden" name="id_comentario" id="id_comentario_perfil_<?= $comentario['id_comentario']; ?>" value="">
                                    <textarea name="conteudo_comentario" id="textarea_comentario_perfil_<?= $comentario['id_comentario']; ?>" rows="3" placeholder="Edite seu comentário..." required></textarea>

                                    <button type="submit" id="submit-button-perfil_<?= $comentario['id_comentario']; ?>" name="update_comment">Enviar</button>
                                    <button type="button" onclick="closeCommentFormPerfil(<?= $comentario['id_comentario']; ?>)">Cancelar</button>
                                </form>
                            </div>

                        </div>
                    <?php endforeach; ?>
            
                </div>
            <?php else: ?>
                <p style="font-size: 1.2rem; ">Você ainda não deixou nenhum comentário.</p><br><br>
            <?php endif; ?>
        </div>
    </section>
    
    <div id="postModal" class="post-modal">
        <div class="post-modal-content">
            <span class="post-modal-close" onclick="closePostModal()">&times;</span>
            <h2>Criar Nova Publicação</h2>
            
            <form id="postForm" action="profile.php" method="POST" enctype="multipart/form-data">
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
                            <select name="state" id="state" class="required campo-endereco" data-type="estado" data-required="true">
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

    <div id="editModal" class="modal">
        <div class="modal-content<?php if ($user['tipo_conta'] == 'Perfil de ONG' || $user['tipo_conta'] == 'Perfil de cidadão') {echo ' adress-modal';} ?>">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Editar Perfil</h2>
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <div class="form-content">
                    <div class="form-input<?php 
                        if ($user['tipo_conta'] == 'Perfil de moderador') {
                            echo ' adm-input'; 
                        } 
                    ?>">
                        <div class="column-style">
                            <div class="form-group">
                                <label for="foto_perfil" class="custom-file-label" id="label_foto">📁 Escolher imagem de perfil:</label>
                                <input type="file" name="foto_perfil" id="foto_perfil">
                            </div>

                            <div class="form-group">
                                <label for="nome">Nome:</label>
                                <input type="text" id="nome" name="nome" class="required" value="<?php echo htmlspecialchars($user['nome']); ?>" required data-type="nome" data-required="true">
                                <span class="span-required">Nome não pode conter números e caracteres especiais.</span>
                            </div>

                            <div class="form-group">
                                <label for="email">E-mail:</label>
                                <input type="email" id="email" name="email" class="required" value="<?php echo htmlspecialchars($user['email']); ?>" required data-type="e-mail" data-required="true">
                                <span class="span-required">Por favor, insira um e-mail válido</span>
                            </div>

                            <div class="form-group">
                                <label for="telefone">Telefone:</label>
                                <input type="text" id="telefone" name="telefone" class="required" value="<?php echo htmlspecialchars($user['telefone']); ?>" required data-type="telefone" data-required="true">
                                <span class="span-required">Por favor, insira um telefone válido</span>
                            </div>

                            <div class="form-group">
                                <label for="senha">Senha:</label>
                                <input type="password" id="senha" name="senha" class="required" placeholder="Digite sua nova senha" data-type="senha">
                                <span class="span-required">Sua senha deve conter no mínimo 8 caracteres, combinando letras maiúsculas, minúsculas, números e símbolos especiais.</span>
                            </div>

                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Senha:</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" class="required" placeholder="Confirme sua nova senha" data-type="confirmar senha">
                                <span class="span-required">As senhas não coincidem.</span>
                            </div> 
                        </div>
                    </div>

                    <?php if ($user['tipo_conta'] == 'Perfil de ONG' || $user['tipo_conta'] == 'Perfil de cidadão'): ?>
                        <div class="row-style">
                            <div class="row-style-content">
                                <div class="form-group">
                                    <label for="endereco_rua">Rua:</label>
                                    <input type="text" id="endereco_rua" name="endereco_rua" class="required" data-type="rua" data-required="true" value="<?php echo htmlspecialchars(
                                        $user['tipo_conta'] == 'Perfil de ONG' ? $user['ong_endereco_rua'] : $user['cidadao_endereco_rua']
                                    ); ?>">
                                    <span class="span-required"> Rua não pode conter caracteres especias.</span>
                                </div>
                                <div class="form-group">
                                    <label for="endereco_numero">Número:</label>
                                    <input type="text" id="endereco_numero" name="endereco_numero" class="required" data-type="número" data-required="true" value="<?php echo htmlspecialchars(
                                        $user['tipo_conta'] == 'Perfil de ONG' ? $user['ong_endereco_numero'] : $user['cidadao_endereco_numero']
                                    ); ?>">
                                    <span class="span-required">Número não pode conter letras ou caracteres especiais.</span>
                                </div>
                                <div class="form-group">
                                    <label for="endereco_complemento">Complemento:</label>
                                    <input type="text" id="endereco_complemento" name="endereco_complemento" class="required" value="<?php echo htmlspecialchars(
                                        $user['tipo_conta'] == 'Perfil de ONG' ? $user['ong_endereco_complemento'] : $user['cidadao_endereco_complemento']
                                    ); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="endereco_bairro">Bairro:</label>
                                    <input type="text" id="endereco_bairro" name="endereco_bairro" class="required" data-type="bairro" data-required="true" value="<?php echo htmlspecialchars(
                                        $user['tipo_conta'] == 'Perfil de ONG' ? $user['ong_endereco_bairro'] : $user['cidadao_endereco_bairro']
                                    ); ?>">
                                    <span class="span-required">Bairro não pode conter números ou caracteres especiais.</span>
                                </div>
                            </div>

                            <div class="row-style-content">
                                <div class="form-group">
                                    <label for="endereco_cidade">Cidade:</label>
                                    <input type="text" id="endereco_cidade" name="endereco_cidade" class="required" data-type="cidade" data-required="true" value="<?php echo htmlspecialchars(
                                        $user['tipo_conta'] == 'Perfil de ONG' ? $user['ong_endereco_cidade'] : $user['cidadao_endereco_cidade']
                                    ); ?>">
                                    <span class="span-required">Cidade não pode conter números ou caracteres especiais.</span>
                                </div>
                                
                                <?php
                                    $estado_usuario = '';
                                    if ($user['tipo_conta'] == 'Perfil de ONG') {
                                        $estado_usuario = $user['ong_endereco_estado'];
                                    } elseif ($user['tipo_conta'] == 'Perfil de cidadão') {
                                        $estado_usuario = $user['cidadao_endereco_estado'];
                                    }
                                ?>

                                <div class="form-group">
                                    <label for="endereco_estado"><b>Estado: *</b></label>
                                    <select name="endereco_estado" id="endereco_estado" class="required" data-type="estado" data-required="true" value="<?php echo htmlspecialchars(
                                        $user['tipo_conta'] == 'Perfil de ONG' ? $user['ong_endereco_estado'] : $user['cidadao_endereco_estado']
                                    ); ?>">
                                        <option value="">Selecione um estado</option>
                                        <option value="AC" <?php echo ($estado_usuario === 'AC') ? 'selected' : ''; ?>>Acre</option>
                                        <option value="AL" <?php echo ($estado_usuario === 'AL') ? 'selected' : ''; ?>>Alagoas</option>
                                        <option value="AP" <?php echo ($estado_usuario === 'AP') ? 'selected' : ''; ?>>Amapá</option>
                                        <option value="AM" <?php echo ($estado_usuario === 'AM') ? 'selected' : ''; ?>>Amazonas</option>
                                        <option value="BA" <?php echo ($estado_usuario === 'BA') ? 'selected' : ''; ?>>Bahia</option>
                                        <option value="CE" <?php echo ($estado_usuario === 'CE') ? 'selected' : ''; ?>>Ceará</option>
                                        <option value="DF" <?php echo ($estado_usuario === 'DF') ? 'selected' : ''; ?>>Distrito Federal</option>
                                        <option value="ES" <?php echo ($estado_usuario === 'ES') ? 'selected' : ''; ?>>Espírito Santo</option>
                                        <option value="GO" <?php echo ($estado_usuario === 'GO') ? 'selected' : ''; ?>>Goiás</option>
                                        <option value="MA" <?php echo ($estado_usuario === 'MA') ? 'selected' : ''; ?>>Maranhão</option>
                                        <option value="MT" <?php echo ($estado_usuario === 'MT') ? 'selected' : ''; ?>>Mato Grosso</option>
                                        <option value="MS" <?php echo ($estado_usuario === 'MS') ? 'selected' : ''; ?>>Mato Grosso do Sul</option>
                                        <option value="MG" <?php echo ($estado_usuario === 'MG') ? 'selected' : ''; ?>>Minas Gerais</option>
                                        <option value="PA" <?php echo ($estado_usuario === 'PA') ? 'selected' : ''; ?>>Pará</option>
                                        <option value="PB" <?php echo ($estado_usuario === 'PB') ? 'selected' : ''; ?>>Paraíba</option>
                                        <option value="PR" <?php echo ($estado_usuario === 'PR') ? 'selected' : ''; ?>>Paraná</option>
                                        <option value="PE" <?php echo ($estado_usuario === 'PE') ? 'selected' : ''; ?>>Pernambuco</option>
                                        <option value="PI" <?php echo ($estado_usuario === 'PI') ? 'selected' : ''; ?>>Piauí</option>
                                        <option value="RJ" <?php echo ($estado_usuario === 'RJ') ? 'selected' : ''; ?>>Rio de Janeiro</option>
                                        <option value="RN" <?php echo ($estado_usuario === 'RN') ? 'selected' : ''; ?>>Rio Grande do Norte</option>
                                        <option value="RS" <?php echo ($estado_usuario === 'RS') ? 'selected' : ''; ?>>Rio Grande do Sul</option>
                                        <option value="RO" <?php echo ($estado_usuario === 'RO') ? 'selected' : ''; ?>>Rondônia</option>
                                        <option value="RR" <?php echo ($estado_usuario === 'RR') ? 'selected' : ''; ?>>Roraima</option>
                                        <option value="SC" <?php echo ($estado_usuario === 'SC') ? 'selected' : ''; ?>>Santa Catarina</option>
                                        <option value="SP" <?php echo ($estado_usuario === 'SP') ? 'selected' : ''; ?>>São Paulo</option>
                                        <option value="SE" <?php echo ($estado_usuario === 'SE') ? 'selected' : ''; ?>>Sergipe</option>
                                        <option value="TO" <?php echo ($estado_usuario === 'TO') ? 'selected' : ''; ?>>Tocantins</option>
                                    </select>
                                    <span class="span-required">Selecione um estado válido.</span>
                                </div>

                                <div class="form-group">
                                    <label for="endereco_pais">País:</label>
                                    <input type="text" id="endereco_pais" name="endereco_pais" class="required" data-type="país" data-required="true"  value="<?php echo htmlspecialchars(
                                    $user['tipo_conta'] == 'Perfil de ONG' ? $user['ong_endereco_pais'] : $user['cidadao_endereco_pais']
                                    ); ?>">
                                    <span class="span-required">País não pode conter números ou caracteres especiais.</span>
                                </div>
                                <div class="form-group">
                                    <label for="endereco_cep">CEP:</label>
                                    <input type="text" id="endereco_cep" name="endereco_cep" class="required" data-type="CEP" data-required="true" value="<?php echo htmlspecialchars(
                                        $user['tipo_conta'] == 'Perfil de ONG' ? $user['ong_endereco_cep'] : $user['cidadao_endereco_cep']
                                    ); ?>">
                                    <span class="span-required">Por favor, insira um CEP válido</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="submit" name="update_profile" class="profile-save" onclick="btnRegisterOnClick(event, this.form)">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <div id="postEditModal" class="post-modal">
        <div class="post-modal-content">
            <span class="post-modal-close" onclick="closeEditPostModal()">&times;</span>
            <h2>Editar Publicação</h2>
            <form id="editPostForm" action="profile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_post_id" name="post_id">
                
                <div class="post-form-group">
                    <label for="edit_titulo">Título</label>
                    <input type="text" id="edit_titulo" name="titulo" required>
                </div>

                <div class="post-form-group">
                    <label for="edit_conteudo">Conteúdo</label>
                    <textarea id="edit_conteudo" name="conteudo" rows="4" required></textarea>
                </div>
                    
                <div class="post-form-group">
                    <label>Imagens atuais:</label>
                    <div id="edit-image-gallery" style="display: flex; flex-wrap: wrap; gap: 5px;"></div>
                </div>

                <div class="post-form-group">
                  <input type="file" name="foto_publicacao[]" id="foto_publicacao_edit" multiple accept="image/*">
                    <label for="foto_publicacao_edit" class="custom-file-label" id="label_foto_post_edit">📁 Escolher imagem:</label>
                </div>

                <div class="post-form-group">
                    <label for="edit_tipo_publicacao">Tipo de Publicação</label>
                    <select id="edit_tipo_publicacao" name="tipo_publicacao" required>
                        <option value="animal">Animal Perdido</option>
                        <option value="resgate">Resgate de Animal</option>
                        <option value="informacao">Informação</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>

                <div class="row-style">
                    <div class="row-style-content">
                        <div class="form-group">
                            <label for="endereco_rua">Rua:</label>
                            <input type="text" id="edit_endereco_rua" name="endereco_rua" class="required campo-endereco-edit" data-type="rua" data-type="rua" data-required="true" placeholder="Insira o nome da rua">
                            <span class="span-required"> Rua não pode conter caracteres especias.</span>
                        </div>

                        <div class="form-group">
                            <label for="endereco_bairro">Bairro:</label>
                            <input type="text" id="edit_endereco_bairro" name="endereco_bairro" class="required campo-endereco-edit" data-type="bairro" data-required="true" placeholder="Insira o bairro">
                            <span class="span-required">Bairro não pode conter números ou caracteres especiais.</span>
                        </div>
                    </div>

                    <div class="row-style-content">
                        <div class="form-group">
                            <label for="endereco_cidade">Cidade:</label>
                            <input type="text" id="edit_endereco_cidade" name="endereco_cidade" class="required campo-endereco-edit" data-type="cidade" data-required="true" placeholder="Insira a cidade">
                            <span class="span-required">Cidade não pode conter números ou caracteres especiais.</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="endereco_estado"><b>Estado: *</b></label>
                            <select name="endereco_estado" id="edit_endereco_estado" class="required campo-endereco-edit" data-type="estado" data-required="true">
                                <option value="">Selecione um estado</option>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <option value="AP">Amapá</option>
                                <option value="AM">Amazonas</option>
                                <option value="BA">Bahia</option>
                                <option value="CE">Ceará</option>
                                <option value="DF">Distrito Federal</option>
                                <option value="ES">Espírito Santo</option>
                                <option value="GO">Goiás</option>
                                <option value="MA">Maranhão</option>
                                <option value="MT">Mato Grosso</option>
                                <option value="MS">Mato Grosso do Sul</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="PA">Pará</option>
                                <option value="PB">Paraíba</option>
                                <option value="PR">Paraná</option>
                                <option value="PE">Pernambuco</option>
                                <option value="PI">Piauí</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="RN">Rio Grande do Norte</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="RO">Rondônia</option>
                                <option value="RR">Roraima</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="SP">São Paulo</option>
                                <option value="SE">Sergipe</option>
                                <option value="TO">Tocantins</option>
                            </select>
                            <span class="span-required">Selecione um estado válido.</span>
                        </div>
                    </div>
                </div>

                <div class="checkbox-wrapper">
                    <input type="checkbox" id="nao_sei_endereco_edit" name="nao_sei_endereco">
                    <label for="nao_sei_endereco_edit">Não sei informar o endereço</label>
                </div>

                <button type="submit" name="update_post" class="create-post" onclick="btnRegisterOnClick(event, this.form)">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../scripts/pages/profile/profile.js"></script>
    <script src="../../scripts/view-comments.js"></script>
    <script src="../../scripts/register-validation.js"></script>
    <script src="../../scripts/left-menu.js"></script>


    <?php if ($isLoggedIn): ?>
        <script>
            let tempoInatividade = 15 * 60 * 1000;
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