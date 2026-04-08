<?php
/**
 * Cancela el pedido en curso vaciando el carrito.
*/

	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';

	exigirLogin();
	exigirRol('cliente');

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'])) {
    
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

    if ($pedido) {
        // Solo dejamos cancelar si no está completado
        if (strtolower($pedido['estado']) == 'en preparación' || strtolower($pedido['estado']) == 'recibido') {
            
			$actualizado = $controller->actualizarEstadoPedido($id_pedido, 'Cancelado');            
            
			if ($actualizado) {
            	$_SESSION['mensaje_exito'] = "El pedido ha sido cancelado correctamente.";
			} else {
				$_SESSION['mensaje_error'] = "Hubo un error en la base de datos al cancelar el pedido.";
			}
        } else {
            $_SESSION['mensaje_error'] = "No puedes cancelar un pedido que ya está completado.";
        }
    } else {
        $_SESSION['mensaje_error'] = "Acción no permitida o pedido no encontrado.";
    }

    header("Location: mis_pedidos.php");
    exit();
}

	// Borramos el carrito de la sesión
	if (isset($_SESSION['carrito'])) {
		unset($_SESSION['carrito']);
	}

	// Borramos también el tipo de pedido (Local/Llevar) para que empiece de cero
	if (isset($_SESSION['tipoPedido'])) {
		unset($_SESSION['tipoPedido']);
	}

	// Mandamos un mensaje para avisar al usuario
	$_SESSION['mensaje_exito'] = "El carrito ha sido vaciado correctamente. Puedes empezar un nuevo pedido cuando quieras.";

	// Devolvemos a la página del catálogo
	header("Location: catalogo.php");
	exit();
?>