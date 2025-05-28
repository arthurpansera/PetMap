<?php
    include('../../../conecta_db.php');

    session_start();

    $tempoInatividade = 300;

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


    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

    $obj = conecta_db();

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
    }

    $query = "
        SELECT u.id_usuario, u.nome, c.email,
            CASE
                WHEN m.id_moderador IS NOT NULL THEN 'Moderador'
                WHEN o.cnpj IS NOT NULL THEN 'ONG'
                WHEN ci.cpf IS NOT NULL THEN 'Cidadão'
                ELSE 'Desconhecido'
            END AS tipo_conta
        FROM usuario u
        LEFT JOIN contato c ON u.id_usuario = c.id_usuario
        LEFT JOIN moderador m ON u.id_usuario = m.id_usuario
        LEFT JOIN ong o ON u.id_usuario = o.id_usuario
        LEFT JOIN cidadao ci ON u.id_usuario = ci.id_usuario
    ";

    $stmt = $obj->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $hasUsers = $result->num_rows > 0;

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
    <title>PetMap | Usuários Cadastrados</title>
    <link rel="stylesheet" href="../../styles/pages/registered-users/registered-users.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <a href="../../../index.php">
                    <img src="../images/logo-petmap/white-logo.png" alt="Logo PetMap">
                </a>
                <ul class="ul">
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
                            <form action="frequent-questions.php" method="POST">
                                <button type="submit" name="logout">
                                    <img src="../images/perfil-images/icone-sair-branco.png" alt="Sair da Conta">
                                </button>
                            </form>
                        </div>
                    </li>
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
                <li><a href="registered-users.php">Usuários Cadastrados</a></li>
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
            <h2>Usuários Cadastrados</h2>
            <div class="box">
                <div class="table-container">
                    <table class="registered-users-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo de Conta</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $query = "
                                    SELECT u.id_usuario, u.nome, c.email,
                                        CASE
                                            WHEN m.id_moderador IS NOT NULL THEN 'Moderador'
                                            WHEN o.cnpj IS NOT NULL THEN 'ONG'
                                            WHEN ci.cpf IS NOT NULL THEN 'Cidadão'
                                            ELSE 'Desconhecido'
                                        END AS tipo_conta
                                    FROM usuario u
                                    LEFT JOIN contato c ON u.id_usuario = c.id_usuario
                                    LEFT JOIN moderador m ON u.id_usuario = m.id_usuario
                                    LEFT JOIN ong o ON u.id_usuario = o.id_usuario
                                    LEFT JOIN cidadao ci ON u.id_usuario = ci.id_usuario
                                ";

                                $stmt = $obj->prepare($query);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['tipo_conta']) . "</td>";
                                        echo "<td><a href='view-profile.php?id=" . $row['id_usuario'] . "' class='btn-ver-perfil'>Ver Perfil</a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>Nenhum dado encontrado</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>

                    <div class="no-data-message" id="no-data-message" style="display: none;">
                        Nenhum dado encontrado
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>