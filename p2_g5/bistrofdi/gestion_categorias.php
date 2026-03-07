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
<body class="body-gestion">

    <?php include 'includes/nav.php'; ?>

    <main class="main-content">
        <header class="header-gestion">
            <h1>Gestión de Categorías</h1>
            <a href="nueva_categoria.php" class="btn-anadir">+ Añadir Categoría</a>
        </header>

        <section class="contenedor-tabla-profesional">
            <table class="tabla-gestion">
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
                    $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
                    $resultado = $db->query($sql);

                    if ($resultado && $resultado->num_rows > 0):
                        while ($cat = $resultado->fetch_assoc()):
                            
                            // VALIDACIÓN DE IMAGEN
                            $nombre_foto = $cat['imagen'];
                            $ruta_fisica = "img/categorias/" . $nombre_foto;
                            $foto_valida = false;

                            // Comprobamos que el campo no esté vacío y que el archivo exista en la carpeta
                            if (!empty($nombre_foto) && file_exists($ruta_fisica)) {
                                $foto_valida = true;
                            }
                    ?>
                    <tr>
                        <td class="col-foto">
                            <?php if ($foto_valida): ?>
                                <img src="<?php echo $ruta_fisica; ?>" 
                                     alt="<?php echo htmlspecialchars($cat['nombre']); ?>" 
                                     class="img-tabla-pequena">
                            <?php else: ?>
                                <span class="sin-foto-texto">Sin foto</span>
                            <?php endif; ?>
                        </td>
                        <td class="nombre-destacado">
                            <strong><?php echo htmlspecialchars($cat['nombre']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($cat['descripcion']); ?></td>
                        <td class="acciones">
                            <a href="editar_categoria.php?id=<?php echo $cat['id']; ?>" class="btn-editar">Editar</a>
                            <a href="eliminar_categoria.php?id=<?php echo $cat['id']; ?>" 
                               class="btn-borrar" 
                               onclick="return confirm('¿Seguro que deseas eliminar esta categoría?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 20px;">No hay categorías registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>