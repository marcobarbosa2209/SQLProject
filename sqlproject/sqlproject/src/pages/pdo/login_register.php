<?php
require 'connection.php';
require 'user_auth.php';
require 'helpers.php';

function registerUser($email, $nomeUtilizador, $password, $tipoUtilizador) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Utilizador WHERE emailUtilizador = :email OR nomeUtilizador = :nome");
        $stmt->execute(['email' => $email, 'nome' => $nomeUtilizador]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            return newNotification("An account with this email or username already exists.", "error");
        }

        $stmt = $pdo->prepare("SELECT idTipoUtilizador FROM TipoUtilizador WHERE idTipoUtilizador = :tipo");
        $stmt->execute(['tipo' => $tipoUtilizador]);
        $idTipoUtilizador = $stmt->fetchColumn();

        if (!$idTipoUtilizador) {
            return newNotification("Invalid user type provided.", "error");
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO PedidoCriacaoConta (estadoPedidoCriacaoConta) VALUES (:estado)");
        $stmt->execute(['estado' => 'pending']);
        $idPedidoCriacaoConta = $pdo->lastInsertId();

        if (!$idPedidoCriacaoConta) {
            $pdo->rollBack();
            return newNotification("Failed to create account creation request.", "error");
        }

        $stmt = $pdo->prepare("INSERT INTO Utilizador (emailUtilizador, nomeUtilizador, passwordUtilizador, idTipoUtilizador, idPedidoCriacaoConta) 
                               VALUES (:email, :nome, :password, :idTipo, :idPedido)");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([
            'email' => $email,
            'nome' => $nomeUtilizador,
            'password' => $hashedPassword,
            'idTipo' => $idTipoUtilizador,
            'idPedido' => $idPedidoCriacaoConta
        ]);

        $pdo->commit();

        return newNotification("Registration successful!", "success");
    } catch (Exception $e) {
        $pdo->rollBack();
        return newNotification("Error during registration: " . $e->getMessage(), "error");
    }
}

function loginUser($identifier, $password) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT u.*, pcc.estadoPedidoCriacaoConta, tu.nomeTipoUtilizador 
            FROM Utilizador u
            INNER JOIN PedidoCriacaoConta pcc ON u.idPedidoCriacaoConta = pcc.idPedidoCriacaoConta
            INNER JOIN TipoUtilizador tu ON u.idTipoUtilizador = tu.idTipoUtilizador
            WHERE u.emailUtilizador = :identifier OR u.nomeUtilizador = :identifier
        ");
        $stmt->execute(['identifier' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return newNotification("Invalid email, username, or password.", "error");
        }

        switch ($user['estadoPedidoCriacaoConta']) {
            case 'pending':
                return newNotification("Your account request is still pending.", "error");
            case 'denied':
                return newNotification("Your account request was denied. Please contact support.", "error");
            case 'accepted':
                if (password_verify($password, $user['passwordUtilizador'])) {
                    authenticateUserSession($user);

                    if ($user['nomeTipoUtilizador'] === 'Administrador') {
                        header("Location: /sqlproject/src/admin-dashboard.php");
                    } elseif ($user['nomeTipoUtilizador'] === 'Cliente') {
                        header("Location: /sqlproject/src/client-dashboard.php");
                    } elseif ($user['nomeTipoUtilizador'] === 'Organização') {
                        header("Location: /sqlproject/src/organizacao-dashboard.php");
                    } elseif ($user['nomeTipoUtilizador'] === 'Agencia') {
                        header("Location: /sqlproject/src/agencia-dashboard.php");
                    } else {
                        header("Location: /sqlproject/src/index.php");
                    }
                    exit();
                } else {
                    return newNotification("Invalid email, username, or password.", "error");
                }
            default:
                return newNotification("Unexpected account status. Please contact support.", "error");
        }
    } catch (Exception $e) {
        return newNotification("Error during login: " . $e->getMessage(), "error");
    }
}