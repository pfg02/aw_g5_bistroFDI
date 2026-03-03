<?php
session_start();
require_once 'config.php';
require_once 'Producto.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    header("Location: login.php");
    exit();
}

$query = "SELECT p.*, c.nombre AS categoria_nombre 
          FROM productos p 
          LEFT JOIN categorias c ON p.id_categoria = c.id";
$resultado = $db->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos - Bistró FDI</title>
    <link rel="stylesheet" href="estilos.css"> </head>
<body>

<?php include 'nav.php'; ?>

<div class="container">
    <h1>Gestión de Productos</h1>
    
    <a href="editar.php" class="btn btn-primary">+ Añadir Nuevo Producto</a>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Imagen</th>
                <th>Precio Base</th>
                <th>IVA</th>
                <th>Precio Final</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $resultado->fetch_assoc()): 
                $p = new Producto($row['id'], $row['id_categoria'], $row['nombre'], $row['descripcion'], $row['precio_base'], $row['iva'], $row['stock'], $row['ofertado'], $row['imagen']);
                $fotoNombre = !empty($p->imagenes) ? $p->imagenes[0] : null;
                $rutaFoto = "img/productos/" . $fotoNombre;
                $mostrarFoto = ($fotoNombre && file_exists($rutaFoto));
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($p->nombre) ?></strong></td>
                <td class="desc-col"><?= htmlspecialchars($p->descripcion ?? '---') ?></td>
                <td><?= htmlspecialchars($row['categoria_nombre'] ?? 'General') ?></td>
                <td>
                    <div class="img-container">
                        <?php if($mostrarFoto): ?>
                            <img src="<?= htmlspecialchars($rutaFoto) ?>" alt="Plato" class="img-miniatura">
                        <?php else: ?>
                            <div class="img-placeholder">NO FOTO</div>
                        <?php endif; ?>
                    </div>
                </td>
                <td><?= number_format($p->precio_base, 2) ?>€</td>
                <td><?= $p->iva ?>%</td>
                <td class="precio-total"><?= number_format($p->getPrecioFinal(), 2) ?>€</td>
                <td><?= $p->stock ?></td>
                
                <td>
                    <?php if($p->ofertado == 1): ?>
                        <span class="badge badge-success">● EN CARTA</span>
                    <?php else: ?>
                        <span class="badge badge-danger">● RETIRADO</span>
                    <?php endif; ?>
                </td>

                <td class="actions">
                    <?php if($p->ofertado == 1): ?>
                        <a href="editar.php?id=<?= $p->id ?>" class="edit">Editar</a> | 
                        <a href="borrar_producto.php?id=<?= $p->id ?>" class="delete" onclick="return confirm('¿Retirar de la carta?')">Baja</a>
                    <?php else: ?>
                        <a href="editar.php?id=<?= $p->id ?>" class="reactivate">Reactivar / Editar</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>