<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirLogin();

$idPedido = (int)($_POST['id_pedido'] ?? 0);

if ($idPedido <= 0) {
    header('Location: index.php');
    exit;
}

$controller = PedidoController::getInstance();
$ok = $controller->pagarConTarjeta($idPedido, (int)$_SESSION['id_usuario']);

if (!$ok) {
    die('No se ha podido procesar el pago.');
}

header('Location: confirmacion.php?id=' . $idPedido);
exit;