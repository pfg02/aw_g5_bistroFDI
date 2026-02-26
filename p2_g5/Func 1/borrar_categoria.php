<?php
session_start();
require_once 'config.php';

// Seguridad: Solo el gerente puede borrar
if (!tienePermiso('gerente')) {
    die("No tienes permisos para realizar esta acción.");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ejecutamos el borrado físico de la categoría
    $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: gestion_categorias.php");
        exit();
    } else {
        echo "Error: No se pudo eliminar. Es posible que existan productos usando esta categoría.";
    }
}
?>