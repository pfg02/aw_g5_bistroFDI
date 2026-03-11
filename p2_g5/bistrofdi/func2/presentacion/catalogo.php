<?php

/**
 * Vista del catálogo de productos que se pueden añadir a un pedido.
 * @author Gabriel Omaña
 */

session_start();
require_once __DIR__ . '/../../includes/config.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tipo"])) {
    $_SESSION["tipoPedido"] = $_POST["tipo"];
}

// obtenemos la lista de productos de la base de datos
// idealmente, esto se debe hacer a través de ProductoController->ProductoServiceApp->ProductoDAO
$productos = [];
$conn = obtenerConexionBD();
$sql = "SELECT id, nombre, precio_base FROM productos";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Catálogo</title>
</head>

<body>

<h2>Catálogo de productos</h2>

<?php if (empty($productos)): ?>

<p>No hay productos disponibles.</p>

<?php else: ?>

<?php foreach ($productos as $p): ?>

<div>

<strong><?php echo $p["nombre"]; ?></strong>
(<?php echo $p["precio_base"]; ?> €)

<form action="anadir_producto.php" method="POST">

<input type="hidden" name="productoId" value="<?php echo $p["id"]; ?>">

<input type="number" name="cantidad" value="1" min="1">

<button type="submit">Añadir al carrito</button>

</form>

	</div>

	<hr>

	<?php endforeach; ?>
	<?php endif; ?>
	<a href="carrito.php">Ver carrito</a>

</body>
</html>