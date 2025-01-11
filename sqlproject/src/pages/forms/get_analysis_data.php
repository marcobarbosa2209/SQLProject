<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
checkUserAuthentication();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $analiseId = intval($_GET['id']);

    try {
        // Buscar informações da análise e do contrato associado
        $stmtAnalise = $pdo->prepare("
            SELECT a.estadoAnalise, a.descricaoAnalise, a.idContrato
            FROM Analise a
            WHERE a.idAnalise = :idAnalise
        ");
        $stmtAnalise->execute(['idAnalise' => $analiseId]);
        $analiseData = $stmtAnalise->fetch(PDO::FETCH_ASSOC);

        if (!$analiseData) {
            echo json_encode(['error' => 'Análise não encontrada.']);
            exit;
        }

        // Buscar informações do contrato
        $stmtContrato = $pdo->prepare("
            SELECT c.idCliente, c.idAgencia, c.idOrganizacao
            FROM Contrato c
            WHERE c.idContrato = :idContrato
        ");
        $stmtContrato->execute(['idContrato' => $analiseData['idContrato']]);
        $contratoData = $stmtContrato->fetch(PDO::FETCH_ASSOC);

        if (!$contratoData) {
            echo json_encode(['error' => 'Contrato associado não encontrado.']);
            exit;
        }

        // Buscar produtos associados ao contrato
        $stmtProdutos = $pdo->prepare("
            SELECT p.idProduto
            FROM Produto p
            INNER JOIN LicencaProduto lp ON p.idProduto = lp.idProduto
            INNER JOIN ContratoLicenca cl ON lp.idLicenca = cl.idLicenca
            WHERE cl.idContrato = :idContrato
        ");
        $stmtProdutos->execute(['idContrato' => $analiseData['idContrato']]);
        $associatedProducts = $stmtProdutos->fetchAll(PDO::FETCH_COLUMN);

        // A análise está associada a apenas um contrato
        $associatedContracts = [$analiseData['idContrato']];

        echo json_encode([
            'estadoAnalise' => $analiseData['estadoAnalise'],
            'descricaoAnalise' => $analiseData['descricaoAnalise'],
            'cliente' => $contratoData['idCliente'],
            'agencia' => $contratoData['idAgencia'],
            'organizacao' => $contratoData['idOrganizacao'],
            'associatedProducts' => array_map('strval', $associatedProducts),
            'associatedContracts' => array_map('strval', $associatedContracts)
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao buscar os dados: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID da análise não fornecido.']);
}
?>