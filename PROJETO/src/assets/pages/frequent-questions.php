<?php
    include('../../../conecta_db.php');

    session_start();
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
                            $nomes = explode(' ', trim($userName));
                            $doisPrimeirosNomes = implode(' ', array_slice($nomes, 0, 2));
                        ?>
                        <li class="user-info">
                            <p class="welcome-message">Bem-vindo, <?php echo htmlspecialchars($doisPrimeirosNomes); ?>!</p>
                            <a class="profile-image" href="profile.php">
                                <img src="../images/perfil-images/profile-icon.png" alt="Ícone de Perfil">
                            </a>
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
                <li><a href="../../../index.php">Animais Resgatados</a></li>
                <li><a href="lost-animals.php">Animais Perdidos</a></li>
                <li><a href="../../../index.php">Áreas de Maior Abandono</a></li>
                <?php if ($isModerator): ?>
                    <li><a href="../../../index.php">Usuários Cadastrados</a></li>
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
                    <hr style="border: 1px solid #ddd; margin-bottom: 30px;">
                    <ul>
                        <li><strong>Como faço para me cadastrar como ONG, cidadão ou moderador?</strong><br>
                            Você pode escolher o tipo de conta durante o processo de cadastro. Após isso, é só preencher o formulário com as informações solicitadas.</li>

                        <li><strong>Posso alterar meu perfil depois de me cadastrar?</strong><br>
                            Sim. É possível editar informações pessoais e atualizar a foto do perfil acessando a página de perfil.</li>

                        <li><strong>Quem pode fazer publicações na plataforma?</strong><br>
                            Usuários com perfil de cidadão ou ONG podem criar publicações. Elas podem ser sobre animais perdidos, resgates, informações ou outros temas ligados à causa animal.</li>

                        <li><strong>É possível editar ou remover uma publicação ou um comentário?</strong><br>
                            Sim, acesse a área do seu perfil e vá até a seção de publicações ou comentários para editar ou excluir quando necessário.</li>

                        <li><strong>Vi um animal abandonado na rua. O que posso fazer?</strong><br>
                            Você pode criar uma publicação do tipo "Animal Perdido", incluindo fotos e a localização aproximada. Isso permite que ONGs e outros usuários visualizem e possam oferecer ajuda rapidamente.</li>

                        <li><strong>Como entrar em contato com uma ONG?</strong><br>
                            Acesse o perfil da ONG diretamente pela plataforma. Lá, você encontrará o e-mail e o telefone para contato. Além disso, as ONGs podem comentar nas publicações e interagir com você por meio delas.</li>
                    </ul>
                    <hr style="border: 1px solid #ddd; margin-bottom: 30px;">
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
</body>
</html>