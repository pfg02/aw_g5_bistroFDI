<?php
/**
 * Script unificado: Cancela un pedido de la BD o vacía el carrito de la sesión.
*/

	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';

	exigirLogin();
	exigirRol('cliente');

	// Si viene por POST y trae un ID, cancelamos el pedido de la Base de Datos
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'])) {
		
		$idPedido = (int)$_POST['id_pedido'];
		$controller = PedidoController::getInstance();
		
		$pedido = $controller->verPedido($idPedido);
		
		// Comprobamos que el pedido exista, sea del cliente y esté en estado "Recibido" para permitir la cancelación
		if ($pedido && $pedido['cliente_id'] == $_SESSION['id_usuario'] && $pedido['estado'] === 'Recibido') {
			
			$exito = $controller->actualizarEstadoPedido($idPedido, 'Cancelado');
			if ($exito) {
				$_SESSION['mensaje_exito'] = "Tu pedido #{$pedido['numero_pedido']} ha sido cancelado definitivamente.";
			} else {
				$_SESSION['mensaje_error'] = "Hubo un problema al intentar cancelar el pedido.";
			}
		} else {
			$_SESSION['mensaje_error'] = "No puedes cancelar este pedido porque ya está siendo preparado o no te pertenece.";
		}
		
		header("Location: mis_pedidos.php");
		exit();
	}

	// Si viene de un enlace normal, vaciamos el Carrito de la Sesión
	if (isset($_SESSION['carrito'])) {
		unset($_SESSION['carrito']);
	}
	if (isset($_SESSION['tipoPedido'])) {
		unset($_SESSION['tipoPedido']);
	}

	$_SESSION['mensaje_exito'] = "El carrito ha sido vaciado correctamente. Puedes empezar un nuevo pedido.";
	header("Location: pedido_inicio.php");
	exit();
?>