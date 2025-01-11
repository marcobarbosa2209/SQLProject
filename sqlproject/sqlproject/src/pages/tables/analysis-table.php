<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Buscar todas as análises com informações associadas
$stmtAnalises = $pdo->prepare("
    SELECT 
        a.idAnalise,
        a.estadoAnalise,
        a.descricaoAnalise,
        ag.nomeAgencia,
        cl.nomeCliente,
        o.nomeOrganizacao
    FROM 
        Analise a
    INNER JOIN 
        Contrato c ON a.idContrato = c.idContrato
    INNER JOIN 
        Agencia ag ON c.idAgencia = ag.idAgencia
    INNER JOIN 
        Cliente cl ON c.idCliente = cl.idCliente
    INNER JOIN 
        Organizacao o ON c.idOrganizacao = o.idOrganizacao
    ORDER BY 
        a.idAnalise ASC
");
$stmtAnalises->execute();
$analises = $stmtAnalises->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Análises</title>
    <link rel="stylesheet" href="../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png">
    <style>
        /* Estilos para os status com cores de fundo */
        .status-pendente {
            background-color: #ffc107;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .status-aceitado {
            background-color: #28a745;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .status-negado {
            background-color: #dc3545;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-scroller">
        <!-- Navbar -->
        <?php require('../ui/navbar.php'); ?>
        <!-- Page Body Wrapper -->
        <div class="container-fluid page-body-wrapper">
            <!-- Sidebar -->
            <?php require('../ui/navsidebar.php'); ?>
            <!-- Main Panel -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <!-- Notificações -->
                    <?php if (!empty($notification)) echo $notification; ?>
                    <!-- Tabela de Análises -->
                    <div class="row">
                        <div class="col-lg-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Análises</h4>
                                    <p class="card-description">Uma lista de todas as análises</p>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID da Análise</th>
                                                    <th>Estado</th>
                                                    <th>Descrição da Análise</th>
                                                    <th>Agência Associada</th>
                                                    <th>Cliente Associado</th>
                                                    <th>Organização Associada</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($analises as $analise): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($analise['idAnalise']); ?></td>
                                                        <td>
                                                            <?php 
                                                                $estado = strtolower($analise['estadoAnalise']);
                                                                $class = '';
                                                                if ($estado === 'pendente') $class = 'status-pendente';
                                                                elseif ($estado === 'aceitado') $class = 'status-aceitado';
                                                                elseif ($estado === 'negado') $class = 'status-negado';
                                                            ?>
                                                            <span class="<?php echo $class; ?>">
                                                                <?php echo htmlspecialchars(ucfirst($analise['estadoAnalise'])); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                                $descricao = $analise['descricaoAnalise'];
                                                                if (mb_strlen($descricao, 'UTF-8') > 45) {
                                                                    $descricao = mb_substr($descricao, 0, 45, 'UTF-8') . '...';
                                                                }
                                                                echo htmlspecialchars($descricao);
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($analise['nomeAgencia']); ?></td>
                                                        <td><?php echo htmlspecialchars($analise['nomeCliente']); ?></td>
                                                        <td><?php echo htmlspecialchars($analise['nomeOrganizacao']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($analises)): ?>
                                                    <tr>
                                                        <td colspan="10" class="text-center">Nenhuma análise encontrada.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Botões Adicionais -->
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-outline-primary btn-icon-text me-2" onclick="location.href='../forms/add_analysis.php';">
                                            <i class="ti-plus btn-icon-prepend"></i> Nova Análise
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-icon-text" onclick="location.href='../forms/update_analysis.php';">
                                            <i class="ti-pencil btn-icon-prepend"></i> Atualizar Análises
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fim da Tabela de Análises -->
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../../assets/js/off-canvas.js"></script>
    <script src="../../assets/js/template.js"></script>
</body>
</html>