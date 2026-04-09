<?php
/**
 * Cancela un pedido recibido o vacía el carrito actual.
 */

	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';

	exigirLogin();
	exigirRol('cliente');

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		header("Location: carrito.php");
		exit();
	}

	// Caso 1: cancelar un pedido ya creado
	if (isset($_POST['id_pedido'])) {
		$id_pedido = (int)$_POST['id_pedido'];
		$id_usuario = $_SESSION['id_usuario'];

		$controller = PedidoController::getInstance();
		$pedido = $controller->verPedido($id_pedido);

		// Validamos que el pedido exista y pertenezca al usuario que ha iniciado sesión
		if (!$pedido || $pedido['cliente_id'] != $id_usuario) {
			$_SESSION['mensaje_error'] = "Acceso denegado o pedido no encontrado.";
			header("Location: mis_pedidos.php");
			exit();
		}

		// Los pedidos cancelados no deben almacenarse en la BD.
		// Por eso, si todavía está en estado Recibido, lo eliminamos físicamente.
		if (strtolower($pedido['estado']) === 'recibido') {

			$eliminado = $controller->eliminarPedido($id_pedido);

			if ($eliminado) {
				$_SESSION['mensaje_exito'] = "El pedido ha sido cancelado correctamente.";
			} else {
				$_SESSION['mensaje_error'] = "Hubo un error en la base de datos al cancelar el pedido.";
			}
		} else {
			$_SESSION['mensaje_error'] = "Solo puedes cancelar pedidos en estado 'Recibido'.";
		}

		header("Location: mis_pedidos.php");
		exit();
	}

	// Caso 2: vaciar carrito actual
	if (isset($_POST['accion']) && $_POST['accion'] === 'vaciar_carrito') {
		if (isset($_SESSION['carrito'])) {
			unset($_SESSION['carrito']);
		}

		if (isset($_SESSION['tipoPedido'])) {
			unset($_SESSION['tipoPedido']);
		}

		$_SESSION['mensaje_exito'] = "El carrito ha sido vaciado correctamente. Puedes empezar un nuevo pedido cuando quieras.";
		header("Location: catalogo.php");
		exit();
	}

	$_SESSION['mensaje_error'] = "Acción no permitida.";
	header("Location: carrito.php");
	exit();
?>