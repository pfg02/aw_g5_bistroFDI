<?php
session_start();
require_once 'includes/config.php'; // Asegúrate de que la ruta sea correcta

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    die("Acceso denegado.");
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // ACCIÓN: BORRAR LÓGICO (Actualizar propiedad ofertado)
    $stmt = $db->prepare("UPDATE productos SET ofertado = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Redirigimos de vuelta a la lista con un mensaje de éxito
        header("Location: gestion_productos.php?msg=baja_ok");
        exit();
    } else {
        header("Location: gestion_productos.php?msg=error");
        exit();
    }
} else {
    header("Location: gestion_productos.php");
    exit();
}
?>