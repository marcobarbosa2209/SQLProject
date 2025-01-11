<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Buscar clientes existentes
$stmtClientes = $pdo->prepare("SELECT idCliente, nomeCliente FROM Cliente");
$stmtClientes->execute();
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

// Buscar agências existentes
$stmtAgencias = $pdo->prepare("SELECT idAgencia, nomeAgencia FROM Agencia");
$stmtAgencias->execute();
$agencias = $stmtAgencias->fetchAll(PDO::FETCH_ASSOC);

// Buscar organizações existentes
$stmtOrganizacoes = $pdo->prepare("SELECT idOrganizacao, nomeOrganizacao FROM Organizacao");
$stmtOrganizacoes->execute();
$organizacoes = $stmtOrganizacoes->fetchAll(PDO::FETCH_ASSOC);

// Estados da análise
$estados = ['Pendente', 'Aceitado', 'Negado'];

$notification = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteId = intval($_POST['cliente'] ?? 0);
    $agenciaId = intval($_POST['agencia'] ?? 0);
    $organizacaoId = intval($_POST['organizacao'] ?? 0);
    $estadoAnalise = $_POST['estadoAnalise'] ?? '';
    $descricaoAnalise = $_POST['descricaoAnalise'] ?? '';
    
    // Validar campos obrigatórios
    if (!$clienteId || !$agenciaId || !$organizacaoId || empty($estadoAnalise) || empty($descricaoAnalise)) {
        $notification = newNotification("Todos os campos são obrigatórios.", "error");
    } else {
        try {
            $pdo->beginTransaction();

            // Verificar se o contrato já existe para essa combinação
            $stmtContrato = $pdo->prepare("
                SELECT idContrato FROM Contrato 
                WHERE idCliente = :cliente AND idAgencia = :agencia AND idOrganizacao = :organizacao
                LIMIT 1
            ");
            $stmtContrato->execute([
                'cliente' => $clienteId,
                'agencia' => $agenciaId,
                'organizacao' => $organizacaoId
            ]);
            $contrato = $stmtContrato->fetch(PDO::FETCH_ASSOC);

            if ($contrato) {
                $idContrato = $contrato['idContrato'];
            } else {
                // Criar novo contrato
                $stmtInsertContrato = $pdo->prepare("
                    INSERT INTO Contrato (idCliente, idAgencia, idOrganizacao, nomeContrato) 
                    VALUES (:cliente, :agencia, :organizacao, :nomeContrato)
                ");
                // Definir um nome padrão para o contrato ou permitir que o usuário insira
                $nomeContrato = "Contrato de " . htmlspecialchars($clientes[array_search($clienteId, array_column($clientes, 'idCliente'))]['nomeCliente']);
                $stmtInsertContrato->execute([
                    'cliente' => $clienteId,
                    'agencia' => $agenciaId,
                    'organizacao' => $organizacaoId,
                    'nomeContrato' => $nomeContrato
                ]);
                $idContrato = $pdo->lastInsertId();
            }

            // Inserir nova análise
            $stmtInsertAnalise = $pdo->prepare("
                INSERT INTO Analise (estadoAnalise, descricaoAnalise, idContrato) 
                VALUES (:estado, :descricao, :contrato)
            ");
            $stmtInsertAnalise->execute([
                'estado' => $estadoAnalise,
                'descricao' => $descricaoAnalise,
                'contrato' => $idContrato
            ]);

            $pdo->commit();
            $notification = newNotification("Análise adicionada com sucesso!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            $notification = newNotification("Erro ao adicionar análise: " . $e->getMessage(), "error");
        }
    }
}
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
    <link rel="stylesheet" href="../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../../assets/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="../../assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
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
                    <!-- Formulário de Adicionar Análise -->
                    <div class="row">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Adicionar Nova Análise</h4>
                                    <p class="card-description">Preencha os detalhes da análise abaixo.</p>
                                    <?php if (!empty($notification)) echo $notification; ?>
                                    <form class="forms-sample" method="POST" action="">
                                        <!-- Seleção de Cliente -->
                                        <div class="form-group">
                                            <label for="cliente">Cliente</label>
                                            <select name="cliente" class="form-select" id="cliente" required>
                                                <option value="" disabled selected>Selecione um cliente</option>
                                                <?php foreach ($clientes as $cliente): ?>
                                                    <option value="<?php echo $cliente['idCliente']; ?>">
                                                        <?php echo htmlspecialchars($cliente['nomeCliente']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Seleção de Agência -->
                                        <div class="form-group">
                                            <label for="agencia">Agência</label>
                                            <select name="agencia" class="form-select" id="agencia" required>
                                                <option value="" disabled selected>Selecione uma agência</option>
                                                <?php foreach ($agencias as $agencia): ?>
                                                    <option value="<?php echo $agencia['idAgencia']; ?>">
                                                        <?php echo htmlspecialchars($agencia['nomeAgencia']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Seleção de Organização -->
                                        <div class="form-group">
                                            <label for="organizacao">Organização</label>
                                            <select name="organizacao" class="form-select" id="organizacao" required>
                                                <option value="" disabled selected>Selecione uma organização</option>
                                                <?php foreach ($organizacoes as $organizacao): ?>
                                                    <option value="<?php echo $organizacao['idOrganizacao']; ?>">
                                                        <?php echo htmlspecialchars($organizacao['nomeOrganizacao']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Estado da Análise -->
                                        <div class="form-group">
                                            <label for="estadoAnalise">Estado da Análise</label>
                                            <select name="estadoAnalise" class="form-select" id="estadoAnalise" required>
                                                <option value="" disabled selected>Selecione um estado</option>
                                                <?php foreach ($estados as $estado): ?>
                                                    <option value="<?php echo $estado; ?>">
                                                        <?php echo htmlspecialchars(ucfirst($estado)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Descrição da Análise -->
                                        <div class="form-group">
                                            <label for="descricaoAnalise">Descrição da Análise</label>
                                            <textarea name="descricaoAnalise" class="form-control" id="descricaoAnalise" rows="4" placeholder="Insira a descrição da análise" required></textarea>
                                        </div>
                                        <!-- Botões -->
                                        <button type="submit" class="btn btn-primary me-2">Adicionar</button>
                                        <button type="button" class="btn btn-light" onclick="location.href='../tables/analysis-table.php';">Cancelar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fim do Formulário de Adicionar Análise -->
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="../../assets/vendors/select2/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para os selects
            $('.form-select').select2({
                placeholder: "Selecione uma opção",
                allowClear: true,
                width: 'resolve'
            });
        });
    </script>
</body>
</html>