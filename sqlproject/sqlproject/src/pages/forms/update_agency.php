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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_agency'])) {
    $agencyId = intval($_POST['delete_agency']);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM Agencia WHERE idAgencia = :idAgencia");
        $stmt->execute(['idAgencia' => $agencyId]);

        $pdo->commit();
        $notification = newNotification("Agência apagada com sucesso!", "success");
    } catch (Exception $e) {
        $pdo->rollBack();
        $notification = newNotification("Erro ao apagar a agência: " . $e->getMessage(), "error");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agency_id'])) {
  $agencyId = intval($_POST['agency_id']);
  $agencyName = $_POST['name'] ?? '';
  $contactType = intval($_POST['contact_type'] ?? '');
  $contactValue = $_POST['contact_value'] ?? '';
  $streetAddress = $_POST['street_address'] ?? '';
  $doorNumber = intval($_POST['door_number'] ?? 0);
  $zipCode = $_POST['zip_code'] ?? '';
  $cityName = $_POST['city_name'] ?? '';

  if (empty($agencyName) || empty($contactType) || empty($contactValue) || empty($streetAddress) || empty($doorNumber) || empty($zipCode) || empty($cityName)) {
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

          // Verificar se a morada já existe
          $stmt = $pdo->prepare("SELECT COUNT(*) FROM Morada WHERE idAgencia = :idAgencia");
          $stmt->execute(['idAgencia' => $agencyId]);
          $moradaExists = $stmt->fetchColumn();

          if ($moradaExists) {
              // Atualizar morada existente
              $stmt = $pdo->prepare("
                  UPDATE Morada 
                  SET nomeMorada = :morada, porta = :porta, codigoPostal = :codigoPostal
                  WHERE idAgencia = :idAgencia
              ");
              $stmt->execute([
                  'morada' => $streetAddress,
                  'porta' => $doorNumber,
                  'codigoPostal' => $zipCode,
                  'idAgencia' => $agencyId
              ]);
          } else {
              // Criar nova morada
              $stmt = $pdo->prepare("
                  INSERT INTO Morada (nomeMorada, porta, codigoPostal, idAgencia)
                  VALUES (:morada, :porta, :codigoPostal, :idAgencia)
              ");
              $stmt->execute([
                  'morada' => $streetAddress,
                  'porta' => $doorNumber,
                  'codigoPostal' => $zipCode,
                  'idAgencia' => $agencyId
              ]);
          }

          // Verificar se o contacto já existe
          $stmt = $pdo->prepare("SELECT idContacto FROM Agencia WHERE idAgencia = :idAgencia");
          $stmt->execute(['idAgencia' => $agencyId]);
          $contactId = $stmt->fetchColumn();

          if ($contactId) {
              // Atualizar contacto existente
              $stmt = $pdo->prepare("
                  UPDATE Contacto 
                  SET valorContacto = :valor, idTipoContacto = :tipo
                  WHERE idContacto = :idContacto
              ");
              $stmt->execute([
                  'valor' => $contactValue,
                  'tipo' => $contactType,
                  'idContacto' => $contactId
              ]);
          } else {
              // Criar novo contacto
              $stmt = $pdo->prepare("
                  INSERT INTO Contacto (valorContacto, idTipoContacto)
                  VALUES (:valor, :tipo)
              ");
              $stmt->execute([
                  'valor' => $contactValue,
                  'tipo' => $contactType
              ]);
              $newContactId = $pdo->lastInsertId();

              // Associar o novo contacto à agência
              $stmt = $pdo->prepare("UPDATE Agencia SET idContacto = :idContacto WHERE idAgencia = :idAgencia");
              $stmt->execute([
                  'idContacto' => $newContactId,
                  'idAgencia' => $agencyId
              ]);
          }

          // Atualizar agência
          $stmt = $pdo->prepare("
              UPDATE Agencia 
              SET nomeAgencia = :nome
              WHERE idAgencia = :idAgencia
          ");
          $stmt->execute([
              'nome' => $agencyName,
              'idAgencia' => $agencyId
          ]);

          $pdo->commit();
          $notification = newNotification("Agência atualizada com sucesso!", "success");
      } catch (Exception $e) {
          $pdo->rollBack();
          $notification = newNotification("Erro ao atualizar a agência: " . $e->getMessage(), "error");
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
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
    <script>
    async function fetchAgencyData(agencyId) {
    if (!agencyId) {
        return;
    }

    try {
        const response = await fetch(`get_agency_data.php?id=${agencyId}`);
        const data = await response.json();

        if (data.error) {
            alert(data.error);
            return;
        }

        // Preenche os campos do formulário com os dados retornados ou valores padrão
        document.getElementById('name').value = data.nomeAgencia || 'N/A';
        document.getElementById('contactType').value = data.idTipoContacto || '';
        document.getElementById('contactValuePhone').value = data.valorContacto || 'N/A';
        document.getElementById('streetAddress').value = data.streetAddress || 'N/A';
        document.getElementById('doorNumber').value = data.doorNumber || 'N/A';
        document.getElementById('zipCode').value = data.postalCode || 'N/A';
        document.getElementById('cityName').value = data.cityName || 'N/A';

        // Habilita o botão de exclusão quando uma agência é selecionada
        document.getElementById('deleteAgencyButton').disabled = false;
    } catch (error) {
        console.error("Erro ao buscar dados da agência:", error);
    }
}
    </script>
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
                    <h4 class="card-title">Atualizar Agências</h4>
                    <p class="card-description">Seleciona uma Agência para atualizar</p>

                    <?php if (!empty($notification)) echo $notification; ?>

                    <form class="forms-sample" method="POST" action="">
                      <!-- Seleção de Agência -->
                      <div class="form-group">
                        <label for="agencySelect">Selecionar Agência</label>
                        <select name="agency_id" id="agencySelect" class="form-select" onchange="fetchAgencyData(this.value)" required>
                            <option value="" disabled selected>Selecione uma agência</option>
                            <?php foreach ($agencias as $agencia): ?>
                                <option value="<?php echo $agencia['idAgencia']; ?>">
                                    <?php echo htmlspecialchars($agencia['nomeAgencia']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                      </div>

                      <div class="form-group">
                        <label for="name">Nome da Agência</label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="Nome da Agência" required>
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
                          <label for="contactValuePhone">Valor de Contato</label>
                          <input type="text" name="contact_value" class="form-control" id="contactValuePhone" placeholder="Digite o valor de contato" required>
                      </div>
                      <div class="form-group">
                        <label for="streetAddress">Endereço</label>
                        <input type="text" name="street_address" class="form-control" id="streetAddress" placeholder="Rua" required>
                      </div>
                      <div class="form-group">
                        <label for="doorNumber">Número</label>
                        <input type="text" name="door_number" class="form-control" id="doorNumber" placeholder="Número da Porta" required>
                      </div>
                      <div class="form-group">
                        <label for="zipCode">Código Postal</label>
                        <input type="text" name="zip_code" class="form-control" id="zipCode" placeholder="Código Postal" required>
                      </div>
                      <div class="form-group">
                        <label for="cityName">Cidade</label>
                        <input type="text" name="city_name" class="form-control" id="cityName" placeholder="Cidade" required>
                      </div>
                      <button type="submit" class="btn btn-primary me-2">Atualizar</button>
                      <button type="button" class="btn btn-light" onclick="location.href='../tables/agencies-table.php';">Cancelar</button>
                      <button type="button" id="deleteAgencyButton" class="btn btn-danger ms-2" onclick="confirmDelete()" disabled>Apagar Agência</button>
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
          const contactValue = document.getElementById('contactValuePhone');

          if (contactType === '2') { 
              contactValue.type = 'tel';
              contactValue.placeholder = 'Digite o número de telefone';
          } else if (contactType === '1') { 
              contactValue.type = 'email';
              contactValue.placeholder = 'Digite o endereço de email';
          } else {
              contactValue.type = 'text';
              contactValue.placeholder = 'Digite o valor de contato';
          }
      }

      function confirmDelete() {
          const agencySelect = document.getElementById('agencySelect');
          const agencyId = agencySelect.value;

          if (!agencyId) {
              alert('Selecione uma agência antes de tentar apagar.');
              return;
          }

          if (confirm('Tem certeza de que deseja apagar esta agência? Essa ação não pode ser desfeita.')) {
              const form = document.createElement('form');
              form.method = 'POST';
              form.action = ''; 

              const deleteInput = document.createElement('input');
              deleteInput.type = 'hidden';
              deleteInput.name = 'delete_agency';
              deleteInput.value = agencyId;

              form.appendChild(deleteInput);
              document.body.appendChild(form);
              form.submit();
          }
      }

      document.addEventListener('DOMContentLoaded', () => {
          const deleteButton = document.getElementById('deleteAgencyButton');
          deleteButton.disabled = true;
      });
    </script>
  </body>
</html>