<?php
    include('../../../conecta_db.php');

    session_start();

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

    $query_areas = "SELECT endereco_estado, endereco_cidade, endereco_bairro, COUNT(*) AS total_publicacoes
                FROM publicacao
                WHERE tipo_publicacao = ?
                  AND endereco_rua IS NOT NULL
                  AND endereco_bairro IS NOT NULL
                  AND endereco_cidade IS NOT NULL
                  AND endereco_estado IS NOT NULL
                GROUP BY endereco_estado, endereco_cidade, endereco_bairro
                ORDER BY total_publicacoes DESC";
    $stmt_areas = $obj->prepare($query_areas);
    $tipo = 'animal';
    $stmt_areas->bind_param("s", $tipo);
    $stmt_areas->execute();
    $result_areas = $stmt_areas->get_result();

    $rows = [];
    while ($row = $result_areas->fetch_assoc()) {
        $rows[] = $row;
    }

    $locations = [];
    foreach ($rows as $row) {
        $estado = $row['endereco_estado'];
        $cidade = $row['endereco_cidade'];
        $bairro = $row['endereco_bairro'];

        if (!isset($locations[$estado])) {
            $locations[$estado] = [];
        }

        if (!isset($locations[$estado][$cidade])) {
            $locations[$estado][$cidade] = [];
        }

        if (!in_array($bairro, $locations[$estado][$cidade])) {
            $locations[$estado][$cidade][] = $bairro;
        }
    }

    $json_locations = json_encode($locations);

    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PetMap | Áreas de Maior Quantidade de Abandono</title>
    <link rel="stylesheet" href="../../styles/pages/areas/areas.css" />
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <a href="../../../index.php">
                    <img src="../images/logo-petmap/white-logo.png" alt="Logo PetMap" />
                </a>
                <ul class="ul">
                    <?php if ($isLoggedIn): ?>
                        <?php
                            $nome = explode(' ', trim($userName));
                            $primeiroNome = $nome[0];
                        ?>
                        <li class="user-info">
                            <p class="welcome-message">Bem-vindo, <?php echo htmlspecialchars($primeiroNome); ?>!</p>
                            <a class="profile-image" href="profile.php">
                                <img src="../images/perfil-images/profile-icon.png" alt="Ícone de Perfil" />
                            </a>
                            <div class="logout-button">
                                <form action="areas.php" method="POST">
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
    <section class="info">
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
                            <form action="areas.php" method="POST">
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
            <h2>Áreas de Maior Abandono</h2>
            <div class="box">
                <div class="filters">
                    <select class="filter-select" id="estado">
                        <option value="">Selecione o Estado</option>
                    </select>

                    <select class="filter-select" id="cidade" disabled>
                        <option value="">Selecione a Cidade</option>
                    </select>

                    <select class="filter-select" id="bairro" disabled>
                        <option value="">Selecione o Bairro</option>
                    </select>
                </div>

                <div class="table-container">
                    <table id="abandonos-table">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Cidade</th>
                                <th>Bairro</th>
                                <th>Casos de Animais Perdidos Registrados</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <?php
                                if (count($rows) > 0) {
                                    foreach ($rows as $row) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['endereco_estado']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['endereco_cidade']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['endereco_bairro']) . "</td>";
                                        echo "<td>" . (int)$row['total_publicacoes'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="4">Nenhum dado encontrado</td></tr>';
                                }
                            ?>
                        </tbody>
                    </table>

                    <div class="no-data-message" id="no-data-message" style="display:none;">
                        Nenhum dado encontrado
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../scripts/left-menu.js"></script>

    <script>
        const locations = <?php echo $json_locations; ?>;
    </script>
    <script src="../../scripts/pages/areas/areas.js"></script>
    
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