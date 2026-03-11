<?php

/**
 * Controlador para confirmar un pedido en curso.
 * @author Gabriel Omaña
 */

session_start();

require_once __DIR__ . '/includes/negocio/PedidoController.php';

$controller = PedidoController::getInstance();

$clienteId = $_SESSION["id_usuario"]; 
$tipo = $_SESSION["tipoPedido"];
$productos = $_SESSION["carrito"];

$idPedido = $controller->crearPedido($clienteId, $tipo, $productos);

unset($_SESSION["carrito"]);

header("Location: confirmacion.php?id=" . $idPedido);
exit();