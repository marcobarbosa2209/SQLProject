<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('pages/pdo/connection.php');
require('pages/pdo/user_auth.php');
checkUserAuthentication();

// Função para enviar consulta ao ChatGPT e obter SQL
function getSQLFromChatGPT($userQuery, $dbSchemaText, $apiKey) {
    $apiUrl = 'https://api.openai.com/v1/chat/completions';

    $messages = [
        ["role" => "system", "content" => "Você é um assistente SQL. Converta consultas em linguagem natural para SQL. Retorne apenas a consulta SQL sem explicações ou mensagens de erro. Também quero que formates sempre o nome de cada tabela para um nome mais sofisticado, ou seja, invés de idCliente escreve ID Cliente. Se souberes o layout da base de dados, ao mostrar tabelas joined à original, mostra o nome ou descrição da mesma invés do id. Remova o 'sql' do início. Para os nomes de cada tabela, usa o seguinte exemplo: Select nomeCliente as Nome do Cliente"],
        ["role" => "system", "content" => $dbSchemaText],
        ["role" => "user", "content" => $userQuery]
    ];

    $data = [
        'model' => 'gpt-4', // Você pode mudar para 'gpt-3.5-turbo' se necessário
        'messages' => $messages,
        'max_tokens' => 200,
    ];

    $ch = curl_init($apiUrl);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if ($response === FALSE) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['error' => 'Erro na comunicação com a API do OpenAI.', 'details' => $error];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode !== 200) {
        return ['error' => 'Erro na API do OpenAI.', 'status_code' => $httpCode, 'response' => $result];
    }

    if (!isset($result['choices'][0]['message']['content'])) {
        return ['error' => 'Erro ao gerar a consulta SQL.', 'response' => $result];
    }

    $sqlQuery = trim($result['choices'][0]['message']['content']);

    if (str_starts_with($sqlQuery, 'sql ')) { $sqlQuery = substr($sqlQuery, 3); }

    // Validação básica da consulta SQL
    $validSqlCommands = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP', 'ALTER'];
    $isValidSql = false;

    foreach ($validSqlCommands as $command) {
        if (stripos($sqlQuery, $command) === 0) {
            $isValidSql = true;
            break;
        }
    }

    if (!$isValidSql) {
        return ['error' => 'A consulta SQL gerada não é válida.', 'consulta_gerada' => $sqlQuery];
    }

    return ['sql' => $sqlQuery];
}

// Função para executar a consulta SQL e retornar resultados
function executeSQL($pdo, $sqlQuery) {
    try {
        $stmt = $pdo->query($sqlQuery);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            return ['message' => 'Nenhum resultado encontrado.'];
        }

        return ['columns' => array_keys($results[0]), 'results' => $results];
    } catch (PDOException $e) {
        return ['error' => 'Erro ao executar a consulta: ' . $e->getMessage()];
    }
}

// Função para escapar saída HTML e substituir null por 'N/A'
function escape($string) {
    return htmlspecialchars($string ?? 'N/A', ENT_QUOTES, 'UTF-8');
}

// Inicializar variáveis para armazenar resultados ou erros
$queryResult = null;
$queryError = null;

// Verificar se há uma consulta personalizada enviada via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom_query']) && isset($_POST['form_type'])) {
    $userQuery = trim($_POST['custom_query']);
    $formType = $_POST['form_type'];

    if (empty($userQuery)) {
        $queryError = 'A consulta é obrigatória.';
    } else {
        // Descrição concisa do esquema do banco de dados em uma linha
        $dbSchemaText = "
A base de dados LicencaAutomovel inclui as seguintes tabelas:

1. **Agencia**:
   - `idAgencia`: int(11), não nulo, chave primária.
   - `nomeAgencia`: varchar(45), não nulo.
   - `idContacto`: int(11), permite NULL, chave estrangeira referenciando `Contacto.idContacto`.
   - `idUtilizador`: int(11), permite NULL, chave estrangeira referenciando `Utilizador.idUtilizador`.

2. **Analise**:
   - `idAnalise`: int(11), não nulo, chave primária.
   - `estadoAnalise`: varchar(45), permite NULL.
   - `descricaoAnalise`: varchar(150), permite NULL.
   - `idContrato`: int(11), não nulo, chave estrangeira referenciando `Contrato.idContrato`.

3. **Cliente**:
   - `idCliente`: int(11), não nulo, chave primária.
   - `nomeCliente`: varchar(45), não nulo.
   - `idAgencia`: int(11), permite NULL, chave estrangeira referenciando `Agencia.idAgencia`.
   - `idTipoCliente`: int(11), permite NULL, chave estrangeira referenciando `TipoCliente.idTipoCliente`.
   - `idContacto`: int(11), permite NULL, chave estrangeira referenciando `Contacto.idContacto`.
   - `idUtilizador`: int(11), permite NULL, chave estrangeira referenciando `Utilizador.idUtilizador`.

4. **CodigoPostal**:
   - `codigoPostal`: varchar(8), não nulo, chave primária.
   - `nomeLocalidade`: varchar(45), não nulo.

5. **Contacto**:
   - `idContacto`: int(11), não nulo, chave primária.
   - `valorContacto`: varchar(45), não nulo.
   - `idTipoContacto`: int(11), permite NULL, chave estrangeira referenciando `TipoContacto.idTipoContacto`.

6. **Contrato**:
   - `idContrato`: int(11), não nulo, chave primária.
   - `nomeContrato`: varchar(45), permite NULL.
   - `estadoContrato`: varchar(45), permite NULL.
   - `idOrganizacao`: int(11), não nulo, chave estrangeira referenciando `Organizacao.idOrganizacao`.
   - `idAgencia`: int(11), não nulo, chave estrangeira referenciando `Agencia.idAgencia`.
   - `idCliente`: int(11), não nulo, chave estrangeira referenciando `Cliente.idCliente`.

7. **ContratoLicenca**:
   - `idContrato`: int(11), não nulo, chave estrangeira referenciando `Contrato.idContrato`.
   - `idLicenca`: int(11), não nulo, chave estrangeira referenciando `Licenca.idLicenca`.
   - **Chave Primária**: (`idContrato`, `idLicenca`).

8. **Ficheiro**:
   - `idFicheiro`: int(11), não nulo, chave primária.
   - `nomeFicheiro`: varchar(255), não nulo.
   - `idContrato`: int(11), não nulo, chave estrangeira referenciando `Contrato.idContrato`.

9. **Licenca**:
   - `idLicenca`: int(11), não nulo, chave primária.
   - `nomeLicenca`: varchar(45), permite NULL.
   - `idTipoLicenca`: int(11), permite NULL, chave estrangeira referenciando `TipoLicenca.idTipoLicenca`.

10. **LicencaProduto**:
    - `idLicenca`: int(11), não nulo, chave estrangeira referenciando `Licenca.idLicenca`.
    - `idProduto`: int(11), não nulo, chave estrangeira referenciando `Produto.idProduto`.
    - **Chave Primária**: (`idLicenca`, `idProduto`).

11. **Morada**:
    - `idMorada`: int(11), não nulo, chave primária.
    - `nomeMorada`: varchar(45), não nulo.
    - `porta`: int(11), não nulo.
    - `codigoPostal`: varchar(8), não nulo, chave estrangeira referenciando `CodigoPostal.codigoPostal`.
    - `idAgencia`: int(11), permite NULL, chave estrangeira referenciando `Agencia.idAgencia`.
    - `idOrganizacao`: int(11), permite NULL, chave estrangeira referenciando `Organizacao.idOrganizacao`.

12. **Organizacao**:
    - `idOrganizacao`: int(11), não nulo, chave primária.
    - `nomeOrganizacao`: varchar(45), não nulo.
    - `idAgencia`: int(11), não nulo, chave estrangeira referenciando `Agencia.idAgencia`.
    - `idContacto`: int(11), não nulo, chave estrangeira referenciando `Contacto.idContacto`.
    - `idUtilizador`: int(11), permite NULL, chave estrangeira referenciando `Utilizador.idUtilizador`.

13. **PedidoCriacaoConta**:
    - `idPedidoCriacaoConta`: int(11), não nulo, chave primária.
    - `estadoPedidoCriacaoConta`: varchar(45), não nulo.

14. **Produto**:
    - `idProduto`: int(11), não nulo, chave primária.
    - `nomeProduto`: varchar(45), permite NULL.

15. **TipoCliente**:
    - `idTipoCliente`: int(11), não nulo, chave primária.
    - `nomeTipoCliente`: varchar(45), permite NULL.

16. **TipoContacto**:
    - `idTipoContacto`: int(11), não nulo, chave primária.
    - `nomeTipoContacto`: varchar(45), permite NULL.

17. **TipoLicenca**:
    - `idTipoLicenca`: int(11), não nulo, chave primária.
    - `nomeTipoLicenca`: varchar(45), permite NULL.

18. **TipoUtilizador**:
    - `idTipoUtilizador`: int(11), não nulo, chave primária.
    - `nomeTipoUtilizador`: varchar(45), permite NULL.

19. **Utilizador**:
    - `idUtilizador`: int(11), não nulo, chave primária.
    - `nomeUtilizador`: varchar(45), não nulo.
    - `emailUtilizador`: varchar(45), permite NULL, restrição única.
    - `passwordUtilizador`: varchar(255), não nulo.
    - `idTipoUtilizador`: int(11), permite NULL, chave estrangeira referenciando `TipoUtilizador.idTipoUtilizador`.
    - `idPedidoCriacaoConta`: int(11), permite NULL, chave estrangeira referenciando `PedidoCriacaoConta.idPedidoCriacaoConta`.
";
    
        // Recuperar a chave da API do OpenAI
        $apiKey = 'api key placeholder';

        if (!$apiKey) {
            $queryError = 'API Key não encontrada.';
        } else {
            // Obter SQL do ChatGPT
            $chatGPTResponse = getSQLFromChatGPT($userQuery, $dbSchemaText, $apiKey);

            if (isset($chatGPTResponse['error'])) {
                $queryError = $chatGPTResponse['error'];
                if (isset($chatGPTResponse['details'])) {
                    $queryError .= ' Detalhes: ' . $chatGPTResponse['details'];
                } elseif (isset($chatGPTResponse['consulta_gerada'])) {
                    $queryError .= ' Consulta Gerada: ' . $chatGPTResponse['consulta_gerada'];
                } elseif (isset($chatGPTResponse['status_code'])) {
                    $queryError .= ' Código de Status: ' . $chatGPTResponse['status_code'];
                }
            } else {
                // Executar a consulta SQL gerada
                $executeResponse = executeSQL($pdo, $chatGPTResponse['sql']);

                if (isset($executeResponse['error'])) {
                    $queryError = $executeResponse['error'];
                } elseif (isset($executeResponse['message'])) {
                    $queryResult = $executeResponse['message'];
                } else {
                    $queryResult = $executeResponse;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licenses Admin</title>
    <link rel="stylesheet" href="assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png">
</head>
<body>
    <div class="container-scroller">
        <?php require('pages/ui/navbar.php'); ?>
        <div class="container-fluid page-body-wrapper">
            <?php require('pages/ui/navsidebar.php'); ?>
            <div class="main-panel">
                <div class="content-wrapper">
                    <!-- Seção: Consulta Personalizada -->
                    <div class="row">
                        <div class="col-lg-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Consulta Personalizada</h4>
                                    <form method="POST" class="form-samples">
                                        <input type="hidden" name="form_type" value="consulta_1">
                                        <div class="form-group">
                                            <label for="custom_query">Escreva sua consulta:</label>
                                            <input type="text" id="custom_query" name="custom_query" class="form-control" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-2">Enviar Consulta</button>
                                    </form>

                                    <?php if ($queryError && $formType === 'consulta_1'): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?php echo escape($queryError); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($queryResult && $formType === 'consulta_1'): ?>
                                        <?php if (is_array($queryResult)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <?php foreach ($queryResult['columns'] as $column): ?>
                                                                <th><?php echo escape($column); ?></th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($queryResult['results'] as $row): ?>
                                                            <tr>
                                                                <?php foreach ($row as $cell): ?>
                                                                    <td><?php echo escape($cell); ?></td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info mt-3" role="alert">
                                                <?php echo escape($queryResult); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fim da Seção: Consulta Personalizada -->
                </div>
            </div>
        </div>
    </div>
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/template.js"></script>
</body>
</html>