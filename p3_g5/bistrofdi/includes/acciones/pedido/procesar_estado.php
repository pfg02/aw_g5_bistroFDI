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
 * Acción especial: sala marca una bebida/café concreta como servida.
 * No cambia todo el pedido de golpe salvo que ya estén todos los productos preparados.
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

    $marcado = $controller->marcarProductoServidoSala((int) $idPedido, (int) $idProducto);

    if (!$marcado) {
        $_SESSION['mensaje_error'] = 'No se pudo marcar el producto como servido. Puede que ya estuviera servido o que no sea una bebida/café.';
        header('Location: ' . $redirect);
        exit();
    }

    /*
     * Si después de marcar esta bebida/café ya están todos los productos preparados,
     * el pedido pasa a Terminado.
     *
     * Ejemplo:
     * - Solo bebidas: Listo cocina -> Terminado
     * - Comida + bebida: solo pasará a Terminado cuando también esté lista la comida.
     */
    if (todosLosProductosDelPedidoEstanPreparados($controller, (int) $idPedido)) {
        $controller->actualizarEstadoPedido((int) $idPedido, 'Terminado');
        $controller->marcarPedidoServidoSala((int) $idPedido);

        $_SESSION['mensaje_exito'] = 'Producto servido correctamente. El pedido ha pasado a Terminado.';
    } else {
        $_SESSION['mensaje_exito'] = 'Producto marcado como servido correctamente.';
    }

    header('Location: ' . $redirect);
    exit();
}

/*
 * Cobrar:
 * - Si el pedido solo tiene bebidas, pasa a Listo cocina.
 * - Si tiene comida o mezcla comida/bebida, pasa a En preparación.
 */
if ($accion === 'cobrar') {
    $nuevoEstado = $controller->obtenerEstadoTrasPago((int) $idPedido);

    if ($controller->actualizarEstadoPedido((int) $idPedido, $nuevoEstado)) {
        $_SESSION['mensaje_exito'] = "Pedido cobrado correctamente. Ahora está en '$nuevoEstado'.";
    } else {
        $_SESSION['mensaje_error'] = 'Error al actualizar el pedido.';
    }

    header('Location: ' . $redirect);
    exit();
}

$accionesPermitidas = [
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

function todosLosProductosDelPedidoEstanPreparados(PedidoController $controller, int $idPedido): bool
{
    $productos = $controller->obtenerProductosDePedido($idPedido);

    if (empty($productos)) {
        return false;
    }

    foreach ($productos as $producto) {
        $preparado = (int) ($producto['preparado'] ?? 0);

        if ($preparado !== 1) {
            return false;
        }
    }

    return true;
}
?>