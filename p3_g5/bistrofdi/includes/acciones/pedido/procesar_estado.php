<?php
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('camarero');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['accion'])) {
    
    $idPedido = (int)$_POST['id_pedido'];
    $accion = $_POST['accion'];

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
            $_SESSION['mensaje_error'] = 'Acción no reconocida.';
            header('Location: ' . BASE_URL . '/includes/vistas/camarero/panel_camarero.php');
            exit();
    }

    if ($nuevoEstado) {
        if ($controller->actualizarEstadoPedido($idPedido, $nuevoEstado)) {
            $_SESSION['mensaje_exito'] = "Pedido actualizado a '$nuevoEstado'.";
        } else {
            $_SESSION['mensaje_error'] = 'Error al actualizar el pedido.';
        }
    }
}

header('Location: ' . BASE_URL . '/includes/vistas/camarero/panel_camarero.php');
exit();
?>