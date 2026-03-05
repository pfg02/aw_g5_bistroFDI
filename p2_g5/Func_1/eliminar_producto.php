<?php
session_start();
require_once 'config.php';

// Seguridad
if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    die("Acceso denegado.");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ejecutamos el borrado físico (DELETE)
    $stmt = $db->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: gestion_productos.php?msg=eliminado_ok");
        exit();
    } else {
        // Si falla suele ser por integridad referencial (el producto está en un pedido)
        header("Location: gestion_productos.php?msg=error_fk");
        exit();
    }
}
?>