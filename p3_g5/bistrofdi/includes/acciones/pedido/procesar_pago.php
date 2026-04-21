<?php
declare(strict_types=1);

/**
 * Script de acción: simula el procesamiento del pago de un pedido.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('cliente');

$redirectMisPedidos = BASE_URL . '/includes/vistas/pedido/mis_pedidos.php';
$redirectIndex = BASE_URL . '/index.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectIndex);
    exit();
}

$idUsuario = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$idPedido = filter_input(INPUT_POST, 'id_pedido', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$metodo = trim((string) (filter_input(INPUT_POST, 'metodo_pago', FILTER_UNSAFE_RAW) ?? ''));
$metodosPermitidos = ['tarjeta', 'camarero'];

if ($idUsuario === false || $idUsuario === null) {
    $_SESSION['mensaje_error'] = 'Sesión no válida.';
    header('Location: ' . BASE_URL . '/includes/vistas/auth/login.php');
    exit();
}

if ($idPedido === false || $idPedido === null || !in_array($metodo, $metodosPermitidos, true)) {
    $_SESSION['mensaje_error'] = 'Faltan datos válidos para procesar el pago.';
    header('Location: ' . $redirectMisPedidos);
    exit();
}

$controller = PedidoController::getInstance();
$pedido = $controller->verPedido((int) $idPedido);

if ($pedido === null || $pedido === false) {
    $_SESSION['mensaje_error'] = 'Pedido no encontrado.';
    header('Location: ' . $redirectMisPedidos);
    exit();
}

$clienteIdPedido = obtenerClienteIdPedido($pedido);
$estadoPedido = obtenerEstadoPedido($pedido);

if ($clienteIdPedido === null || $clienteIdPedido !== (int) $idUsuario || $estadoPedido !== 'Recibido') {
    $_SESSION['mensaje_error'] = 'Acción no permitida o el pedido ya fue procesado.';
    header('Location: ' . $redirectMisPedidos);
    exit();
}

if ($metodo === 'tarjeta') {
    $tarjeta = preg_replace('/\D+/', '', (string) (filter_input(INPUT_POST, 'tarjeta', FILTER_UNSAFE_RAW) ?? ''));
    $caducidad = trim((string) (filter_input(INPUT_POST, 'caducidad', FILTER_UNSAFE_RAW) ?? ''));
    $cvv = preg_replace('/\D+/', '', (string) (filter_input(INPUT_POST, 'cvv', FILTER_UNSAFE_RAW) ?? ''));

    if (!preg_match('/^[0-9]{13,16}$/', $tarjeta)) {
        $_SESSION['mensaje_error'] = 'El número de tarjeta no es válido.';
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/pago.php?id=' . urlencode((string) $idPedido));
        exit();
    }

    if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $caducidad)) {
        $_SESSION['mensaje_error'] = 'La fecha de caducidad no es válida.';
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/pago.php?id=' . urlencode((string) $idPedido));
        exit();
    }

    if (!caducidadNoExpirada($caducidad)) {
        $_SESSION['mensaje_error'] = 'La tarjeta está caducada.';
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/pago.php?id=' . urlencode((string) $idPedido));
        exit();
    }

    if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
        $_SESSION['mensaje_error'] = 'El CVV no es válido.';
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/pago.php?id=' . urlencode((string) $idPedido));
        exit();
    }

    if ($controller->actualizarEstadoPedido((int) $idPedido, 'En preparación')) {
        $_SESSION['mensaje_exito'] = "¡Pago aprobado! Tu pedido ha pasado a 'En preparación'.";
    } else {
        $_SESSION['mensaje_error'] = 'No se pudo actualizar el estado del pedido.';
    }

    header('Location: ' . $redirectMisPedidos);
    exit();
}

if ($metodo === 'camarero') {
    $_SESSION['mensaje_exito'] = '¡Pedido registrado! Avisa al camarero para pagar.';
    header('Location: ' . $redirectMisPedidos);
    exit();
}

$_SESSION['mensaje_error'] = 'Ha ocurrido un error con el método de pago seleccionado.';
header('Location: ' . $redirectMisPedidos);
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

/**
 * Comprueba si una fecha MM/AA no está expirada.
 */
function caducidadNoExpirada(string $caducidad): bool
{
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $caducidad, $matches)) {
        return false;
    }

    $mes = (int) $matches[1];
    $anio = 2000 + (int) $matches[2];

    $ultimoInstanteMes = strtotime(sprintf('%04d-%02d-01 23:59:59 +1 month -1 day', $anio, $mes));
    if ($ultimoInstanteMes === false) {
        return false;
    }

    return $ultimoInstanteMes >= time();
}
?>