<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    die("Acceso denegado.");
}

$id = $_GET['id'] ?? null;
$cat = null;

if ($id) {
    $stmt = $db->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $cat = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    
    // Mantenemos las imágenes actuales por defecto
    $imagenes_finales = ($cat) ? $cat['imagen'] : 'default_cat.png';

    // Comprobamos si se han subido archivos nuevos
    if (isset($_FILES['imagenes'])) {
        $nombres_archivos = [];
        
        // Recorremos los 3 campos de archivo
        foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
            // Solo procesamos si no hay error en ese campo concreto
            if ($_FILES['imagenes']['error'][$key] === 0) {
                $nombre_original = basename($_FILES['imagenes']['name'][$key]);
                $nombre_foto = time() . "_" . $key . "_" . $nombre_original;
                $ruta_destino = "../img/categorias/" . $nombre_foto;

                if (move_uploaded_file($tmp_name, $ruta_destino)) {
                    $nombres_archivos[] = $nombre_foto;
                }
            }
        }

        // Si se subieron fotos nuevas, las unimos por comas
        if (!empty($nombres_archivos)) {
            $imagenes_finales = implode(',', $nombres_archivos);
        }
    }

    if ($id) {
        $stmt = $db->prepare("UPDATE categorias SET nombre=?, descripcion=?, imagen=? WHERE id=?");
        $stmt->bind_param("sssi", $nombre, $descripcion, $imagenes_finales, $id);
    } else {
        $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $descripcion, $imagenes_finales);
    }
    
    if ($stmt->execute()) {
        header("Location: gestion_categorias.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Nueva' ?> Categoría - Bistró FDI</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<?php include 'nav.php'; ?>

<div class="form-container">
    <h2><?= $id ? 'Editar' : 'Nueva' ?> Categoría</h2>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($cat['nombre'] ?? '') ?>" required>

        <label>Descripción:</label>
        <textarea name="descripcion" rows="3"><?= htmlspecialchars($cat['descripcion'] ?? '') ?></textarea>

        <label>Imágenes de la Categoría (Sube una o varias foto a foto):</label>
        <div style="background: #f4f4f4; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 15px;">
            <input type="file" name="imagenes[]" accept="image/*"><br><br>
            <input type="file" name="imagenes[]" accept="image/*"><br><br>
            <input type="file" name="imagenes[]" accept="image/*">
            <p><small>* Si seleccionas archivos nuevos, se reemplazarán los actuales.</small></p>
        </div>

        <?php if($cat && $cat['imagen']): ?>
            <label>Imágenes actuales:</label>
            <div style="margin-bottom: 10px; display: flex; gap: 10px; flex-wrap: wrap;">
                <?php 
                $lista_img = explode(',', $cat['imagen']);
                foreach($lista_img as $img): ?>
                    <img src="../img/categorias/<?= htmlspecialchars(trim($img)) ?>" width="80" style="border-radius: 6px; border: 1px solid #ccc;">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary"><?= $id ? 'Actualizar' : 'Crear' ?> Categoría</button>
            <a href="gestion_categorias.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

</body>
</html>