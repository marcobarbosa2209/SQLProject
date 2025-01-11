<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Buscar clientes
$stmtClientes = $pdo->prepare("SELECT c.idCliente, c.nomeCliente FROM Cliente c");
$stmtClientes->execute();
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

// Buscar agências
$stmtAgencias = $pdo->prepare("SELECT idAgencia, nomeAgencia FROM Agencia");
$stmtAgencias->execute();
$agencias = $stmtAgencias->fetchAll(PDO::FETCH_ASSOC);

// Buscar tipos de contato
$stmtTiposContato = $pdo->prepare("SELECT idTipoContacto, nomeTipoContacto FROM TipoContacto");
$stmtTiposContato->execute();
$tiposContato = $stmtTiposContato->fetchAll(PDO::FETCH_ASSOC);

// Buscar tipos de cliente
$stmtTiposCliente = $pdo->prepare("SELECT idTipoCliente, nomeTipoCliente FROM TipoCliente");
$stmtTiposCliente->execute();
$tiposCliente = $stmtTiposCliente->fetchAll(PDO::FETCH_ASSOC);

$notification = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_client'])) {
  $clientId = intval($_POST['delete_client']); // Sanitizar o ID

  try {
      $pdo->beginTransaction();

      $stmt = $pdo->prepare("DELETE FROM Cliente WHERE idCliente = :idCliente");
      $stmt->execute(['idCliente' => $clientId]);

      $pdo->commit();
      $notification = newNotification("Cliente apagado com sucesso!", "success");
  } catch (Exception $e) {
      $pdo->rollBack();
      $notification = newNotification("Erro ao apagar o cliente: " . $e->getMessage(), "error");
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'])) {
    $clientId = $_POST['client_id'];
    $clientName = $_POST['name'] ?? '';
    $clientType = $_POST['client_type'] ?? '';
    $contactType = $_POST['contact_type'] ?? '';
    $contactValue = $_POST['contact_value'] ?? '';
    $agencyId = $_POST['associated_agency'] ?? null;

    // Validação dos campos
    if (empty($clientName) || empty($clientType) || empty($contactType) || empty($contactValue) || empty($agencyId)) {
        $notification = newNotification("Todos os campos são obrigatórios.", "error");
    } else {
        try {
            $pdo->beginTransaction();

            // Atualizar contato
            $stmt = $pdo->prepare("
                UPDATE Contacto 
                SET valorContacto = :valor, idTipoContacto = :tipo
                WHERE idContacto = (
                    SELECT idContacto FROM Cliente WHERE idCliente = :idCliente
                )
            ");
            $stmt->execute([
                'valor' => $contactValue,
                'tipo' => $contactType,
                'idCliente' => $clientId
            ]);

            // Atualizar cliente
            $stmt = $pdo->prepare("
                UPDATE Cliente 
                SET nomeCliente = :nome, idAgencia = :agencia, idTipoCliente = :tipo
                WHERE idCliente = :idCliente
            ");
            $stmt->execute([
                'nome' => $clientName,
                'agencia' => $agencyId,
                'tipo' => $clientType,
                'idCliente' => $clientId
            ]);

            $pdo->commit();
            $notification = newNotification("Cliente atualizado com sucesso!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            $notification = newNotification("Erro ao atualizar o cliente: " . $e->getMessage(), "error");
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
    <script>
    async function fetchClientData(clientId) {
        const response = await fetch(`get_client_data.php?id=${clientId}`);
        const data = await response.json();

        if (data) {
            document.getElementById('name').value = data.nomeCliente || '';
            document.getElementById('clientType').value = data.idTipoCliente || '';
            document.getElementById('contactType').value = data.idTipoContacto || '';
            document.getElementById('contactValuePhone').value = data.valorContacto || '';
            document.getElementById('associatedAgency').value = data.idAgencia || '';
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
                    <h4 class="card-title">Atualizar Cliente</h4>
                    <p class="card-description">Selecione um cliente para atualizar os detalhes de</p>

                    <?php if (!empty($notification)) echo $notification; ?>

                    <form class="forms-sample" method="POST" action="">
                      <!-- Seleção de Cliente -->
                      <div class="form-group">
                        <label for="clientSelect">Selecionar Cliente</label>
                        <select name="client_id" id="clientSelect" class="form-select" onchange="handleClientSelection(this.value)" required>
                            <option value="" disabled selected>Selecione um cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['idCliente']; ?>">
                                    <?php echo htmlspecialchars($cliente['nomeCliente']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                      </div>

                      <!-- Campo de Nome -->
                      <div class="form-group">
                          <label for="name">Nome</label>
                          <input type="text" name="name" class="form-control" id="name" placeholder="Nome" required>
                      </div>

                      <!-- Campo de Tipo de Cliente -->
                      <div class="form-group">
                          <label for="clientType">Tipo de Cliente</label>
                          <select name="client_type" class="form-select" id="clientType" required>
                              <option value="" disabled selected>Selecione o tipo de cliente</option>
                              <?php foreach ($tiposCliente as $tipo): ?>
                                  <option value="<?php echo $tipo['idTipoCliente']; ?>">
                                      <?php echo htmlspecialchars($tipo['nomeTipoCliente']); ?>
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
                        <label for="contactValuePhone">Valor de Contato</label>
                        <input type="text" name="contact_value" class="form-control" id="contactValuePhone" placeholder="Digite o valor de contato" required>
                    </div>

                      <!-- Campo de Agência Associada -->
                      <div class="form-group">
                          <label for="associatedAgency">Agência Associada</label>
                          <select name="associated_agency" class="form-select" id="associatedAgency" required>
                              <option value="" disabled selected>Selecione uma agência</option>
                              <?php foreach ($agencias as $agencia): ?>
                                  <option value="<?php echo $agencia['idAgencia']; ?>">
                                      <?php echo htmlspecialchars($agencia['nomeAgencia']); ?>
                                  </option>
                              <?php endforeach; ?>
                          </select>
                      </div>

                      <!-- Botões de Ação -->
                      <button type="submit" class="btn btn-primary me-2">Atualizar</button>
                      <button type="button" class="btn btn-light" onclick="location.href='../tables/clients-table.php';">Cancelar</button>
                      <button type="button" id="deleteClientButton" class="btn btn-danger ms-2" onclick="confirmDelete()" disabled>Apagar Cliente</button>
                    </form>
                    <script>
                      function toggleContactField() {
                          const contactType = document.getElementById('contactType').value;
                          const contactValue = document.getElementById('contactValuePhone');

                          // Ajusta o tipo de input com base no tipo de contato selecionado
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

                      // Executa ao carregar a página para ajustar o tipo inicial com base no valor selecionado
                      document.addEventListener('DOMContentLoaded', toggleContactField);

                      function handleClientSelection(clientId) {
                          // Habilitar ou desabilitar o botão "Apagar Cliente" com base na seleção
                          const deleteButton = document.getElementById('deleteClientButton');
                          deleteButton.disabled = !clientId;

                          // Chamar a função fetchClientData para buscar os dados do cliente selecionado
                          fetchClientData(clientId);
                      }

                      function confirmDelete() {
                          const clientSelect = document.getElementById('clientSelect');
                          const clientId = clientSelect.value;

                          if (!clientId) {
                              alert('Selecione um cliente antes de tentar apagar.');
                              return;
                          }

                          if (confirm('Tem certeza de que deseja apagar este cliente? Essa ação não pode ser desfeita.')) {
                              const form = document.createElement('form');
                              form.method = 'POST';
                              form.action = ''; // Envia para a mesma página

                              // Adiciona o campo para indicar que é uma exclusão
                              const deleteInput = document.createElement('input');
                              deleteInput.type = 'hidden';
                              deleteInput.name = 'delete_client';
                              deleteInput.value = clientId;

                              form.appendChild(deleteInput);
                              document.body.appendChild(form);
                              form.submit();
                          }
                      }
                    </script>
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