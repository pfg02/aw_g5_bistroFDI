<?php
	/**
	* Catálogo de productos para añadir a un pedido.
	*/

	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/config.php';

	// Exigimos inicio de sesión y rol de cliente
	exigirLogin();
	exigirRol('cliente');

	if (!isset($_SESSION['carrito'])) {
		$_SESSION['carrito'] = [];
	}

	// Si venimos de pedido_inicio.php, guardamos si es Local o Llevar
	if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tipo"])) {
		$_SESSION["tipoPedido"] = $_POST["tipo"];
	}

	// Obtener productos ofertados desde la base de datos
	$productos = [];
	$conn = obtenerConexionBD();
	$sql = "SELECT id, nombre, precio_base, imagen FROM productos WHERE ofertado = 1";
	$result = $conn->query($sql);

	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$productos[] = $row;
		}
	}

	// Calculamos cuántos artículos diferentes hay en el carrito ahora mismo
	$itemsEnCarrito = count($_SESSION['carrito']);
?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Bistró FDI - Catálogo</title>
		<link rel="stylesheet" href="css/estilos.css">
	</head>
	<body class="body-inicio">

		<?php include __DIR__ . '/includes/nav.php'; ?>

		<main class="main-bienvenida">
			<section class="tarjeta-presentacion tarjeta-ancha">
				
				<h1>Nuestro <span>Catálogo</span></h1>
				<p class="lema">
					Tipo de Pedido: <strong><?php echo htmlspecialchars($_SESSION["tipoPedido"] ?? 'No definido'); ?></strong>
				</p>
				
				<div class="divisor"></div>

				<div class="contenedor-botones-index">
					<a href="carrito.php" class="btn-admin">Ver mi carrito
						<?php if ($itemsEnCarrito > 0): ?>
							<span class="badge-carrito"><?php echo $itemsEnCarrito; ?></span>
						<?php endif; ?>
					</a>
				</div>
				
				<div class="mensaje-sesion">
					
					<?php if (empty($productos)): ?>
						<p>Lo sentimos, no hay productos disponibles en este momento.</p>
					<?php else: ?>
						
						<div class="catalogo-grid">
							<?php foreach ($productos as $p): ?>
								
								<div class="producto-card">
									<img src="img/productos/<?php echo htmlspecialchars($p['imagen'] ?? 'default_prod.png'); ?>" 
										alt="<?php echo htmlspecialchars($p['nombre']); ?>" 
										class="producto-img-catalogo"
										onerror="this.src='img/productos/default_prod.png'">
									
									<div class="producto-nombre"><?php echo htmlspecialchars($p["nombre"]); ?></div>
									<div class="producto-precio"><?php echo number_format($p["precio_base"], 2); ?> €</div>
									
									<form action="anadir_producto.php" method="POST" class="form-add-carrito">
										<input type="hidden" name="productoId" value="<?php echo htmlspecialchars($p["id"]); ?>">
										
										<input type="number" name="cantidad" value="1" min="1" class="input-cantidad">
										
										<button type="submit" class="btn-login btn-accion">Añadir</button>
									</form>
								</div>

							<?php endforeach; ?>
						</div>

					<?php endif; ?>
				</div>

				<div class="contenedor-volver">
					<a href="pedido_inicio.php" class="btn-admin btn-cancelar">Cambiar tipo de pedido</a>
				</div>

			</section>
		</main>

		<?php include __DIR__ . '/includes/vistas/footer.php'; ?>

	</body>
</html>