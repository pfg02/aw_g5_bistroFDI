<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: carrito.php');
    exit;
}

$carrito = $_SESSION['carrito'] ?? [];
$tipo = $_SESSION['tipo_pedido'] ?? '';

if (empty($carrito) || $tipo === '') {
    header('Location: carrito.php');
    exit;
}

$controller = PedidoController::getInstance();

try {
    $pedidoId = $controller->crearPedido((int)$_SESSION['id_usuario'], $tipo, $carrito);
    unset($_SESSION['carrito']);
    header('Location: pago.php?id=' . $pedidoId);
    exit;
} catch (Exception $e) {
    die('Error al crear el pedido: ' . htmlspecialchars($e->getMessage()));
}