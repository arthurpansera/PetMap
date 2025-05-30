<?php
    include('../../../conecta_db.php');
    session_start();

    $isLoggedIn = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

    $obj = conecta_db();

    if (!$obj) {
        header("Location: database-error.php");
        exit;
    }

    $userId = $_SESSION['id_usuario'];

    $query = "SELECT nome, descricao FROM usuario u 
              JOIN perfil p ON u.id_usuario = p.id_usuario 
              WHERE u.id_usuario = ?";
    $stmt = $obj->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userProfile = $stmt->get_result()->fetch_assoc();

    $userName = $userProfile ? $userProfile['nome'] : 'Usuário';

    $query = "
        SELECT u.id_usuario, u.nome, c.email, p.status_perfil,
            CASE
                WHEN m.id_moderador IS NOT NULL THEN 'Moderador'
                WHEN o.cnpj IS NOT NULL THEN 'ONG'
                WHEN ci.cpf IS NOT NULL THEN 'Cidadão'
                ELSE 'Desconhecido'
            END AS tipo_conta
        FROM usuario u
        LEFT JOIN contato c ON u.id_usuario = c.id_usuario
        LEFT JOIN perfil p ON u.id_usuario = p.id_usuario
        LEFT JOIN moderador m ON u.id_usuario = m.id_usuario
        LEFT JOIN ong o ON u.id_usuario = o.id_usuario
        LEFT JOIN cidadao ci ON u.id_usuario = ci.id_usuario
    ";

    $stmt = $obj->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuarios = $result->fetch_all(MYSQLI_ASSOC);

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
                            <form action="about-us.php" method="POST">
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
                            if (count($usuarios) > 0) {
                                foreach ($usuarios as $row) {
                                    $status = $row['status_perfil'];
                                    $tipo = $row['tipo_conta'];

                                    $statusIcon = '';
                                    if ($tipo === 'Moderador') {
                                        $statusIcon = '<img src="../images/perfil-images/moderador.png" alt="Moderador" class="status-icon" title="Moderador">';
                                    } elseif ($status === 'verificado') {
                                        $statusIcon = '<img src="../images/perfil-images/verificado.png" alt="Verificado" class="status-icon" title="Verificado">';
                                    } elseif ($status === 'nao_verificado') {
                                        $statusIcon = '<img src="../images/perfil-images/nao-verificado.png" alt="Não Verificado" class="status-icon" title="Não Verificado">';
                                    } elseif ($status === 'banido') {
                                        $statusIcon = '<img src="../images/perfil-images/banido.png" alt="Banido" class="status-icon" title="Banido">';
                                    }

                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['nome']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($tipo) . "</td>";
                                    echo "<td>
                                            <a href='view-profile.php?id=" . $row['id_usuario'] . "' class='btn-ver-perfil'>Ver Perfil</a>
                                            $statusIcon
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>Nenhum dado encontrado</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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