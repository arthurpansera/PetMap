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
    $isModerator = false;

    $obj = conecta_db();

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
    <title>PetMap | Perguntas Frequentes</title>
    <link rel="stylesheet" href="../../styles/pages/frequent-questions/frequent-questions.css">
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
                                <form action="frequent-questions.php" method="POST">
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
            <h2>Perguntas Frequentes</h2>
            <div class="questions">
                <div class="faq-info-1">
                    <p>O PetMap foi criado para facilitar a conexão entre pessoas, ONGs e comunidades que se preocupam com o bem-estar animal. Aqui reunimos as perguntas mais frequentes para ajudar você a entender melhor como a plataforma funciona e como utilizá-la da melhor forma.</p>
                    <p>Se você está com alguma dúvida sobre como registrar um animal perdido, relatar um avistamento ou interagir com ONGs, esta seção foi feita para você.</p>
                </div>
                <div class="faq-info-2">
                    <button class="accordion">Como faço para me cadastrar como ONG, cidadão ou moderador?</button>
                    <div class="panel">
                        <p>Você pode escolher o tipo de conta durante o processo de cadastro. Após isso, é só preencher o formulário com as informações solicitadas.</p>
                    </div>

                    <button class="accordion">Posso alterar meu perfil depois de me cadastrar?</button>
                    <div class="panel">
                        <p>Sim. É possível editar informações pessoais e atualizar a foto do perfil acessando a página de perfil.</p>
                    </div>

                    <button class="accordion">Quem pode fazer publicações na plataforma?</button>
                    <div class="panel">
                        <p>Usuários com perfil de cidadão ou ONG podem criar publicações. Elas podem ser sobre animais perdidos, resgates, informações ou outros temas ligados à causa animal.</p>
                    </div>

                    <button class="accordion">É possível editar ou remover uma publicação ou um comentário?</button>
                    <div class="panel">
                        <p>Sim, acesse a área do seu perfil e vá até a seção de publicações ou comentários para editar ou excluir quando necessário.</p>
                    </div>

                    <button class="accordion">Vi um animal abandonado na rua. O que posso fazer?</button>
                    <div class="panel">
                        <p>Você pode criar uma publicação do tipo "Animal Perdido", incluindo fotos e a localização aproximada. Isso permite que ONGs e outros usuários visualizem e possam oferecer ajuda rapidamente.</p>
                    </div>

                    <button class="accordion">Como entrar em contato com uma ONG?</button>
                    <div class="panel">
                        <p>Acesse o perfil da ONG diretamente pela plataforma. Lá, você encontrará o e-mail e o telefone para contato. Além disso, as ONGs podem comentar nas publicações e interagir com você por meio delas.</p>
                    </div>
                </div>
                <div class="faq-info-3">
                    <div>
                        <p>Se a sua dúvida não foi respondida aqui, entre em contato com nosso suporte.</p>
                        <p>Juntos, podemos fazer a diferença na vida de muitos animais!</p>
                    </div>
                    <div>
                        <a href="support.php">Suporte</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../scripts/pages/frequent-questions/frequent-questions.js"></script>

</body>
</html>