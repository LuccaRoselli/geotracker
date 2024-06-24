<?php
// Iniciar sessão
session_start();

// Checar se o usuário está logado, se já estiver redirecione para a index
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index");
    exit;
}

// Incluir config
require_once "controllers/config.php";
require_once "controllers/php_login.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>GeoTracker - Seu sistema de ocorrências!</title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="Lucca" />
    <!-- Favicon icon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- fontawesome icon -->
    <link rel="stylesheet" href="assets/fonts/fontawesome/css/fontawesome-all.min.css">
    <!-- animation css -->
    <link rel="stylesheet" href="assets/plugins/animation/css/animate.min.css">
    <!-- vendor css -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<style>
.help-block {
    color: red;
}
</style>

<body>
    <div class="auth-wrapper">
        <div class="auth-content">
            <div class="card">
                <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <img src="assets/images/logo.png" alt="Logo" class="img-fluid mx-auto d-block"
                                style="max-width: 150px;">
                        </div>
                        <h3 class="mb-4">Realize seu login</h3>
                        <div class="input-group mb-3" <?= (!empty($email_err)) ? 'has-error' : ''; ?>>
                            <input type="email" class="form-control" name="email" placeholder="Email..."
                                value="<?= $email; ?>">
                        </div>
                        <span class="help-block"><?= $email_err; ?></span>
                        <div class="input-group mb-4" <?= (!empty($password_err)) ? 'has-error' : ''; ?>>
                            <input type="password" class="form-control" name="password" placeholder="Senha...">
                        </div>
                        <span class="help-block"><?= $password_err; ?></span>
                        <div class="form-group text-left">
                            <div class="checkbox checkbox-fill d-inline">
                                <input type="checkbox" name="checkbox-fill-1" id="checkbox-fill-a1" checked="">
                                <label for="checkbox-fill-a1" class="cr"> Lembrar-me</label>
                            </div>
                        </div>
                        <button class="btn btn-primary shadow-2 mb-4" type="submit">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Required Js -->
    <script src="assets/js/vendor-all.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
    <?php if (isset($_SESSION['login_err'])): ?>
    toastr.error('<?= $_SESSION['login_err'] ?>', 'ERRO DE LOGIN');
    <?php unset($_SESSION['login_err']); ?>
    <?php endif; ?>
    </script>
</body>

</html>