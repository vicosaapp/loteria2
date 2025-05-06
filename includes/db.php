<?php
$host = 'localhost';
$dbname = 'loteria2'; // Alterado aqui para loteria2
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco: " . $e->getMessage());
}