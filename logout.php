<?php
/**
 * Author: Noah S. Kipp
 */
session_destroy();
$_SESSION = array();
header("Location: index.php");
exit;
?>