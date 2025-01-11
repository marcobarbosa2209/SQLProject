<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Registrar</title>
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
  </head>
  <body>
    <?php
    require '../pdo/login_register.php';

    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = '';
    $confirmPassword = '';
    $userType = $_POST['user_type'] ?? 1; // Default to administrator if not set
    $notification = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($password !== $confirmPassword) {
            $notification = newNotification("Passwords do not match!", "error");
        } else {
          if (strlen($password) > 45 or strlen($password) < 6) $notification = newNotification("Password must be between 6 and 45 characters!", "error");
          else $notification = registerUser($email, $username, $password, $userType);
        }
    }

    echo $notification;
    ?>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
          <div class="row w-100 mx-0">
            <div class="col-lg-4 mx-auto">
              <div class="auth-form-light text-left py-5 px-4 px-sm-5">
                <div class="brand-logo">
                  <img src="../../assets/images/logo.svg" alt="logo">
                </div>
                <h4>Novo aqui?</h4>
                <h6 class="font-weight-light">Fazer Sign up é fácil. Só é preciso uns poucos passos</h6>
                <form class="pt-3" method="POST" action="">
                  <div class="form-group">
                    <input type="text" name="username" class="form-control form-control-lg" placeholder="Utilizador" value="<?php echo htmlspecialchars($username); ?>" required>
                  </div>
                  <div class="form-group">
                    <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                  </div>
                  <div class="form-group">
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                  </div>
                  <div class="form-group">
                    <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Confirmar Password" required>
                  </div>
                  <div class="form-group">
                    <label for="exampleSelectUserType">Tipo de Utilizador</label>
                    <select class="form-select" name="user_type" id="exampleSelectUserType" required>
                      <option value="1" <?php echo $userType == 1 ? 'selected' : ''; ?>>Administrador</option>
                      <option value="2" <?php echo $userType == 2 ? 'selected' : ''; ?>>Cliente</option>
                      <option value="3" <?php echo $userType == 3 ? 'selected' : ''; ?>>Organização</option>
                      <option value="4" <?php echo $userType == 4 ? 'selected' : ''; ?>>Agência</option>
                    </select>
                  </div>
                  <div class="mt-3 d-grid gap-2">
                    <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">SIGN UP</button>
                  </div>
                  <div class="text-center mt-4 font-weight-light"> Já tem uma conta? <a href="login.php" class="text-primary">Faça Login aqui!</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>