<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';
require_once __DIR__ . '/includes/config.php';

exigirLogin();
exigirRol('cocinero', 'gerente', 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id_pedido'])) {
    $accion = $_POST['accion'];
    $idPedido = (int)$_POST['id_pedido'];
    $idCocinero = $_SESSION['id_usuario'];
    $controller = PedidoController::getInstance();

    if (!isset($_SESSION['pedido_activo_cocinero'])) {
        $_SESSION['pedido_activo_cocinero'] = [];
    }

    if ($accion === 'reclamar') {
        $reclamado = $controller->asignarCocineroAPedido($idPedido, $idCocinero, 'Cocinando');

        if ($reclamado) {
            $_SESSION['pedido_activo_cocinero'][$idCocinero] = $idPedido;
        } else {
            $_SESSION['mensaje_error'] = 'Ese pedido ya no está disponible para ser reclamado.';
        }

    } elseif ($accion === 'marcar_plato' && isset($_POST['id_producto'])) {
        $idProducto = (int)$_POST['id_producto'];
        $controller->marcarProductoComoPreparado($idPedido, $idProducto);

    } elseif ($accion === 'finalizar_pedido') {
        $actualizado = $controller->actualizarEstadoPedido($idPedido, 'Listo cocina');

        if ($actualizado) {
            unset($_SESSION['pedido_activo_cocinero'][$idCocinero]);
        }

    } elseif ($accion === 'liberar_pedido') {
		$actualizado = $controller->asignarCocineroAPedido($idPedido, NULL, 'En preparación');
       
		if ($actualizado) {
            unset($_SESSION['pedido_activo_cocinero'][$idCocinero]);
        }

        $_SESSION['mensaje_exito'] = 'El pedido ha sido liberado y ha vuelto a En preparación.';
    }
}

header("Location: panel_cocina.php");
exit();
?>