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

unset($_SESSION['mensaje_error']);
unset($_SESSION['mensaje_exito']);

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

    if (!preg_match('/^[0-9]{13,19}$/', $tarjeta)) {
        $_SESSION['mensaje_error'] = 'El número de tarjeta no tiene un formato válido.';
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/pago.php?id=' . urlencode((string) $idPedido));
        exit();
    }

    if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $caducidad)) {
        $_SESSION['mensaje_error'] = 'La fecha de caducidad no tiene un formato válido.';
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/pago.php?id=' . urlencode((string) $idPedido));
        exit();
    }

    if (!caducidadSuperiorAlMesActual($caducidad)) {
        $_SESSION['mensaje_error'] = 'La fecha de caducidad debe ser posterior al mes actual.';
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/pago.php?id=' . urlencode((string) $idPedido));
        exit();
    }

    if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
        $_SESSION['mensaje_error'] = 'El CVV no tiene un formato válido.';
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/pago.php?id=' . urlencode((string) $idPedido));
        exit();
    }

    /*
     * Regla después del pago:
     * - Si el pedido solo tiene bebidas, pasa directamente a "Listo cocina".
     * - Si tiene comida o mezcla comida/bebida, pasa a "En preparación".
     */
    $estadoTrasPago = $controller->obtenerEstadoTrasPago((int) $idPedido);

    if ($controller->actualizarEstadoPedido((int) $idPedido, $estadoTrasPago)) {
        $_SESSION['mensaje_exito'] = "¡Pago aprobado! Tu pedido ha pasado a '$estadoTrasPago'.";
    } else {
        $_SESSION['mensaje_error'] = 'No se pudo actualizar el estado del pedido.';
    }

    header('Location: ' . $redirectMisPedidos);
    exit();
}

if ($metodo === 'camarero') {
    /*
     * Si paga al camarero, todavía no está pagado.
     * Por eso se queda en "Recibido" y todavía puede cancelarse.
     */
    $_SESSION['mensaje_exito'] = '¡Pedido registrado! Avisa al camarero para pagar.';
    header('Location: ' . $redirectMisPedidos);
    exit();
}

$_SESSION['mensaje_error'] = 'Ha ocurrido un error con el método de pago seleccionado.';
header('Location: ' . $redirectMisPedidos);
exit();

function obtenerClienteIdPedido($pedido): ?int
{
    if (is_array($pedido)) {
        $valor = $pedido['cliente_id'] ?? null;
        $id = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        return $id === false ? null : (int) $id;
    }

    if (is_object($pedido) && method_exists($pedido, 'getClienteId')) {
        $valor = $pedido->getClienteId();
        $id = filter_var($valor, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        return $id === false ? null : (int) $id;
    }

    return null;
}

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

function caducidadSuperiorAlMesActual(string $caducidad): bool
{
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $caducidad, $matches)) {
        return false;
    }

    $mesTarjeta = (int) $matches[1];
    $anioTarjeta = 2000 + (int) $matches[2];

    $mesActual = (int) date('m');
    $anioActual = (int) date('Y');

    return $anioTarjeta > $anioActual
        || ($anioTarjeta === $anioActual && $mesTarjeta > $mesActual);
}