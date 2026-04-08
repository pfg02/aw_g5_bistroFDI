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

   	if ($accion === 'reclamar') {
        // El cocinero se asigna el pedido. 
        // Internamente el Controller debe pasar el estado de 'En preparación' a 'Cocinando'
        $controller->($idPedido, $idCocinero);
        
    } elseif ($accion === 'marcar_plato' && isset($_POST['id_producto'])) {
        // Marca un producto específico como listo
        $idProducto = (int)$_POST['id_producto'];
        $controller->marcarProductoComoListo($idPedido, $idProducto);

    } elseif ($accion === 'finalizar_pedido') {
        // Pasa el pedido de 'Cocinando' a 'Listo cocina'
        $controller->actualizarEstadoPedido($idPedido, 'Listo cocina');
    }
}

// Redirige de vuelta a la tablet de forma casi invisible
header("Location: panel_cocina.php");
exit();
?>