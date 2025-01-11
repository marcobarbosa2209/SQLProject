<?php
require '../pdo/user_auth.php';
checkUserAuthentication();
require '../pdo/connection.php';

// Buscar informações das organizações
$stmtOrganizacoes = $pdo->prepare("
    SELECT 
        o.idOrganizacao,
        o.nomeOrganizacao,
        COUNT(DISTINCT ct.idContrato) AS numContratos,
        a.nomeAgencia AS nomeAgencia,
        co.valorContacto AS contacto,
        CASE co.idTipoContacto
            WHEN 1 THEN 'Email'
            WHEN 2 THEN 'Telemóvel'
            ELSE 'N/A'
        END AS tipoContacto
    FROM Organizacao o
    LEFT JOIN Contrato ct ON o.idOrganizacao = ct.idOrganizacao
    LEFT JOIN Agencia a ON o.idAgencia = a.idAgencia
    LEFT JOIN Contacto co ON o.idContacto = co.idContacto
    GROUP BY o.idOrganizacao, o.nomeOrganizacao, a.nomeAgencia, co.valorContacto, co.idTipoContacto
    ORDER BY o.idOrganizacao
");

$stmtOrganizacoes->execute();
$organizacoes = $stmtOrganizacoes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Organizações</title>
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
                    <h4 class="card-title">Organizações</h4>
                    <p class="card-description">Lista de todas as Organizações</p>
                    <div class="table-responsive">
                      <table class="table table-striped">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Numero de Contratos</th>
                            <th>Nome da Agência</th>
                            <th>Contacto</th>
                            <th>Tipo de Contacto</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($organizacoes as $organizacao): ?>
                            <tr>
                              <td><?php echo htmlspecialchars($organizacao['idOrganizacao']); ?></td>
                              <td><?php echo htmlspecialchars($organizacao['nomeOrganizacao']); ?></td>
                              <td><?php echo htmlspecialchars($organizacao['numContratos']); ?></td>
                              <td><?php echo htmlspecialchars($organizacao['nomeAgencia'] ?? 'N/A'); ?></td>
                              <td><?php echo htmlspecialchars($organizacao['contacto'] ?? 'N/A'); ?></td>
                              <td><?php echo htmlspecialchars($organizacao['tipoContacto'] ?? 'N/A'); ?></td>
                            </tr>
                          <?php endforeach; ?>
                          <?php if (empty($organizacoes)): ?>
                            <tr>
                                <td colspan="10" class="text-center">Nenhuma organização encontrada.</td>
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
              <button type="button" class="btn btn-outline-primary btn-icon-text me-2" onclick="location.href='../forms/add_organization.php';">
                <i class="ti-plus btn-icon-prepend"></i> Nova Organização
              </button>
              <button type="button" class="btn btn-outline-secondary btn-icon-text" onclick="location.href='../forms/update_organization.php';">
                <i class="ti-pencil btn-icon-prepend"></i> Atualizar Organizações
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