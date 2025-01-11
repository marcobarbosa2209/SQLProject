<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
require '../pdo/helpers.php';
checkUserAuthentication();

// Buscar dados necessários
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar dados do formulário
    $contractName = $_POST['name'] ?? '';
    $contractStatus = $_POST['status'] ?? '';
    $organizationId = intval($_POST['organization_id'] ?? 0);
    $agencyId = intval($_POST['agency_id'] ?? 0);
    $clientId = intval($_POST['client_id'] ?? 0);
    $selectedLicenses = $_POST['selected_licenses'] ?? [];
    $newLicenses = $_POST['new_licenses'] ?? [];
    $uploadedFiles = $_FILES['files'] ?? null;

    // **Depuração Temporária: Logar o Conteúdo de new_licenses**
    error_log("Conteúdo de new_licenses: " . print_r($newLicenses, true));

    // **Depuração Temporária: Logar o Conteúdo de $_FILES**
    error_log("Conteúdo de \$_FILES: " . print_r($uploadedFiles, true));

    // Validação de campos obrigatórios
    if (empty($contractName) || empty($contractStatus) || !$organizationId || !$agencyId || !$clientId) {
        $notification = newNotification("Todos os campos obrigatórios devem ser preenchidos.", "danger");
        error_log("Validação falhou: Campos obrigatórios não foram preenchidos.");
    } else {
        try {
            $pdo->beginTransaction();

            // Inserir contrato
            $stmt = $pdo->prepare("
                INSERT INTO Contrato (nomeContrato, estadoContrato, idOrganizacao, idAgencia, idCliente) 
                VALUES (:nome, :estado, :organizacao, :agencia, :cliente)
            ");
            $stmt->execute([
                'nome' => $contractName,
                'estado' => $contractStatus,
                'organizacao' => $organizationId,
                'agencia' => $agencyId,
                'cliente' => $clientId,
            ]);
            $contractId = $pdo->lastInsertId();
            error_log("Contrato inserido com ID: " . $contractId);

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

                    // Opcional: Associar produtos existentes às licenças existentes
                    // Dependendo da lógica do seu sistema, você pode querer associar produtos às licenças existentes aqui
                }
            }

            // Upload de arquivos
            if ($uploadedFiles && isset($uploadedFiles['error'][0]) && $uploadedFiles['error'][0] !== UPLOAD_ERR_NO_FILE) {
                foreach ($uploadedFiles['tmp_name'] as $index => $tmpName) {
                    if ($uploadedFiles['error'][$index] === UPLOAD_ERR_OK) {
                        $originalFileName = basename($uploadedFiles['name'][$index]);
                        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
                        
                        // Gerar um nome único para o arquivo para evitar conflitos
                        $uniqueFileName = uniqid('file_', true) . '.' . $fileExtension;
                        $uploadDir = __DIR__ . '/../upload/'; // Caminho absoluto
                        $targetFile = $uploadDir . $uniqueFileName;

                        // Log do caminho de upload
                        error_log("Tentando mover o arquivo para: " . $targetFile);

                        if (!is_dir($uploadDir)) {
                            if (mkdir($uploadDir, 0755, true)) {
                                error_log("Diretório de upload criado: " . $uploadDir);
                            } else {
                                error_log("Falha ao criar o diretório de upload: " . $uploadDir);
                                continue; // Pular este arquivo
                            }
                        }

                        // **Verificação Opcional: Tipo MIME do Arquivo**
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $tmpName);
                        finfo_close($finfo);

                        // Definir tipos MIME permitidos
                        $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf', 'text/plain']; // Ajuste conforme necessário

                        if (!in_array($mimeType, $allowedMimeTypes)) {
                            error_log("Tipo de arquivo não permitido: " . $mimeType . " para o arquivo " . $originalFileName);
                            $notification = newNotification("Tipo de arquivo não permitido: " . htmlspecialchars($originalFileName), "danger");
                            continue; // Pular este arquivo
                        }

                        // **Mover o Arquivo**
                        if (move_uploaded_file($tmpName, $targetFile)) {
                            error_log("Arquivo movido com sucesso para: " . $targetFile);
                            $stmt = $pdo->prepare("
                                INSERT INTO Ficheiro (nomeFicheiro, idContrato) 
                                VALUES (:nome, :contrato)
                            ");
                            $stmt->execute([
                                'nome' => $uniqueFileName, // Usar o nome único
                                'contrato' => $contractId,
                            ]);
                            error_log("Arquivo $uniqueFileName inserido na tabela Ficheiro para o contrato $contractId");
                        } else {
                            error_log("Falha ao mover o arquivo para: " . $targetFile);
                            $notification = newNotification("Falha ao mover o arquivo: " . htmlspecialchars($originalFileName), "danger");
                        }
                    } else {
                        error_log("Erro no upload do arquivo: " . $uploadedFiles['error'][$index]);
                        $notification = newNotification("Erro no upload do arquivo: " . $uploadedFiles['error'][$index], "danger");
                    }
                }
            }

            $pdo->commit();
            $notification = newNotification("Contrato criado com sucesso!", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            $notification = newNotification("Erro ao criar contrato: " . $e->getMessage(), "danger");
            error_log("Erro ao criar contrato: " . $e->getMessage());
        }
    }
}
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
                <!-- Formulário de Adição de Contrato -->
                <div class="row">
                    <div class="col-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Adicionar Contrato</h4>
                                <form class="forms-sample" method="POST" action="" enctype="multipart/form-data">
                                    <!-- Nome do Contrato -->
                                    <div class="form-group">
                                        <label for="name">Nome do Contrato</label>
                                        <input type="text" name="name" id="name" class="form-control" placeholder="Nome do Contrato" required>
                                    </div>
                                    <!-- Estado do Contrato -->
                                    <div class="form-group">
                                        <label for="status">Estado</label>
                                        <select name="status" id="status" class="form-control" required>
                                            <option value="" disabled selected>Selecione o estado</option>
                                            <option value="active">Ativo</option>
                                            <option value="inactive">Inativo</option>
                                            <option value="pending">Pendente</option>
                                        </select>
                                    </div>
                                    <!-- Organização -->
                                    <div class="form-group">
                                        <label for="organization">Organização</label>
                                        <select name="organization_id" id="organization" class="form-control" required>
                                            <option value="" disabled selected>Selecione uma organização</option>
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
                                            <option value="" disabled selected>Selecione uma agência</option>
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
                                            <option value="" disabled selected>Selecione um cliente</option>
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
                                    <!-- Upload de Arquivos -->
                                    <div class="form-group">
                                        <label for="files">Arquivos</label>
                                        <input type="file" name="files[]" id="files" class="form-control" multiple>
                                    </div>
                                    <!-- Botões de Submissão -->
                                    <button type="submit" class="btn btn-primary me-2">Adicionar</button>
                                    <button type="button" class="btn btn-light" onclick="location.href='../tables/contracts-table.php';">Cancelar</button>
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
    </script>
</body>
</html>