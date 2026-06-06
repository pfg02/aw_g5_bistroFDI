<?php
declare(strict_types=1);

/**
 * Añade un producto al carrito en la sesión y devuelve al catálogo.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';

exigirLogin();
exigirRol('cliente');

$redirect = BASE_URL . '/includes/vistas/tienda/catalogo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje_error'] = 'Método no permitido.';
    header('Location: ' . $redirect);
    exit();
}

$idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idProducto === false || $idProducto === null) {
    $_SESSION['mensaje_error'] = 'El producto indicado no es válido.';
    header('Location: ' . $redirect);
    exit();
}

if ($cantidad === false || $cantidad === null) {
    $_SESSION['mensaje_error'] = 'La cantidad indicada no es válida.';
    header('Location: ' . $redirect);
    exit();
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (isset($_SESSION['carrito'][(int) $idProducto])) {
    $_SESSION['carrito'][(int) $idProducto] += (int) $cantidad;
} else {
    $_SESSION['carrito'][(int) $idProducto] = (int) $cantidad;
}

$_SESSION['mensaje_exito'] = 'Artículo añadido al carrito.';
header('Location: ' . $redirect);
exit();