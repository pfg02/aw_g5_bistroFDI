<?php
session_start();
require_once 'config.php';
require_once 'Producto.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    header("Location: login.php");
    exit();
}

// Consulta para obtener productos y el nombre de su categoría
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
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container">
    <h1>Gestión de Productos</h1>

    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'eliminado'): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-weight: bold;">
                Producto eliminado definitivamente de la base de datos.
            </div>
        <?php elseif($_GET['msg'] == 'error_fk'): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-weight: bold;">
                Error: No se puede eliminar porque este producto está asociado a pedidos históricos. Usa la opción "Baja".
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <a href="editar_producto.php" class="btn btn-primary" style="margin-bottom: 20px;">+ Añadir Nuevo Producto</a>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Imágenes Asociadas</th> 
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
                // Adaptamos la cadena de la DB al array que espera tu clase Producto
                $listaImagenes = !empty($row['imagen']) ? explode(',', $row['imagen']) : [];
                
                // Instanciamos el objeto con los datos de la fila
                $p = new Producto(
                    $row['id'], 
                    $row['id_categoria'], 
                    $row['nombre'], 
                    $row['descripcion'], 
                    $row['precio_base'], 
                    $row['iva'], 
                    $row['stock'], 
                    $row['ofertado'], 
                    $listaImagenes
                );
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($p->nombre) ?></strong></td>
                <td class="desc-col"><?= htmlspecialchars($p->descripcion ?? '---') ?></td>
                <td><?= htmlspecialchars($row['categoria_nombre'] ?? 'General') ?></td>
                
                <td>
                    <div style="display: flex; gap: 5px; flex-wrap: wrap; width: 100px;">
                        <?php if(!empty($p->imagenes)): ?>
                            <?php foreach($p->imagenes as $img): 
                                $ruta = "../img/productos/" . trim($img);
                            ?>
                                <img src="<?= $ruta ?>" 
                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;"
                                     onerror="this.src='https://via.placeholder.com/40?text=Error'">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <small style="color: gray;">Sin fotos</small>
                        <?php endif; ?>
                    </div>
                </td>

                <td><?= number_format($p->precio_base, 2) ?>€</td>
                <td><?= $p->iva ?>%</td>
                <td class="precio-total"><strong><?= number_format($p->getPrecioFinal(), 2) ?>€</strong></td>
                <td><?= $p->stock ?></td>
                
                <td>
                    <?php if($p->ofertado == 1): ?>
                        <span class="badge badge-success">● EN CARTA</span>
                    <?php else: ?>
                        <span class="badge badge-danger">● RETIRADO</span>
                    <?php endif; ?>
                </td>

                <td class="actions">
                    <a href="editar_producto.php?id=<?= $p->id ?>" class="edit">Editar</a> | 
                    
                    <?php if($p->ofertado == 1): ?>
                        <a href="borrar_producto.php?id=<?= $p->id ?>" class="delete" onclick="return confirm('¿Retirar de la carta? (Solo se dará de baja)')">Baja</a> | 
                    <?php else: ?>
                        <span style="color: #888;">Inactivo</span> | 
                    <?php endif; ?>
                    
                    <a href="eliminar_producto.php?id=<?= $p->id ?>" style="color: #e74c3c; font-weight: bold;" onclick="return confirm('¡ADVERTENCIA! Vas a borrar este producto para siempre de la base de datos. ¿Continuar?')">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>