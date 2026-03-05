<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    header("Location: login.php");
    exit();
}

// Traemos las categorías (mantenemos el count por si lo necesitas en el futuro, pero no se muestra)
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
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container">
    <h1>Gestión de Categorías</h1>
    
    <p class="instrucciones">Haz clic en el nombre o icono para ver los productos de esa categoría.</p>
    
    <a href="editar_categoria.php" class="btn btn-primary" style="margin-bottom: 20px;">+ Añadir Nueva Categoría</a>

    <table>
        <thead>
            <tr>
                <th>Icono</th>
                <th>Nombre de Categoría</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($cat = $resultado->fetch_assoc()): 
                // Extraemos solo la primera imagen para el icono
                $fotos = !empty($cat['imagen']) ? explode(',', $cat['imagen']) : [];
                $icono = !empty($fotos) ? trim($fotos[0]) : 'default_cat.png';
            ?>
            <tr>
                <td style="width: 60px; text-align: center;">
                    <a href="gestion_productos.php?cat_id=<?= $cat['id'] ?>">
                        <img src="../img/categorias/<?= $icono ?>" 
                             style="width: 45px; height: 45px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;"
                             onerror="this.src='https://via.placeholder.com/45?text=Cat'">
                    </a>
                </td>
                <td>
                    <a href="gestion_productos.php?cat_id=<?= $cat['id'] ?>" style="text-decoration: none; color: #2c3e50; font-weight: bold;">
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </a>
                </td>
                <td class="desc-col">
                    <small><?= htmlspecialchars($cat['descripcion'] ?: 'Sin descripción') ?></small>
                </td>
                <td class="actions">
                    <a href="editar_categoria.php?id=<?= $cat['id'] ?>" class="edit">Editar</a> |
                    <a href="borrar_categoria.php?id=<?= $cat['id'] ?>" class="delete" onclick="return confirm('¿Borrar categoría?')">Borrar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>