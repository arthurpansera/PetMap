<?php
session_start();

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

$query = "SELECT u.id_usuario, u.nome, c.email, c.telefone, p.foto, p.descricao AS tipo_conta 
          FROM usuario u
          JOIN perfil p ON u.id_usuario = p.id_usuario
          JOIN contato c ON u.id_usuario = c.id_usuario
          WHERE u.id_usuario = ?";
$stmt = $obj->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Erro ao buscar os dados do usuário.";
    exit();
}

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

if (isset($_POST['update_profile'])) {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($senha !== $confirmar_senha) {
        echo "<span class='alert alert-danger'>As senhas não coincidem. Por favor, tente novamente.</span>";
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

    $stmt_usuario->execute();
    $stmt_contato->execute();

    header("Location: profile.php");
    exit();
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
        <nav class="left-menu">
            <ul>
                <li><a href="../../../index.php">Página Principal</a></li>
                <li><a href="../../../index.php">Animais Resgatados</a></li>
                <li><a href="../../../index.php">Animais Perdidos</a></li>
                <li><a href="../../../index.php">Sobre Nós</a></li>
                <li><a href="../../../index.php">Perguntas Frequentes</a></li>
                <li><a href="../../../index.php">Suporte</a></li>
            </ul>
            <div class="footer">
                <p>&copy;2025 - PetMap.</p>
                <p>Todos os direitos reservados.</p>
            </div>
        </nav>
    </section>

    <section class="profile">
        <div class="profile-info">
            <div class="profile-header">
                <img src="<?php echo $user['foto'] ? $user['foto'] : '../images/perfil-images/imagem-perfil-teste.png'; ?>" alt="Foto de Perfil">
                <h2><?php echo htmlspecialchars($user['nome']); ?></h2>
            </div>
            <p><span class="label">Tipo de Conta:</span> <?php echo htmlspecialchars($user['tipo_conta']); ?></p>
            <p><span class="label">E-mail:</span> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><span class="label">Telefone:</span> <?php echo htmlspecialchars($user['telefone']); ?></p>

            <div class="profile-buttons">
                <form action="profile.php" method="POST">
                    <button type="submit" name="logout" class="profile-logout">Sair da Conta</button>
                </form>
                <div class="functions-buttons">
                    <a href="javascript:void(0);" class="profile-edit" onclick="openModal()">Editar Informações</a>
                    <form action="profile.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir sua conta?');">
                        <button type="submit" name="delete_account" class="profile-delete">Excluir Conta</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Editar Perfil</h2>
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone:</label>
                    <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($user['telefone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite sua nova senha">
                </div>

                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha:</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua nova senha">
                </div>

                <button type="submit" name="update_profile" class="profile-save">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script src="../../scripts/pages/profile/profile.js"></script>

</body>
</html>