<?php
require '../pdo/user_auth.php';
checkUserAuthentication();
require '../pdo/connection.php';

// Buscar informações dos produtos e quantidade de licenças associadas
$stmtProdutos = $pdo->prepare("
    SELECT 
        p.idProduto,
        p.nomeProduto,
        COUNT(lp.idLicenca) AS numLicencas
    FROM Produto p
    LEFT JOIN LicencaProduto lp ON p.idProduto = lp.idProduto
    GROUP BY p.idProduto, p.nomeProduto
    ORDER BY p.idProduto
");
$stmtProdutos->execute();
$produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png">
</head>
<body>
    <div class="container-scroller">
        <?php require('../ui/navbar.php'); ?>
        <div class="container-fluid page-body-wrapper">
            <?php require('../ui/navsidebar.php'); ?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-lg-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Produtos</h4>
                                    <p class="card-description"> Lista de todos os Produtos </p>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th> ID </th>
                                                    <th> Nome </th>
                                                    <th> Numero de Licenças </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($produtos as $produto): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($produto['idProduto']); ?></td>
                                                        <td><?php echo htmlspecialchars($produto['nomeProduto']); ?></td>
                                                        <td><?php echo htmlspecialchars($produto['numLicencas']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($produtos)): ?>
                                                    <tr>
                                                        <td colspan="10" class="text-center">Nenhum produto encontrado.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 grid-margin stretch-card">
                        <button type="button" class="btn btn-outline-primary btn-icon-text me-2" onclick="location.href='../forms/add_product.php';">
                            <i class="ti-plus btn-icon-prepend"></i> Novo Produto
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-icon-text" onclick="location.href='../forms/update_product.php';">
                            <i class="ti-pencil btn-icon-prepend"></i> Atualizar Produtos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../../assets/js/off-canvas.js"></script>
    <script src="../../assets/js/template.js"></script>
</body>
</html>