<?php
$host      = "localhost";   
$user      = "gruppe21";    
$passwort  = "JR^bs.6ZG{l]";
$datenbank = "gruppe21";          

$verbindung = mysqli_connect($host, $user, $passwort, $datenbank);

if (!$verbindung) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}
echo "Verbindung erfolgreich!";
?>