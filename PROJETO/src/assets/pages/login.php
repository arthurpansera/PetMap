<?php
    include('../../../conecta_db.php');

    session_start();

    if (isset($_GET['expired']) && $_GET['expired'] == 1) {
        $_SESSION['error_message'] = 'Sua sessão expirou por inatividade.';
    }
    
    if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    $_SESSION['success_message'] = "Senha redefinida com sucesso!";
    }

    if (isset($_SESSION['login_error'])) {
        $_SESSION['error_message'] = 'Usuário e/ou senha incorretos.';
        unset($_SESSION['login_error']);
    }

    if (isset($_SESSION['error_message'])) {
        $mensagem = addslashes($_SESSION['error_message']);
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Erro!',
                    text: '{$mensagem}',
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

    if (isset($_SESSION['success_message'])) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Sucesso!',
                    text: '{$_SESSION['success_message']}',
                    icon: 'success',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#7A00CC',
                    allowOutsideClick: true,
                    heightAuto: false
                });
            });
        </script>";
        unset($_SESSION['success_message']);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $obj = conecta_db();

        if (!$obj) {
            die("Erro ao conectar ao banco de dados.");
        }

        $query = "SELECT u.id_usuario, u.senha 
                FROM usuario u
                JOIN contato c ON u.id_usuario = c.id_usuario 
                WHERE c.email = ?";
        $stmt = $obj->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['senha'])) {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['id_usuario'] = $user['id_usuario'];
                header("Location: ../../../index.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Usuário e/ou senha incorretos";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Usuário e/ou senha incorretos";
            header("Location: login.php");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetMap | Login</title>
    <link rel="stylesheet" href="../../styles/pages/login/login.css?v=<?php echo time(); ?>">
</head>
<body>
    </style>
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
                    <form id="form" name="form" method="POST" action="login.php">
                        <label for="email" class="label-input">E-mail: </label>
                        <input type="text" name="email" id="email" class="input-box" placeholder="exemplo@gmail.com" required>
                        <label for="password" class="label-input">Senha:</label>
                        <input type="password" name="password" id="password" class="input-box-1" placeholder="Insira sua senha" required>
                        <a class="forgot-password" href="forgot-password.php">Esqueci minha senha</a>
                        <input type="submit" value="Login" class="login-btn">
                      
                    </form>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../scripts/pages/login/login.js"></script>

</body>
</html>