<?php
session_start();
require_once 'config.php';

if (!tienePermiso('gerente')) die("No autorizado");

$id = $_GET['id'] ?? null;
$producto = null;

if ($id) {
    $res = $db->query("SELECT * FROM productos WHERE id = " . intval($id));
    $producto = $res->fetch_assoc();
}

$cats = $db->query("SELECT id, nombre FROM categorias");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio_base'];
    $iva = $_POST['iva'];
    $cat_id = $_POST['id_categoria'];
    $stock = $_POST['stock'];
    $ofertado = $_POST['ofertado'];

    // Lógica para MÚLTIPLES imágenes
    $nombres_imagenes = $producto['imagen'] ?? ''; // Mantenemos las fotos viejas por defecto
    
    if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
        $subidas = [];
        foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
            $nombre_img = time() . "_" . $_FILES['fotos']['name'][$key];
            if (move_uploaded_file($tmp_name, "img/productos/" . $nombre_img)) {
                $subidas[] = $nombre_img;
            }
        }
        if (!empty($subidas)) {
            $nombres_imagenes = implode(',', $subidas); // Guardamos como "img1,img2"
        }
    }

    if ($id) {
        $stmt = $db->prepare("UPDATE productos SET nombre=?, precio_base=?, iva=?, id_categoria=?, stock=?, ofertado=?, imagen=? WHERE id=?");
        $stmt->bind_param("sdiiiisi", $nombre, $precio, $iva, $cat_id, $stock, $ofertado, $nombres_imagenes, $id);
    } else {
        $stmt = $db->prepare("INSERT INTO productos (nombre, precio_base, iva, id_categoria, stock, ofertado, imagen) VALUES (?, ?, ?, ?, ?, 1, ?)");
        $stmt->bind_param("sdiiis", $nombre, $precio, $iva, $cat_id, $stock, $nombres_imagenes);
    }
    
    $stmt->execute();
    header("Location: gestion_productos.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editor de Producto</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .form-box { max-width: 500px; margin: auto; border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        input, select { width: 100%; margin-bottom: 15px; padding: 8px; box-sizing: border-box; }
        .btn { background: #007bff; color: white; border: none; padding: 10px; width: 100%; cursor: pointer; }
    </style>
</head>
<body>
    <div class="form-box">
        <h1><?= $id ? "Editar" : "Nuevo" ?> Producto</h1>
        <form method="POST" enctype="multipart/form-data">
            Nombre: <input type="text" name="nombre" value="<?= $producto['nombre'] ?? '' ?>" required>
            
            Categoría:
            <select name="id_categoria">
                <?php while($c = $cats->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= ($producto['id_categoria'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= $c['nombre'] ?></option>
                <?php endwhile; ?>
            </select>

            Precio Base: <input type="number" step="0.01" name="precio_base" value="<?= $producto['precio_base'] ?? '' ?>">
            
            IVA: 
            <select name="iva">
                <option value="4" <?= ($producto['iva'] ?? '') == 4 ? 'selected' : '' ?>>4%</option>
                <option value="10" <?= ($producto['iva'] ?? '') == 10 ? 'selected' : '' ?>>10% (Normal)</option>
                <option value="21" <?= ($producto['iva'] ?? '') == 21 ? 'selected' : '' ?>>21%</option>
            </select>

            Stock: <input type="number" name="stock" value="<?= $producto['stock'] ?? 0 ?>">

            Estado:
            <select name="ofertado">
                <option value="1" <?= ($producto['ofertado'] ?? 1) == 1 ? 'selected' : '' ?>>En Carta</option>
                <option value="0" <?= ($producto['ofertado'] ?? 1) == 0 ? 'selected' : '' ?>>Retirado</option>
            </select>

            Imágenes (puedes seleccionar varias):
            <input type="file" name="fotos[]" multiple accept="image/*">

            <button type="submit" class="btn">Guardar Producto</button>
        </form>
    </div>
</body>
</html>