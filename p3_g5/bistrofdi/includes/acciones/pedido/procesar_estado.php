<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('camarero');

$redirect = BASE_URL . '/includes/vistas/camarero/panel_camarero.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje_error'] = 'Método no permitido.';
    header('Location: ' . $redirect);
    exit();
}

$idPedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$accion = trim((string) (filter_input(INPUT_POST, 'accion', FILTER_UNSAFE_RAW) ?? ''));

if ($idPedido === false || $idPedido === null) {
    $_SESSION['mensaje_error'] = 'El identificador del pedido no es válido.';
    header('Location: ' . $redirect);
    exit();
}

$controller = PedidoController::getInstance();

/*
 * Acción especial para bebidas, cafés o productos que no pasan por cocina.
 * No cambia el estado del pedido completo.
 * Solo marca ese producto como servido por sala.
 */
if ($accion === 'servir_producto_sala') {
    $idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    if ($idProducto === false || $idProducto === null) {
        $_SESSION['mensaje_error'] = 'El identificador del producto no es válido.';
        header('Location: ' . $redirect);
        exit();
    }

    $resultado = $controller->marcarProductoServidoSala((int) $idPedido, (int) $idProducto);

    if ($resultado === false) {
        $_SESSION['mensaje_error'] = 'Error al marcar el producto como servido.';
    } else {
        $_SESSION['mensaje_exito'] = 'Producto marcado como servido.';
    }

    header('Location: ' . $redirect);
    exit();
}

$accionesPermitidas = [
    'cobrar' => 'En preparación',
    'terminar' => 'Terminado',
    'entregar' => 'Entregado',
];

if (!array_key_exists($accion, $accionesPermitidas)) {
    $_SESSION['mensaje_error'] = 'Acción no reconocida.';
    header('Location: ' . $redirect);
    exit();
}

$nuevoEstado = $accionesPermitidas[$accion];

if ($controller->actualizarEstadoPedido((int) $idPedido, $nuevoEstado)) {
    $_SESSION['mensaje_exito'] = "Pedido actualizado a '$nuevoEstado'.";
} else {
    $_SESSION['mensaje_error'] = 'Error al actualizar el pedido.';
}

header('Location: ' . $redirect);
exit();
?>