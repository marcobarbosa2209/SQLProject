<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

$notification = '';

// Remover análise
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_analise'])) {
    $analiseId = intval($_POST['delete_analise']);
    
    try {
        $pdo->beginTransaction();
        
        // Verificar se a análise existe
        $stmtVerificar = $pdo->prepare("SELECT idContrato FROM Analise WHERE idAnalise = :idAnalise");
        $stmtVerificar->execute(['idAnalise' => $analiseId]);
        $analise = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
        
        if ($analise) {
            // Remover a análise
            $stmtDelete = $pdo->prepare("DELETE FROM Analise WHERE idAnalise = :idAnalise");
            $stmtDelete->execute(['idAnalise' => $analiseId]);
            
            $pdo->commit();
            $notification = newNotification("Análise removida com sucesso!", "success");
        } else {
            throw new Exception("Análise não encontrada.");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $notification = newNotification("Erro ao remover análise: " . $e->getMessage(), "error");
    }
}

// Atualizar análise
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['analise_id'])) {
    $analiseId = intval($_POST['analise_id']);
    $clienteId = intval($_POST['cliente'] ?? 0);
    $agenciaId = intval($_POST['agencia'] ?? 0);
    $organizacaoId = intval($_POST['organizacao'] ?? 0);
    $estadoAnalise = $_POST['estadoAnalise'] ?? '';
    $descricaoAnalise = $_POST['descricaoAnalise'] ?? '';
    
    // Validar campos obrigatórios
    if (!$analiseId || !$clienteId || !$agenciaId || !$organizacaoId || empty($estadoAnalise) || empty($descricaoAnalise)) {
        $notification = newNotification("Todos os campos são obrigatórios.", "error");
    } else {
        try {
            $pdo->beginTransaction();
            
            // Verificar se a análise existe
            $stmtVerificar = $pdo->prepare("SELECT idContrato FROM Analise WHERE idAnalise = :idAnalise");
            $stmtVerificar->execute(['idAnalise' => $analiseId]);
            $analise = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
            
            if (!$analise) {
                throw new Exception("Análise não encontrada.");
            }
            
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
            
            // Atualizar análise
            $stmtUpdateAnalise = $pdo->prepare("
                UPDATE Analise 
                SET estadoAnalise = :estado, descricaoAnalise = :descricao, idContrato = :contrato
                WHERE idAnalise = :idAnalise
            ");
            $stmtUpdateAnalise->execute([
                'estado' => $estadoAnalise,
                'descricao' => $descricaoAnalise,
                'contrato' => $idContrato,
                'idAnalise' => $analiseId
            ]);
            
            $pdo->commit();
            $notification = newNotification("Análise atualizada com sucesso!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            $notification = newNotification("Erro ao atualizar análise: " . $e->getMessage(), "error");
        }
    }
}

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

// Buscar análises existentes para a seleção com detalhes necessários
$stmtAnalisesDisponiveis = $pdo->prepare("
    SELECT 
        a.idAnalise,
        a.descricaoAnalise,
        cl.nomeCliente,
        ag.nomeAgencia,
        o.nomeOrganizacao
    FROM 
        Analise a
    INNER JOIN 
        Contrato c ON a.idContrato = c.idContrato
    INNER JOIN 
        Cliente cl ON c.idCliente = cl.idCliente
    INNER JOIN 
        Agencia ag ON c.idAgencia = ag.idAgencia
    INNER JOIN 
        Organizacao o ON c.idOrganizacao = o.idOrganizacao
    ORDER BY 
        a.idAnalise ASC
");
$stmtAnalisesDisponiveis->execute();
$analisesDisponiveis = $stmtAnalisesDisponiveis->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Análise</title>
    <link rel="stylesheet" href="../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="../../assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="shortcut icon" href="../../assets/images/favicon.png" />
    <script>
        async function fetchAnalysisData(analiseId) {
            if (!analiseId) return;

            try {
                const response = await fetch(`get_analysis_data.php?id=${analiseId}`);
                const data = await response.json();

                console.log("Dados recebidos:", data); // Para depuração

                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Preenche os campos da análise
                document.getElementById('cliente').value = data.cliente;
                document.getElementById('agencia').value = data.agencia;
                document.getElementById('organizacao').value = data.organizacao;
                document.getElementById('estadoAnalise').value = data.estadoAnalise;
                document.getElementById('descricaoAnalise').value = data.descricaoAnalise;

                // Atualizar Select2 para refletir as mudanças
                $('#cliente').trigger('change');
                $('#agencia').trigger('change');
                $('#organizacao').trigger('change');
                $('#estadoAnalise').trigger('change');
            } catch (error) {
                console.error("Erro ao buscar dados da análise:", error);
            }
        }

        function confirmDelete(analiseId) {
            if (!analiseId) {
                alert('Selecione uma análise para remover.');
                return;
            }

            if (confirm('Tem certeza de que deseja remover esta análise? Essa ação não pode ser desfeita.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = ''; // Enviar para a mesma página

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_analise';
                input.value = analiseId;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
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
                    <!-- Formulário de Atualizar Análise -->
                    <div class="row">
                        <div class="col-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Atualizar Análise</h4>
                                    <p class="card-description">Selecione uma análise para atualizar os detalhes de</p>
                                    <form class="forms-sample" method="POST" action="">
                                        <!-- Seleção de Análise -->
                                        <div class="form-group">
                                            <label for="analiseSelect">Selecionar Análise</label>
                                            <select name="analise_id" id="analiseSelect" class="form-select" onchange="fetchAnalysisData(this.value)" required>
                                                <option value="" disabled selected>Selecione uma análise</option>
                                                <?php foreach ($analisesDisponiveis as $analise): ?>
                                                    <?php
                                                        // Limitar a descrição a 20 caracteres
                                                        $descricao = $analise['descricaoAnalise'];
                                                        if (mb_strlen($descricao, 'UTF-8') > 20) {
                                                            $descricao = mb_substr($descricao, 0, 20, 'UTF-8') . '...';
                                                        }

                                                        // Combinar NomeCliente, NomeAgencia, NomeOrganizacao
                                                        $comboNome = $analise['nomeCliente'] . ' - ' . $analise['nomeAgencia'] . ' - ' . $analise['nomeOrganizacao'];
                                                    ?>
                                                    <option value="<?php echo $analise['idAnalise']; ?>">
                                                        <?php echo htmlspecialchars($descricao . ' - ' . $comboNome); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Seleção de Cliente -->
                                        <div class="form-group">
                                            <label for="cliente">Cliente</label>
                                            <select name="cliente" id="cliente" class="form-select" required>
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
                                            <select name="agencia" id="agencia" class="form-select" required>
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
                                            <select name="organizacao" id="organizacao" class="form-select" required>
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
                                            <select name="estadoAnalise" id="estadoAnalise" class="form-select" required>
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
                                            <textarea name="descricaoAnalise" id="descricaoAnalise" class="form-control" rows="4" placeholder="Insira a descrição da análise" required></textarea>
                                        </div>
                                        <!-- Botões -->
                                        <button type="submit" class="btn btn-primary me-2">Atualizar</button>
                                        <button type="button" class="btn btn-light" onclick="location.href='../tables/analyses_table.php';">Cancelar</button>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete(document.getElementById('analiseSelect').value)">Remover</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fim do Formulário de Atualizar Análise -->
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

        async function fetchAnalysisData(analiseId) {
            if (!analiseId) return;

            try {
                const response = await fetch(`get_analysis_data.php?id=${analiseId}`);
                const data = await response.json();

                console.log("Dados recebidos:", data); // Para depuração

                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Preenche os campos da análise
                document.getElementById('cliente').value = data.cliente;
                document.getElementById('agencia').value = data.agencia;
                document.getElementById('organizacao').value = data.organizacao;
                document.getElementById('estadoAnalise').value = data.estadoAnalise;
                document.getElementById('descricaoAnalise').value = data.descricaoAnalise;

                // Atualizar Select2 para refletir as mudanças
                $('#cliente').trigger('change');
                $('#agencia').trigger('change');
                $('#organizacao').trigger('change');
                $('#estadoAnalise').trigger('change');
            } catch (error) {
                console.error("Erro ao buscar dados da análise:", error);
            }
        }

        function confirmDelete(analiseId) {
            if (!analiseId) {
                alert('Selecione uma análise para remover.');
                return;
            }

            if (confirm('Tem certeza de que deseja remover esta análise? Essa ação não pode ser desfeita.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = ''; // Enviar para a mesma página

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_analise';
                input.value = analiseId;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>