<?php
session_start();
require_once 'config.php';
require_once 'Producto.php';

// Seguridad: Solo Gerente
if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    header("Location: login.php");
    exit();
}

// Consulta para obtener productos con su categoría
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
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f0f2f5; }
        .container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; margin-top: 20px;}
        th, td { padding: 12px 15px; border-bottom: 1px solid #e4e6eb; text-align: left; vertical-align: middle;}
        th { background: #343a40; color: white; text-transform: uppercase; font-size: 0.85em; }
        
        /* Contenedor fijo para la imagen para evitar temblores */
        .img-container { width: 60px; height: 60px; overflow: hidden; border-radius: 6px; border: 1px solid #ddd; background: #f8f9fa; display: flex; align-items: center; justify-content: center;}
        .img-miniatura { width: 100%; height: 100%; object-fit: cover; }
        
        /* Imagen por defecto si no carga la original (Placeholder) */
        .img-placeholder { width: 100%; height: 100%; background: #e4e6eb; color: #8a8d91; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; text-align: center;}

        .desc-col { max-width: 180px; color: #65676b; font-size: 0.9em; line-height: 1.2; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8em; font-weight: bold; white-space: nowrap;}
        .badge-success { background: #e7f3ff; color: #1877f2; }
        .badge-danger { background: #fde8e8; color: #e41e3f; }
        .precio-total { font-weight: bold; color: #28a745; font-size: 1.05em; }
        .btn-add { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block; transition: 0.3s; }
        tr:hover { background-color: #f8f9fa; }
    </style>
</head>
<body>

<div class="container">
    <h1>Gestión de Productos</h1>
    
    <a href="editar.php" class="btn-add">+ Añadir Nuevo Producto</a>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Imagen</th> <th>Precio Base</th>
                <th>IVA</th>
                <th>Precio Final</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $resultado->fetch_assoc()): 
                $p = new Producto(
                    $row['id'], $row['id_categoria'], $row['nombre'], 
                    $row['descripcion'], $row['precio_base'], $row['iva'], 
                    $row['stock'], $row['ofertado'], $row['imagen']
                );
                
                // Lógica PHP para determinar qué imagen mostrar
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

                <td>
                    <a href="editar.php?id=<?= $p->id ?>">Editar</a> | 
                    <a href="borrar_producto.php?id=<?= $p->id ?>" style="color:#e41e3f" onclick="return confirm('¿Baja?')">Baja</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>