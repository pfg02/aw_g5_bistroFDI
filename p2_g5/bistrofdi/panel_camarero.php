<?php
	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/config.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';

	// Si no hay sesión o el rol no es autorizado, lo echamos
	if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol'])) {
		header("Location: login.php");
		exit();
	}

	$rol = $_SESSION['rol'];
	if ($rol !== 'camarero' && $rol !== 'gerente') {
		header("Location: index.php");
		exit();
	}

	$controller = PedidoController::getInstance();
	$pedidosActivos = $controller->verPedidosActivos();

	$tituloPagina = 'Panel de Sala - Bistró FDI';
	$bodyClass    = 'f0-body';

	ob_start();
?>

<div class="main-bienvenida">
	<section class="tarjeta-presentacion tarjeta-ancha">
		
		<h1>Panel de <span>Sala</span></h1>
		<p class="lema">Gestión de pedidos activos (Vista Camarero)</p>
		
		<div class="divisor"></div>
		
		<div class="mensaje-sesion">
			<?php if (empty($pedidosActivos)): ?>
				<p>No hay pedidos activos en este momento. ¡Buen trabajo!</p>
			<?php else: ?>
				<table class="tabla-pedidos">
					<thead>
						<tr>
							<th>Nº Pedido</th>
							<th>Cliente</th>
							<th>Tipo</th>
							<th>Estado Actual</th>
							<th>Total</th>
							<th>Actualizar Estado</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($pedidosActivos as $pedido): ?>
							<tr>
								<td><strong>#<?php echo htmlspecialchars($pedido['numero_pedido']); ?></strong></td>
								<td><?php echo htmlspecialchars($pedido['nombre_cliente'] . ' ' . $pedido['apellidos_cliente']); ?></td>
								<td><?php echo htmlspecialchars($pedido['tipo']); ?></td>
								<td><strong><?php echo htmlspecialchars($pedido['estado']); ?></strong></td>
								<td><?php echo number_format($pedido['total'], 2); ?> €</td>
								
								<td>
									<form action="procesar_estado.php" method="POST">
										<input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($pedido['id']); ?>">
										
										<select name="nuevo_estado" class="select-estado">
											<option value="Recibido" <?php if($pedido['estado'] == 'Recibido') echo 'selected'; ?>>Recibido (Pendiente pago)</option>
											<option value="En preparación" <?php if($pedido['estado'] == 'En preparación') echo 'selected'; ?>>En preparación (Pagado)</option>
											<option value="Listo cocina" <?php if($pedido['estado'] == 'Listo cocina') echo 'selected'; ?>>Listo cocina</option>
											<option value="Terminado" <?php if($pedido['estado'] == 'Terminado') echo 'selected'; ?>>Terminado (Listo para recoger)</option>
											<option value="Entregado">Entregado al cliente</option>
											<option value="Cancelado">Cancelar Pedido</option>
										</select>
										
										<button type="submit" class="btn-accion">Actualizar</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="contenedor-volver">
			<a href="index.php" class="btn-login">Volver al Inicio</a>
		</div>

	</section>
</div>

<?php
	$contenidoPrincipal = ob_get_clean();
	require_once __DIR__ . '/includes/vistas/comun/plantilla.php';
?>