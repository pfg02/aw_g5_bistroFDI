<?php
$host = "localhost";
$db_name = "bistrofdi(g5)";
$user = "root";
$pass = "tu_password_vps";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>