<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetMap | Login</title>
    <link rel="stylesheet" href="../../styles/pages/login/login.css">
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
            <section class="login-content show" id="login-content">
                <section class="no-account">
                    <h1>Bem-vindo ao <br> PetMap!</h1>
                    <h3>Ainda não possui uma conta?</h3>
                    <a href="#" id="show-register">Cadastrar-se</a>
                </section>
    
                <section class="login-box">
                    <div class="back-btn">
                        <a href="../../../index.php">Voltar</a>
                    </div>

                    <h1>Login</h1>
                    <label for="email" class="label-input">E-mail: </label>
                    <input type="text" name="email" id="email" class="input-box" placeholder="exemplo@gmail.com" required>
                    <label for="password" class="label-input">Senha:</label>
                    <input type="password" name="password" id="password" class="input-box" placeholder="Insira sua senha" required>
                    <input type="submit" value="Login" class="login-btn">
                </section>
            </section>
    
            <section class="register-content hidden" id="register-content">
               
                <section class="register-box">
                    <div class="back-btn">
                        <a href="../../../index.php">Voltar</a>
                    </div>

                    <h1>Selecione o tipo de Cadastro</h1>
                    <div class="user-options-box">
                        <a class="user-options" href="./register-user.php">Cidadão</a>
                        <a class="user-options" href="./register-ong.php">ONG</a>
                        <a class="user-options" href="./register-adm.php">Moderador</a>

                    </div>
            
                </section>
    
                <section class="has-account">
                    <h1>Bem-vindo ao <br> PetMap!</h1>
                    <h3>Já possui uma conta?</h3>
                    <a id="show-login">Login</a>
                </section>
            </section>
        </section>
    </section>

    <footer class="footer">
        <p>&copy;2025 - PetMap - Onde tem pet, tem PetMap!. Todos os direitos reservados.</p>
    </footer>

    <script src="../../scripts/pages/login/login.js"></script>
</body>
</html>


