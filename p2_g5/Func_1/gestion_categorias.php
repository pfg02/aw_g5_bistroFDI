<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    header("Location: login.php");
    exit();
}

$query = "SELECT c.*, COUNT(p.id) AS total_productos 
          FROM categorias c 
          LEFT JOIN productos p ON c.id = p.id_categoria 
          GROUP BY c.id";
$resultado = $db->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Categorías - Bistró FDI</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container">
    <h1>Gestión de Categorías</h1>
    
    <a href="editar_categoria.php" class="btn btn-primary" style="margin-bottom: 20px;">+ Añadir Nueva Categoría</a>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'error_fk'): ?>
        <div style="background: #fde8e8; color: #e41e3f; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold;">
            Error: No puedes borrar esta categoría porque tiene productos asignados.
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Icono</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Productos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($cat = $resultado->fetch_assoc()): ?>
            <tr>
                <td>
                    <img src="img/categorias/<?= $cat['imagen'] ?: 'default_cat.png' ?>" 
                         class="img-cat" onerror="this.src='https://via.placeholder.com/50?text=Cat'">
                </td>
                <td><strong><?= htmlspecialchars($cat['nombre']) ?></strong></td>
                <td class="desc-col"><?= htmlspecialchars($cat['descripcion'] ?: 'Sin descripción') ?></td>
                <td><span class="badge badge-success"><?= $cat['total_productos'] ?> ítems</span></td>
                <td class="actions">
                    <a href="editar_categoria.php?id=<?= $cat['id'] ?>" class="edit">Editar</a> |
                    <a href="borrar_categoria.php?id=<?= $cat['id'] ?>" 
                       class="delete" 
                       onclick="return confirm('¿Borrar esta categoría? Solo se podrá si no tiene productos.')">Borrar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>