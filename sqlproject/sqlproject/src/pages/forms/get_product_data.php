<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
checkUserAuthentication();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);

    try {
        // Buscar informações do produto
        $stmt = $pdo->prepare("SELECT nomeProduto FROM Produto WHERE idProduto = :idProduto");
        $stmt->execute(['idProduto' => $productId]);
        $productData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$productData) {
            echo json_encode(['error' => 'Produto não encontrado.']);
            exit;
        }

        // Buscar licenças associadas ao produto
        $stmtLicencas = $pdo->prepare("
            SELECT idLicenca 
            FROM LicencaProduto 
            WHERE idProduto = :idProduto
        ");
        $stmtLicencas->execute(['idProduto' => $productId]);
        $associatedLicenses = $stmtLicencas->fetchAll(PDO::FETCH_COLUMN);

        // Converter IDs para strings (se necessário)
        $associatedLicenses = array_map('strval', $associatedLicenses);

        echo json_encode([
            'nomeProduto' => $productData['nomeProduto'],
            'associatedLicenses' => $associatedLicenses
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao buscar os dados: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID do produto não fornecido.']);
}
?>