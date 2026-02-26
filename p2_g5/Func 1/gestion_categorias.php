<?php
session_start();
require_once 'config.php';
include 'nav.php'; 

// Seguridad: Solo el gerente accede a la gestión
if (!tienePermiso('gerente')) {
    die("Acceso denegado. Se requiere rol de Gerente.");
}

// Obtenemos todas las categorías usando la conexión $db
$res = $db->query("SELECT * FROM categorias");
$categorias = $res->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Categorías - Bistró FDI</title>
    <style>
        body { font-family: sans-serif; margin: 30px; background-color: #f8f9fa; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
        th { background-color: #343a40; color: white; }
        .img-cat { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .btn-add { background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-edit { color: #007bff; text-decoration: none; font-weight: bold; }
        .btn-delete { color: #dc3545; text-decoration: none; font-weight: bold; }
        tr:hover { background-color: #f1f1f1; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Categorías</h1>
        <p>Administra las secciones de la carta: Nombre, Descripción e Imágenes.</p>
        
        <div style="margin: 20px 0;">
            <a href="editar_categoria.php" class="btn-add">+ Añadir Nueva Categoría</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                    <tr><td colspan="4" style="text-align:center;">No hay categorías registradas.</td></tr>
                <?php else: ?>
                    <?php foreach($categorias as $cat): ?>
                    <tr>
                        <td>
                            <img src="img/categorias/<?= htmlspecialchars($cat['imagen'] ?? 'default.png') ?>" 
                                 alt="Icono" class="img-cat" 
                                 onerror="this.src='https://via.placeholder.com/60?text=No+Img'">
                        </td>
                        <td><strong><?= htmlspecialchars($cat['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($cat['descripcion'] ?? 'Sin descripción') ?></td>
                        <td>
                            <a href="editar_categoria.php?id=<?= $cat['id'] ?>" class="btn-edit">Editar</a> | 
                            <a href="borrar_categoria.php?id=<?= $cat['id'] ?>" 
                               class="btn-delete" 
                               onclick="return confirm('¿Estás seguro de eliminar la categoría <?= htmlspecialchars($cat['nombre']) ?>?')">
                               Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            <a href="gestion_productos.php">← Volver a Gestión de Productos</a>
        </div>
    </div>
</body>
</html>