<?php
// Archivo de conexión reutilizable
$servername = "localhost";
//$servername = 127.0.0.1";
$username = "root";
$password = "";
$dbname = "aguas";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
