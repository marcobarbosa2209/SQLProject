<?php
require '../pdo/user_auth.php';
checkUserAuthentication();
require '../pdo/connection.php';

// Buscar informações das agências
$stmtAgencias = $pdo->prepare("
    SELECT 
        a.idAgencia,
        a.nomeAgencia,
        COUNT(DISTINCT c.idCliente) AS numClientes,
        COUNT(DISTINCT ct.idContrato) AS numContratos,
        COUNT(DISTINCT o.idOrganizacao) AS numOrganizacoes,
        co.valorContacto AS contacto,
        CASE co.idTipoContacto
            WHEN 1 THEN 'Email'
            WHEN 2 THEN 'Telemóvel'
            ELSE 'N/A'
        END AS tipoContacto
    FROM Agencia a
    LEFT JOIN Cliente c ON a.idAgencia = c.idAgencia
    LEFT JOIN Contrato ct ON a.idAgencia = ct.idAgencia
    LEFT JOIN Organizacao o ON a.idAgencia = o.idAgencia
    LEFT JOIN Contacto co ON a.idContacto = co.idContacto
    GROUP BY a.idAgencia, a.nomeAgencia, co.valorContacto, co.idTipoContacto
    ORDER BY a.idAgencia
");
$stmtAgencias->execute();
$agencias = $stmtAgencias->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Agências</title>
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
                    <h4 class="card-title">Agências</h4>
                    <p class="card-description"> Lista de todas as Agências </p>
                    <div class="table-responsive">
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th> ID </th>
                            <th> Nome </th>
                            <th> Numero de Clientes </th>
                            <th> Numero de Contratos </th>
                            <th> Numero de Organizações </th>
                            <th> Contacto </th>
                            <th> Tipo de Contacto </th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($agencias as $agencia): ?>
                            <tr>
                              <td><?php echo htmlspecialchars($agencia['idAgencia']); ?></td>
                              <td><?php echo htmlspecialchars($agencia['nomeAgencia']); ?></td>
                              <td><?php echo htmlspecialchars($agencia['numClientes']); ?></td>
                              <td><?php echo htmlspecialchars($agencia['numContratos']); ?></td>
                              <td><?php echo htmlspecialchars($agencia['numOrganizacoes']); ?></td>
                              <td><?php echo htmlspecialchars($agencia['contacto'] ?? 'N/A'); ?></td>
                              <td><?php echo htmlspecialchars($agencia['tipoContacto'] ?? 'N/A'); ?></td>
                            </tr>
                          <?php endforeach; ?>
                          <?php if (empty($agencias)): ?>
                              <tr>
                                  <td colspan="10" class="text-center">Nenhuma agência encontrada.</td>
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
                <button type="button" class="btn btn-outline-primary btn-icon-text me-2" onclick="location.href='../forms/add_agency.php';">
                    <i class="ti-plus btn-icon-prepend"></i> Nova Agência
                </button>
                <button type="button" class="btn btn-outline-secondary btn-icon-text" onclick="location.href='../forms/update_agency.php';">
                    <i class="ti-pencil btn-icon-prepend"></i> Atualizar Agências
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