<?php
// remove_user.php

require 'pages/pdo/connection.php'; // Conexão com o banco de dados
require 'pages/pdo/user_auth.php'; // Função `checkUserAuthentication`
require 'pages/pdo/helpers.php';   // Helper para mensagens de notificação ou outros utilitários

checkUserAuthentication();  

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idUtilizador'])) {
    $idUtilizador = intval($_POST['idUtilizador']);

    try {
        // Preparar e chamar a procedure RemoveUtilizador
        $stmt = $pdo->prepare("CALL RemoveUtilizador(:id)");
        $stmt->bindParam(':id', $idUtilizador, PDO::PARAM_INT);
        $stmt->execute();

        // Redirecionar de volta para a página admin com uma mensagem de sucesso
        header("Location: admin-dashboard.php?message=Utilizador removido com sucesso.");
        exit();
    } catch (Exception $e) {
        // Redirecionar de volta com uma mensagem de erro
        header("Location: admin-dashboard.php?error=Erro ao remover o utilizador: " . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirecionar de volta se o acesso não for via POST
    header("Location: admin-dashboard.php");
    exit();
}
?>
