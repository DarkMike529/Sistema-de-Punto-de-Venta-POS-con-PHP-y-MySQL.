<?php
$conn = new mysqli("localhost", "pos_user", "1234", "pos_system");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
