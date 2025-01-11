<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Buscar organizações existentes
$stmtOrganizacoes = $pdo->prepare("SELECT idOrganizacao, nomeOrganizacao FROM Organizacao");
$stmtOrganizacoes->execute();
$organizacoes = $stmtOrganizacoes->fetchAll(PDO::FETCH_ASSOC);

// Buscar agências existentes
$stmtAgencias = $pdo->prepare("SELECT idAgencia, nomeAgencia FROM Agencia");
$stmtAgencias->execute();
$agencias = $stmtAgencias->fetchAll(PDO::FETCH_ASSOC);

// Buscar tipos de contato existentes
$stmtTiposContato = $pdo->prepare("SELECT idTipoContacto, nomeTipoContacto FROM TipoContacto");
$stmtTiposContato->execute();
$tiposContato = $stmtTiposContato->fetchAll(PDO::FETCH_ASSOC);

$notification = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_organization'])) {
  $organizationId = intval($_POST['delete_organization']); // Sanitizar o ID

  try {
      $pdo->beginTransaction();

      // Excluir a organização
      $stmt = $pdo->prepare("DELETE FROM Organizacao WHERE idOrganizacao = :idOrganizacao");
      $stmt->execute(['idOrganizacao' => $organizationId]);

      $pdo->commit();
      $notification = newNotification("Organização apagada com sucesso!", "success");
  } catch (Exception $e) {
      $pdo->rollBack();
      $notification = newNotification("Erro ao apagar a organização: " . $e->getMessage(), "error");
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['organization_id'])) {
    $organizationId = intval($_POST['organization_id']);
    $organizationName = $_POST['name'] ?? '';
    $agencyId = intval($_POST['agency_id']);
    $contactType = intval($_POST['contact_type']);
    $contactValue = $_POST['contact_value'] ?? '';
    $streetAddress = $_POST['street_address'] ?? '';
    $doorNumber = intval($_POST['door_number']);
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

            // Verificar ou criar contato
            $stmt = $pdo->prepare("SELECT idContacto FROM Organizacao WHERE idOrganizacao = :idOrganizacao");
            $stmt->execute(['idOrganizacao' => $organizationId]);
            $contactId = $stmt->fetchColumn();

            if ($contactId) {
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
                $stmt = $pdo->prepare("
                    INSERT INTO Contacto (valorContacto, idTipoContacto)
                    VALUES (:valor, :tipo)
                ");
                $stmt->execute([
                    'valor' => $contactValue,
                    'tipo' => $contactType
                ]);
                $newContactId = $pdo->lastInsertId();

                $stmt = $pdo->prepare("UPDATE Organizacao SET idContacto = :idContacto WHERE idOrganizacao = :idOrganizacao");
                $stmt->execute([
                    'idContacto' => $newContactId,
                    'idOrganizacao' => $organizationId
                ]);
            }

            // Verificar ou criar morada
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Morada WHERE idOrganizacao = :idOrganizacao");
            $stmt->execute(['idOrganizacao' => $organizationId]);
            $moradaExists = $stmt->fetchColumn();

            if ($moradaExists) {
                $stmt = $pdo->prepare("
                    UPDATE Morada 
                    SET nomeMorada = :morada, porta = :porta, codigoPostal = :codigoPostal
                    WHERE idOrganizacao = :idOrganizacao
                ");
                $stmt->execute([
                    'morada' => $streetAddress,
                    'porta' => $doorNumber,
                    'codigoPostal' => $zipCode,
                    'idOrganizacao' => $organizationId
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO Morada (nomeMorada, porta, codigoPostal, idOrganizacao)
                    VALUES (:morada, :porta, :codigoPostal, :idOrganizacao)
                ");
                $stmt->execute([
                    'morada' => $streetAddress,
                    'porta' => $doorNumber,
                    'codigoPostal' => $zipCode,
                    'idOrganizacao' => $organizationId
                ]);
            }

            // Atualizar organização
            $stmt = $pdo->prepare("
                UPDATE Organizacao 
                SET nomeOrganizacao = :nome, idAgencia = :idAgencia
                WHERE idOrganizacao = :idOrganizacao
            ");
            $stmt->execute([
                'nome' => $organizationName,
                'idAgencia' => $agencyId,
                'idOrganizacao' => $organizationId
            ]);

            $pdo->commit();
            $notification = newNotification("Organização atualizada com sucesso!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            $notification = newNotification("Erro ao atualizar a organização: " . $e->getMessage(), "error");
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
    <script>
    async function fetchOrganizationData(organizationId) {
        if (!organizationId) {
            return;
        }

        try {
            const response = await fetch(`get_organization_data.php?id=${organizationId}`);
            const data = await response.json();

            if (data.error) {
                alert(data.error);
                return;
            }

            // Preenche os campos do formulário
            document.getElementById('organizationName').value = data.nomeOrganizacao || '';
            document.getElementById('agencySelect').value = data.idAgencia || '';
            document.getElementById('contactType').value = data.idTipoContacto || '';
            document.getElementById('contactValue').value = data.valorContacto || '';
            document.getElementById('streetAddress').value = data.streetAddress || '';
            document.getElementById('doorNumber').value = data.doorNumber || '';
            document.getElementById('zipCode').value = data.postalCode || '';
            document.getElementById('cityName').value = data.cityName || '';
        } catch (error) {
            console.error("Erro ao buscar dados da organização:", error);
        }
    }

    function toggleContactField() {
        const contactType = document.getElementById('contactType').value;
        const contactValue = document.getElementById('contactValue');

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

    document.addEventListener('DOMContentLoaded', () => {
        const deleteButton = document.getElementById('deleteOrganizationButton');
        deleteButton.disabled = true;

        document.getElementById('organizationSelect').addEventListener('change', () => {
            deleteButton.disabled = !document.getElementById('organizationSelect').value;
        });
    });

    function confirmDelete() {
        const organizationSelect = document.getElementById('organizationSelect');
        const organizationId = organizationSelect.value;

        if (!organizationId) {
            alert('Selecione uma organização antes de tentar apagar.');
            return;
        }

        if (confirm('Tem certeza de que deseja apagar esta organização? Essa ação não pode ser desfeita.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; // Submete no mesmo script

            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_organization';
            deleteInput.value = organizationId;

            form.appendChild(deleteInput);
            document.body.appendChild(form);
            form.submit();
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
                    <h4 class="card-title">Atualizar Organização</h4>
                    <p class="card-description">Selecione uma organização para atualizar os dados de</p>

                    <?php if (!empty($notification)) echo $notification; ?>

                    <form class="forms-sample" method="POST" action="">
                      <div class="form-group">
                          <label for="organizationSelect">Selecionar Organização</label>
                          <select name="organization_id" id="organizationSelect" class="form-select" onchange="fetchOrganizationData(this.value)" required>
                              <option value="" disabled selected>Selecione uma organização</option>
                              <?php foreach ($organizacoes as $organizacao): ?>
                                  <option value="<?php echo $organizacao['idOrganizacao']; ?>">
                                      <?php echo htmlspecialchars($organizacao['nomeOrganizacao']); ?>
                                  </option>
                              <?php endforeach; ?>
                          </select>
                      </div>
                      <div class="form-group">
                          <label for="organizationName">Nome da Organização</label>
                          <input type="text" name="name" class="form-control" id="organizationName" placeholder="Nome da Organização" value="" required>
                      </div>
                      <div class="form-group">
                          <label for="agencySelect">Agência Associada</label>
                          <select name="agency_id" class="form-select" id="agencySelect" required>
                              <option value="" disabled>Selecione uma agência</option>
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
                              <option value="" disabled>Selecione o tipo de contato</option>
                              <?php foreach ($tiposContato as $tipo): ?>
                                  <option value="<?php echo $tipo['idTipoContacto']; ?>">
                                      <?php echo htmlspecialchars($tipo['nomeTipoContacto']); ?>
                                  </option>
                              <?php endforeach; ?>
                          </select>
                      </div>
                      <div class="form-group">
                          <label for="contactValue">Valor de Contato</label>
                          <input type="text" name="contact_value" class="form-control" id="contactValue" placeholder="Digite o valor de contato" value="">
                      </div>
                      <div class="form-group">
                          <label for="streetAddress">Endereço</label>
                          <input type="text" name="street_address" class="form-control" id="streetAddress" placeholder="Rua" value="">
                      </div>
                      <div class="form-group">
                          <label for="doorNumber">Número</label>
                          <input type="number" name="door_number" class="form-control" id="doorNumber" placeholder="Número da Porta" value="">
                      </div>
                      <div class="form-group">
                          <label for="zipCode">Código Postal</label>
                          <input type="text" name="zip_code" class="form-control" id="zipCode" placeholder="Código Postal" value="">
                      </div>
                      <div class="form-group">
                          <label for="cityName">Cidade</label>
                          <input type="text" name="city_name" class="form-control" id="cityName" placeholder="Cidade" value="">
                      </div>
                      <button type="submit" class="btn btn-primary me-2">Atualizar</button>
                      <button type="button" class="btn btn-light" onclick="location.href='../tables/organizations-table.php';">Cancelar</button>
                      <button type="button" id="deleteOrganizationButton" class="btn btn-danger ms-2" onclick="confirmDelete()" disabled>Apagar Organização</button>
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
  </body>
</html>