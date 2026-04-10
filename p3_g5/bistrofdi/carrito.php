<?php
/**
 * Vista del carrito de un pedido en curso.
 */

	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/integracion/ProductoDAO.php';

	exigirLogin();
	exigirRol('cliente');

	$carrito = $_SESSION["carrito"] ?? [];
	$tipoPedido = $_SESSION["tipoPedido"] ?? 'No definido';

	$productoDAO = new ProductoDAO();

	$productos = [];
	$total = 0;

	if (!empty($carrito)) {
		foreach ($carrito as $id_producto => $cantidad) {
			$productoDTO = $productoDAO->obtenerPorId($id_producto);
        
			if ($productoDTO) {
				$productos[$id_producto] = [
					"nombre"      => $productoDTO->nombre,
					"precio_base" => $productoDTO->precio,
					"iva"         => $productoDTO->iva ?? 21
				];
        	}
		}
	}

	$tituloPagina = 'Bistró FDI - Mi Carrito';
	$bodyClass    = 'f0-body';

	ob_start();
?>


<div class="main-bienvenida">
	<section class="tarjeta-presentacion tarjeta-ancha">
		
		<h1>Mi <span>Carrito</span></h1>
		<p class="lema">Revisa tu pedido (<?php echo htmlspecialchars($tipoPedido); ?>)</p>
		
		<div class="divisor"></div>

		<?php if (isset($_SESSION['mensaje_exito'])): ?>
			<div class="alerta alerta-exito">
				<?php echo htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?>
			</div>
		<?php endif; ?>

		<div class="mensaje-sesion mensaje-sesion-ancho">
			
			<?php if (empty($carrito)): ?>
				<p>Tu carrito está vacío ahora mismo.</p>
				<div class="contenedor-botones-carrito">
					<a href="catalogo.php" class="btn-login">Volver al Catálogo</a>
				</div>
			
			<?php else: ?>
				
				<table class="tabla-pedidos">
					<thead>
						<tr>
							<th>Producto</th>
							<th>Precio (con IVA)</th>
							<th>Cantidad</th>
							<th>Subtotal</th>
							<th>Quitar</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						foreach ($carrito as $productoId => $cantidad): 
							if (!isset($productos[$productoId])) continue; 

							$producto = $productos[$productoId];
							$precioBase = $producto["precio_base"];
							$porcentajeIva = $producto["iva"];
							
							$precioConIva = $precioBase + ($precioBase * ($porcentajeIva / 100));
							$subtotal = $precioConIva * $cantidad;
							$total += $subtotal;
						?>
						<tr>
							<td><strong><?php echo htmlspecialchars($producto["nombre"]); ?></strong></td>
							<td><?php echo number_format($precioConIva, 2); ?> €</td>
							<td><?php echo $cantidad; ?></td>
							<td><strong><?php echo number_format($subtotal, 2); ?> €</strong></td>
							
							<td>
								<form action="eliminar_del_carrito.php" method="POST">
									<input type="hidden" name="productoId" value="<?php echo htmlspecialchars($productoId); ?>">
									<button type="submit" class="btn-accion btn-peligro" title="Eliminar del carrito">x</button>
								</form>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<div class="resumen-carrito">
					<h3>Total a Pagar:</h3>
					<div class="total-destacado"><?php echo number_format($total, 2); ?> €</div>
				</div>

				<div>
					<h3>¿Cómo quieres abonar tu pedido?</h3>
                
					<div class="botones-pago-horizontal">
						<form action="confirmar_pedido.php" method="POST">
                        	<button type="submit" class="btn-confirmar-compra">Pagar con Tarjeta</button>
                    	</form>

						<form action="confirmar_pedido.php" method="POST" class="form-confirmar">
							<input type="hidden" name="metodo_pago" value="camarero">
							<button type="submit" class="btn-confirmar-compra btn-camarero">Solicitar cobro al personal</button>
						</form>
					</div>
				</div>

			<?php endif; ?>

		</div>

		<?php if (!empty($carrito)): ?>
			<div class="contenedor-botones-carrito">
				<a href="catalogo.php" class="btn-admin">Seguir comprando</a>

				<form action="cancelar_pedido.php" method="POST" style="display:inline;">
					<input type="hidden" name="accion" value="vaciar_carrito">
					<button type="submit" class="btn-login btn-peligro">Vaciar Carrito</button>
				</form>
			</div>
		<?php endif; ?>

	</section>
</div>

<?php
	$contenidoPrincipal = ob_get_clean();
	require_once __DIR__ . '/includes/vistas/comun/plantilla.php';
?>