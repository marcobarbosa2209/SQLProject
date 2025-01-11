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

$notification = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organizationName = $_POST['name'] ?? '';
    $agencyId = intval($_POST['agency_id']);
    $contactType = intval($_POST['contact_type']);
    $contactValue = $_POST['contact_value'] ?? '';
    $streetAddress = $_POST['street_address'] ?? '';
    $doorNumber = intval($_POST['door_number'] ?? 0);
    $zipCode = $_POST['zip_code'] ?? '';
    $cityName = $_POST['city_name'] ?? '';

    if (empty($organizationName) || empty($agencyId) || empty($contactType) || empty($contactValue) || empty($streetAddress) || empty($doorNumber) || empty($zipCode) || empty($cityName)) {
        $notification = newNotification("Todos os campos são obrigatórios.", "error");
    } else {
        try {
            $pdo->beginTransaction();

            // Garantir que o código postal existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM CodigoPostal WHERE codigoPostal = :codigoPostal");
            $stmt->execute(['codigoPostal' => $zipCode]);
            $exists = $stmt->fetchColumn();

            if (!$exists) {
                $stmt = $pdo->prepare("INSERT INTO CodigoPostal (codigoPostal, nomeLocalidade) VALUES (:codigoPostal, :cidade)");
                $stmt->execute(['codigoPostal' => $zipCode, 'cidade' => $cityName]);
            }

            // Criar contato
            $stmt = $pdo->prepare("
                INSERT INTO Contacto (valorContacto, idTipoContacto)
                VALUES (:valor, :tipo)
            ");
            $stmt->execute([
                'valor' => $contactValue,
                'tipo' => $contactType
            ]);
            $newContactId = $pdo->lastInsertId();

            // Criar morada
            $stmt = $pdo->prepare("
                INSERT INTO Morada (nomeMorada, porta, codigoPostal)
                VALUES (:morada, :porta, :codigoPostal)
            ");
            $stmt->execute([
                'morada' => $streetAddress,
                'porta' => $doorNumber,
                'codigoPostal' => $zipCode
            ]);
            $newAddressId = $pdo->lastInsertId();

            // Criar organização
            $stmt = $pdo->prepare("
                INSERT INTO Organizacao (nomeOrganizacao, idAgencia, idContacto)
                VALUES (:nome, :idAgencia, :idContacto)
            ");
            $stmt->execute([
                'nome' => $organizationName,
                'idAgencia' => $agencyId,
                'idContacto' => $newContactId
            ]);

            $pdo->commit();
            $notification = newNotification("Organização criada com sucesso!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            $notification = newNotification("Erro ao criar a organização: " . $e->getMessage(), "error");
        }
    }
}
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
                    <h4 class="card-title">Criar nova Organização</h4>
                    <p class="card-description">Info da Organization</p>
                    <?php if (!empty($notification)) echo $notification; ?>
                    <form class="forms-sample" method="POST" action="">
                      <div class="form-group">
                          <label for="organizationName">Nome da Organização</label>
                          <input type="text" name="name" class="form-control" id="organizationName" placeholder="Nome da Organização" required>
                      </div>
                      <div class="form-group">
                          <label for="agencySelect">Agência Associada</label>
                          <select name="agency_id" class="form-select" id="agencySelect" required>
                              <option value="" disabled selected>Selecione uma agência</option>
                              <?php foreach ($agencias as $agencia): ?>
                                  <option value="<?php echo $agencia['idAgencia']; ?>">
                                      <?php echo htmlspecialchars($agencia['nomeAgencia']); ?>
                                  </option>
                              <?php endforeach; ?>
                          </select>
                      </div>
                      <div class="form-group">
                          <label for="contactType">Tipo de Contato</label>
                          <select name="contact_type" class="form-select" id="contactType" onchange="toggleContactField()" required>
                              <option value="" disabled selected>Selecione o tipo de contato</option>
                              <?php foreach ($tiposContato as $tipo): ?>
                                  <option value="<?php echo $tipo['idTipoContacto']; ?>">
                                      <?php echo htmlspecialchars($tipo['nomeTipoContacto']); ?>
                                  </option>
                              <?php endforeach; ?>
                          </select>
                      </div>
                      <div class="form-group">
                          <label for="contactValue">Valor de Contato</label>
                          <input type="text" name="contact_value" class="form-control" id="contactValue" placeholder="Digite o valor de contato" required>
                      </div>
                      <div class="form-group">
                          <label for="streetAddress">Endereço</label>
                          <input type="text" name="street_address" class="form-control" id="streetAddress" placeholder="Rua" required>
                      </div>
                      <div class="form-group">
                          <label for="doorNumber">Número</label>
                          <input type="number" name="door_number" class="form-control" id="doorNumber" placeholder="Número da Porta" required>
                      </div>
                      <div class="form-group">
                          <label for="zipCode">Código Postal</label>
                          <input type="text" name="zip_code" class="form-control" id="zipCode" placeholder="Código Postal" required>
                      </div>
                      <div class="form-group">
                          <label for="cityName">Cidade</label>
                          <input type="text" name="city_name" class="form-control" id="cityName" placeholder="Cidade" required>
                      </div>
                      <button type="submit" class="btn btn-primary me-2">Submeter</button>
                      <button type="button" class="btn btn-light" onclick="location.href='../tables/organizations-table.php';">Cancelar</button>
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
          const contactValue = document.getElementById('contactValue');
          if (contactType === '2') {
              contactValue.type = 'tel';
              contactValue.placeholder = 'Introduza o número de telemóvel';
          } else if (contactType === '1') {
              contactValue.type = 'email';
              contactValue.placeholder = 'Introduza o enedereço de email';
          } else {
              contactValue.type = 'text';
              contactValue.placeholder = 'Valor';
          }
      }
    </script>
  </body>
</html>