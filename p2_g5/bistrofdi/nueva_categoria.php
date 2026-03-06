<?php
// 1. Carga de configuración y sesión
require_once 'includes/config.php';
require_once 'includes/sesion.php';

// 2. Seguridad: Solo el gerente entra aquí
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header("Location: index.php");
    exit();
}

// 3. Lógica para insertar la categoría si se ha enviado el formulario
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $nombre_imagen = "default.jpg"; // Por defecto

    // Manejo de la subida de imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $ruta_destino = "img/categorias/";
        $nombre_archivo = time() . "_" . $_FILES['imagen']['name']; // Nombre único
        
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino . $nombre_archivo)) {
            $nombre_imagen = $nombre_archivo;
        }
    }

    // Insertar en la base de datos
    $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $descripcion, $nombre_imagen);

    if ($stmt->execute()) {
        header("Location: gestion_categorias.php");
        exit();
    } else {
        $mensaje = "Error al crear la categoría: " . $db->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Nueva Categoría - Bistró FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <?php include 'includes/nav.php'; ?>

    <main class="contenedor-principal">
        <header class="cabecera-seccion">
            <h1>Nueva Categoría</h1>
        </header>

        <section class="formulario-edicion">
            <?php if ($mensaje): ?>
                <p class="error"><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <form action="nueva_categoria.php" method="POST" enctype="multipart/form-data">
                
                <div class="campo">
                    <label>Nombre de la Categoría:</label>
                    <input type="text" name="nombre" placeholder="Ej: Postres, Bebidas..." required>
                </div>

                <div class="campo">
                    <label>Descripción:</label>
                    <textarea name="descripcion" placeholder="Breve descripción de la categoría..."></textarea>
                </div>

                <div class="campo">
                    <label>Imagen de la Categoría:</label>
                    <input type="file" name="imagen" accept="image/*">
                </div>

                <div class="botones-form">
                    <button type="submit" class="btn-guardar">Crear Categoría</button>
                    <a href="gestion_categorias.php" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

</body>
</html>