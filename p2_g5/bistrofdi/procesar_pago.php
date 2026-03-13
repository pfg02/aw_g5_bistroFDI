<?php
/**
 * Script de acción: Simula el procesamiento del pago de un pedido.
 * @author Gabriel Omaña
 */

require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirLogin();
exigirRol('cliente');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['metodo_pago'])) {
    
    $idPedido = (int)$_POST['id_pedido'];
    $metodo = $_POST['metodo_pago'];

    $controller = PedidoController::getInstance();
    $pedido = $controller->verPedido($idPedido);

    // BLINDAJE DE SEGURIDAD: Comprobamos que el pedido sea suyo y esté "Recibido"
    if (!$pedido || $pedido['cliente_id'] != $_SESSION['id_usuario'] || $pedido['estado'] !== 'Recibido') {
        $_SESSION['mensaje_error'] = "❌ Acción no permitida o el pedido ya fue procesado.";
        header("Location: mis_pedidos.php");
        exit();
    }

    // --- SIMULACIÓN DE LA PASARELA DE PAGO ---
    if ($metodo === 'tarjeta') {
        $controller->actualizarEstadoPedido($idPedido, 'En preparación');
        $_SESSION['mensaje_exito'] = "💳 ¡Pago aprobado! Tu pedido ha pasado a 'En preparación'.";
        
    } elseif ($metodo === 'camarero') {
        $_SESSION['mensaje_exito'] = "🙋‍♂️ ¡Pedido registrado! Avisa al camarero para pagar.";
        
    } else {
        $_SESSION['mensaje_error'] = "Ha ocurrido un error con el método de pago seleccionado.";
    }

    header("Location: mis_pedidos.php");
    exit();
}

header("Location: index.php");
exit();
?>