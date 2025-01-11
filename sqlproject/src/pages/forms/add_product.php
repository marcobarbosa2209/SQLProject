<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Buscar licenças existentes
$stmtLicencas = $pdo->prepare("SELECT idLicenca, nomeLicenca FROM Licenca");
$stmtLicencas->execute();
$licencas = $stmtLicencas->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['name'] ?? '';
    $selectedLicenses = $_POST['licenses'] ?? [];

    if (empty($productName)) {
        echo newNotification("Product name is required.", "error");
    } else {
        try {
            $pdo->beginTransaction();

            // Inserir o produto
            $stmt = $pdo->prepare("INSERT INTO Produto (nomeProduto) VALUES (:nome)");
            $stmt->execute(['nome' => $productName]);
            $productId = $pdo->lastInsertId();

            // Associar licenças ao produto
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
            echo newNotification("Product created successfully!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            echo newNotification("Error creating product: " . $e->getMessage(), "error");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Produtos</title>
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
                                    <h4 class="card-title">Adicionar novo Produto</h4>
                                    <p class="card-description">Info do Produto</p>
                                    <form class="forms-sample" method="POST" action="">
                                        <!-- Nome do Produto -->
                                        <div class="form-group">
                                            <label for="name">Nome</label>
                                            <input type="text" name="name" class="form-control" id="name" placeholder="Nome" required>
                                        </div>
                                        <!-- Seleção de Licenças -->
                                        <div class="form-group">
                                            <label for="licenses">Selecione a(s) Licença(s)</label>
                                            <select name="licenses[]" id="licenses" class="js-example-basic-multiple w-100" multiple="multiple">
                                                <?php foreach ($licencas as $licenca): ?>
                                                    <option value="<?php echo $licenca['idLicenca']; ?>">
                                                        <?php echo htmlspecialchars($licenca['nomeLicenca']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Botões -->
                                        <button type="submit" class="btn btn-primary me-2">Submeter</button>
                                        <button type="button" class="btn btn-light" onclick="location.href='../tables/products-table.php';">Cancelar</button>
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
        // Inicializar Select2 para o multi-select
        $(document).ready(function() {
            $('.js-example-basic-multiple').select2({
                placeholder: "Selecione a(s) Licença(s)",
                allowClear: true,
                width: 'resolve'
            });
        });
    </script>
</body>
</html>