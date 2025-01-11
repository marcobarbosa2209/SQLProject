<?php
try {
    $host = 'localhost';
    $port = '3306';
    $dbname = 'LicencaAutomovel';
    $username = 'root';
    $password = '';

    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    echo "Error connecting to the database: " . $e->getMessage();
} ?>