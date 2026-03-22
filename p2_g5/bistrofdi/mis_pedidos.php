<?php
	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/config.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php'; 

	// Si no está logueado, lo mandamos al login
	if (!isset($_SESSION['id_usuario'])) {
		header("Location: login.php");
		exit();
	}

	$controller = PedidoController::getInstance();
	$idCliente = $_SESSION['id_usuario'];
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
								<th>Detalles</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($historialPedidos as $pedido): ?>
								<tr>
									<td><strong>#<?php echo htmlspecialchars($pedido['numero_pedido']); ?></strong></td>
									<td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></td>
									<td><?php echo htmlspecialchars($pedido['tipo']); ?></td>
									<td><?php echo htmlspecialchars($pedido['estado']); ?></td>
									<td><strong><?php echo number_format($pedido['total'], 2); ?> €</strong></td>
									<td>
										<a href="confirmacion.php?id=<?php echo urlencode($pedido['id']); ?>" class="btn-accion">Ver</a>
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
