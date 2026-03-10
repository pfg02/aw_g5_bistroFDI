/**
 * Vista del carrito de productos.
 * @author Gabriel Omaña
 */

<?php
session_start();

require_once 'includes/config.php';

$carrito = $_SESSION["carrito"] ?? [];

$productos = [];
$total = 0;

if (!empty($carrito)) {

    $ids = implode(",", array_keys($carrito));

    $sql = "SELECT id, nombre, precio FROM productos WHERE id IN ($ids)";

    $result = $db->query($sql);

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
$subtotal = $producto["precio"] * $cantidad;
$total += $subtotal;

?>

<div>

<strong><?php echo $producto["nombre"]; ?></strong><br>

Cantidad: <?php echo $cantidad; ?><br>

Precio: <?php echo $producto["precio"]; ?> €<br>

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