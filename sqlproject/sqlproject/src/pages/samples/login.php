<?php
require '../pdo/login_register.php';

$notification = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $notification = newNotification("Both email/username and password are required.", "error");
    } else {
        $notification = loginUser($identifier, $password);
    }
}

echo $notification;
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login</title>
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
  </head>
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
          <div class="row w-100 mx-0">
            <div class="col-lg-4 mx-auto">
              <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                <div class="brand-logo">
                  <img src="../../assets/images/logo.svg" alt="logo">
                </div>
                <h4>Olá! Vamos começar</h4>
                <h6 class="font-weight-light">Fça Sign in para continuar.</h6>
                <form class="pt-3" method="POST" action="">
                  <div class="form-group">
                    <input type="text" name="email" class="form-control form-control-lg" placeholder="Utilizador/Email" required>
                  </div>
                  <div class="form-group">
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                  </div>
                  <div class="mt-3 d-grid gap-2">
                    <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">SIGN IN</button>
                  </div>
                  <div class="text-center mt-4 font-weight-light"> Não tem conta? <a href="register.php" class="text-primary">Crie aqui</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../../assets/js/template.js"></script>
  </body>
</html>