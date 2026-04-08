<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

	exigirLogin();
	exigirRol('camarero', 'gerente');

	// Comproba que tenemos los datos necesarios
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['accion'])) {
		
		$idPedido = (int)$_POST['id_pedido'];
		$accion = $_POST['accion'];

		// Llamamos al controlador para actualizar el estado del pedido
		$controller = PedidoController::getInstance();
		$nuevoEstado = null;

		switch ($accion) {
        case 'cobrar':
            $nuevoEstado = 'En preparación';
            break;
        case 'terminar':
            $nuevoEstado = 'Terminado';
            break;
        case 'entregar':
            $nuevoEstado = 'Entregado';
            break;
        default:
            $_SESSION['mensaje_error'] = "Acción no reconocida.";
            header("Location: panel_camarero.php");
            exit();
    	}

		if ($nuevoEstado) {
        	if ($controller->actualizarEstadoPedido($idPedido, $nuevoEstado)) {
            	$_SESSION['mensaje_exito'] = "Pedido actualizado a '$nuevoEstado'.";
			} else {
				$_SESSION['mensaje_error'] = "Error al actualizar el pedido.";
			}
    	}
	}

header('Location: panel_camarero.php');
exit;