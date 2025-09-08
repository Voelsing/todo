<?php
// db.php

$host = 'localhost';
$db   = 'verbandssoftware'; // Dein Datenbankname
$user = 'root';             // Dein MySQL-Benutzer
$pass = '';                 // Dein Passwort
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Fehlerbehandlung
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Assoziative Arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Native Prepared Statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Verbindungsfehler: ' . $e->getMessage());
}

// Zusätzliches MySQLi-Objekt für ältere Skripte
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Verbindungsfehler: ' . $conn->connect_error);
}
$conn->set_charset($charset);

?>
