<?php
session_start();

function checkUserAuthentication() {
    if (!isset($_SESSION['user'])) {
      header("Location: /sqlproject/src/pages/samples/login.php");
      exit();
    }
}

function getAuthenticatedUser() {
    return $_SESSION['user'] ?? null;
}

function authenticateUserSession($user) {
    $_SESSION['user'] = $user;
}

function logoutUser() {
    session_unset();
    session_destroy();
    header("Location: ../samples/login.php");
    exit();
}