<?php
// Autor: Julian Ploch
// Beschreibung: Datenbankverbindung


$host     = "localhost";
$user     = "gruppe21";
$passwort = "JR^bs.6ZG{l]";
$datenbank = "gruppe21";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$datenbank;charset=utf8", $user, $passwort);
    
  
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}
?>