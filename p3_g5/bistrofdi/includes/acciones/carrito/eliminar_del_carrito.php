<?php
declare(strict_types=1);

/**
 * Elimina un producto individual del carrito.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';

exigirLogin();
exigirRol('cliente');

$redirect = BASE_URL . '/includes/vistas/tienda/carrito.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje_error'] = 'Método no permitido.';
    header('Location: ' . $redirect);
    exit();
}

$idProducto = filter_input(INPUT_POST, 'productoId', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idProducto === false || $idProducto === null) {
    $_SESSION['mensaje_error'] = 'El producto indicado no es válido.';
    header('Location: ' . $redirect);
    exit();
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['mensaje_error'] = 'El carrito no es válido.';
    header('Location: ' . $redirect);
    exit();
}

if (isset($_SESSION['carrito'][(int) $idProducto])) {
    unset($_SESSION['carrito'][(int) $idProducto]);
    $_SESSION['mensaje_exito'] = 'Artículo eliminado del carrito.';
	if (!empty($_SESSION['ofertas_aplicadas'])) {
        $ofertasService = new OfertasServiceApp();
        
        $nuevasOfertas = $ofertasService->revalidarOfertasTrasEliminacion(
            $_SESSION['carrito'], 
            $_SESSION['ofertas_aplicadas']
        );

        if (count($nuevasOfertas) < count($_SESSION['ofertas_aplicadas'])) {
            $_SESSION['mensaje_info'] = "Algunas ofertas se han eliminado porque ya no cumples los requisitos.";
        }
        
        $_SESSION['ofertas_aplicadas'] = $nuevasOfertas;
    }
} else {
    $_SESSION['mensaje_error'] = 'El producto no estaba en el carrito.';
}

header('Location: ' . $redirect);
exit();
?>