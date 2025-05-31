<?php
$host = "localhost";
$dbname = "demoex";
$username = "root";
$password = "";


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // $pdo->setAttribute(3,2);
} catch (PDOException $e) {
    echo 'Error:' . $e->getMessage();
}
