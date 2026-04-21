<?php
declare(strict_types=1);

/**
 * Cancela un pedido recibido o vacía el carrito actual.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('cliente');

$redirectCarrito = BASE_URL . '/includes/vistas/tienda/carrito.php';
$redirectCatalogo = BASE_URL . '/includes/vistas/tienda/catalogo.php';
$redirectMisPedidos = BASE_URL . '/includes/vistas/pedido/mis_pedidos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectCarrito);
    exit();
}

$idUsuario = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idUsuario === false || $idUsuario === null) {
    $_SESSION['mensaje_error'] = 'Sesión no válida.';
    header('Location: ' . BASE_URL . '/includes/vistas/auth/login.php');
    exit();
}

$idPedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$accion = trim((string) (filter_input(INPUT_POST, 'accion', FILTER_UNSAFE_RAW) ?? ''));

$controller = PedidoController::getInstance();

/*
 * Caso 1: cancelar un pedido ya creado
 */
if ($idPedido !== false && $idPedido !== null) {
    $pedido = $controller->verPedido((int) $idPedido);

    if ($pedido === null || $pedido === false) {
        $_SESSION['mensaje_error'] = 'Pedido no encontrado.';
        header('Location: ' . $redirectMisPedidos);
        exit();
    }

    $clienteIdPedido = obtenerClienteIdPedido($pedido);
    $estadoPedido = obtenerEstadoPedido($pedido);

    if ($clienteIdPedido === null || $clienteIdPedido !== (int) $idUsuario) {
        $_SESSION['mensaje_error'] = 'Acceso denegado o pedido no encontrado.';
        header('Location: ' . $redirectMisPedidos);
        exit();
    }

    if ($estadoPedido !== 'Recibido') {
        $_SESSION['mensaje_error'] = "Solo puedes cancelar pedidos en estado 'Recibido'.";
        header('Location: ' . $redirectMisPedidos);
        exit();
    }

    $eliminado = $controller->eliminarPedido((int) $idPedido);

    if ($eliminado) {
        $_SESSION['mensaje_exito'] = 'El pedido ha sido cancelado correctamente.';
    } else {
        $_SESSION['mensaje_error'] = 'Hubo un error en la base de datos al cancelar el pedido.';
    }

    header('Location: ' . $redirectMisPedidos);
    exit();
}

/*
 * Caso 2: vaciar carrito actual
 */
if ($accion === 'vaciar_carrito') {
    unset($_SESSION['carrito'], $_SESSION['tipoPedido']);

    $_SESSION['mensaje_exito'] = 'El carrito ha sido vaciado correctamente. Puedes empezar un nuevo pedido cuando quieras.';
    header('Location: ' . $redirectCatalogo);
    exit();
}

$_SESSION['mensaje_error'] = 'Acción no permitida.';
header('Location: ' . $redirectCarrito);
exit();

/**
 * Obtiene el cliente_id de un pedido, tanto si viene como array como si viene como DTO.
 */
function obtenerClienteIdPedido($pedido): ?int
{
    if (is_array($pedido)) {
        $valor = $pedido['cliente_id'] ?? null;
        return filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }

    if (is_object($pedido) && method_exists($pedido, 'getClienteId')) {
        $valor = $pedido->getClienteId();
        return is_int($valor) ? $valor : null;
    }

    return null;
}

/**
 * Obtiene el estado de un pedido, tanto si viene como array como si viene como DTO.
 */
function obtenerEstadoPedido($pedido): ?string
{
    if (is_array($pedido)) {
        $estado = $pedido['estado'] ?? null;
        return is_string($estado) ? trim($estado) : null;
    }

    if (is_object($pedido) && method_exists($pedido, 'getEstado')) {
        $estado = $pedido->getEstado();
        return is_string($estado) ? trim($estado) : null;
    }

    return null;
}