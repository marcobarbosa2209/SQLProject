<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Buscar tipos de contato existentes
$stmtTiposContato = $pdo->prepare("SELECT idTipoContacto, nomeTipoContacto FROM TipoContacto");
$stmtTiposContato->execute();
$tiposContato = $stmtTiposContato->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agencyName = $_POST['name'] ?? '';
    $contactType = $_POST['contact_type'] ?? '';
    $contactValue = $_POST['contact_value'] ?? '';
    $streetAddress = $_POST['street_address'] ?? '';
    $doorNumber = $_POST['door_number'] ?? '';
    $zipCode = $_POST['zip_code'] ?? '';
    $cityName = $_POST['city_name'] ?? '';

    // Validação dos campos obrigatórios
    if (empty($agencyName) || empty($contactType) || empty($contactValue) || empty($streetAddress) || empty($doorNumber) || empty($zipCode) || empty($cityName)) {
        echo newNotification("All fields are required.", "error");
    } else {
        try {
            // Iniciar transação
            $pdo->beginTransaction();

            // Inserir contato
            $stmt = $pdo->prepare("INSERT INTO Contacto (valorContacto, idTipoContacto) VALUES (:valor, :tipo)");
            $stmt->execute([
                'valor' => $contactValue,
                'tipo' => $contactType
            ]);
            $contactId = $pdo->lastInsertId();

            // Inserir agência
            $stmt = $pdo->prepare("INSERT INTO Agencia (nomeAgencia, idContacto) VALUES (:nome, :contacto)");
            $stmt->execute([
                'nome' => $agencyName,
                'contacto' => $contactId
            ]);
            $agencyId = $pdo->lastInsertId();

            // Antes de inserir Morada, garanta que o código postal exista
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM CodigoPostal WHERE codigoPostal = :codigoPostal");
            $stmt->execute(['codigoPostal' => $zipCode]);
            $exists = $stmt->fetchColumn();

            if (!$exists) {
                // Insere o código postal caso não exista
                $stmt = $pdo->prepare("INSERT INTO CodigoPostal (codigoPostal, nomeLocalidade) VALUES (:codigoPostal, :cidade)");
                $stmt->execute(['codigoPostal' => $zipCode, 'cidade' => $cityName]);
            }

            // Insere a morada relacionada à agência
            $stmt = $pdo->prepare("INSERT INTO Morada (nomeMorada, porta, codigoPostal, idAgencia) VALUES (:morada, :porta, :cep, :agencia)");
            $stmt->execute([
                'morada' => $streetAddress,
                'porta' => $doorNumber,
                'cep' => $zipCode,
                'agencia' => $agencyId
            ]);
            $pdo->commit();
            echo newNotification("Agency created successfully!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            echo newNotification("Error creating agency: " . $e->getMessage(), "error");
        }
    }
}
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
                    <h4 class="card-title">Adicionar nova Agência</h4>
                    <p class="card-description">Detalhes da Agência</p>
                    <form class="forms-sample" method="POST" action="">
                      <div class="form-group">
                        <label for="name">Nome da Agência</label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="Nome" required>
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
                      <div class="form-group" id="contactField">
                        <label for="contactValue">Contacto</label>
                        <input type="text" name="contact_value" class="form-control" id="contactValue" placeholder="Email" required>
                      </div>
                      <div class="form-group">
                        <label for="streetAddress">Endereço</label>
                        <input type="text" name="street_address" class="form-control" id="streetAddress" placeholder="Endereço" required>
                      </div>
                      <div class="form-group">
                        <label for="doorNumber">Número de Porta</label>
                        <input type="number" name="door_number" class="form-control" id="doorNumber" placeholder="Número de Porta" required>
                      </div>
                      <div class="form-group">
                        <label for="zipCode">Código Zip</label>
                        <input type="text" name="zip_code" class="form-control" id="zipCode" placeholder="Código Zip" required>
                      </div>
                      <div class="form-group">
                        <label for="cityName">Nome da Cidade</label>
                        <input type="text" name="city_name" class="form-control" id="cityName" placeholder="Nome da Cidade" required>
                      </div>
                      <button type="submit" class="btn btn-primary me-2">Submeter</button>
                      <button type="button" class="btn btn-light" onclick="location.href='../tables/agencies-table.php';">Cancelar</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
      function toggleContactField() {
          const contactType = document.getElementById('contactType').value;
          const contactField = document.getElementById('contactValue');

          if (contactType === '2') { 
              contactField.type = 'tel';
              contactField.placeholder = 'Enter phone number';
          } else if (contactType === '1') { 
              contactField.type = 'email';
              contactField.placeholder = 'Enter email address';
          } else {
              contactField.type = 'text';
              contactField.placeholder = 'Enter value';
          }
      }
      document.addEventListener('DOMContentLoaded', toggleContactField);
    </script>
  </body>
</html>