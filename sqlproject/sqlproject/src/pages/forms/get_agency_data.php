<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
checkUserAuthentication();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $agencyId = intval($_GET['id']); // Sanitiza o ID da agência

    try {
        // Consulta para obter os dados da agência, contato, morada e cidade
        $stmt = $pdo->prepare("
            SELECT 
                a.nomeAgencia,
                c.idTipoContacto,
                c.valorContacto,
                m.nomeMorada AS streetAddress,
                m.porta AS doorNumber,
                cp.nomeLocalidade AS cityName,
                cp.codigoPostal AS postalCode
            FROM 
                Agencia a
            LEFT JOIN 
                Contacto c ON c.idContacto = a.idContacto
            LEFT JOIN 
                Morada m ON m.idAgencia = a.idAgencia
            LEFT JOIN 
                CodigoPostal cp ON cp.codigoPostal = m.codigoPostal
            WHERE 
                a.idAgencia = :id
        ");
        $stmt->execute(['id' => $agencyId]);
        $agencyData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar se a agência foi encontrada
        if ($agencyData) {
            // Verificar se os campos de morada estão presentes; caso contrário, enviar valores padrão
            $agencyData['streetAddress'] = $agencyData['streetAddress'] ?? 'N/A';
            $agencyData['doorNumber'] = $agencyData['doorNumber'] ?? 'N/A';
            $agencyData['cityName'] = $agencyData['cityName'] ?? 'N/A';
            $agencyData['postalCode'] = $agencyData['postalCode'] ?? 'N/A';

            echo json_encode($agencyData);
        } else {
            echo json_encode(['error' => 'Agência não encontrada.']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro ao buscar os dados: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID da agência não fornecido.']);
}
?>