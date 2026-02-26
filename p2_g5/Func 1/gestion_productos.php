<?php
session_start();
require_once 'config.php';
require_once 'Producto.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    header("Location: login.php");
    exit();
}

// Traemos los productos de la base de datos bistrofdi(g5)
$query = "SELECT p.*, c.nombre AS categoria_nombre 
          FROM productos p 
          LEFT JOIN categorias c ON p.id_categoria = c.id";
$resultado = $db->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #333; color: white; }
        .precio-final { color: #28a745; font-weight: bold; font-size: 1.1em; }
        .iva-detalle { font-size: 0.85em; color: #666; }
    </style>
</head>
<body>
    <h1>Panel de Gestión de Productos</h1>
    <p>Bienvenido, <strong><?= $_SESSION['username'] ?></strong> (<?= $_SESSION['rol'] ?>)</p>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Precio Base</th>
                <th>IVA (%)</th>
                <th>Precio Final</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $resultado->fetch_assoc()): 
                // Instanciamos la clase pasando el string de imágenes para el array
                $p = new Producto(
                    $row['id'], $row['id_categoria'], $row['nombre'], 
                    $row['descripcion'], $row['precio_base'], $row['iva'], 
                    $row['stock'], $row['ofertado'], $row['imagen']
                );
                
                // Calculamos el importe del IVA solo para mostrarlo en el detalle
                $cuotaIva = $p->precio_base * ($p->iva / 100);
            ?>
            <tr>
                <td><?= htmlspecialchars($p->nombre) ?></td>
                <td><?= htmlspecialchars($row['categoria_nombre'] ?? 'Sin categoría') ?></td>
                <td><?= number_format($p->precio_base, 2) ?>€</td>
                <td>
                    <?= $p->iva ?>% 
                    <div class="iva-detalle">(+<?= number_format($cuotaIva, 2) ?>€)</div>
                </td>
                <td class="precio-final">
                    <?= number_format($p->getPrecioFinal(), 2) ?>€
                </td>
                <td><?= $p->stock ?> uds.</td>
                <td>
                    <a href="editar.php?id=<?= $p->id ?>">Editar</a> | 
                    <a href="borrar_producto.php?id=<?= $p->id ?>" style="color:red" onclick="return confirm('¿Baja?')">Baja</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>