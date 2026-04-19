<?php
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('cliente');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

if (!isset($_POST['id_pedido'], $_POST['metodo_pago'])) {
    $_SESSION['mensaje_error'] = 'Faltan datos para procesar el pago.';
    header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
    exit();
}

$idPedido = (int) $_POST['id_pedido'];
$metodo = $_POST['metodo_pago'];

$controller = PedidoController::getInstance();
$pedido = $controller->verPedido($idPedido);

if (!$pedido || (int) $pedido['cliente_id'] !== (int) $_SESSION['id_usuario']) {
    $_SESSION['mensaje_error'] = 'Acción no permitida o pedido no encontrado.';
    header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
    exit();
}

if ($pedido['estado'] !== 'Recibido') {
    $_SESSION['mensaje_error'] = 'Ese pedido ya no se puede pagar desde esta pantalla.';
    header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
    exit();
}

if ($metodo === 'tarjeta') {
    $ok = $controller->actualizarEstadoPedido($idPedido, 'En preparación');

    if ($ok) {
        $_SESSION['mensaje_exito'] = "¡Pago aprobado! Tu pedido ha pasado a 'En preparación'.";
    } else {
        $_SESSION['mensaje_error'] = 'No se pudo actualizar el estado del pedido.';
    }
} else {
    $_SESSION['mensaje_error'] = 'Método de pago no válido.';
}

header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
exit();