<?php
require_once 'config.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Borrado lógico: cambiamos ofertado a 0
    $sql = "UPDATE productos SET ofertado = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    header("Location: admin_productos.php");
}
?>