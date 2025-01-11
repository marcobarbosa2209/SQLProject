  <?php
require 'pages/pdo/connection.php'; // Conexão com o banco de dados
require 'pages/pdo/user_auth.php'; // Função `checkUserAuthentication`
require 'pages/pdo/helpers.php';   // Helper para mensagens de notificação ou outros utilitários
checkUserAuthentication();  

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
  $requestId = intval($_POST['request_id']);
  $action = $_POST['action'];

  try {
      if ($action === 'accept') {
          $stmt = $pdo->prepare("UPDATE PedidoCriacaoConta SET estadoPedidoCriacaoConta = 2 WHERE idPedidoCriacaoConta = :id");
          $stmt->execute(['id' => $requestId]);
          echo "<script>alert('Pedido aceito com sucesso!');</script>";
      } elseif ($action === 'deny') {
          $stmt = $pdo->prepare("DELETE FROM PedidoCriacaoConta WHERE idPedidoCriacaoConta = :id");
          $stmt->execute(['id' => $requestId]);
          echo "<script>alert('Pedido negado e removido com sucesso!');</script>";
      }
  } catch (Exception $e) {
      echo "<script>alert('Erro ao processar o pedido: " . $e->getMessage() . "');</script>";
  }
}

  ?>

  <!DOCTYPE html>
  <html lang="en">
    <head>
      <!-- Required meta tags -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <title>Dashboard</title>
      <!-- plugins:css -->
      <link rel="stylesheet" href="assets/vendors/feather/feather.css">
      <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
      <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
      <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
      <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
      <!-- endinject -->
      <!-- Plugin css for this page -->
      <!-- <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css"> -->
      <link rel="stylesheet" href="assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css">
      <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
      <link rel="stylesheet" type="text/css" href="assets/js/select.dataTables.min.css">
      <!-- End plugin css for this page -->
      <!-- inject:css -->
      <link rel="stylesheet" href="assets/css/style.css">
      <!-- endinject -->
      <link rel="shortcut icon" href="assets/images/favicon.png" />
    </head>
    <body>
        <?php 
        
        require('pages/ui/navbar.php')

        ?>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
          <!-- partial:partials/_sidebar.html -->
          <?php 
        
        require('pages/ui/navsidebar.php')

        ?>
  <!-- partial -->
  <div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        <div class="col-md-12 grid-margin">
          <div class="row">
            <div class="col-12 col-xl-8 mb-4 mb-xl-0">
              <h3 class="font-weight-bold">Bem-vindo Administrador</h3>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
          <div class="card position-relative">
            <div class="card-body">
              <div id="detailedReports" class="carousel slide detailed-report-carousel position-static pt-2" data-bs-ride="carousel">
                <div class="carousel-inner">
                  <div class="carousel-item active">
                    <div class="row">
                      <div class="col-md-4 stretch-card transparent">
                        <div class="card card-light-blue">
                          <a class="nav-link" href="pages/tables/clients-table.php">
                            <div class="card-body">
                              <h3 class="mb-4"><i class="mdi mdi-account"></i>Clients</h3>
                              <h4 class="mb-2">Página de gestão de clientes</h4>
                            </div>
                          </a>
                        </div>
                      </div>
                      <div class="col-md-4 stretch-card transparent">
                        <div class="card card-dark-blue">
                          <a class="nav-link" href="pages/tables/agencies-table.php">
                            <div class="card-body">
                              <h3 class="mb-4"><i class="mdi mdi-briefcase"></i>Agências</h3>
                              <h4 class="mb-2">Página de gestão de Agências</h4>
                            </div>
                          </a>
                        </div>
                      </div>
                      <div class="col-md-4 stretch-card transparent">
                        <div class="card card-tale">
                          <a class="nav-link" href="pages/tables/organizations-table.php">
                            <div class="card-body">
                              <h3 class="mb-4"><i class="mdi mdi-tie"></i> Organizações</h3>
                              <h4 class="mb-2">Página de gestão de Organizações</h4>
                            </div>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="carousel-item">
                    <div class="row">
                      <div class="col-md-4 stretch-card transparent">
                        <div class="card card-tale">
                          <a class="nav-link" href="pages/tables/contracts-table.php">
                            <div class="card-body">
                              <h3 class="mb-4"><i class="mdi mdi-file"></i>Contratos</h3>
                              <h4 class="mb-2">Página de gestão de Contratos</h4>
                            </div>
                          </a>
                        </div>
                      </div>
                      <div class="col-md-4 stretch-card transparent">
                        <div class="card card-light-blue">
                          <a class="nav-link" href="pages/tables/products-table.php">
                            <div class="card-body">
                              <h3 class="mb-4"><i class="mdi mdi-cart"></i>Produtos</h3>
                              <h4 class="mb-2">Página de gestão de Produtos</h4>
                            </div>
                          </a>
                        </div>
                      </div>
                      <div class="col-md-4 stretch-card transparent">
                        <div class="card card-dark-blue">
                          <a class="nav-link" href="pages/tables/licenses-table.php">
                            <div class="card-body">
                              <h3 class="mb-4"><i class="mdi mdi-certificate"></i>Licença</h3>
                              <h4 class="mb-2">Página de gestão de Licença</h4>
                            </div>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="carousel-item">
                    <div class="row">
                      <div class="col-md-10 stretch-card transparent">
                        <div class="card card-light-blue">
                          <a class="nav-link" href="pages/tables/analysis-table.php">
                            <div class="card-body">
                              <h3 class="mb-4"><i class="mdi mdi-chart-bar"></i>Análises</h3>
                              <h4 class="mb-2">Página de Gerenciamento de Análises </h4>
                            </div>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                <a class="carousel-control-prev" href="#detailedReports" role="button" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </a>
                <a class="carousel-control-next" href="#detailedReports" role="button" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </a>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <p class="card-title mb-0">Pedidos de criação de contas</p>
                    <div class="table-responsive">
                      <table class="table table-striped table-borderless">
                          <thead>
                              <tr>
                                  <th>ID</th>
                                  <th>Nome do Utilizador</th>
                                  <th>Email do Utilizador</th>
                                  <th>Tipo de Utilizador</th>
                                  <th>Estado do Pedido</th>
                                  <th>Ações</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php
                              // Busca os pedidos de criação de conta com tipo de utilizador
                              $stmt = $pdo->prepare("
                                  SELECT 
                                      pc.idPedidoCriacaoConta AS idPedido,
                                      pc.estadoPedidoCriacaoConta AS estadoPedido,
                                      u.nomeUtilizador AS nome,
                                      u.emailUtilizador AS email,
                                      tu.nomeTipoUtilizador AS tipoUtilizador
                                  FROM 
                                      PedidoCriacaoConta pc
                                  JOIN 
                                      Utilizador u ON u.idPedidoCriacaoConta = pc.idPedidoCriacaoConta
                                  LEFT JOIN 
                                      TipoUtilizador tu ON u.idTipoUtilizador = tu.idTipoUtilizador
                                  WHERE 
                                      pc.estadoPedidoCriacaoConta NOT LIKE 'accepted'
                              ");
                              $stmt->execute();
                              $pedidos = $stmt->fetchAll();

                              foreach ($pedidos as $pedido): 
                                  // Determinar a classe CSS com base no estado
                                  $estadoClass = '';
                                  if ($pedido['estadoPedido'] === 'pending') {
                                      $estadoClass = 'bg-warning text-dark';
                                  } elseif ($pedido['estadoPedido'] === 'denied') {
                                      $estadoClass = 'bg-danger text-white';
                                  }
                              ?>
                                  <tr>
                                      <td><?= htmlspecialchars($pedido['idPedido']); ?></td>
                                      <td><?= htmlspecialchars($pedido['nome']); ?></td>
                                      <td><?= htmlspecialchars($pedido['email']); ?></td>
                                      <td><?= htmlspecialchars($pedido['tipoUtilizador']); ?></td>
                                      <td>
                                          <span class="badge <?= $estadoClass; ?>">
                                              <?= ucfirst(htmlspecialchars($pedido['estadoPedido'])); ?>
                                          </span>
                                      </td>
                                      <td>
                                          <!-- Botão para aceitar -->
                                          <form method="POST" style="display:inline;" action="accept_requests.php">
                                              <input type="hidden" name="idPedido" value="<?= htmlspecialchars($pedido['idPedido']); ?>">
                                              <button type="submit" class="btn btn-success btn-sm">
                                                  <i class="mdi mdi-check"></i>
                                              </button>
                                          </form>

                                          <!-- Botão para negar -->
                                          <form method="POST" style="display:inline;" action="deny_requests.php">
                                              <input type="hidden" name="idPedido" value="<?= htmlspecialchars($pedido['idPedido']); ?>">
                                              <button type="submit" class="btn btn-danger btn-sm">
                                                  <i class="mdi mdi-close"></i>
                                              </button>
                                          </form>
                                      </td>
                                  </tr>
                              <?php endforeach; ?>
                              <?php if (empty($pedidos)): ?>
                              <tr>
                                  <td colspan="10" class="text-center">Nenhum pedido de criação de conta encontrado.</td>
                              </tr>
                          <?php endif; ?>
                          </tbody>
                      </table>
                  </div>
                  </div>
              </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 grid-margin stretch-card">
              <div class="card">
                  <div class="card-body">
                      <p class="card-title mb-0">Todos os Utilizadores</p>
                      <div class="table-responsive">
                          <table class="table table-striped table-borderless">
                              <thead>
                                  <tr>
                                      <th>ID do Utilizador</th>
                                      <th>Username</th>
                                      <th>Email</th>
                                      <th>Tipo de Utilizador</th>
                                      <th>Ações</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php
                                  $stmt = $pdo->prepare("
                                      SELECT 
                                          u.idUtilizador AS idUtilizador,
                                          u.nomeUtilizador AS username,
                                          u.emailUtilizador AS email,
                                          tu.nomeTipoUtilizador AS tipoUtilizador
                                      FROM 
                                          Utilizador u
                                      LEFT JOIN 
                                          TipoUtilizador tu ON u.idTipoUtilizador = tu.idTipoUtilizador
                                  ");
                                  $stmt->execute();
                                  $utilizadores = $stmt->fetchAll();

                                  foreach ($utilizadores as $utilizador): 
                                      $tipoClass = '';
                                      if ($utilizador['tipoUtilizador'] === 'Administrador') {
                                          $tipoClass = 'bg-dark text-white';
                                      } elseif ($utilizador['tipoUtilizador'] === 'Cliente') {
                                          $tipoClass = 'bg-success text-white';
                                      } elseif ($utilizador['tipoUtilizador'] === 'Agencia') {
                                          $tipoClass = 'bg-warning text-dark';
                                      } elseif ($utilizador['tipoUtilizador'] === 'Organização') {
                                          $tipoClass = 'bg-info text-white';
                                      }
                                  ?>
                                      <tr>
                                          <td><?= htmlspecialchars($utilizador['idUtilizador']); ?></td>
                                          <td><?= htmlspecialchars($utilizador['username']); ?></td>
                                          <td><?= htmlspecialchars($utilizador['email']); ?></td>
                                          <td>
                                              <span class="badge <?= $tipoClass; ?>">
                                                  <?= htmlspecialchars($utilizador['tipoUtilizador']); ?>
                                              </span>
                                          </td>
                                          <td>
                                              <!-- Botão para remover utilizador -->
                                              <form method="POST" action="remove_user.php" onsubmit="return confirm('Tem certeza que deseja remover este utilizador?');">
                                                  <input type="hidden" name="idUtilizador" value="<?= htmlspecialchars($utilizador['idUtilizador']); ?>">
                                                  <button type="submit" class="btn btn-outline-danger btn-sm">
                                                      <i class="mdi mdi-delete"></i> Remover
                                                  </button>
                                              </form>
                                          </td>
                                      </tr>
                                  <?php endforeach; ?>
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>
          </div>
      </div>
        <div class="row">
          <div class="col-md-12 grid-margin">
            <div class="card">
                <div class="card-body">
                  <p class="card-title mb-0">Pesquisa personalizada</p>
                  <form action="custom_query.php" method="post" class="forms-sample">
                    <input type="hidden" name="form_type" value="consulta_1">
                    <div class="form-group">
                        <label for="custom_query">Escreva sua consulta personalizada a fazer na base de dados</label>
                        <input type="text" name="custom_query" class="form-control" id="custom_query" placeholder="Pesquisa personalizada" required>
                    </div>
                    <button type="submit" class="btn btn-primary me-2">Enviar Consulta</button>
                </form>
                </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
    <!-- content-wrapper ends -->
    <!-- partial:partials/_footer.html -->
    <!-- partial -->
  </div>
  <!-- main-panel ends -->
  </div>
  <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->
  <!-- plugins:js -->
  <script src="assets/vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <script src="assets/vendors/chart.js/chart.umd.js"></script>
  <script src="assets/vendors/datatables.net/jquery.dataTables.js"></script>
  <!-- <script src="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script> -->
  <script src="assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js"></script>
  <script src="assets/js/dataTables.select.min.js"></script>
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="assets/js/off-canvas.js"></script>
  <script src="assets/js/template.js"></script>
  <script src="assets/js/settings.js"></script>
  <script src="assets/js/todolist.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="assets/js/jquery.cookie.js" type="text/javascript"></script>
  <script src="assets/js/dashboard.js"></script>
  <!-- <script src="assets/js/Chart.roundedBarCharts.js"></script> -->
  <!-- End custom js for this page-->
  </body>
  </html>