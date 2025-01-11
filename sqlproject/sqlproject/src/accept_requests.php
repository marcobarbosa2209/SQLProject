<?php
require 'pages/pdo/connection.php';
require 'pages/pdo/user_auth.php';
checkUserAuthentication();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idPedido'])) {
    $idPedido = intval($_POST['idPedido']);

    try {
        // Atualiza o estado para aceito
        $stmt = $pdo->prepare("UPDATE PedidoCriacaoConta SET estadoPedidoCriacaoConta = 'accepted' WHERE idPedidoCriacaoConta = :idPedido");
        $stmt->execute(['idPedido' => $idPedido]);

        // Redireciona com mensagem de sucesso
        header('Location: admin-dashboard.php?success=accepted');
        exit();
    } catch (Exception $e) {
        // Redireciona com mensagem de erro
        header('Location: admin-dashboard.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}
?>