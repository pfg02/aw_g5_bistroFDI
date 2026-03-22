<?php

/**
	* Vista del carrito de un pedido en curso.
	* @author Gabriel Omaña
	*/

session_start();
require_once __DIR__ . '/includes/config.php';

$carrito = $_SESSION["carrito"] ?? [];

$productos = [];
$total = 0;

if (!empty($carrito)) {

	$ids = implode(",", array_keys($carrito));

	// esta consulta se debería hacer de acuerdo al modelo de 3 capas
	$conn = obtenerConexionBD();
	$sql = "SELECT id, nombre, precio_base FROM productos WHERE id IN ($ids)";
	$result = $conn->query($sql);

	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$productos[$row["id"]] = $row;
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Carrito</title>
</head>

<body>

<h2>Carrito</h2>

<?php if (empty($carrito)): ?>

<p>El carrito está vacío.</p>

<?php else: ?>

<?php foreach ($carrito as $productoId => $cantidad): 

$producto = $productos[$productoId];
$subtotal = $producto["precio_base"] * $cantidad;
$total += $subtotal;

?>

<div>

<strong><?php echo $producto["nombre"]; ?></strong><br>

Cantidad: <?php echo $cantidad; ?><br>

Precio: <?php echo $producto["precio_base"]; ?> €<br>

Subtotal: <?php echo $subtotal; ?> €

</div>

<hr>

<?php endforeach; ?>

<h3>Total: <?php echo $total; ?> €</h3>

<form action="confirmar_pedido.php" method="POST">
<button type="submit">Confirmar pedido</button>
</form>

<?php endif; ?>

<br>

<a href="catalogo.php">Seguir comprando</a>

</body>
</html>
// Revision P2

// Revision P2
