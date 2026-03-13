<?php
/**
 * Script de acción: Simula el procesamiento del pago de un pedido.
 * @author Gabriel Omaña
 */

require_once __DIR__ . '/includes/sesion.php';
// Requerimos el controlador por si en el futuro quieres hacer un UPDATE en la BD 
// para guardar si pagó en efectivo o con tarjeta.
require_once __DIR__ . '/includes/negocio/PedidoController.php';

// 1. Seguridad de siempre
exigirLogin();
exigirRol('cliente');

// 2. Comprobamos que lleguen los datos del formulario de pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['metodo_pago'])) {
    
    $idPedido = (int)$_POST['id_pedido'];
    $metodo = $_POST['metodo_pago'];

    // --- SIMULACIÓN DE LA PASARELA DE PAGO ---
    // En la vida real, aquí conectarías con Stripe, Redsys o PayPal.
    // Nosotros vamos a asumir que todas las tarjetas simuladas tienen saldo y se aprueban.

    if ($metodo === 'tarjeta') {
        // Generamos el mensaje Flash para la tarjeta
        $_SESSION['mensaje_exito'] = "💳 ¡Pago aprobado! Tu pedido ha sido procesado correctamente y ya estamos preparándolo.";
        
    } elseif ($metodo === 'camarero') {
        // Generamos el mensaje Flash para el pago manual
        $_SESSION['mensaje_exito'] = "🙋‍♂️ ¡Pedido registrado! Por favor, ten preparado el importe o tu tarjeta para cuando te atienda nuestro personal.";
        
    } else {
        // Por si alguien manipula el HTML con el inspector de elementos
        $_SESSION['mensaje_error'] = "Ha ocurrido un error con el método de pago seleccionado.";
    }

    // 3. Redirigimos al cliente a su historial para que vea el pedido
    header("Location: mis_pedidos.php");
    exit();
}

// Si alguien intenta escribir 'procesar_pago.php' en la barra de direcciones sin enviar el formulario
header("Location: index.php");
exit();
?>