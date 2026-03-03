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
    
    $imagen = ($cat) ? $cat['imagen'] : 'default_cat.png';

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $nombre_foto = time() . "_" . basename($_FILES['imagen']['name']);
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], "img/categorias/" . $nombre_foto)) {
            $imagen = $nombre_foto;
        }
    }

    if ($id) {
        $stmt = $db->prepare("UPDATE categorias SET nombre=?, descripcion=?, imagen=? WHERE id=?");
        $stmt->bind_param("sssi", $nombre, $descripcion, $imagen, $id);
    } else {
        $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $descripcion, $imagen);
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
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<?php include 'nav.php'; ?>

<div class="form-container">
    <h2><?= $id ? 'Editar' : 'Nueva' ?> Categoría</h2>
    
    <form method="POST" enctype="multipart/form-data" novalidate>
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($cat['nombre'] ?? '') ?>" required>

        <label>Descripción:</label>
        <textarea name="descripcion" rows="3"><?= htmlspecialchars($cat['descripcion'] ?? '') ?></textarea>

        <label>Imagen de la Categoría:</label>
        <?php if($cat && $cat['imagen']): ?>
            <div style="margin-bottom: 10px;">
                <img src="img/categorias/<?= htmlspecialchars($cat['imagen']) ?>" width="80" style="border-radius: 6px;">
            </div>
        <?php endif; ?>
        <input type="file" name="imagen" accept="image/*">

        <button type="submit" class="btn btn-success" style="margin-top: 20px;">Guardar Categoría</button>
        <p style="text-align:center; margin-top:20px;"><a href="gestion_categorias.php" style="color:#666; text-decoration:none;">← Volver al listado</a></p>
    </form>
</div>

</body>
</html>