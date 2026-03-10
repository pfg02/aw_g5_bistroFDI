/**
 * Vista del catálogo de productos a agregar al carrito.
 * @author Gabriel Omaña
 */

<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tipo"])) {
    $_SESSION["tipoPedido"] = $_POST["tipo"];
}

// bbtenemos la lista de productos de la base de datos:
$productos = [];

$sql = "SELECT id, nombre, precio FROM productos";

$result = $db->query($sql);

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
(<?php echo $p["precio"]; ?> €)

<form action="anadir_producto.php" method="POST">

<input type="hidden" name="productoId" value="<?php echo $p["id"]; ?>">

<input type="number" name="cantidad" value="1" min="1">

<button type="submit">Añadir al carrito</button>

</form>

</div>

<hr>

<?php endforeach; ?>

<a href="carrito.php">Ver carrito</a>

</body>
</html>