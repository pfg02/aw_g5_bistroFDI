<?php
session_start();
require_once 'includes/config.php';

// Seguridad: solo gerente
if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    die("Acceso denegado.");
}

// Comprobamos que llegue un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ejecutamos el borrado físico
    // LIMIT 1 por seguridad
    $stmt = $db->prepare("DELETE FROM categorias WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: gestion_categorias.php?msg=eliminado_ok");
        exit();
    } else {
        // Si falla, es porque hay productos asociados a esta categoría
        header("Location: gestion_categorias.php?msg=error_fk");
        exit();
    }
}

// Si no hay ID, devolvemos a la gestión
header("Location: gestion_categorias.php");
exit();
?>