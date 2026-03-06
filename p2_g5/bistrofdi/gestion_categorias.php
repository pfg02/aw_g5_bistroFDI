<?php
// 1. Carga de configuración y sesión
require_once 'includes/config.php';
require_once 'includes/sesion.php';

// 2. Seguridad: Solo el gerente puede gestionar
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Categorías - Bistró FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <?php include 'includes/nav.php'; ?>

    <main class="contenedor-principal">
        <header class="cabecera-seccion">
            <h1>Gestión de Categorías</h1>
            <a href="nueva_categoria.php" class="boton-añadir">Añadir Categoría</a>
        </header>

        <section class="listado-tabla">
            <table class="tabla-categorias">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consulta a la tabla categorias
                    $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
                    $resultado = $db->query($sql);

                    if ($resultado && $resultado->num_rows > 0):
                        while ($cat = $resultado->fetch_assoc()):
                            // Ruta de la imagen corregida para la raíz
                            $nombre_foto = !empty($cat['imagen']) ? $cat['imagen'] : 'default.jpg';
                            $ruta_foto = "img/categorias/" . $nombre_foto;
                    ?>
                    <tr>
                        <td class="col-foto">
                            <img src="<?php echo $ruta_foto; ?>" 
                                 alt="<?php echo htmlspecialchars($cat['nombre']); ?>" 
                                 class="img-miniatura">
                        </td>
                        <td class="nombre-destacado">
                            <strong><?php echo htmlspecialchars($cat['nombre']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($cat['descripcion']); ?></td>
                        <td class="acciones">
                            <a href="editar_categoria.php?id=<?php echo $cat['id']; ?>" class="btn-edit">Editar</a>
                            <a href="eliminar_categoria.php?id=<?php echo $cat['id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('¿Seguro?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="4">No hay categorías.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>