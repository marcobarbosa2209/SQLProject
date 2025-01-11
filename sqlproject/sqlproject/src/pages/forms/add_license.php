<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Fetch license types
$stmtTipoLicencas = $pdo->prepare("SELECT idTipoLicenca, nomeTipoLicenca FROM TipoLicenca");
$stmtTipoLicencas->execute();
$tipoLicencas = $stmtTipoLicencas->fetchAll(PDO::FETCH_ASSOC);

// Fetch products
$stmtProdutos = $pdo->prepare("SELECT idProduto, nomeProduto FROM Produto");
$stmtProdutos->execute();
$produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);

// Fetch contracts
$stmtContratos = $pdo->prepare("SELECT idContrato, nomeContrato FROM Contrato");
$stmtContratos->execute();
$contratos = $stmtContratos->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $licenseName = $_POST['name'] ?? '';
    $licenseType = $_POST['type'] ?? null;
    $selectedProducts = $_POST['products'] ?? [];
    $selectedContracts = $_POST['contracts'] ?? [];

    if (empty($licenseName) || !$licenseType) {
        echo newNotification("License name and type are required.", "error");
    } else {
        try {
            $pdo->beginTransaction();

            // Insert the license
            $stmt = $pdo->prepare("INSERT INTO Licenca (nomeLicenca, idTipoLicenca) VALUES (:nome, :tipo)");
            $stmt->execute([
                'nome' => $licenseName,
                'tipo' => $licenseType
            ]);
            $licenseId = $pdo->lastInsertId();

            // Associate products with the license
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

            // Associate contracts with the license
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
            echo newNotification("License created successfully!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            echo newNotification("Error creating license: " . $e->getMessage(), "error");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Licenças</title>
    <link rel="stylesheet" href="../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
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
                                    <h4 class="card-title">Adicionar nova Licença</h4>
                                    <p class="card-description">Detalhes da Licença</p>
                                    <form class="forms-sample" method="POST" action="">
                                        <!-- License Name -->
                                        <div class="form-group">
                                            <label for="name">Nome</label>
                                            <input type="text" name="name" class="form-control" id="name" placeholder="Nome da Licença" required>
                                        </div>
                                        <!-- License Type -->
                                        <div class="form-group">
                                            <label for="type">Tipo da Licença</label>
                                            <select name="type" id="type" class="form-select" required>
                                                <option value="" disabled selected>Selecione o tipo de Licença</option>
                                                <?php foreach ($tipoLicencas as $tipoLicenca): ?>
                                                    <option value="<?php echo $tipoLicenca['idTipoLicenca']; ?>">
                                                        <?php echo htmlspecialchars($tipoLicenca['nomeTipoLicenca']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Associated Products -->
                                        <div class="form-group">
                                            <label for="products">Selecione produtos associados</label>
                                            <select name="products[]" id="products" class="js-example-basic-multiple w-100" multiple="multiple">
                                                <?php foreach ($produtos as $produto): ?>
                                                    <option value="<?php echo $produto['idProduto']; ?>">
                                                        <?php echo htmlspecialchars($produto['nomeProduto']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Associated Contracts -->
                                        <div class="form-group">
                                            <label for="contracts">Selecione contratos associados</label>
                                            <select name="contracts[]" id="contracts" class="js-example-basic-multiple w-100" multiple="multiple">
                                                <?php foreach ($contratos as $contrato): ?>
                                                    <option value="<?php echo $contrato['idContrato']; ?>">
                                                        <?php echo htmlspecialchars($contrato['nomeContrato']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Buttons -->
                                        <button type="submit" class="btn btn-primary me-2">Submeter</button>
                                        <button type="button" class="btn btn-light" onclick="location.href='../tables/licenses-table.php';">Cancelar</button>
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
    <script src="../../assets/vendors/select2/select2.min.js"></script>
    <script>
        // Initialize Select2 for the multi-selects
        $(document).ready(function() {
            $('.js-example-basic-multiple').select2({
                placeholder: "Select options",
                allowClear: true,
                width: 'resolve'
            });
        });
    </script>
</body>
</html>