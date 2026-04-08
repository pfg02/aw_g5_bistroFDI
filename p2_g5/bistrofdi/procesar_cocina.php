<?php
/**
 * Procesa las acciones de los cocineros en su tablet.
 */
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirLogin();
exigirRol('cocinero', 'gerente', 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_pedido'])) {
    
    $accion = $_POST['accion'];
    $idPedido = (int)$_POST['id_pedido'];
	$idCocinero = $_SESSION['id_usuario'];
    $controller = PedidoController::getInstance();

	// Inicializar el array en la sesión si no existe
    if (!isset($_SESSION['preparados'])) {
        $_SESSION['preparados'] = [];
    }
	if (!isset($_SESSION['pedido_activo_cocinero'])) {
        $_SESSION['pedido_activo_cocinero'] = [];
    }

   	if ($accion === 'reclamar') { 
        // 'En preparación' a 'Cocinando'
        $controller->actualizarEstadoPedido($idPedido, 'Cocinando');
		$_SESSION['pedido_activo_cocinero'][$idCocinero] = $idPedido;
        
    } elseif ($accion === 'marcar_plato' && isset($_POST['id_producto'])) {
        // Guardamos que este producto está listo
        $idProducto = (int)$_POST['id_producto'];
        $_SESSION['preparados'][$idPedido][$idProducto] = true;

    } elseif ($accion === 'finalizar_pedido') {
        // Pasa el pedido de 'Cocinando' a 'Listo cocina'
        $controller->actualizarEstadoPedido($idPedido, 'Listo cocina');

		// Limpiamos la sesión del cocinero para ese pedido
        if (isset($_SESSION['pedido_activo_cocinero'][$idCocinero])) {
            unset($_SESSION['pedido_activo_cocinero'][$idCocinero]);
        }
		// Limpiamos la sesión de platos preparados para ese pedido
		if (isset($_SESSION['preparados'][$idPedido])) {
            unset($_SESSION['preparados'][$idPedido]);
        }

    }
}

// Redirige de vuelta a la tablet de forma casi invisible
header("Location: panel_cocina.php");
exit();
?>