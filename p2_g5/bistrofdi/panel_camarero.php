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
?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Bistró FDI - Panel de Sala</title>
		<link rel="stylesheet" href="css/estilos.css">
	</head>

	<body class="body-inicio">

		<?php include __DIR__ . '/includes/nav.php'; ?>

		<main class="main-bienvenida">
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
													<option value="Recibido" <?php if($pedido['estado'] == 'Recibido') echo 'selected'; ?>>Recibido</option>
													<option value="En preparación" <?php if($pedido['estado'] == 'En preparación') echo 'selected'; ?>>En preparación</option>
													<option value="Listo cocina" <?php if($pedido['estado'] == 'Listo cocina') echo 'selected'; ?>>Listo cocina</option>
													<option value="Entregado">Entregado / Cobrado</option>
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
		</main>

		<?php include __DIR__ . '/includes/footer.php'; ?>

	</body>
</html>	
// Revision P2

// Revision P2
