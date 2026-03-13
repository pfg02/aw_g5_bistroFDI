<?php
/**
 * Controlador frontal para iniciar un nuevo pedido.
 */

	require_once __DIR__ . '/includes/sesion.php';

	exigirLogin();
	exigirRol('cliente');
?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Bistró FDI - Nuevo Pedido</title>
		<link rel="stylesheet" href="css/estilos.css">
	</head>
	<body class="body-inicio">

		<?php include __DIR__ . '/includes/nav.php';?>

		<main class="main-bienvenida">
			<section class="tarjeta-presentacion">
				
				<h1>Nuevo <span>Pedido</span></h1>
				<p class="lema">¡Prepara tu paladar para disfrutar!</p>
				
				<div class="divisor"></div>
				
				<div class="mensaje-sesion">
					
					<form action="catalogo.php" method="POST">
						
						<div>
							<label for="tipo">¿Cómo prefieres tu pedido?</label>
							<select name="tipo" id="tipo" class="select-estado">
								<option value="Local">Consumir en el local</option>
								<option value="Llevar">Para llevar</option>
							</select>
						</div>

						<button type="submit" class="btn-login" style="width: 100%; font-size: 1.1rem; padding: 15px; margin-top: 10px;"> Empezar a pedir </button>

					</form>

				</div>

				<div class="contenedor-volver">
					<a href="index.php" class="btn-admin">Cancelar</a>
				</div>

			</section>
		</main>

		<?php include __DIR__ . '/includes/vistas/footer.php'; ?>

	</body>
</html>