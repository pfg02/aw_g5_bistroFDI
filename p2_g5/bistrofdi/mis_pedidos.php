<?php
    require_once __DIR__ . '/includes/sesion.php';
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/includes/negocio/PedidoController.php'; 

    // Usamos las funciones de seguridad que ya tenemos preparadas
    exigirLogin();
    exigirRol('cliente');

    $controller = PedidoController::getInstance();
    $idCliente = $_SESSION['id_usuario'];
    
    // Obtenemos todos los pedidos de este cliente
    $historialPedidos = $controller->verPedidosCliente($idCliente);
?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Bistró FDI - Mis Pedidos</title>
		<link rel="stylesheet" href="css/estilos.css">
	</head>

	<body class="body-inicio">

		<?php include __DIR__ . '/includes/nav.php'; ?>

		<main class="main-bienvenida">
			<section class="tarjeta-presentacion tarjeta-ancha">
				
				<h1>Mis <span>Pedidos</span></h1>
				<p class="lema">Historial de tus compras</p>
				
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
						<p>Aún no has realizado ningún pedido. ¡Anímate a probar nuestros platos!</p>
						<a href="pedido_inicio.php" class="btn-login">Hacer un Pedido</a>
					<?php else: ?>
						<table class="tabla-pedidos">
							<thead>
								<tr>
									<th>Nº Pedido</th>
									<th>Fecha</th>
									<th>Tipo</th>
									<th>Estado</th>
									<th>Total</th>
									<th>Acciones</th> </tr>
							</thead>
							<tbody>
								<?php foreach ($historialPedidos as $pedido): ?>
									<tr>
										<td><strong>#<?php echo htmlspecialchars($pedido['numero_pedido'] ?? $pedido['id']); ?></strong></td>
										<td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></td>
										<td><?php echo htmlspecialchars($pedido['tipo']); ?></td>
										
										<td>
											<?php if ($pedido['estado'] === 'Cancelado'): ?>
												<span class="badge badge-danger"><?php echo htmlspecialchars($pedido['estado']); ?></span>
											<?php else: ?>
												<span class="badge badge-success"><?php echo htmlspecialchars($pedido['estado']); ?></span>
											<?php endif; ?>
										</td>
										
										<td><strong><?php echo number_format($pedido['total'], 2); ?> €</strong></td>
										
										<td>
											<?php if ($pedido['estado'] === 'Recibido'): ?>
												<form action="cancelar_pedido.php" method="POST" class="form-actualizar-estado">
													<input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($pedido['id']); ?>">
													<button type="submit" class="btn-accion btn-peligro">
														Cancelar
													</button>
												</form>
											<?php else: ?>
												<span class="texto-ayuda">No disponible</span>
											<?php endif; ?>
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
		</main>

		<?php include __DIR__ . '/includes/vistas/footer.php'; ?>

	</body>
</html>