<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirLogin();

if (isset($_GET['carrito'])) {
    unset($_SESSION['carrito'], $_SESSION['tipo_pedido']);
    header('Location: index.php');
    exit;
}

$idPedido = (int)($_GET['id'] ?? 0);
if ($idPedido <= 0) {
    header('Location: mis_pedidos.php');
    exit;
}

$controller = PedidoController::getInstance();
$ok = $controller->cancelarPedidoCliente($idPedido, (int)$_SESSION['id_usuario']);

header('Location: mis_pedidos.php');
exit;