<?php
include('../../../conecta_db.php');

session_start();

if (isset($_POST['name'], $_POST['email'], $_POST['telephone'], $_POST['password'])) {
    $nome = $_POST['name'];
    $email = $_POST['email'];
    $telefone = $_POST['telephone'];
    $senha = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $obj = conecta_db();

    $query_check_email = "SELECT id_usuario FROM contato WHERE email = ?";
    $stmt_check_email = $obj->prepare($query_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows > 0) {
        $_SESSION['error_message'] = "Usuário já cadastrado!";
        header("Location: register-ong.php");
        exit();
    }

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

        $query_moderador = "INSERT INTO moderador (id_usuario) VALUES (?)";
        $stmt_moderador = $obj->prepare($query_moderador);
        $stmt_moderador->bind_param("i", $id_usuario);
        $stmt_moderador->execute();

        if ($stmt_moderador->affected_rows > 0 && $stmt_contato->affected_rows > 0) {
            $descricao = "Perfil de moderador";
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
            echo "<span class='alert alert-danger'><h5>Erro ao cadastrar o moderador ou contato!</h5></span>";
        }
    } else {
        echo "<span class='alert alert-danger'><h5>Erro ao cadastrar o usuário!</h5></span>";
    }
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
    <title>PetMap | Cadastro de Moderador</title>
    <link rel="stylesheet" href="../../styles/pages/register-adm/register-adm.css">
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
            
            <h1>Dados Cadastrais - Moderador</h1>

            <section class="input-register">
                <form id="form" name="form" method="POST" action="register-adm.php">
                    <div class="full-inputBox">
                        <label for="name"><b>Nome: *</b></label>
                        <input type="text" id="name" name="name" class="full-inputUser required" placeholder="Insira seu nome completo" oninput="inputWithoutNumbersValidate(0)">
                        <span class="span-required">Nome não pode conter números e caracteres especiais.</span>
                    </div>

                    <div class="full-inputBox">
                        <label for="text"><b>E-mail: *</b></label>
                        <input type="text" id="email" name="email"class="full-inputUser required" placeholder="exemplo@gmail.com" oninput="emailValidate()">
                        <span class="span-required">Insira um e-mail válido!</span>
                    </div>

                    <div class="full-inputBox">
                        <label for="telephone"><b>Telefone: *</b></label>
                        <input type="text" name="telephone" id="telephone" class="mid-inputUser required" placeholder="(XX) XXXXX-XXXX" oninput="telephoneValidate()">
                        <span class="span-required">Insira um telefone válido</span>
                     </div>
            
                    <div class="full-inputBox">
                            <label for="password"><b>Senha: *</b></label>
                            <input type="password" name="password" id="password" class="full-inputUser required" placeholder="Crie uma senha" oninput="passwordValidate()">
                            <span class="span-required">Sua senha deve conter no mínimo 8 caracteres, combinando letras maiúsculas, minúsculas, números e símbolos especiais.</span>
                    </div>

                    <div class="full-inputBox">
                        <label for="confirm-pass"><b>Confirme sua senha: *</b></label>
                        <input type="password" name="confirm-pass" id="confirm-pass" class="full-inputUser required" placeholder="Repita a senha" oninput="confirmPasswordValidate()">
                        <span class="span-required">As senhas não coincidem.</span>
                    </div>
                
                    <input type="submit" value="Cadastrar-se" class="register-btn" onclick="btnRegisterOnClick(event)">

                </form>
            </section>
        </section>
    </section>

    <footer class="footer">
        <p>&copy;2025 - PetMap - Onde tem pet, tem PetMap!. Todos os direitos reservados.</p>
    </footer>

    <script src="../../scripts/pages/register-adm/register-adm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>