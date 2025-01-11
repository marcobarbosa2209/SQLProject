<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Buscar agências existentes
$stmtAgencias = $pdo->prepare("SELECT idAgencia, nomeAgencia FROM Agencia");
$stmtAgencias->execute();
$agencias = $stmtAgencias->fetchAll(PDO::FETCH_ASSOC);

// Buscar tipos de contato existentes
$stmtTiposContato = $pdo->prepare("SELECT idTipoContacto, nomeTipoContacto FROM TipoContacto");
$stmtTiposContato->execute();
$tiposContato = $stmtTiposContato->fetchAll(PDO::FETCH_ASSOC);

// Buscar tipos de cliente existentes
$stmtTiposCliente = $pdo->prepare("SELECT idTipoCliente, nomeTipoCliente FROM TipoCliente");
$stmtTiposCliente->execute();
$tiposCliente = $stmtTiposCliente->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientName = $_POST['name'] ?? '';
    $clientType = $_POST['client_type'] ?? '';
    $contactType = $_POST['contact_type'] ?? '';
    $contactValue = $_POST['contact_value'] ?? '';
    $agencyId = $_POST['associated_agency'] ?? null;

    if (empty($clientName) || empty($clientType) || empty($contactType) || empty($contactValue) || empty($agencyId)) {
        echo newNotification("All fields are required.", "error");
    } else {
        try {
            // Inserir o contato
            $stmt = $pdo->prepare("INSERT INTO Contacto (valorContacto, idTipoContacto) VALUES (:valor, :tipo)");
            $stmt->execute([
                'valor' => $contactValue,
                'tipo' => $contactType
            ]);
            $contactId = $pdo->lastInsertId();

            // Inserir o cliente
            $stmt = $pdo->prepare("
                INSERT INTO Cliente (nomeCliente, idAgencia, idTipoCliente, idContacto)
                VALUES (:nome, :agencia, :tipo, :contacto)
            ");
            $stmt->execute([
                'nome' => $clientName,
                'agencia' => $agencyId,
                'tipo' => $clientType,
                'contacto' => $contactId
            ]);

            echo newNotification("Client created successfully!", "success");
        } catch (Exception $e) {
            echo newNotification("Error creating client: " . $e->getMessage(), "error");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Clientes</title>
    <link rel="stylesheet" href="../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../assets/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="../../assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
  </head>
  <body>
    <div class="container-scroller">
      <?php require('../ui/navbar.php'); ?>
      <div class="container-fluid page-body-wrapper">
        <?php require('../ui/navsidebar.php'); ?>
        <div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title">Criar novo Cliente</h4>
                    <p class="card-description">Info do Cliente</p>
                    <form class="forms-sample" method="POST" action="">
                      <div class="form-group">
                        <label for="name">Nome</label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="Nome" required>
                      </div>
                      <div class="form-group">
                        <label for="clientType">Tipo de Cliente</label>
                        <select name="client_type" class="form-select" id="clientType" required>
                            <?php foreach ($tiposCliente as $tipo): ?>
                                <option value="<?php echo $tipo['idTipoCliente']; ?>">
                                    <?php echo htmlspecialchars($tipo['nomeTipoCliente']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="contactType">Tipo de Contacto</label>
                        <select name="contact_type" class="form-select" id="contactType" onchange="toggleContactField()" required>
                            <?php foreach ($tiposContato as $tipo): ?>
                                <option value="<?php echo $tipo['idTipoContacto']; ?>">
                                    <?php echo htmlspecialchars($tipo['nomeTipoContacto']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="form-group" id="phoneField">
                          <label for="contactValuePhone">Contacto</label>
                          <input type="text" name="contact_value" class="form-control" id="contactValuePhone" placeholder="Valor" required>
                      </div>
                      <div class="form-group">
                        <label for="associatedAgency">Agência Associada</label>
                        <select name="associated_agency" class="form-select" id="associatedAgency" required>
                            <?php foreach ($agencias as $agencia): ?>
                                <option value="<?php echo $agencia['idAgencia']; ?>">
                                    <?php echo htmlspecialchars($agencia['nomeAgencia']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                      </div>
                      <button type="submit" class="btn btn-primary me-2">Submeter</button>
                      <button type="button" class="btn btn-light" onclick="location.href='../tables/clients-table.php';">Cancelar</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <script>
      function toggleContactField() {
          const contactType = document.getElementById('contactType').value;
          const contactValue = document.getElementById('contactValuePhone');
          
          // Ajusta o tipo de input com base no tipo de contato selecionado
          if (contactType === '2') { 
              contactValue.type = 'tel';
              contactValue.placeholder = 'Enter phone number';
          } else if (contactType === '1') { 
              contactValue.type = 'email';
              contactValue.placeholder = 'Enter email address';
          } else {
              contactValue.type = 'text';
              contactValue.placeholder = 'Enter value';
          }
      }

      // Executa ao carregar a página para ajustar o tipo inicial com base no valor selecionado
      document.addEventListener('DOMContentLoaded', toggleContactField);
    </script>
  </body>
</html>