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

$query = "SELECT u.id_usuario, u.nome, c.email, c.telefone, p.foto, p.descricao AS tipo_conta, 
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

    header("Location: profile.php");
    exit();
}

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
                <?php if ($user['tipo_conta'] == 'Perfil de moderador'): ?>
                    <li><a href="../../../index.php">Usuários Cadastrados</a></li>
                <?php endif; ?>
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

            <?php if ($user['tipo_conta'] == 'Perfil de ONG' || $user['tipo_conta'] == 'Perfil de cidadão'): ?>
                <p><span class="label">Endereço:</span> 
                    <?php 
                        if ($user['tipo_conta'] == 'Perfil de ONG') {
                            $endereco_rua = htmlspecialchars($user['ong_endereco_rua']);
                            $endereco_numero = htmlspecialchars($user['ong_endereco_numero']);
                        } else {
                            $endereco_rua = htmlspecialchars($user['cidadao_endereco_rua']);
                            $endereco_numero = htmlspecialchars($user['cidadao_endereco_numero']);
                        }
                        
                        if (stripos($endereco_rua, 'Rua ') !== 0) {
                            echo 'Rua ' . $endereco_rua . ', ' . $endereco_numero;
                        } else {
                            echo $endereco_rua . ', ' . $endereco_numero;
                        }
                    ?>
                </p>

                <?php if (!empty($user['ong_endereco_complemento']) || !empty($user['cidadao_endereco_complemento'])): ?>
                    <p><span class="label">Complemento:</span> 
                        <?php 
                            if ($user['tipo_conta'] == 'Perfil de ONG') {
                                echo htmlspecialchars($user['ong_endereco_complemento']);
                            } else {
                                echo htmlspecialchars($user['cidadao_endereco_complemento']);
                            }
                        ?>
                    </p>
                <?php endif; ?>

                <p><span class="label">Bairro:</span> 
                    <?php 
                        if ($user['tipo_conta'] == 'Perfil de ONG') {
                            echo htmlspecialchars($user['ong_endereco_bairro']);
                        } else {
                            echo htmlspecialchars($user['cidadao_endereco_bairro']);
                        }
                    ?>
                </p>

                <p><span class="label">Cidade:</span> 
                    <?php 
                        if ($user['tipo_conta'] == 'Perfil de ONG') {
                            echo htmlspecialchars($user['ong_endereco_cidade']);
                        } else {
                            echo htmlspecialchars($user['cidadao_endereco_cidade']);
                        }
                    ?>
                </p>

                <p><span class="label">Estado:</span> 
                    <?php 
                        if ($user['tipo_conta'] == 'Perfil de ONG') {
                            echo htmlspecialchars($user['ong_endereco_estado']);
                        } else {
                            echo htmlspecialchars($user['cidadao_endereco_estado']);
                        }
                    ?>
                </p>

                <p><span class="label">País:</span> 
                    <?php 
                        if ($user['tipo_conta'] == 'Perfil de ONG') {
                            echo htmlspecialchars($user['ong_endereco_pais']);
                        } else {
                            echo htmlspecialchars($user['cidadao_endereco_pais']);
                        }
                    ?>
                </p>

                <p><span class="label">CEP:</span> 
                    <?php 
                        if ($user['tipo_conta'] == 'Perfil de ONG') {
                            echo htmlspecialchars($user['ong_endereco_cep']);
                        } else {
                            echo htmlspecialchars($user['cidadao_endereco_cep']);
                        }
                    ?>
                </p>
            <?php endif; ?>

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

    <?php if ($user['tipo_conta'] == 'Perfil de ONG' || $user['tipo_conta'] == 'Perfil de cidadão'): ?>
        <button class="floating-button" title="Nova Publicação" onclick="openPostModal()">
            +
        </button>
    <?php endif; ?>

    <div id="postModal" class="post-modal">
        <div class="post-modal-content">
            <span class="post-modal-close" onclick="closePostModal()">&times;</span>
            <h2>Criar Nova Publicação</h2>
            <form action="processar_publicacao.php" method="POST">
                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>
                <div class="form-group">
                    <label for="conteudo">Conteúdo</label>
                    <textarea id="conteudo" name="conteudo" rows="4" required></textarea>
                </div>
                <button type="submit">Publicar</button>
            </form>
        </div>
    </div>


    <div id="editModal" class="modal">

        <div class="modal-content<?php 
            if ($user['tipo_conta'] == 'Perfil de ONG' || $user['tipo_conta'] == 'Perfil de cidadão') {
                    echo ' adress-modal'; 
            } 
        ?>">
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
                                <input type="password" id="senha" name="senha" class="required" placeholder="Digite sua nova senha" data-type="senha" data-required="true">
                                <span class="span-required">Sua senha deve conter no mínimo 8 caracteres, combinando letras maiúsculas, minúsculas, números e símbolos especiais.</span>
                            </div>

                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Senha:</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" class="required" placeholder="Confirme sua nova senha" data-type="confirmar senha" data-required="true">
                                <span class="span-required">As senhas não coincidem.</span>
                            </div> 
                        </div>
                        
                    </div>

                    <?php if ($user['tipo_conta'] == 'Perfil de ONG' || $user['tipo_conta'] == 'Perfil de Cidadão'): ?>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../scripts/pages/profile/profile.js"></script>
    <script src="../../scripts/register-validation.js"></script>

</body>
</html>