<?php
include('../../../conecta_db.php');

session_start();

if (isset($_POST['name'], $_POST['cpf'], $_POST['birthYear'], $_POST['telephone'], $_POST['email'], $_POST['password'])) {
    $nome = $_POST['name'];
    $cpf = $_POST['cpf'];
    $data_nascimento = $_POST['birthYear'];
    $telefone = $_POST['telephone'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $obj = conecta_db();

    $query = "INSERT INTO usuario (nome, senha) VALUES (?, ?)";
    $stmt = $obj->prepare($query);
    $stmt->bind_param("ss", $nome, $senha);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $id_usuario = $obj->insert_id;

        $query_contato = "INSERT INTO contato (id_usuario, telefone, email) VALUES (?, ?, ?)";
        $stmt_contato = $obj->prepare($query_contato);
        $stmt_contato->bind_param("iss", $id_usuario, $telefone, $email);
        $stmt_contato->execute();

        $query_cidadao = "INSERT INTO cidadao (id_usuario, cpf, data_nascimento) VALUES (?, ?, ?, ?)";
        $stmt_cidadao = $obj->prepare($query_cidadao);
        $stmt_cidadao->bind_param("isss", $id_usuario, $cnpj, $data_nascimento);
        $stmt_cidadao->execute();

        if ($stmt_cidadao->affected_rows > 0 && $stmt_contato->affected_rows > 0) {
            $descricao = "Perfil de cidadão";
            $foto = null;

            $query_perfil = "INSERT INTO perfil (id_usuario, descricao, foto) VALUES (?, ?, ?)";
            $stmt_perfil = $obj->prepare($query_perfil);
            $stmt_perfil->bind_param("iss", $id_usuario, $descricao, $foto);
            $stmt_perfil->execute();

            if ($stmt_perfil->affected_rows > 0) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['id_usuario'] = $id_usuario;
                header("Location: ../../../index.php");
                exit();
            } else {
                echo "<span class='alert alert-danger'><h5>Erro ao cadastrar o perfil!</h5></span>";
            }
        } else {
            echo "<span class='alert alert-danger'><h5>Erro ao cadastrar o cidadão ou contato!</h5></span>";
        }
    } else {
        echo "<span class='alert alert-danger'><h5>Erro ao cadastrar o usuário!</h5></span>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetMap | Cadastro de Cidadão</title>
    <link rel="stylesheet" href="../../styles/pages/register-user/register-user.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <a href="../../../index.php">
                    <img src="../images/logo-petmap/white-logo.png" alt="Logo PetMap">
                </a>
            </nav>
        </div> 
    </header>

    <section class="main-content">
        <section class="box-container">
            <section class="btn-back">
                <div class="back-btn">
                    <a href="../../assets/pages/login.php">Voltar</a>
                </div>

            </section>
            

            <h1>Dados Cadastrais - Cidadão</h1>

            <section class="input-register">
                <form id="form" name="form" method="POST" action="ong-register-process.php">
                    <div class="full-inputBox">
                        <label for="name"><b>Nome: *</b></label>
                        <input type="text" id="name" name="name" class="full-inputUser required" placeholder="Insira o nome da ONG" oninput="inputWithoutNumbersValidate(0)">
                        <span class="span-required">Nome não pode conter números e caracteres especiais.</span>
                    </div>

                    <div class="container-row">
                        <div class="mid-inputBox">
                            <label for="CNPJ"><b>CPF: *</b></label>
                            <input type="text" name="cnpj" id="cnpj" class="mid-inputUser required" placeholder="XX.XXX.XXX/XXXX-XX">
                            <span class="span-required"></span>
                        </div>

                        <div class="mid-inputBox">
                            <label for="birthYear"><b>Data de Nascimento: *</b></label>
                            <input type="text" name="birthYear" id="birthYear" class="mid-inputUser required" placeholder="DD/MM/AAAA">
                            <span class="span-required">Insira uma data de nascimento válida.</span>
                        </div>
                    </div>

                    <div class="container-row">
                        <div class="mid-inputBox">
                            <label for="telephone"><b>Telefone: *</b></label>
                            <input type="text" name="telephone" id="telephone" class="mid-inputUser required" placeholder="XX XXXXX-XXXX">
                            <span class="span-required">Insira um telefone válido</span>
                        </div>

                        <div class="mid-inputBox">
                            <label for="text"><b>E-mail: *</b></label>
                            <input type="text" id="email" name="email"class="full-inputUser required" placeholder="exemplo@gmail.com">
                            <span class="span-required">Insira um e-mail válido!</span>
                        </div>
                    </div>
            
                    <div class="full-inputBox">
                            <label for="password"><b>Senha: *</b></label>
                            <input type="password" name="password" id="password" class="full-inputUser required" placeholder="Crie uma senha">
                            <span class="span-required">Sua senha deve conter no mínimo 8 caracteres, combinando letras maiúsculas, minúsculas, números e símbolos especiais.</span>
                    </div>

                    <div class="full-inputBox">
                        <label for="confirm-pass"><b>Confirme sua senha: *</b></label>
                        <input type="password" name="confirm-pass" id="confirm-pass" class="full-inputUser required" placeholder="Repita a senha">
                        <span class="span-required">As senhas não coincidem.</span>
                    </div>
                    
                    <div class="full-inputBox complement">
                        <label for="complement"><b>Complemento: (opcional)</b></label>
                        <input type="text" name="complement" id="complement" class="full-inputUser" placeholder="Insira o complemento">
                    </div>

                    <input type="submit" value="Cadastrar-se" class="register-btn" onclick="btnRegisterOnClick(event)">

                </form>
            </section>
        </section>
    </section>

    <footer class="footer">
        <p>&copy;2025 - PetMap - Onde tem pet, tem PetMap!. Todos os direitos reservados.</p>
    </footer>

    <script src="../../scripts/pages/reigster-ong/register-ong.js"></script>
</body>
</html>

