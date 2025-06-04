<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Erro de Conexão</title>
    <link rel="stylesheet" href="../../styles/pages/database-error/database-error.css" />
</head>
<body>
    <div class="container">
        <p>O <strong>PetMap</strong> está temporariamente fora do ar.<br />
        Por favor, tente novamente mais tarde.</p>
        
        <img src="../images/no-posts-image/sem-posts.png" alt="Ícone de Erro" class="error-image">
        
        <button id="retryBtn">Tentar novamente</button>
    </div>


    <script>
        const button = document.getElementById('retryBtn');
        button.addEventListener('click', function () {
            button.disabled = true;
            button.textContent = 'Carregando...';
            window.location.href = '../../../index.php';
        });
    </script>
</body>
</html>