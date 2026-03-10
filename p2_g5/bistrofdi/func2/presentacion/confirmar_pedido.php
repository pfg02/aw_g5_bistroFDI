/**
 * Vista solicitando confirmación del pedido.
 * @author Gabriel Omaña
 */

<?php
session_start();

require_once "../business/PedidoController.php";

$controller = PedidoController::getInstance();

$clienteId = $_SESSION["usuario_id"]; 
$tipo = $_SESSION["tipoPedido"];
$productos = $_SESSION["carrito"];

$idPedido = $controller->crearPedido($clienteId, $tipo, $productos);

unset($_SESSION["carrito"]);

header("Location: confirmacion.php?id=" . $idPedido);
exit();