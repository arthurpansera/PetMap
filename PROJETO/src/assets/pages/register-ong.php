<?php
include('../../../conecta_db.php');

session_start();

if (isset($_POST['name'], $_POST['email'], $_POST['cnpj'], $_POST['telephone'], $_POST['password'], $_POST['CEP'], $_POST['road'], $_POST['num'], $_POST['neighborhood'], $_POST['city'], $_POST['state'], $_POST['country'], $_POST['complement'])) {
    $nome = $_POST['name'];
    $email = $_POST['email'];
    $cnpj = $_POST['cnpj'];
    $telefone = $_POST['telephone'];
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

    $query_check_cnpj = "SELECT id_usuario FROM ong WHERE cnpj = ?";
    $stmt_check_cnpj = $obj->prepare($query_check_cnpj);
    $stmt_check_cnpj->bind_param("s", $cnpj);
    $stmt_check_cnpj->execute();
    $stmt_check_cnpj->store_result();


    if ($stmt_check_email->num_rows > 0 || $stmt_check_cnpj->num_rows > 0) {
        $_SESSION['error_message'] = "Usuário já cadastrado!";
        header("Location: register-ong.php");
        exit();
    }

    try {
        $query_usuario = "INSERT INTO usuario (nome, senha) VALUES (?, ?)";
        $stmt_usuario = $obj->prepare($query_usuario);
        $stmt_usuario->bind_param("ss", $nome, $senha);
        $stmt_usuario->execute();

        if ($stmt_usuario->affected_rows > 0) {
            $id_usuario = $obj->insert_id;

            $query_contato = "INSERT INTO contato (id_usuario, telefone, email) VALUES (?, ?, ?)";
            $stmt_contato = $obj->prepare($query_contato);
            $stmt_contato->bind_param("iss", $id_usuario, $telefone, $email);
            $stmt_contato->execute();

            $query_ong = "INSERT INTO ong (id_usuario, cnpj, endereco_cep, endereco_rua, endereco_numero, endereco_bairro, endereco_cidade, endereco_estado, endereco_pais, endereco_complemento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_ong = $obj->prepare($query_ong);
            $complemento = !empty($complemento) ? $complemento : null;
            $stmt_ong->bind_param("isssssssss", $id_usuario, $cnpj, $cep, $rua, $numero, $bairro, $cidade, $estado, $pais, $complemento);
            $stmt_ong->execute();

            if ($stmt_contato->affected_rows > 0 && $stmt_ong->affected_rows > 0) {
                $descricao = "Perfil de ONG";
                $foto = null;

                $query_perfil = "INSERT INTO perfil (id_usuario, descricao, foto) VALUES (?, ?, ?)";
                $stmt_perfil = $obj->prepare($query_perfil);
                $stmt_perfil->bind_param("iss", $id_usuario, $descricao, $foto);
                $stmt_perfil->execute();

                $_SESSION['user_logged_in'] = true;
                $_SESSION['id_usuario'] = $id_usuario;
                header("Location: ../../../index.php");
                exit();
            } else {
                echo "<span class='alert alert-danger'><h5>Erro ao cadastrar dados de contato, ONG ou endereço.</h5></span>";
            }
        } else {
            echo "<span class='alert alert-danger'><h5>Erro ao cadastrar usuário.</h5></span>";
        }
    } catch (Exception $e) {
        echo "<span class='alert alert-danger'><h5>Erro ao cadastrar: " . $e->getMessage() . "</h5></span>";
    } finally {
        $obj->close();
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
    <link rel="stylesheet" href="../../styles/pages/register-ong/register-ong.css">
</head>

<style>
        select.mid-inputUser {
        color: #8A8A8A;
    }
    select.mid-inputUser:focus {
        color: #000000;
    }
    select.mid-inputUser option:checked {
        color: var(--dark-purple);
    }
    select.mid-inputUser option {
        color: #8A8A8A;
    }
</style>

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
            
            <h1>Dados Cadastrais - ONG</h1>

            <section class="input-register">
                <form id="form" name="form" method="POST" action="register-ong.php">
                    <div class="full-inputBox">
                        <label for="name"><b>Nome: *</b></label>
                        <input type="text" id="name" name="name" class="full-inputUser required" placeholder="Insira o nome da ONG" oninput="inputWithoutNumbersValidate(0)">
                        <span class="span-required">Nome não pode conter números e caracteres especiais.</span>
                    </div>
                        
                    <div class="full-inputBox">
                        <label for="email"><b>E-mail: *</b></label>
                        <input type="text" id="email" name="email"class="full-inputUser required" placeholder="exemplo@gmail.com" oninput="emailValidate()">
                        <span class="span-required">Por favor, insira um e-mail válido</span>
                    </div>
            
                    <div class="container-row">
                        <div class="mid-inputBox">
                            <label for="CNPJ"><b>CNPJ: *</b></label>
                            <input type="text" name="cnpj" id="cnpj" class="mid-inputUser required" placeholder="XX.XXX.XXX/XXXX-XX" oninput="cnpjValidate()">
                            <span class="span-required">Por favor, insira um CNPJ válido.</span>
                        </div>

                        <div class="mid-inputBox">
                            <label for="telephone"><b>Telefone: *</b></label>
                            <input type="text" name="telephone" id="telephone" class="mid-inputUser required" placeholder="(XX) XXXXX-XXXX" oninput="telephoneValidate()">
                            <span class="span-required">Por favor, insira um telefone válido</span>
                        </div>
                    </div>
            
                    <div class="container-row">
                        <div class="mid-inputBox">
                            <label for="password"><b>Senha: *</b></label>
                            <input type="password" name="password" id="password" class="full-inputUser required" placeholder="Crie uma senha" oninput="passwordValidate()">
                            <span class="span-required">Sua senha deve conter no mínimo 8 caracteres, combinando letras maiúsculas, minúsculas, números e símbolos especiais.</span>
                        </div>
                        <div class="mid-inputBox">
                            <label for="confirm-pass"><b>Confirme sua senha: *</b></label>
                            <input type="password" name="confirm-pass" id="confirm-pass" class="full-inputUser required" placeholder="Repita a senha" oninput="confirmPasswordValidate() ">
                            <span class="span-required">As senhas não coincidem.</span>
                        </div>
                    </div>

        
        
                    <h1>Endereço</h1>
        
                    <div class="container-row">
                        <div class="mid-inputBox">
                            <label for="CEP"><b>CEP: *</b></label>
                            <input type="text" name="CEP" id="CEP" class="mid-inputUser required" placeholder="XXXXX-XXX" oninput="cepValidate()">
                            <span class="span-required">Por favor, insira um CEP válido</span>
                        </div>

                        <div class="mid-inputBox">
                            <label for="road"><b>Rua: *</b></label>
                            <input type="text" name="road" id="road" class="mid-inputUser required" placeholder="Insira o nome da rua" oninput="roadValidate()">
                            <span class="span-required"> Rua não pode conter caracteres especias.</span>
                        </div>

                        <div class="mid-inputBox">
                            <label for="num"><b>Número: *</b></label>
                            <input type="text" name="num" id="num" class="mid-inputUser required" placeholder="Insira o número" oninput="numValidate()">
                            <span class="span-required">Número não pode conter letras ou caracteres especiais.</span>
                        </div>
                    </div>
            
                    <div class="container-row">
                        <div class="mid-inputBox">
                            <label for="neighborhood"><b>Bairro: *</b></label>
                            <input type="text" name="neighborhood" id="neighborhood" class="mid-inputUser required" placeholder="Insira o bairro" oninput="inputWithoutNumbersValidate(9)">
                            <span class="span-required">Bairro não pode conter números ou caracteres especiais.</span>
                        </div>
                        <div class="mid-inputBox">
                            <label for="city"><b>Cidade: *</b></label>
                            <input type="text" name="city" id="city" class="mid-inputUser required" placeholder="Insira a cidade" oninput="inputWithoutNumbersValidate(10)">
                            <span class="span-required">Cidade não pode conter números ou caracteres especiais.</span>
                        </div>
                    </div>
            
                    <div class="container-row" >

                    <div class="mid-inputBox">
                        <label for="state"><b>Estado: *</b></label>
                        <select name="state" id="state" class="mid-inputUser required">
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
                            <input type="text" name="country" id="country" class="mid-inputUser required" placeholder="Insira o país" oninput="inputWithoutNumbersValidate(12)">
                            <span class="span-required">País não pode conter números ou caracteres especiais.</span>
                        </div>
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
    <script src="../../scripts/pages/register-ong/register-ong.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>