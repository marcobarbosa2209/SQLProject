<?php 
  require '../pdo/user_auth.php';
  checkUserAuthentication();
  require '../pdo/connection.php';

  $stmt = $pdo->prepare("
    SELECT 
      c.idCliente,
      c.nomeCliente,
      COUNT(ct.idContrato) AS numContratos,
      tc.nomeTipoCliente AS tipoCliente,
      a.nomeAgencia AS nomeAgencia,
      co.valorContacto AS contacto
    FROM Cliente c
    LEFT JOIN Contrato ct ON c.idCliente = ct.idCliente
    LEFT JOIN TipoCliente tc ON c.idTipoCliente = tc.idTipoCliente
    LEFT JOIN Agencia a ON c.idAgencia = a.idAgencia
    LEFT JOIN Contacto co ON c.idContacto = co.idContacto
    GROUP BY c.idCliente, c.nomeCliente, tc.nomeTipoCliente, a.nomeAgencia, co.valorContacto
    ORDER BY c.idCliente
  ");
  $stmt->execute();
  $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en"><head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Clientes</title>
  <link rel="stylesheet" href="../../assets/vendors/feather/feather.css">
  <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="../../assets/vendors/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="../../assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="shortcut icon" href="../../assets/images/favicon.png">
</head>
<body>
  <div class="container-scroller">
    <?php require('../ui/navbar.php'); ?>
    <div class="container-fluid page-body-wrapper">
      <?php require('../ui/navsidebar.php'); ?>
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Clientes</h4>
                  <p class="card-description"> Lista de Clientes </p>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th> ID </th>
                          <th> Nome </th>
                          <th> Numero de Contratos </th>
                          <th> Tipo </th>
                          <th> Nome da Agência </th>
                          <th> Contacto </th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($cliente['idCliente']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['nomeCliente']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['numContratos']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['tipoCliente'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($cliente['nomeAgencia'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($cliente['contacto'] ?? 'N/A'); ?></td>
                          </tr>
                        <?php endforeach; ?>
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="10" class="text-center">Nenhum cliente encontrado.</td>
                            </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-12 grid-margin stretch-card">
              <button type="button" class="btn btn-outline-primary btn-icon-text me-2" onclick="location.href='../forms/add_client.php';">
                  <i class="ti-plus btn-icon-prepend"></i> Novo Cliente
              </button>
              <button type="button" class="btn btn-outline-secondary btn-icon-text" onclick="location.href='../forms/update_client.php';">
                  <i class="ti-pencil btn-icon-prepend"></i> Atualizar Cliente
              </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="../../assets/js/off-canvas.js"></script>
  <script src="../../assets/js/template.js"></script>
</body>
</html>