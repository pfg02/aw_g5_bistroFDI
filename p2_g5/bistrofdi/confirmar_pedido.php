<?php
/**
 * Script de acción: Convierte el carrito en un pedido real en la BD.
 * @author Gabriel Omaña
 */

require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';
// Requerimos el DTO para empaquetar los datos
require_once __DIR__ . '/includes/negocio/PedidoDTO.php'; 

exigirLogin();
exigirRol('cliente');

// Si no hay carrito, los echamos de vuelta al catálogo
if (empty($_SESSION["carrito"])) {
    header("Location: catalogo.php");
    exit();
}

$controller = PedidoController::getInstance();

// 1. Empaquetamos los datos en la "caja de transporte" que creamos
$pedidoDTO = new PedidoDTO();
$pedidoDTO->setClienteId($_SESSION["id_usuario"]);
$pedidoDTO->setTipo($_SESSION["tipoPedido"]);
$pedidoDTO->setProductos($_SESSION["carrito"]);
// (El total, estado, número y fecha se calculan solos en tu PedidoServiceApp, ¿recuerdas?)

// 2. Mandamos la caja al Controlador y guardamos el ID generado
$idPedido = $controller->crearPedido($pedidoDTO);

// 3. ¡Vaciamos el carrito y el tipo de la memoria para que no queden restos!
unset($_SESSION["carrito"]);
unset($_SESSION["tipoPedido"]);

// 4. Redirigimos instantáneamente a la nueva pantalla de pago
header("Location: pago.php?id=" . urlencode($idPedido));
exit();
?>