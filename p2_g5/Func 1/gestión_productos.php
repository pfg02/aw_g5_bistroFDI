<?php
require_once 'config.php';
include 'nav.php';
$productos = $conn->query("SELECT p.*, c.nombre as cat_nombre FROM productos p JOIN categorias c ON p.id_categoria = c.id WHERE p.ofertado = 1")->fetchAll();
?>
<h1>Gestión de Productos</h1>
<a href="editar_producto.php">Nuevo Producto</a>
<table border="1">
    <tr><th>Nombre</th><th>Precio Base</th><th>IVA</th><th>Stock</th><th>Acciones</th></tr>
    <?php foreach($productos as $p): ?>
    <tr>
        <td><?= $p['nombre'] ?></td>
        <td><?= $p['precio_base'] ?>€</td>
        <td><?= $p['iva'] ?>%</td>
        <td><?= $p['stock'] ?></td>
        <td><a href="editar_producto.php?id=<?= $p['id'] ?>">Editar</a></td>
    </tr>
    <?php endforeach; ?>
</table>