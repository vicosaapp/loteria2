<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loteria - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h2>Login do Sistema</h2>
            <form action="includes/auth.php" method="POST">
                <div class="input-group">
                    <label for="username">Usuário:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="tipo">Tipo de Usuário:</label>
                    <select id="tipo" name="tipo" required>
                        <option value="admin">Administrador</option>
                        <option value="revendedor">Revendedor</option>
                        <option value="usuario">Apostador</option>
                    </select>
                </div>
                <div class="input-group">
                    <button type="submit" name="login">Entrar</button>
                </div>
                <?php
                if (isset($_GET['error'])) {
                    echo '<div class="error">Usuário ou senha inválidos!</div>';
                }
                ?>
            </form>
        </div>
    </div>
</body>
</html> 