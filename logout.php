<?php
/**
 * Author: Noah S. Kipp
 */
// PHP Session schließen, Session-Variablen leeren und zurück zum Login verweisen
session_destroy();
$_SESSION = array();
header("Location: index.php");
exit;
?>