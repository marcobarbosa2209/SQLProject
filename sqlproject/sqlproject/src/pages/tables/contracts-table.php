<?php
require '../pdo/user_auth.php';
checkUserAuthentication();
require '../pdo/connection.php';

// Buscar informações dos contratos
$stmtContratos = $pdo->prepare("
    SELECT 
        ct.idContrato,
        ct.nomeContrato,
        ct.estadoContrato,
        c.nomeCliente,
        a.nomeAgencia,
        o.nomeOrganizacao
    FROM 
        Contrato ct
    LEFT JOIN Cliente c ON ct.idCliente = c.idCliente
    LEFT JOIN Agencia a ON ct.idAgencia = a.idAgencia
    LEFT JOIN Organizacao o ON ct.idOrganizacao = o.idOrganizacao
    ORDER BY ct.idContrato
");
$stmtContratos->execute();
$contratos = $stmtContratos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Contratos</title>
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
                    <p class="card-description">Lista de todos os Contratos</p>
                    <div class="table-responsive">
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th> ID </th>
                            <th> Nome do Contrato </th>
                            <th> Estado </th>
                            <th> Nome do Cliente </th>
                            <th> Nome da Agência </th>
                            <th> Nome da Organização </th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($contratos as $contrato): ?>
                            <tr>
                              <td><?php echo htmlspecialchars($contrato['idContrato']); ?></td>
                              <td><?php echo htmlspecialchars($contrato['nomeContrato']); ?></td>
                              <td>
                                <?php if ($contrato['estadoContrato'] === 'active'): ?>
                                  <span class="badge bg-success">Ativo</span>
                                <?php elseif ($contrato['estadoContrato'] === 'pending'): ?>
                                  <span class="badge bg-warning">Em Espera</span>
                                <?php else: ?>
                                  <span class="badge bg-danger">Inativo</span>
                                <?php endif; ?>
                              </td>
                              <td><?php echo htmlspecialchars($contrato['nomeCliente']); ?></td>
                              <td><?php echo htmlspecialchars($contrato['nomeAgencia']); ?></td>
                              <td><?php echo htmlspecialchars($contrato['nomeOrganizacao']); ?></td>
                            </tr>
                          <?php endforeach; ?>
                          <?php if (empty($contratos)): ?>
                            <tr>
                                <td colspan="10" class="text-center">Nenhum contrato encontrado.</td>
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
                <button type="button" class="btn btn-outline-primary btn-icon-text me-2" onclick="location.href='../forms/add_contract.php';">
                    <i class="ti-plus btn-icon-prepend"></i> Novo Contrato
                </button>
                <button type="button" class="btn btn-outline-secondary btn-icon-text" onclick="location.href='../forms/update_contract.php';">
                    <i class="ti-pencil btn-icon-prepend"></i> Atualizar Contratos
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