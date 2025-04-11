<?php
include('../../../conecta_db.php');

session_start();

if (isset($_POST['name'], $_POST['cpf'], $_POST['birthYear'], $_POST['telephone'], $_POST['email'], $_POST['password'], $_POST['CEP'], $_POST['road'], $_POST['num'], $_POST['neighborhood'], $_POST['city'], $_POST['state'], $_POST['country'], $_POST['complement'])) {
    $nome = $_POST['name'];
    $cpf = $_POST['cpf'];
    $data_nascimento = DateTime::createFromFormat('d/m/Y', $_POST['birthYear'])->format('Y-m-d');
    $telefone = $_POST['telephone'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $cep = $_POST['CEP'];
    $rua = $_POST['road'];
    $numero = $_POST['num'];
    $bairro = $_POST['neighborhood'];
    $cidade = $_POST['city'];
    $estado = $_POST['state'];
    $pais = $_POST['country'];
    $complemento = $_POST['complement'];

    $obj = conecta_db();

    $query_check_email = "SELECT id_usuario FROM contato WHERE email = ?";
    $stmt_check_email = $obj->prepare($query_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    $query_check_cpf = "SELECT id_usuario FROM cidadao WHERE cpf = ?";
    $stmt_check_cpf = $obj->prepare($query_check_cpf);
    $stmt_check_cpf->bind_param("s", $cpf);
    $stmt_check_cpf->execute();
    $stmt_check_cpf->store_result();


    if ($stmt_check_email->num_rows > 0 || $stmt_check_cpf->num_rows > 0) {
        $_SESSION['error_message'] = "Usuário já cadastrado!";
        header("Location: register-user.php");
        exit();
    }

    $query = "INSERT INTO usuario (nome, senha) VALUES (?, ?)";
    $stmt = $obj->prepare($query);
    
    if (!$stmt) {
        die("<span class='alert alert-danger'><h5>Erro na preparação da query de usuário: " . $obj->error . "</h5></span>");
    }

    $stmt->bind_param("ss", $nome, $senha);
    if (!$stmt->execute()) {
        die("<span class='alert alert-danger'><h5>Erro ao cadastrar o usuário: " . $stmt->error . "</h5></span>");
    }

    if ($stmt->affected_rows > 0) {
        $id_usuario = $obj->insert_id;

        $query_contato = "INSERT INTO contato (id_usuario, telefone, email) VALUES (?, ?, ?)";
        $stmt_contato = $obj->prepare($query_contato);
        
        if (!$stmt_contato) {
            die("<span class='alert alert-danger'><h5>Erro na preparação da query de contato: " . $obj->error . "</h5></span>");
        }

        $stmt_contato->bind_param("iss", $id_usuario, $telefone, $email);
        if (!$stmt_contato->execute()) {
            die("<span class='alert alert-danger'><h5>Erro ao cadastrar o contato: " . $stmt_contato->error . "</h5></span>");
        }

        $query_cidadao = "INSERT INTO cidadao (id_usuario, cpf, data_nasc, endereco_cep, endereco_rua, endereco_numero, endereco_bairro, endereco_cidade, endereco_estado, endereco_pais, endereco_complemento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_cidadao = $obj->prepare($query_cidadao);
        
        if (!$stmt_cidadao) {
            die("<span class='alert alert-danger'><h5>Erro na preparação da query de cidadão: " . $obj->error . "</h5></span>");
        }

        $complemento = !empty($complemento) ? $complemento : null;
        $stmt_cidadao->bind_param("issssssssss", $id_usuario, $cpf, $data_nascimento, $cep, $rua, $numero, $bairro, $cidade, $estado, $pais, $complemento);
        if (!$stmt_cidadao->execute()) {
            die("<span class='alert alert-danger'><h5>Erro ao cadastrar o cidadão: " . $stmt_cidadao->error . "</h5></span>");
        }

        if ($stmt_cidadao->affected_rows > 0 && $stmt_contato->affected_rows > 0) {
            $descricao = "Perfil de cidadão";
            $foto = null;

            $query_perfil = "INSERT INTO perfil (id_usuario, descricao, foto) VALUES (?, ?, ?)";
            $stmt_perfil = $obj->prepare($query_perfil);

            if (!$stmt_perfil) {
                die("<span class='alert alert-danger'><h5>Erro na preparação da query de perfil: " . $obj->error . "</h5></span>");
            }

            $stmt_perfil->bind_param("iss", $id_usuario, $descricao, $foto);
            if (!$stmt_perfil->execute()) {
                die("<span class='alert alert-danger'><h5>Erro ao cadastrar o perfil: " . $stmt_perfil->error . "</h5></span>");
            }

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
                <form id="form" name="form" method="POST" action="register-user.php">
                    <div class="full-inputBox">
                        <label for="name"><b>Nome: *</b></label>
                        <input type="text" id="name" name="name" class="full-inputUser required" data-type="nome" data-required="true" placeholder="Insira seu nome completo">
                        <span class="span-required">Nome não pode conter números e caracteres especiais.</span>
                    </div>

                    <div class="container-row">
                        <div class="mid-inputBox">
                            <label for="cpf"><b>CPF: *</b></label>
                            <input type="text" name="cpf" id="cpf" class="mid-inputUser required" data-type="CPF" data-required="true" placeholder="XXX.XXX.XXX-XX">
                            <span class="span-required">Por faovr, insira um CPF válido</span>
                        </div>

                        <div class="mid-inputBox">
                            <label for="birthYear"><b>Data de Nascimento: *</b></label>
                            <input type="text" name="birthYear" id="birthYear" class="mid-inputUser required" data-type="data de nascimento" data-required="true" placeholder="DD/MM/AAAA">
                            <span class="span-required">Insira uma data de nascimento válida.</span>
                        </div>
                    </div>

                    <div class="container-row">
                        <div class="mid-inputBox">
                            <label for="telephone"><b>Telefone: *</b></label>
                            <input type="text" name="telephone" id="telephone" class="mid-inputUser required" data-type="telefone" data-required="true" placeholder="(XX) XXXXX-XXXX">
                            <span class="span-required">Insira um telefone válido</span>
                        </div>

                        <div class="mid-inputBox">
                            <label for="text"><b>E-mail: *</b></label>
                            <input type="text" id="email" name="email"class="full-inputUser required" data-type="e-mail" data-required="true" placeholder="exemplo@gmail.com">
                            <span class="span-required">Insira um e-mail válido!</span>
                        </div>
                    </div>
            
                    <div class="full-inputBox">
                            <label for="password"><b>Senha: *</b></label>
                            <input type="password" name="password" id="password" class="full-inputUser required" data-type="senha" data-required="true" placeholder="Crie uma senha">
                            <span class="span-required">Sua senha deve conter no mínimo 8 caracteres, combinando letras maiúsculas, minúsculas, números e símbolos especiais.</span>
                    </div>

                    <div class="full-inputBox">
                        <label for="confirm-pass"><b>Confirme sua senha: *</b></label>
                        <input type="password" name="confirm-pass" id="confirm-pass" class="full-inputUser required" data-type="confirmar senha" data-required="true" placeholder="Repita a senha">
                        <span class="span-required">As senhas não coincidem.</span>
                    </div>

                    <h1>Endereço</h1>
                    
                    <div class="container-row">
                        <div class="mid-inputBox">
                            <label for="CEP"><b>CEP: *</b></label>
                            <input type="text" name="CEP" id="CEP" class="mid-inputUser required" data-type="CEP" data-required="true" placeholder="XXXXX-XXX">
                            <span class="span-required">Por favor, insira um CEP válido</span>
                        </div>
                        <div class="mid-inputBox">
                            <label for="road"><b>Rua: *</b></label>
                            <input type="text" name="road" id="road" class="mid-inputUser required" data-type="rua" data-required="true" placeholder="Insira o nome da rua">
                            <span class="span-required"> Rua não pode conter caracteres especias.</span>
                        </div>

                        <div class="mid-inputBox">
                            <label for="num"><b>Número: *</b></label>
                            <input type="text" name="num" id="num" class="mid-inputUser required" data-type="número" data-required="true" placeholder="Insira o número">
                            <span class="span-required">Número não pode conter letras ou caracteres especiais.</span>
                        </div>
                    </div>

                    <div class="container-row">
                        <div class="mid-inputBox">
                            <label for="neighborhood"><b>Bairro: *</b></label>
                            <input type="text" name="neighborhood" id="neighborhood" class="mid-inputUser required" data-type="bairro" data-required="true" placeholder="Insira o bairro">
                            <span class="span-required">Bairro não pode conter números ou caracteres especiais.</span>
                        </div>
                        <div class="mid-inputBox">
                            <label for="city"><b>Cidade: *</b></label>
                            <input type="text" name="city" id="city" class="mid-inputUser required" data-type="cidade" data-required="true" placeholder="Insira a cidade">
                            <span class="span-required">Cidade não pode conter números ou caracteres especiais.</span>
                        </div>
                    </div>

                    <div class="container-row" >

                    <div class="mid-inputBox">
                        <label for="state"><b>Estado: *</b></label>
                        <select name="state" id="state" class="mid-inputUser required" data-type="estado" data-required="true">
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


                        <div class="mid-inputBox">
                            <label for="country"><b>País: *</b></b></label>
                            <input type="text" name="country" id="country" class="mid-inputUser required" data-type="país" data-required="true" placeholder="Insira o país">
                            <span class="span-required">País não pode conter números ou caracteres especiais.</span>
                        </div>
                    </div>

                    <div class="full-inputBox complement">
                        <label for="complement"><b>Complemento: (opcional)</b></label>
                        <input type="text" name="complement" id="complement" class="full-inputUser" placeholder="Insira o complemento">
                    </div>

                    <input type="submit" value="Cadastrar-se" class="register-btn" onclick="btnRegisterOnClick(event, this.form)">

                </form>
            </section>
        </section>
    </section>

    <footer class="footer">
        <p>&copy;2025 - PetMap - Onde tem pet, tem PetMap!. Todos os direitos reservados.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../scripts/register-validation.js"></script>
</body>
</html>