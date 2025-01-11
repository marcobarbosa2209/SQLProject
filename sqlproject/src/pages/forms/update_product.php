<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Lógica para remover contrato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productId'])) {
  $productId = intval($_POST['productId']); // Sanitizar o ID

  try {
      $pdo->beginTransaction();

      // Remova o produto pelo ID
      $stmt = $pdo->prepare("DELETE FROM Produto WHERE idProduto = :idProduto");
      $stmt->execute(['idProduto' => $productId]);

      $pdo->commit();
      $notification = newNotification("Produto removido com sucesso!", "success");
  } catch (Exception $e) {
      $pdo->rollBack();
      $notification = newNotification("Erro ao remover o produto: " . $e->getMessage(), "error");
  }
}

else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);
    $productName = $_POST['name'] ?? '';
    $selectedLicenses = $_POST['licenses'] ?? [];

    if (empty($productName)) {
        $notification = newNotification("Product name is required.", "error");
    } else {
        try {
            $pdo->beginTransaction();

            // Atualizar nome do produto
            $stmt = $pdo->prepare("UPDATE Produto SET nomeProduto = :nome WHERE idProduto = :idProduto");
            $stmt->execute([
                'nome' => $productName,
                'idProduto' => $productId
            ]);

            // Remover todas as licenças associadas ao produto
            $stmt = $pdo->prepare("DELETE FROM LicencaProduto WHERE idProduto = :idProduto");
            $stmt->execute(['idProduto' => $productId]);

            // Adicionar novas associações de licenças
            if (!empty($selectedLicenses)) {
                $stmtLicencaProduto = $pdo->prepare("
                    INSERT INTO LicencaProduto (idProduto, idLicenca) 
                    VALUES (:produto, :licenca)
                ");
                foreach ($selectedLicenses as $licenseId) {
                    $stmtLicencaProduto->execute([
                        'produto' => $productId,
                        'licenca' => intval($licenseId),
                    ]);
                }
            }

            $pdo->commit();
            $notification = newNotification("Product updated successfully!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            $notification = newNotification("Error updating product: " . $e->getMessage(), "error");
        }
    }
}


// Buscar produtos existentes
$stmtProdutos = $pdo->prepare("SELECT idProduto, nomeProduto FROM Produto");
$stmtProdutos->execute();
$produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);

// Buscar licenças existentes
$stmtLicencas = $pdo->prepare("SELECT idLicenca, nomeLicenca FROM Licenca");
$stmtLicencas->execute();
$licencas = $stmtLicencas->fetchAll(PDO::FETCH_ASSOC);

$notification = '';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produtos</title>
    <link rel="stylesheet" href="../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="../../assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
    <script>
    function confirmDelete(productId) {
      if (!productId) {
          alert('Selecione um contrato para remover.');
          return;
      }

      if (confirm('Tem certeza de que deseja remover este produto? Essa ação não pode ser desfeita.')) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = ''; // Enviar para a mesma página

          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'productId';
          input.value = productId;

          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
      }
    }

    async function fetchProductData(productId) {
        if (!productId) return;

        try {
            const response = await fetch(`get_product_data.php?id=${productId}`);
            const data = await response.json();

            if (data.error) {
                alert(data.error);
                return;
            }

            // Preenche o nome do produto
            document.getElementById('name').value = data.nomeProduto || '';

            // Preenche o multi-select de licenças associadas
            const licensesSelect = $('#licenses');

            // Define os valores selecionados
            licensesSelect.val(data.associatedLicenses).trigger('change');
        } catch (error) {
            console.error("Error fetching product data:", error);
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
                                    <h4 class="card-title">Atualizar Produto</h4>
                                    <p class="card-description">Selecione um produto para atualizar os dados de</p>
                                    <?php if (!empty($notification)) echo $notification; ?>
                                    <form class="forms-sample" method="POST" action="">
                                        <!-- Seleção de Produto -->
                                        <div class="form-group">
                                            <label for="productSelect">Selecionar Produto</label>
                                            <select name="product_id" id="productSelect" class="form-select" onchange="fetchProductData(this.value)" required>
                                                <option value="" disabled selected>Selecione um Produto</option>
                                                <?php foreach ($produtos as $produto): ?>
                                                    <option value="<?php echo $produto['idProduto']; ?>">
                                                        <?php echo htmlspecialchars($produto['nomeProduto']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Nome do Produto -->
                                        <div class="form-group">
                                            <label for="name">Nome do Produto</label>
                                            <input type="text" name="name" class="form-control" id="name" placeholder="Introduza o nome do Produto" required>
                                        </div>
                                        <!-- Seleção de Licenças -->
                                        <div class="form-group">
                                            <label for="licenses">Selecione as Licenças</label>
                                            <select name="licenses[]" id="licenses" class="js-example-basic-multiple w-100" multiple="multiple">
                                                <?php foreach ($licencas as $licenca): ?>
                                                    <option value="<?php echo $licenca['idLicenca']; ?>">
                                                        <?php echo htmlspecialchars($licenca['nomeLicenca']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Botões -->
                                        <button type="submit" class="btn btn-primary me-2">Atualizar</button>
                                        <button type="button" class="btn btn-light" onclick="location.href='../tables/products-table.php';">Cancelar</button>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete(document.getElementById('productSelect').value)">Remover</button>
                                    </form>
                                </div>
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
                placeholder: "Selecionar Licença(s)",
                allowClear: true,
                width: 'resolve'
            });
        });
    </script>
</body>
</html>