<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
checkUserAuthentication();

// Fetch licenses with associated counts
$stmtLicenses = $pdo->prepare("
    SELECT 
        l.idLicenca,
        l.nomeLicenca,
        tl.nomeTipoLicenca AS tipoLicenca,
        COUNT(DISTINCT cl.idContrato) AS countContratos,
        COUNT(DISTINCT lp.idProduto) AS countProdutos
    FROM 
        Licenca l
    LEFT JOIN 
        TipoLicenca tl ON l.idTipoLicenca = tl.idTipoLicenca
    LEFT JOIN 
        ContratoLicenca cl ON l.idLicenca = cl.idLicenca
    LEFT JOIN 
        LicencaProduto lp ON l.idLicenca = lp.idLicenca
    GROUP BY 
        l.idLicenca, l.nomeLicenca, tl.nomeTipoLicenca
    ORDER BY 
        l.nomeLicenca;
");
$stmtLicenses->execute();
$licenses = $stmtLicenses->fetchAll(PDO::FETCH_ASSOC);
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
                                    <h4 class="card-title">Licenças</h4>
                                    <p class="card-description">Lista de todas as Licenças</p>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nome</th>
                                                    <th>Tipo</th>
                                                    <th>Contratos Associados</th>
                                                    <th>Produtos Associados</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($licenses as $license): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($license['idLicenca']); ?></td>
                                                        <td><?php echo htmlspecialchars($license['nomeLicenca']); ?></td>
                                                        <td><?php echo htmlspecialchars($license['tipoLicenca']); ?></td>
                                                        <td><?php echo htmlspecialchars($license['countContratos']); ?></td>
                                                        <td><?php echo htmlspecialchars($license['countProdutos']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($licenses)): ?>
                                                    <tr>
                                                        <td colspan="10" class="text-center">Nenhuma licença encontrada.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-outline-primary btn-icon-text me-2" onclick="location.href='../forms/add_license.php';">
                                <i class="ti-plus btn-icon-prepend"></i> Nova Licença
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-icon-text" onclick="location.href='../forms/update_license.php';">
                                <i class="ti-pencil btn-icon-prepend"></i> Atualizar Licença
                            </button>
                        </div>
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