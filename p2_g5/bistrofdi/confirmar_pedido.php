<?php
/**
 * Convierte el carrito en un pedido real en la BD.
*/

	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';
	require_once __DIR__ . '/includes/negocio/PedidoDTO.php'; 

	exigirLogin();
	exigirRol('cliente');

	if (empty($_SESSION["carrito"])) {
		header("Location: catalogo.php");
		exit();
	}

	if (!isset($_SESSION["tipoPedido"])) {
		header("Location: pedido_inicio.php");
		exit();
	}

	$controller = PedidoController::getInstance();

	// Creamos el DTO con los datos del pedido
	$pedidoDTO = new PedidoDTO();
	$pedidoDTO->setClienteId($_SESSION["id_usuario"]);
	$pedidoDTO->setTipo($_SESSION["tipoPedido"]);
	$pedidoDTO->setProductos($_SESSION["carrito"]);

	// Intentamos crear el pedido en la BD
	$idPedido = $controller->crearPedido($pedidoDTO);

	// Comprobamos que se ha creado correctamente y redirigimos a la pasarela de pago
	if ($idPedido) {
		// Vaciamos el carrito y el tipo de pedido de la sesión
		unset($_SESSION["carrito"]);
		unset($_SESSION["tipoPedido"]);
		
		header("Location: pago.php?id=" . urlencode($idPedido));
		exit();

	} else {
		$_SESSION['mensaje_error'] = "Hubo un problema al conectar con la cocina. Por favor, vuelve a intentarlo.";
		header("Location: carrito.php");
		exit();
	}
?>