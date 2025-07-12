<?php
// logout.php - Destruir sesión y redirigir al inicio
session_start();
$_SESSION = array();
session_destroy();
header('Location: index.php');
exit;
