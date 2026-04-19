<?php
/**
 * Añade un producto al carrito en la sesión y devuelve al catálogo.
 */

require_once __DIR__ . '/../../core/sesion.php';

exigirLogin();
exigirRol('cliente');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'], $_POST['cantidad'])) {
    
    $id = (int)$_POST['id_producto'];
    $cantidad = (int)$_POST['cantidad'];

    if ($cantidad > 0) {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id] += $cantidad;
        } else {
            $_SESSION['carrito'][$id] = $cantidad;
        }

        $_SESSION['mensaje_exito'] = "Artículo añadido al carrito.";
    }
}

header("Location: " . BASE_URL . "/includes/vistas/tienda/catalogo.php");
exit();