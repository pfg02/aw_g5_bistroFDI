<?php
	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';

	// Solo camareros y gerentes pueden actualizar estados
	if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol'])) {
		header("Location: login.php");
		exit();
	}

	$rol = $_SESSION['rol'];
	if ($rol !== 'camarero' && $rol !== 'gerente') {
		header("Location: index.php");
		exit();
	}

	// Comproba que tenemos los datos necesarios
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['nuevo_estado'])) {
		
		$idPedido = (int)$_POST['id_pedido'];
		$nuevoEstado = $_POST['nuevo_estado'];

		// Llamamos al controlador para actualizar el estado del pedido
		$controller = PedidoController::getInstance();
		$controller->actualizarEstadoPedido($idPedido, $nuevoEstado);
	}

	header("Location: panel_camarero.php");
	exit();
?>
// Revision P2
