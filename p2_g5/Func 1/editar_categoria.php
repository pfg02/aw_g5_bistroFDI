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
    
    // Si no hay foto nueva, mantenemos la anterior
    $imagen = ($cat) ? $cat['imagen'] : 'default_cat.png';

    // Solo procesamos si el archivo se subió correctamente
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
<html>
<head>
    <meta charset="UTF-8">
    <title>Editor</title>
    <style>
        body { font-family: sans-serif; padding: 30px; }
        .form-container { max-width: 400px; margin: auto; padding: 20px; border: 1px solid #ccc; }
        input, textarea { width: 100%; margin: 10px 0; display: block; }
        .btn { background: #28a745; color: white; padding: 10px; border: none; width: 100%; cursor: pointer; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2><?= $id ? 'Editar' : 'Nueva' ?> Categoría</h2>
        
        <form method="POST" enctype="multipart/form-data" novalidate>
            
            Nombre:
            <input type="text" name="nombre" value="<?= htmlspecialchars($cat['nombre'] ?? '') ?>" required>

            Descripción:
            <textarea name="descripcion"><?= htmlspecialchars($cat['descripcion'] ?? '') ?></textarea>

            Imagen (Puedes dejarlo vacío):
            <input type="file" name="imagen">
            
            <button type="submit" class="btn">GUARDAR</button>
            <p><a href="gestion_categorias.php">Cancelar</a></p>
        </form>
    </div>
</body>
</html>