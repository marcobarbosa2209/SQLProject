<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Lógica para remover contrato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_contract'])) {
  $contractId = intval($_POST['delete_contract']); // Sanitizar o ID

  try {
      $pdo->beginTransaction();

      // Remova o contrato pelo ID
      $stmt = $pdo->prepare("DELETE FROM Contrato WHERE idContrato = :idContrato");
      $stmt->execute(['idContrato' => $contractId]);

      $pdo->commit();
      $notification = newNotification("Contrato removido com sucesso!", "success");
  } catch (Exception $e) {
      $pdo->rollBack();
      $notification = newNotification("Erro ao remover o contrato: " . $e->getMessage(), "error");
  }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['contract_id'])) {
        // Atualizar contrato existente
        $contractId = intval($_POST['contract_id']);
        $contractName = $_POST['name'] ?? '';
        $contractStatus = $_POST['status'] ?? '';
        $organizationId = intval($_POST['organization_id'] ?? 0);
        $agencyId = intval($_POST['agency_id'] ?? 0);
        $clientId = intval($_POST['client_id'] ?? 0);
        $selectedLicenses = $_POST['selected_licenses'] ?? [];
        $newLicenses = $_POST['new_licenses'] ?? [];
        // Arquivos serão ignorados por enquanto conforme solicitado

        // Validação de campos obrigatórios
        if (empty($contractName) || empty($contractStatus) || !$organizationId || !$agencyId || !$clientId) {
            $notification = newNotification("Todos os campos obrigatórios devem ser preenchidos.", "danger");
            error_log("Validação falhou: Campos obrigatórios não foram preenchidos.");
        } else {
            try {
                $pdo->beginTransaction();

                // Atualizar contrato
                $stmt = $pdo->prepare("
                    UPDATE Contrato 
                    SET nomeContrato = :nome, estadoContrato = :estado, idOrganizacao = :organizacao, idAgencia = :agencia, idCliente = :cliente
                    WHERE idContrato = :idContrato
                ");
                $stmt->execute([
                    'nome' => $contractName,
                    'estado' => $contractStatus,
                    'organizacao' => $organizationId,
                    'agencia' => $agencyId,
                    'cliente' => $clientId,
                    'idContrato' => $contractId,
                ]);
                error_log("Contrato atualizado com ID: " . $contractId);

                // Remover associações existentes de licenças
                $stmt = $pdo->prepare("DELETE FROM ContratoLicenca WHERE idContrato = :idContrato");
                $stmt->execute(['idContrato' => $contractId]);
                error_log("Associações de licenças removidas para o contrato $contractId");

                // Inserir novas licenças e produtos associados
                foreach ($newLicenses as $licenseIndex => $license) {
                    if (!empty($license['name']) && !empty($license['type'])) {
                        // Inserir nova licença
                        $stmt = $pdo->prepare("
                            INSERT INTO Licenca (nomeLicenca, idTipoLicenca) 
                            VALUES (:nome, :tipo)
                        ");
                        $stmt->execute([
                            'nome' => $license['name'],
                            'tipo' => $license['type'],
                        ]);
                        $licenseId = $pdo->lastInsertId();
                        error_log("Licença inserida com ID: " . $licenseId);

                        // Associar licença ao contrato
                        $stmt = $pdo->prepare("
                            INSERT INTO ContratoLicenca (idContrato, idLicenca) 
                            VALUES (:contrato, :licenca)
                        ");
                        $stmt->execute([
                            'contrato' => $contractId,
                            'licenca' => $licenseId,
                        ]);
                        error_log("Licença $licenseId associada ao contrato $contractId");

                        // Associar produtos existentes à licença
                        if (!empty($license['products'])) {
                            foreach ($license['products'] as $productId) {
                                if (!empty($productId)) { // Verificar se o productId não está vazio
                                    $stmt = $pdo->prepare("
                                        INSERT INTO LicencaProduto (idLicenca, idProduto) 
                                        VALUES (:licenca, :produto)
                                    ");
                                    $stmt->execute([
                                        'licenca' => $licenseId,
                                        'produto' => $productId,
                                    ]);
                                    error_log("Produto $productId associado à licença $licenseId");
                                }
                            }
                        }

                        // Inserir novos produtos específicos para a licença
                        if (!empty($license['new_products'])) {
                            foreach ($license['new_products'] as $newProductName) {
                                if (!empty($newProductName)) {
                                    $stmt = $pdo->prepare("
                                        INSERT INTO Produto (nomeProduto) 
                                        VALUES (:nome)
                                    ");
                                    $stmt->execute(['nome' => $newProductName]);
                                    $newProductId = $pdo->lastInsertId();
                                    error_log("Novo produto inserido com ID: " . $newProductId . " para a licença $licenseId");

                                    // Associar o novo produto à licença
                                    $stmt = $pdo->prepare("
                                        INSERT INTO LicencaProduto (idLicenca, idProduto) 
                                        VALUES (:licenca, :produto)
                                    ");
                                    $stmt->execute([
                                        'licenca' => $licenseId,
                                        'produto' => $newProductId,
                                    ]);
                                    error_log("Novo produto $newProductId associado à licença $licenseId");
                                }
                            }
                        }
                    } else {
                        error_log("Licença ignorada devido a campos vazios. Índice: $licenseIndex");
                    }
                }

                // Associar licenças existentes ao contrato
                foreach ($selectedLicenses as $licenseId) {
                    if (!empty($licenseId)) { // Verificar se o licenseId não está vazio
                        $stmt = $pdo->prepare("
                            INSERT INTO ContratoLicenca (idContrato, idLicenca) 
                            VALUES (:contrato, :licenca)
                        ");
                        $stmt->execute([
                            'contrato' => $contractId,
                            'licenca' => $licenseId,
                        ]);
                        error_log("Licença existente $licenseId associada ao contrato $contractId");
                    }
                }

                // Arquivos serão ignorados por agora conforme solicitado

                $pdo->commit();
                $notification = newNotification("Contrato atualizado com sucesso!", "success");
            } catch (Exception $e) {
                $pdo->rollBack();
                $notification = newNotification("Erro ao atualizar contrato: " . $e->getMessage(), "danger");
                error_log("Erro ao atualizar contrato: " . $e->getMessage());
            }
        }
    }
}

// Buscar dados necessários para os selects
$stmtOrganizacoes = $pdo->prepare("SELECT idOrganizacao, nomeOrganizacao FROM Organizacao");
$stmtOrganizacoes->execute();
$organizacoes = $stmtOrganizacoes->fetchAll(PDO::FETCH_ASSOC);

$stmtAgencias = $pdo->prepare("SELECT idAgencia, nomeAgencia FROM Agencia");
$stmtAgencias->execute();
$agencias = $stmtAgencias->fetchAll(PDO::FETCH_ASSOC);

$stmtClientes = $pdo->prepare("SELECT idCliente, nomeCliente FROM Cliente");
$stmtClientes->execute();
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

$stmtTiposLicenca = $pdo->prepare("SELECT idTipoLicenca, nomeTipoLicenca FROM TipoLicenca");
$stmtTiposLicenca->execute();
$tiposLicenca = $stmtTiposLicenca->fetchAll(PDO::FETCH_ASSOC);

$stmtLicencas = $pdo->prepare("SELECT idLicenca, nomeLicenca FROM Licenca");
$stmtLicencas->execute();
$licencas = $stmtLicencas->fetchAll(PDO::FETCH_ASSOC);

$stmtProdutos = $pdo->prepare("SELECT idProduto, nomeProduto FROM Produto");
$stmtProdutos->execute();
$produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);

// Buscar todos os contratos para seleção
$stmtContratos = $pdo->prepare("SELECT idContrato, nomeContrato FROM Contrato");
$stmtContratos->execute();
$contratos = $stmtContratos->fetchAll(PDO::FETCH_ASSOC);

$notification = '';

?>
    
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Contratos</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="../../assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="../../assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/vendors/mdi/css/materialdesignicons.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="../../assets/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="../../assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <!-- endinject -->
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
                <!-- Notificações -->
                <?php if (!empty($notification)) echo $notification; ?>
                <!-- Formulário de Atualização de Contrato -->
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Atualizar Contrato</h4>
                                <form class="forms-sample" method="POST" action="">
                                    <!-- Seleção do Contrato -->
                                    <div class="form-group">
                                        <label for="contractSelect">Selecionar Contrato</label>
                                        <select name="contract_id" id="contractSelect" class="form-control" onchange="fetchContractData(this.value)" required>
                                            <option value="" disabled selected>Selecione um contrato</option>
                                            <?php foreach ($contratos as $contrato): ?>
                                                <option value="<?= htmlspecialchars($contrato['idContrato']) ?>">
                                                    <?= htmlspecialchars($contrato['nomeContrato']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Nome do Contrato -->
                                    <div class="form-group">
                                        <label for="name">Nome do Contrato</label>
                                        <input type="text" name="name" id="name" class="form-control" placeholder="Nome do Contrato" required>
                                    </div>
                                    <!-- Estado do Contrato -->
                                    <div class="form-group">
                                        <label for="status">Estado</label>
                                        <select name="status" id="status" class="form-control" required>
                                            <option value="" disabled>Selecione o estado</option>
                                            <option value="active">Ativo</option>
                                            <option value="inactive">Inativo</option>
                                            <option value="pending">Pendente</option>
                                        </select>
                                    </div>
                                    <!-- Organização -->
                                    <div class="form-group">
                                        <label for="organization">Organização</label>
                                        <select name="organization_id" id="organization" class="form-control" required>
                                            <option value="" disabled>Selecione uma organização</option>
                                            <?php foreach ($organizacoes as $organizacao): ?>
                                                <option value="<?= htmlspecialchars($organizacao['idOrganizacao']) ?>">
                                                    <?= htmlspecialchars($organizacao['nomeOrganizacao']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <!-- Agência -->
                                    <div class="form-group">
                                        <label for="agency">Agência</label>
                                        <select name="agency_id" id="agency" class="form-control" required>
                                            <option value="" disabled>Selecione uma agência</option>
                                            <?php foreach ($agencias as $agencia): ?>
                                                <option value="<?= htmlspecialchars($agencia['idAgencia']) ?>">
                                                    <?= htmlspecialchars($agencia['nomeAgencia']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <!-- Cliente -->
                                    <div class="form-group">
                                        <label for="client">Cliente</label>
                                        <select name="client_id" id="client" class="form-control" required>
                                            <option value="" disabled>Selecione um cliente</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                                <option value="<?= htmlspecialchars($cliente['idCliente']) ?>">
                                                    <?= htmlspecialchars($cliente['nomeCliente']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <!-- Seleção Múltipla de Licenças Existentes -->
                                    <div class="form-group">
                                        <label for="existingLicenses">Selecionar Licenças Existentes</label>
                                        <select name="selected_licenses[]" id="existingLicenses" class="js-example-basic-multiple w-100" multiple="multiple">
                                            <?php foreach ($licencas as $licenca): ?>
                                                <option value="<?= htmlspecialchars($licenca['idLicenca']) ?>">
                                                    <?= htmlspecialchars($licenca['nomeLicenca']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <!-- Adicionar Novas Licenças -->
                                    <div class="form-group">
                                        <label>Adicionar Novas Licenças</label>
                                        <br>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addLicense()">Adicionar Nova Licença</button>
                                        <div id="newLicensesContainer" class="mt-3">
                                            <!-- Licenças adicionadas dinamicamente serão inseridas aqui -->
                                        </div>
                                    </div>
                                    <!-- Seleção Múltipla de Arquivos Existentes -->
                                    <div class="form-group">
                                        <label for="existingFiles">Arquivos Associados</label>
                                        <select name="existing_files[]" id="existingFiles" class="js-example-basic-multiple w-100" multiple="multiple">
                                            <!-- Opções serão preenchidas via JavaScript -->
                                        </select>
                                    </div>
                                    <!-- Adicionar Novos Arquivos -->
                                    <div class="form-group">
                                        <label>Adicionar Novos Arquivos</label>
                                        <br>
                                        <input type="file" name="new_files[]" id="newFiles" class="form-control" multiple>
                                    </div>
                                    <!-- Botões de Submissão -->
                                    <button type="submit" class="btn btn-primary me-2">Atualizar</button>
                                    <button type="button" class="btn btn-light me-2" onclick="location.href='../tables/contracts-table.php';">Cancelar</button>
                                    <button type="button" class="btn btn-danger" onclick="confirmDelete(document.getElementById('contractSelect').value)">Remover</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Content Wrapper Ends -->
            </div>
            <!-- Main Panel Ends -->
        </div>
        <!-- Page Body Wrapper Ends -->
    </div>
    <!-- Container Scroller Ends -->
    <!-- plugins:js -->
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="../../assets/vendors/typeahead.js/typeahead.bundle.min.js"></script>
    <script src="../../assets/vendors/select2/select2.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="../../assets/js/off-canvas.js"></script>
    <script src="../../assets/js/template.js"></script>
    <script src="../../assets/js/settings.js"></script>
    <script src="../../assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="../../assets/js/file-upload.js"></script>
    <script src="../../assets/js/typeahead.js"></script>
    <script src="../../assets/js/select2.js"></script>
    <!-- End custom js for this page-->
    <script>
    let licenseCount = 0; // Contador para licenças

    // Função para adicionar novas licenças
    function addLicense() {
        const container = document.getElementById('newLicensesContainer');
        const currentLicenseIndex = licenseCount; // Capturar o índice atual

        const licenseDiv = document.createElement('div');
        licenseDiv.classList.add('license-item', 'mb-4');
        licenseDiv.style.border = '1px solid #ccc';
        licenseDiv.style.padding = '15px';
        licenseDiv.style.borderRadius = '5px';
        licenseDiv.style.position = 'relative';

        // Campo para o nome da licença
        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.name = `new_licenses[${currentLicenseIndex}][name]`;
        nameInput.placeholder = 'Nome da Licença';
        nameInput.classList.add('form-control', 'mb-2');

        // Campo para o tipo de licença (select de uma única opção)
        const typeSelect = document.createElement('select');
        typeSelect.name = `new_licenses[${currentLicenseIndex}][type]`;
        typeSelect.classList.add('form-control', 'mb-2', 'js-example-basic-single-new-license');
        typeSelect.innerHTML = `<option value="" disabled selected>Selecione o Tipo de Licença</option>`;
        <?php foreach ($tiposLicenca as $tipoLicenca): ?>
            typeSelect.innerHTML += `<option value="<?= htmlspecialchars($tipoLicenca['idTipoLicenca']) ?>"><?= htmlspecialchars($tipoLicenca['nomeTipoLicenca']) ?></option>`;
        <?php endforeach; ?>

        // Label para produtos relacionados à licença
        const productsLabel = document.createElement('label');
        productsLabel.textContent = 'Selecionar Produtos Relacionados';
        productsLabel.classList.add('mt-2');

        // Seleção múltipla de produtos relacionados à licença
        const productsSelect = document.createElement('select');
        productsSelect.name = `new_licenses[${currentLicenseIndex}][products][]`;
        productsSelect.classList.add('js-example-basic-multiple-products', 'w-100', 'mb-2');
        productsSelect.setAttribute('multiple', 'multiple');
        <?php foreach ($produtos as $produto): ?>
            productsSelect.innerHTML += `<option value="<?= htmlspecialchars($produto['idProduto']) ?>"><?= htmlspecialchars($produto['nomeProduto']) ?></option>`;
        <?php endforeach; ?>

        // Botão para adicionar novo produto específico à licença
        const addProductButton = document.createElement('button');
        addProductButton.type = 'button';
        addProductButton.textContent = 'Adicionar Novo Produto';
        addProductButton.classList.add('btn', 'btn-outline-secondary', 'btn-sm', 'mt-2');
        addProductButton.onclick = function() {
            addProductToLicense(licenseDiv, currentLicenseIndex); // Usar o índice capturado
        };

        // Container para novos produtos específicos à licença
        const newProductsLicenseContainer = document.createElement('div');
        newProductsLicenseContainer.classList.add('new-products-license-container', 'mt-2');

        // Botão para remover a licença
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.textContent = 'Remover Licença';
        removeButton.classList.add('btn', 'btn-outline-danger', 'btn-sm', 'mt-2');
        removeButton.onclick = function() {
            container.removeChild(licenseDiv);
        };

        // Adicionar elementos ao container da licença
        licenseDiv.appendChild(nameInput);
        licenseDiv.appendChild(typeSelect);
        licenseDiv.appendChild(productsLabel);
        licenseDiv.appendChild(productsSelect);
        licenseDiv.appendChild(addProductButton);
        licenseDiv.appendChild(newProductsLicenseContainer);
        licenseDiv.appendChild(removeButton);

        // Adicionar a licença ao container principal
        container.appendChild(licenseDiv);

        // Re-inicializar Select2 para os novos selects
        $(typeSelect).select2({
            placeholder: "Selecione o Tipo de Licença",
            allowClear: true,
            width: 'resolve'
        });

        $(productsSelect).select2({
            placeholder: "Selecione Produtos",
            allowClear: true,
            width: 'resolve'
        });

        licenseCount++; // Incrementar após adicionar a licença
    }

    // Função para adicionar novos produtos específicos à licença
    function addProductToLicense(licenseDiv, index) {
        const container = licenseDiv.querySelector('.new-products-license-container');

        const productDiv = document.createElement('div');
        productDiv.classList.add('product-item', 'mb-2');
        productDiv.style.display = 'flex';
        productDiv.style.alignItems = 'center';

        // Campo para o nome do produto
        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.name = `new_licenses[${index}][new_products][]`;
        nameInput.placeholder = 'Nome do Produto';
        nameInput.classList.add('form-control', 'mb-2');
        nameInput.style.flex = '1';

        // Botão para remover o produto
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.textContent = 'Remover';
        removeButton.classList.add('btn', 'btn-outline-danger', 'btn-sm', 'ms-2');
        removeButton.onclick = function() {
            container.removeChild(productDiv);
        };

        // Adicionar elementos ao container do produto
        productDiv.appendChild(nameInput);
        productDiv.appendChild(removeButton);

        // Adicionar o produto ao container específico da licença
        container.appendChild(productDiv);
    }

    // Função para buscar dados do contrato selecionado
    async function fetchContractData(contractId) {
        if (!contractId) {
            return;
        }

        try {
            const response = await fetch(`get_contract_data.php?id=${contractId}`);
            const data = await response.json();

            if (data.error) {
                alert(data.error);
                return;
            }

            // Preenche os campos do formulário com os dados retornados ou valores padrão
            document.getElementById('name').value = data.nomeContrato || '';
            document.getElementById('status').value = data.estadoContrato || '';
            document.getElementById('organization').value = data.idOrganizacao || '';
            document.getElementById('agency').value = data.idAgencia || '';
            document.getElementById('client').value = data.idCliente || '';

            // Atualizar licenças existentes selecionadas
            const existingLicensesSelect = $('#existingLicenses');
            existingLicensesSelect.val(data.associatedLicenses).trigger('change');

            // Atualizar arquivos existentes
            const existingFilesSelect = $('#existingFiles');
            existingFilesSelect.empty(); // Limpar opções existentes
            if (data.files && data.files.length > 0) {
                data.files.forEach(file => {
                    existingFilesSelect.append(new Option(file.nomeFicheiro, file.idFicheiro, true, true));
                });
            }
            existingFilesSelect.trigger('change');

            // Atualizar novas licenças adicionadas dinamicamente
            document.getElementById('newLicensesContainer').innerHTML = ''; // Limpar licenças anteriores
            licenseCount = 0; // Resetar contador
        } catch (error) {
            console.error("Erro ao buscar dados do contrato:", error);
        }
    }

    function confirmDelete(contractId) {
      if (!contractId) {
          alert('Selecione um contrato para remover.');
          return;
      }

      if (confirm('Tem certeza de que deseja remover este contrato? Essa ação não pode ser desfeita.')) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = ''; // Enviar para a mesma página

          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'delete_contract';
          input.value = contractId;

          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
      }
    }

    $(document).ready(function() {
        // Inicializar Select2 para multi-selects
        $('.js-example-basic-multiple').select2({
            placeholder: "Selecione opções",
            allowClear: true,
            width: 'resolve'
        });

        $('.js-example-basic-multiple-products').select2({
            placeholder: "Selecione Produtos",
            allowClear: true,
            width: 'resolve'
        });

        $('.js-example-basic-single-new-license').select2({
            placeholder: "Selecione o Tipo de Licença",
            allowClear: true,
            width: 'resolve'
        });

        // Inicializar Select2 para arquivos existentes
        $('#existingFiles').select2({
            placeholder: "Selecione Arquivos",
            allowClear: true,
            width: 'resolve'
        });
    });
    </script>
</body>
</html>