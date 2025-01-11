<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
checkUserAuthentication();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $licenseId = intval($_GET['id']);

    try {
        // Buscar informações da licença e do tipo da licença
        $stmt = $pdo->prepare("
            SELECT l.nomeLicenca, l.idTipoLicenca, t.nomeTipoLicenca
            FROM Licenca l
            LEFT JOIN TipoLicenca t ON l.idTipoLicenca = t.idTipoLicenca
            WHERE l.idLicenca = :idLicenca
        ");
        $stmt->execute(['idLicenca' => $licenseId]);
        $licenseData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$licenseData) {
            echo json_encode(['error' => 'Licença não encontrada.']);
            exit;
        }

        // Buscar produtos associados à licença
        $stmtProdutos = $pdo->prepare("
            SELECT idProduto 
            FROM LicencaProduto 
            WHERE idLicenca = :idLicenca
        ");
        $stmtProdutos->execute(['idLicenca' => $licenseId]);
        $associatedProducts = $stmtProdutos->fetchAll(PDO::FETCH_COLUMN);

        // Buscar contratos associados à licença
        $stmtContratos = $pdo->prepare("
            SELECT idContrato 
            FROM ContratoLicenca 
            WHERE idLicenca = :idLicenca
        ");
        $stmtContratos->execute(['idLicenca' => $licenseId]);
        $associatedContracts = $stmtContratos->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'nomeLicenca' => $licenseData['nomeLicenca'],
            'tipoLicenca' => $licenseData['idTipoLicenca'], // ID do TipoLicenca
            'nomeTipoLicenca' => $licenseData['nomeTipoLicenca'], // Nome do TipoLicenca (opcional)
            'associatedProducts' => array_map('strval', $associatedProducts),
            'associatedContracts' => array_map('strval', $associatedContracts), // IDs dos contratos associados
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao buscar os dados: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID da licença não fornecido.']);
}
?>