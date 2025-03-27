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

                    <input type="submit" value="Cadastrar-se" class="register-btn">

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

