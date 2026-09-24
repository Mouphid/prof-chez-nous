<?php
// Connexion locale pour l'import legacy (environnement dev uniquement)
$host = "127.0.0.1";
$port = "3306";
$db_name = "joieenseignante";
$username = "root";
$password = "";

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4",
    $username,
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);