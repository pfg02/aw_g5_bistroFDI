<?php
require_once 'config.php';
include 'nav.php';
$categorias = $conn->query("SELECT * FROM categorias")->fetchAll();
?>
<h1>Listado de Categorías</h1>
<a href="editar_categoria.php">Añadir Categoría</a>
<table>
    <?php foreach($categorias as $cat): ?>
    <tr>
        <td><?= $cat['nombre'] ?></td>
        <td><a href="editar_categoria.php?id=<?= $cat['id'] ?>">Editar</a></td>
    </tr>
    <?php endforeach; ?>
</table>