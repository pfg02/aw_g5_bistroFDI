<?php
require_once __DIR__ . '/includes/config.php';

session_start();

// Validar que el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
	header("Location: login.php");
	exit();
}

// 2. Comprobar que nos llega un ID de pedido (suponiendo que lo pasas por la URL: pago.php?id=5)
if (!isset($_GET['id'])) {
	die("Error: No se ha especificado ningún pedido para pagar.");
}

$pedido_id = $_GET['id'];

$conn = obtenerConexionBD();
$sql = "SELECT total FROM pedidos WHERE id IN ($pedido_id)";
$total = $conn->query($sql);

if ($total && $total->num_rows > 0) {
	$fila = $total->fetch_assoc();
	$total_pagar = $fila['total'];
} else {
	$total_pagar = "0.00";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>Pagar Pedido - Bistro FDI</title>
	<style>
	.tarjeta-detalles { display: none; margin-top: 15px; padding: 10px; border: 1px solid #ccc; }
	</style>
</head>
<body>

	<h2>Pasarela de Pago</h2>
	<p>Pedido número: <strong>#<?php echo htmlspecialchars($pedido_id); ?></strong></p>
	<p>Total a pagar: <strong><?php echo htmlspecialchars($total_pagar); ?> €</strong></p>

	<form action="confirmacion.php" method="POST">
	<input type="hidden" name="pedido_id" value="<?php echo htmlspecialchars($pedido_id); ?>">

	<h3>¿Cómo deseas pagar?</h3>
	
	<div>
	<input type="radio" id="pago_camarero" name="metodo_pago" value="camarero" checked onclick="mostrarTarjeta(false)">
	<label for="pago_camarero">Pagar al camarero en efectivo/tarjeta</label>
	</div>
	
	<div>
	<input type="radio" id="pago_tarjeta" name="metodo_pago" value="tarjeta" onclick="mostrarTarjeta(true)">
	<label for="pago_tarjeta">Pagar ahora con Tarjeta de Crédito (Simulación)</label>
	</div>

	<div id="detalles_tarjeta" class="tarjeta-detalles">
	<label for="num_tarjeta">Número de tarjeta:</label><br>
	<input type="text" id="num_tarjeta" name="num_tarjeta" pattern="\d{16}" title="Debe contener exactamente 16 números" maxlength="16" placeholder="1234567812345678"><br><br>

	<label for="caducidad">Fecha de caducidad (MM/AA):</label><br>
	<input type="text" id="caducidad" name="caducidad" pattern="(0[1-9]|1[0-2])\/[0-9]{2}" title="Formato MM/AA" maxlength="5" placeholder="12/26"><br><br>

	<label for="cvv">CVV:</label><br>
	<input type="text" id="cvv" name="cvv" pattern="\d{3}" title="Debe contener 3 números" maxlength="3" placeholder="123"><br>
	</div>

	<br>
	<button type="submit">Confirmar Pago</button>
	</form>

	<script>
	// Un pequeño script para ocultar/mostrar los campos de la tarjeta
	function mostrarTarjeta(mostrar) {
	const divTarjeta = document.getElementById('detalles_tarjeta');
	const inputsTarjeta = divTarjeta.querySelectorAll('input');
	
	if (mostrar) {
	divTarjeta.style.display = 'block';
	// Hacemos obligatorios los campos si elige tarjeta
	inputsTarjeta.forEach(input => input.required = true);
	} else {
	divTarjeta.style.display = 'none';
	// Quitamos la obligación si elige pagar al camarero
	inputsTarjeta.forEach(input => input.required = false);
	}
	}
	</script>

</body>
</html>