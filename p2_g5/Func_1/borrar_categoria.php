<?php
session_start();
require_once 'config.php';

if (!tienePermiso('gerente')) {
    die("No tienes permisos para realizar esta acción.");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Verificamos si existen productos en esta categoría antes de borrar
    $check = $db->prepare("SELECT COUNT(*) FROM productos WHERE id_categoria = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $res = $check->get_result()->fetch_row();

    if ($res[0] > 0) {
        // Si hay productos, no borramos y devolvemos con un aviso
        header("Location: gestion_categorias.php?msg=error_fk");
        exit();
    }
    
    // Si no hay productos, procedemos a eliminar
    $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: gestion_categorias.php");
        exit();
    }
}
header("Location: gestion_categorias.php");
?>