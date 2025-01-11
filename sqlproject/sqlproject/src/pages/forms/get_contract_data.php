<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
checkUserAuthentication();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $contractId = intval($_GET['id']); // Sanitiza o ID do contrato

    try {
        // Consulta para obter os dados do contrato
        $stmt = $pdo->prepare("
            SELECT 
                c.nomeContrato,
                c.estadoContrato,
                c.idOrganizacao,
                c.idAgencia,
                c.idCliente
            FROM 
                Contrato c
            WHERE 
                c.idContrato = :id
        ");
        $stmt->execute(['id' => $contractId]);
        $contractData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($contractData) {
            // Buscar licenças associadas ao contrato
            $stmt = $pdo->prepare("
                SELECT licenca.idLicenca, licenca.nomeLicenca 
                FROM Licenca 
                JOIN ContratoLicenca ON Licenca.idLicenca = ContratoLicenca.idLicenca 
                WHERE ContratoLicenca.idContrato = :idContrato
            ");
            $stmt->execute(['idContrato' => $contractId]);
            $associatedLicenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar arquivos associados ao contrato
            $stmt = $pdo->prepare("
                SELECT idFicheiro, nomeFicheiro 
                FROM Ficheiro 
                WHERE idContrato = :idContrato
            ");
            $stmt->execute(['idContrato' => $contractId]);
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Estrutura de resposta
            $response = [
                'nomeContrato' => $contractData['nomeContrato'],
                'estadoContrato' => $contractData['estadoContrato'],
                'idOrganizacao' => $contractData['idOrganizacao'],
                'idAgencia' => $contractData['idAgencia'],
                'idCliente' => $contractData['idCliente'],
                'associatedLicenses' => array_column($associatedLicenses, 'idLicenca'),
                'files' => $files
            ];

            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'Contrato não encontrado.']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao buscar os dados: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID do contrato não fornecido.']);
}
?>