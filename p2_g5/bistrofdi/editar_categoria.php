<?php
session_start();
require_once 'includes/config.php';

// 1. Seguridad: Solo gerente
if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    die("Acceso denegado.");
}

// 2. CAPTURAR EL ID DE FORMA SEGURA (Evita el id=0)
$id = $_POST['id'] ?? $_GET['id'] ?? null;
if (empty($id)) {
    $id = null;
}

$categoria = null;

// 3. Cargar datos si es edición
if ($id) {
    $stmt = $db->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $categoria = $stmt->get_result()->fetch_assoc();
}

// 4. Procesar formulario al guardar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    // Lógica de imagen (Mantenemos la actual por defecto)
    $imagen_db = $categoria['imagen'] ?? 'default_cat.png';

    // Si el usuario sube una nueva imagen para la categoría
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $nombre_archivo = time() . "_" . basename($_FILES['foto']['name']);
        // Guardamos en la carpeta img/categorias/
        $ruta_destino = "img/categorias/" . $nombre_archivo;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
            $imagen_db = $nombre_archivo;
        }
    }

    if ($id) {
        // ACTUALIZAR CATEGORÍA EXISTENTE
        $stmt = $db->prepare("UPDATE categorias SET nombre=?, descripcion=?, imagen=? WHERE id=?");
        $stmt->bind_param("sssi", $nombre, $descripcion, $imagen_db, $id);
    } else {
        // CREAR NUEVA CATEGORÍA
        $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $descripcion, $imagen_db);
    }

    if ($stmt->execute()) {
        header("Location: gestion_categorias.php?msg=guardado");
        exit();
    } else {
        die("Error al guardar: " . $db->error);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Nueva' ?> Categoría</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<?php include 'includes/nav.php'; ?>

<div class="form-container" style="max-width: 500px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h2 style="text-align: center; color: #333; margin-bottom: 20px;">
        <?= $id ? 'Editar Categoría: ' . htmlspecialchars($categoria['nombre']) : 'Añadir Nueva Categoría' ?>
    </h2>
    
    <form method="POST" enctype="multipart/form-data">
        
        <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">

        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Nombre de la Categoría:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">

        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Descripción:</label>
        <textarea name="descripcion" style="width: 100%; height: 80px; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>

        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Imagen de Portada:</label>
        <div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
            <input type="file" name="foto" accept="image/*" style="width: 100%;">
            <p><small style="color: #666;">* Si seleccionas un archivo, reemplazará la imagen actual.</small></p>
        </div>

        <?php if (!empty($categoria['imagen'])): ?>
            <div style="margin-bottom: 20px; text-align: center;">
                <p style="margin-bottom: 5px; font-weight: bold; font-size: 14px;">Imagen actual:</p>
                <img src="img/categorias/<?= htmlspecialchars($categoria['imagen']) ?>" style="max-width: 150px; border-radius: 5px; border: 1px solid #ccc; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            </div>
        <?php endif; ?>

        <button type="submit" style="width: 100%; background: #28a745; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold;">
            <?= $id ? 'GUARDAR CAMBIOS' : 'CREAR CATEGORÍA' ?>
        </button>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="gestion_categorias.php" style="color: #6c757d; text-decoration: none; font-size: 14px;">← Cancelar y volver</a>
        </div>
    </form>
</div>

</body>
</html>