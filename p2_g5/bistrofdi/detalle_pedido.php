<?php
	/**
	 * Vista para mostrar el ticket detallado de un pedido.
	 */

	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';
	
	exigirLogin();
	exigirRol('cliente', 'admin');

	// Validar que llega el ID por la URL
	if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
		header("Location: mis_pedidos.php");
		exit();
	}

	$id_pedido = (int)$_GET['id'];
	$id_usuario = $_SESSION['id_usuario'];

	$controller = PedidoController::getInstance();
	$pedido = $controller->verPedido($id_pedido);

	// Validamos que el pedido exista y pertenezca al usuario que ha iniciado sesión
	if (!$pedido || $pedido['cliente_id'] != $id_usuario) {
		$_SESSION['mensaje_error'] = "Acceso denegado o pedido no encontrado.";
		header("Location: mis_pedidos.php");
		exit();
	}

	$lineas_pedido = $controller->obtenerProductosDePedido($id_pedido);

	$tituloPagina = 'Bistró FDI - Pedido';
	$bodyClass    = 'f0-body';

	ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        
        <h1>Detalle del <span>Pedido</span></h1>
        <div class="divisor"></div>

        <div class="mensaje-sesion">
            
            <div>
                <div>
                    <h3>Ticket #<?= $pedido['numero_pedido'] ?></h3>
                    <p>
                        <?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?> | <strong><?= htmlspecialchars($pedido['tipo']) ?></strong>
                    </p>
                </div>
                <div>
                    <span><?= htmlspecialchars($pedido['estado']) ?></span>
                </div>
            </div>
            
            <table class="tabla-pedidos">
                <thead>
                    <tr>
                        <th style="text-align: left;">Producto</th>
                        <th>Precio Ud.</th>
                        <th>Cant.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineas_pedido as $linea): 
                        $subtotal_linea = $linea['precio_base'] * $linea['cantidad'];
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($linea['nombre']) ?></strong></td>
                        <td><?= number_format($linea['precio_base'], 2) ?> €</td>
                        <td><?= $linea['cantidad'] ?></td>
                        <td><strong><?= number_format($subtotal_linea, 2) ?> €</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="modal-footer">
                <h3>Total:</h3>
                <div class="precio-modal">
                    <?= number_format($pedido['total'], 2) ?> €
                </div>
            </div>

            <div class="contenedor-botones-index">
                <a href="mis_pedidos.php" class="btn-login">Volver a Mis Pedidos</a>
            </div>
        </div>
    </section>
</div>

<?php
	$contenidoPrincipal = ob_get_clean();
	require_once __DIR__ . '/includes/vistas/comun/plantilla.php';
?>