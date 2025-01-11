<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();


$notification = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_license'])) {
  $licenseId = intval($_POST['delete_license']); // Sanitizar o ID
  
  try {
    $pdo->beginTransaction();
    
    // Remover associações da licença com produtos e contratos
    $stmt = $pdo->prepare("DELETE FROM LicencaProduto WHERE idLicenca = :idLicenca");
    $stmt->execute(['idLicenca' => $licenseId]);
    
    $stmt = $pdo->prepare("DELETE FROM ContratoLicenca WHERE idLicenca = :idLicenca");
    $stmt->execute(['idLicenca' => $licenseId]);
    
    // Remover a licença
    $stmt = $pdo->prepare("DELETE FROM Licenca WHERE idLicenca = :idLicenca");
    $stmt->execute(['idLicenca' => $licenseId]);
    
    $pdo->commit();
    $notification = newNotification("License removed successfully!", "success");
  } catch (Exception $e) {
    $pdo->rollBack();
    $notification = newNotification("Error removing license: " . $e->getMessage(), "error");
  }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['license_id'])) {
  $licenseId = intval($_POST['license_id']);
  $licenseName = $_POST['name'] ?? '';
  $licenseType = $_POST['type'] ?? null;
  $selectedProducts = $_POST['products'] ?? [];
  $selectedContracts = $_POST['contracts'] ?? [];
  
  if (empty($licenseName) || !$licenseType) {
    $notification = newNotification("License name and type are required.", "error");
  } else {
    try {
      $pdo->beginTransaction();
      
      // Atualizar nome e tipo da licença
      $stmt = $pdo->prepare("UPDATE Licenca SET nomeLicenca = :nome, idTipoLicenca = :tipo WHERE idLicenca = :idLicenca");
      $stmt->execute([
        'nome' => $licenseName,
        'tipo' => $licenseType,
        'idLicenca' => $licenseId
      ]);
      
      // Remover todas as associações de produtos
      $stmt = $pdo->prepare("DELETE FROM LicencaProduto WHERE idLicenca = :idLicenca");
      $stmt->execute(['idLicenca' => $licenseId]);
      
      // Adicionar novas associações de produtos
      if (!empty($selectedProducts)) {
        $stmtProdutoLicenca = $pdo->prepare("
        INSERT INTO LicencaProduto (idLicenca, idProduto) 
        VALUES (:licenca, :produto)
        ");
        foreach ($selectedProducts as $productId) {
          $stmtProdutoLicenca->execute([
            'licenca' => $licenseId,
            'produto' => intval($productId),
          ]);
        }
      }
      
      // Remover todas as associações de contratos
      $stmt = $pdo->prepare("DELETE FROM ContratoLicenca WHERE idLicenca = :idLicenca");
      $stmt->execute(['idLicenca' => $licenseId]);
      
      // Adicionar novas associações de contratos
      if (!empty($selectedContracts)) {
        $stmtContratoLicenca = $pdo->prepare("
        INSERT INTO ContratoLicenca (idLicenca, idContrato) 
        VALUES (:licenca, :contrato)
        ");
        foreach ($selectedContracts as $contractId) {
                    $stmtContratoLicenca->execute([
                      'licenca' => $licenseId,
                      'contrato' => intval($contractId),
                    ]);
                }
              }
              
              $pdo->commit();
              $notification = newNotification("License updated successfully!", "success");
            } catch (Exception $e) {
            $pdo->rollBack();
            $notification = newNotification("Error updating license: " . $e->getMessage(), "error");
        }
      }
    }
    // Buscar licenças existentes
    $stmtLicencas = $pdo->prepare("SELECT idLicenca, nomeLicenca FROM Licenca");
    $stmtLicencas->execute();
    $licencas = $stmtLicencas->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar produtos existentes
    $stmtProdutos = $pdo->prepare("SELECT idProduto, nomeProduto FROM Produto");
    $stmtProdutos->execute();
    $produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar tipos de licença existentes
    $stmtTipoLicencas = $pdo->prepare("SELECT idTipoLicenca, nomeTipoLicenca FROM TipoLicenca");
    $stmtTipoLicencas->execute();
    $tipoLicencas = $stmtTipoLicencas->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar contratos existentes
    $stmtContratos = $pdo->prepare("SELECT idContrato, nomeContrato FROM Contrato");
    $stmtContratos->execute();
    $contratos = $stmtContratos->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Licença</title>
    <link rel="stylesheet" href="../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="../../assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
    <script>
    async function fetchLicenseData(licenseId) {
        if (!licenseId) return;

        try {
            const response = await fetch(`get_license_data.php?id=${licenseId}`);
            const data = await response.json();

            console.log("Dados recebidos:", data); // Para depuração

            if (data.error) {
                alert(data.error);
                return;
            }

            // Preenche o nome e tipo da licença
            document.getElementById('name').value = data.nomeLicenca || '';
            document.getElementById('type').value = data.tipoLicenca || '';

            // Preenche o multi-select de produtos associados
            const productsSelect = $('#products');
            productsSelect.val(data.associatedProducts.map(id => String(id))).trigger('change');

            // Preenche o multi-select de contratos associados
            const contractsSelect = $('#contracts');
            contractsSelect.val(data.associatedContracts.map(id => String(id))).trigger('change');

            console.log("Produtos selecionados:", data.associatedProducts);
            console.log("Contratos selecionados:", data.associatedContracts);
        } catch (error) {
            console.error("Error fetching license data:", error);
        }
    }

    function confirmDelete(licenseId) {
      if (!licenseId) {
            alert('Selecione uma licença para remover.');
            return;
        }

      if (confirm('Tem certeza de que deseja remover esta licença? Essa ação não pode ser desfeita.')) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = ''; // Enviar para a mesma página

          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'delete_license'; // Certifique-se de usar 'delete_license'
          input.value = licenseId;

          form.appendChild(input);
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
                                    <h4 class="card-title">Atualizar Licença</h4>
                                    <p class="card-description">Selecione uma Licença para atualizar os dados de</p>
                                    <?php if (!empty($notification)) echo $notification; ?>
                                    <form class="forms-sample" method="POST" action="">
                                        <!-- Seleção de Licença -->
                                        <div class="form-group">
                                            <label for="licenseSelect">Selecione Licença</label>
                                            <select name="license_id" id="licenseSelect" class="form-select" onchange="fetchLicenseData(this.value)" required>
                                                <option value="" disabled selected>Licença</option>
                                                <?php foreach ($licencas as $licenca): ?>
                                                    <option value="<?php echo $licenca['idLicenca']; ?>">
                                                        <?php echo htmlspecialchars($licenca['nomeLicenca']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Nome da Licença -->
                                        <div class="form-group">
                                            <label for="name">Nome da Licença</label>
                                            <input type="text" name="name" class="form-control" id="name" placeholder="Insira o nome da Licença" required>
                                        </div>
                                        <!-- Tipo da Licença -->
                                        <div class="form-group">
                                            <label for="type">Tipo de Licença</label>
                                            <select name="type" id="type" class="form-select" required>
                                                <option value="" disabled selected>Selecione um Tipo de Licença</option>
                                                <?php foreach ($tipoLicencas as $tipoLicenca): ?>
                                                    <option value="<?php echo $tipoLicenca['idTipoLicenca']; ?>">
                                                        <?php echo htmlspecialchars($tipoLicenca['nomeTipoLicenca']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Seleção de Produtos -->
                                        <div class="form-group">
                                            <label for="products">Selecione os produtos associados</label>
                                            <select name="products[]" id="products" class="js-example-basic-multiple w-100" multiple="multiple">
                                                <?php foreach ($produtos as $produto): ?>
                                                    <option value="<?php echo $produto['idProduto']; ?>">
                                                        <?php echo htmlspecialchars($produto['nomeProduto']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Seleção de Contratos -->
                                        <div class="form-group">
                                            <label for="contracts">Selecione os contratos associados</label>
                                            <select name="contracts[]" id="contracts" class="js-example-basic-multiple w-100" multiple="multiple">
                                                <?php foreach ($contratos as $contrato): ?>
                                                    <option value="<?php echo $contrato['idContrato']; ?>">
                                                        <?php echo htmlspecialchars($contrato['nomeContrato']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Botões -->
                                        <button type="submit" class="btn btn-primary me-2">Atualizar</button>
                                        <button type="button" class="btn btn-light" onclick="location.href='../tables/licenses-table.php';">Cancelar</button>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete(document.getElementById('licenseSelect').value)">Remover</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
        <script src="../../assets/vendors/select2/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.js-example-basic-multiple').select2({
                    placeholder: "Selecione a(s) opção/opções",
                    allowClear: true,
                    width: 'resolve'
                });
            });
        </script>
    </body>
</html>