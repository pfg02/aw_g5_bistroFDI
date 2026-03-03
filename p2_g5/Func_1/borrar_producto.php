<?php
session_start();
require_once 'config.php';

// 1. Seguridad: Solo el gerente puede realizar esta acción
if (!tienePermiso('gerente')) {
    die("Acceso denegado.");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // 2. CORRECCIÓN: Usamos la conexión '$db' de tu config.php
    // Borrado lógico: cambiamos ofertado a 0 según pide el guion
    $stmt = $db->prepare("UPDATE productos SET ofertado = 0 WHERE id = ?");
    
    // 3. CORRECCIÓN: Usamos bind_param porque estás usando la librería mysqli
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // 4. CORRECCIÓN: Redirigimos a tu archivo real 'gestion_productos.php'
        header("Location: gestion_productos.php");
        exit();
    } else {
        echo "Error al dar de baja el producto.";
    }
}
?>