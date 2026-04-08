<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirRol('camarero');

$idPedido = (int)($_POST['id_pedido'] ?? 0);
$accion = $_POST['accion'] ?? '';

if ($idPedido <= 0 || $accion === '') {
    header('Location: panel_camarero.php');
    exit;
}

$controller = PedidoController::getInstance();
$controller->accionCamarero($idPedido, $accion);

header('Location: panel_camarero.php');
exit;