<?php
declare(strict_types=1);

/**
 * Cancela un pedido permitido o vacía el carrito actual.
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
 * Caso 1: cancelar un pedido ya creado.
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
    $cocineroIdPedido = obtenerCocineroIdPedido($pedido);
    $servidoSala = obtenerServidoSalaPedido($pedido);

    if ($clienteIdPedido === null || $clienteIdPedido !== (int) $idUsuario) {
        $_SESSION['mensaje_error'] = 'Acceso denegado o pedido no encontrado.';
        header('Location: ' . $redirectMisPedidos);
        exit();
    }

    /*
     * Si sala ya ha servido/preparado alguna bebida o café,
     * el cliente ya no puede cancelar el pedido.
     */
    if ($servidoSala === 1) {
        $_SESSION['mensaje_error'] = 'No puedes cancelar este pedido porque ya ha sido servido por sala.';
        header('Location: ' . $redirectMisPedidos);
        exit();
    }

    /*
     * Se puede cancelar si:
     * - Está Recibido.
     * - Está En preparación y todavía no tiene cocinero asignado.
     *
     * Esto permite cancelar pedidos solo de bebidas/cafés mientras sala
     * todavía no haya marcado ninguna bebida como servida/preparada.
     */
    $sePuedeCancelar = $estadoPedido === 'Recibido'
        || (
            $estadoPedido === 'En preparación'
            && ($cocineroIdPedido === null || $cocineroIdPedido <= 0)
        );

    if (!$sePuedeCancelar) {
        $_SESSION['mensaje_error'] = 'No puedes cancelar este pedido porque ya está siendo preparado o ya ha avanzado de estado.';
        header('Location: ' . $redirectMisPedidos);
        exit();
    }

    /*
     * Mejor marcar como Cancelado que borrar el pedido.
     * Así queda historial y no se pierden datos.
     */
    $cancelado = $controller->actualizarEstadoPedido((int) $idPedido, 'Cancelado');

    if ($cancelado) {
        $_SESSION['mensaje_exito'] = 'El pedido ha sido cancelado correctamente.';
    } else {
        $_SESSION['mensaje_error'] = 'Hubo un error en la base de datos al cancelar el pedido.';
    }

    header('Location: ' . $redirectMisPedidos);
    exit();
}

/*
 * Caso 2: vaciar carrito actual.
 */
if ($accion === 'vaciar_carrito') {
    unset($_SESSION['carrito'], $_SESSION['tipoPedido']);
	$_SESSION['ofertas_aplicadas'] = [];

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
        $valor = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        return $valor === false ? null : (int) $valor;
    }

    if (is_object($pedido) && method_exists($pedido, 'getClienteId')) {
        $valor = $pedido->getClienteId();
        $valor = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        return $valor === false ? null : (int) $valor;
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

/**
 * Obtiene el cocinero_id de un pedido, tanto si viene como array como si viene como DTO.
 */
function obtenerCocineroIdPedido($pedido): ?int
{
    if (is_array($pedido)) {
        $valor = $pedido['cocinero_id'] ?? null;

        if ($valor === null || $valor === '') {
            return null;
        }

        $valor = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        return $valor === false ? null : (int) $valor;
    }

    if (is_object($pedido) && method_exists($pedido, 'getCocineroId')) {
        $valor = $pedido->getCocineroId();

        if ($valor === null || $valor === '') {
            return null;
        }

        $valor = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        return $valor === false ? null : (int) $valor;
    }

    return null;
}

/**
 * Obtiene servido_sala de un pedido, tanto si viene como array como si viene como DTO.
 */
function obtenerServidoSalaPedido($pedido): int
{
    if (is_array($pedido)) {
        return isset($pedido['servido_sala']) ? (int) $pedido['servido_sala'] : 0;
    }

    if (is_object($pedido) && method_exists($pedido, 'getServidoSala')) {
        return (int) $pedido->getServidoSala();
    }

    return 0;
}
?>