<?php
	/**
	 * Interfaz de tablet para los cocineros.
	 */
	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';

	exigirLogin();
	exigirRol('cocinero', 'gerente', 'admin');

	$idCocinero = $_SESSION['id_usuario'];
	$controller = PedidoController::getInstance();

	// El controller filtrará internamente por el estado 'En preparación'
	$pedidosNuevos = $controller->verPedidosPorEstado('En preparación');

	// Buscamos si el cocinero tiene un pedido guardado en su sesión
	$miPedido = null;
	if (isset($_SESSION['pedido_activo_cocinero'][$idCocinero])) {
		$idPedidoActivo = $_SESSION['pedido_activo_cocinero'][$idCocinero];
		// Buscamos ese pedido con la función normal que ya tienes
		$pedidoTemp = $controller->verPedido($idPedidoActivo);
		
		// Comprobamos que siga existiendo y siga en estado 'Cocinando' (por si lo cancelaron)
		if ($pedidoTemp && strtolower($pedidoTemp['estado']) === 'cocinando') {
			$miPedido = $pedidoTemp;
		} else {
			// Si ya no está cocinando, limpiamos su sesión
			unset($_SESSION['pedido_activo_cocinero'][$idCocinero]);
		}
	}
	// Si tengo un pedido activo, sacamos sus platos a través del controller
	$platos = [];
	$todosPreparados = false;
	if ($miPedido) {
		$idPedidoActivo = $miPedido['id'];
		$platos = $controller->obtenerProductosDePedido($idPedidoActivo);
		
		// Comprobamos si todos los platos están hechos
		if (!empty($platos)) {
            $todosPreparados = true;
            foreach ($platos as &$plato) {
                $idProd = $plato['producto_id'];
                
                // Miramos en la sesión usando el ID del producto
                $estaPreparado = isset($_SESSION['preparados'][$idPedidoActivo][$idProd]) && $_SESSION['preparados'][$idPedidoActivo][$idProd] === true;
                
                $plato['preparado'] = $estaPreparado; 
                
                if (!$estaPreparado) {
                    $todosPreparados = false;
                }
            }
        }
	}

	$tituloPagina = 'Bistró FDI - Cocina';
	$bodyClass    = 'f0-body';

	ob_start();
?>

<div class="main-bienvenida">
	<section class="tarjeta-presentacion tarjeta-ancha">
		<h1>Panel de <span>Cocina</span></h1>
		<p class="lema">Gestión de comandas y preparación</p>
		<div class="divisor"></div>

		<div class="seccion-camarero">
			<h2 class="titulo-seccion">Comandas Nuevas</h2>
			
			<?php if (empty($pedidosNuevos)): ?>
				<p class="p-vacio">No hay comandas pendientes.</p>
			<?php else: ?>
				<table class="tabla-pedidos">
					<thead>
						<tr>
							<th>Ticket</th>
							<th>Tipo</th>
							<th>Hora</th>
							<th>Acción</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($pedidosNuevos as $p): ?>
							<tr>
								<td><strong>#<?= $p['id'] ?></strong></td>
								<td><?= htmlspecialchars($p['tipo_pedido'] ?? 'Local') ?></td>
								<td><?= date('H:i', strtotime($p['fecha'])) ?></td>
								<td>
									<form action="procesar_cocina.php" method="POST" style="margin: 0;">
										<input type="hidden" name="accion" value="reclamar">
										<input type="hidden" name="id_pedido" value="<?= $p['id'] ?>">
										<button type="submit" class="btn-accion btn-cobrar" <?= $miPedido ? 'disabled title="Termina tu pedido actual primero" style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
										Cocinar
										</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="divisor"></div>

		<div class="seccion-camarero">
			<h2 class="titulo-seccion">Mi Mesa de Trabajo</h2>
			
			<?php if (!$miPedido): ?>
				<p class="p-vacio" style="text-align: center; margin-top: 40px; margin-bottom: 40px;">
					<br>Esperando comanda... Selecciona un ticket de arriba para empezar a cocinar.
				</p>
			<?php else: ?>
				<p style="margin-bottom: 15px;">
					<strong>Preparando Ticket #<?= $miPedido['id'] ?> (<?= htmlspecialchars($miPedido['tipo_pedido'] ?? 'Local') ?>)</strong>
				</p>
				
				<table class="tabla-pedidos">
					<thead>
						<tr>
							<th>Cant.</th>
							<th>Producto</th>
							<th>Acción</th>
						</tr>
					</thead>
					<tbody>
						
						<?php foreach ($platos as $plato): 
							$idProd = $plato['producto_id'];
						?>
							<tr>
								<td><strong><?= $plato['cantidad'] ?>x</strong></td>
								<td style="<?= $plato['preparado'] ? 'text-decoration: line-through; color: #aaa;' : '' ?>">
									<?= htmlspecialchars($plato['nombre']) ?>
								</td>
								<td>
									<?php if (!$plato['preparado']): ?>
										<form action="procesar_cocina.php" method="POST" style="margin: 0;">
											<input type="hidden" name="accion" value="marcar_plato">
											<input type="hidden" name="id_pedido" value="<?= $miPedido['id'] ?>">
											<input type="hidden" name="id_producto" value="<?= $plato['producto_id'] ?>">
											<button type="submit" class="btn-accion btn-entregar">Listo</button>
										</form>
									<?php else: ?>
										<span>✅</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<div class = contenedor-botones-index>
					<form action="procesar_cocina.php" method="POST" style="margin: 0;">
						<input type="hidden" name="accion" value="finalizar_pedido">
						<input type="hidden" name="id_pedido" value="<?= $miPedido['id'] ?>">
						<button type="submit" class="btn-login" style="background: <?= $todosPreparados ? '#28a745' : '#ccc' ?>; border: none;" <?= !$todosPreparados ? 'disabled title="Marca todos los platos primero"' : '' ?>>
						¡Pasar a Sala!
						</button>
					</form>
				</div>
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