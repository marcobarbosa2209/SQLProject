<?php
require '../pdo/connection.php';
require '../pdo/user_auth.php';
checkUserAuthentication();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $clientId = intval($_GET['id']); // Sanitiza o ID do cliente

    // Consulta para obter os dados do cliente e seu contato
    $stmt = $pdo->prepare("
        SELECT 
            c.nomeCliente, 
            c.idTipoCliente, 
            c.idAgencia, 
            ct.idTipoContacto, 
            ct.valorContacto
        FROM 
            Cliente c
        LEFT JOIN 
            Contacto ct ON ct.idContacto = c.idContacto
        WHERE 
            c.idCliente = :id
    ");
    $stmt->execute(['id' => $clientId]);
    $clientData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($clientData) {
        echo json_encode($clientData);
    } else {
        echo json_encode(['error' => 'Cliente não encontrado.']);
    }
} else {
    echo json_encode(['error' => 'ID do cliente não fornecido.']);
}
?>