<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

<<<<<<< HEAD
exigirRol('camarero');

$idPedido = (int)($_POST['id_pedido'] ?? 0);
$accion = $_POST['accion'] ?? '';

if ($idPedido <= 0 || $accion === '') {
    header('Location: panel_camarero.php');
    exit;
}

$controller = PedidoController::getInstance();
$controller->accionCamarero($idPedido, $accion);
=======
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
>>>>>>> cd2ee5df9656ed01f92af55382c3f638b842a97d

header('Location: panel_camarero.php');
exit;