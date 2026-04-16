<?php
    require_once __DIR__ . '/includes/sesion.php';
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/includes/negocio/PedidoController.php'; 

    exigirLogin();

    $controller = PedidoController::getInstance();
    $esGerente = isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente';

    // Si es gerente y no viene un id_cliente concreto, no debe ver "mis pedidos":
    // lo mandamos a la gestión de usuarios.
    if ($esGerente && !isset($_GET['id_cliente'])) {
        header("Location: gestionarUsuarios.php");
        exit();
    }

    if ($esGerente && isset($_GET['id_cliente'])) {
        $idCliente = (int) $_GET['id_cliente'];
        $subtituloListado = 'Historial de pedidos del usuario seleccionado';
    } else {
        exigirRol('cliente', 'gerente');
        $idCliente = $_SESSION['id_usuario'];
        $subtituloListado = 'Historial de tus compras';
    }
    
    $historialPedidos = $controller->verPedidosCliente($idCliente);

    $tituloPagina = 'Mis Pedidos - Bistró FDI';
    $bodyClass    = 'f0-body';

    ob_start();
?>

<div class="main-bienvenida">
	<section class="tarjeta-presentacion tarjeta-ancha">
		
		<h1><?= $esGerente && isset($_GET['id_cliente']) ? 'Pedidos del <span>Usuario</span>' : 'Mis <span>Pedidos</span>' ?></h1>
		<p class="lema"><?= htmlspecialchars($subtituloListado) ?></p>
		
		<div class="divisor"></div>
		
		<?php if (isset($_SESSION['mensaje_exito'])): ?>
			<div class="error-msg">
				<?php echo htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?>
			</div>
		<?php endif; ?>

		<?php if (isset($_SESSION['mensaje_error'])): ?>
			<div class="error-msg">
				<?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?>
			</div>
		<?php endif; ?>
		
		<div class="mensaje-sesion">
			<?php if (empty($historialPedidos)): ?>
				<p>No hay pedidos para mostrar.</p>
                <?php if (!$esGerente): ?>
				    <a href="pedido_inicio.php" class="btn-login">Hacer un Pedido</a>
                <?php endif; ?>
			<?php else: ?>
				<table class="tabla-pedidos tabla-mis-pedidos-movil">
					<thead>
						<tr>
							<th>Nº Pedido</th>
							<th>Fecha</th>
							<th>Tipo</th>
							<th>Estado</th>
							<th>Total</th>
							<th>Acciones</th>
                        </tr>
					</thead>
					<tbody>
						<?php foreach ($historialPedidos as $pedido): ?>
							<tr>
								<td data-label="Nº Pedido">
									<strong>#<?php echo htmlspecialchars($pedido['numero_pedido'] ?? $pedido['id']); ?></strong>
								</td>
								<td data-label="Fecha">
									<?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?>
								</td>
								<td data-label="Tipo">
									<?php echo htmlspecialchars($pedido['tipo']); ?>
								</td>
								<td data-label="Estado">
									<span class="badge-success">
										<?php echo htmlspecialchars($pedido['estado']); ?>
									</span>
								</td>
								<td data-label="Total">
									<strong><?php echo number_format($pedido['total'], 2); ?> €</strong>
								</td>
								<td data-label="Acciones">
									<?php if ($esGerente && isset($_GET['id_cliente'])): ?>
										<a href="detalle_pedido.php?id=<?= urlencode($pedido['id']) ?>&id_cliente=<?= urlencode($idCliente) ?>">Ver Detalle</a>
									<?php else: ?>
										<a href="detalle_pedido.php?id=<?= urlencode($pedido['id']) ?>">Ver Detalle</a>
									<?php endif; ?>
									<br>
									<?php if (!$esGerente && $pedido['estado'] === 'Recibido'): ?>
										<form action="cancelar_pedido.php" method="POST" class="form-actualizar-estado">
											<input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($pedido['id']); ?>">
											<button type="submit" class="btn-accion btn-peligro">Cancelar</button>
										</form>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="contenedor-volver">
            <?php if ($esGerente && isset($_GET['id_cliente'])): ?>
                <a href="gestionarUsuarios.php" class="btn-login">Volver a Usuarios</a>
            <?php else: ?>
			    <a href="index.php" class="btn-login">Volver al Inicio</a>
            <?php endif; ?>
		</div>

	</section>
</div>

<?php
    $contenidoPrincipal = ob_get_clean();
    require_once __DIR__ . '/includes/vistas/comun/plantilla.php';
?>