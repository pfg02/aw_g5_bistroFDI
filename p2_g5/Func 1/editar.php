<?php
session_start();
require_once 'config.php';

// Seguridad: Solo Gerente
if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    die("Acceso denegado.");
}

$id = $_GET['id'] ?? null;
$producto = null;

// 1. Cargar datos del producto si es Edición (Vista 3)
if ($id) {
    $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
}

// 2. Cargar categorías para el Selector (USABILIDAD)
$res_cats = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $res_cats->fetch_all(MYSQLI_ASSOC);

// 3. Procesar Formulario (Crear o Actualizar)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio_base'];
    $iva = $_POST['iva'];
    $cat_id = $_POST['id_categoria'];
    $stock = $_POST['stock'];
    $ofertado = $_POST['ofertado'] ?? 1;
    $descripcion = $_POST['descripcion'];

    // Mantener imagen actual o procesar nuevas (Lógica simplificada)
    $imagen = $producto['imagen'] ?? ''; 

    if ($id) {
        $stmt = $db->prepare("UPDATE productos SET nombre=?, precio_base=?, iva=?, id_categoria=?, stock=?, ofertado=?, descripcion=? WHERE id=?");
        $stmt->bind_param("sdiiiisi", $nombre, $precio, $iva, $cat_id, $stock, $ofertado, $descripcion, $id);
    } else {
        $stmt = $db->prepare("INSERT INTO productos (nombre, precio_base, iva, id_categoria, stock, ofertado, descripcion) VALUES (?, ?, ?, ?, ?, 1, ?)");
        $stmt->bind_param("sdiiis", $nombre, $precio, $iva, $cat_id, $stock, $descripcion);
    }
    
    if ($stmt->execute()) {
        header("Location: gestion_productos.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Nuevo' ?> Producto</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 40px; }
        .form-container { max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-top: 0; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #555; }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .precio-final-card { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-top: 20px; border-left: 5px solid #1877f2; }
        .btn-save { background: #28a745; color: white; border: none; padding: 12px; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 20px; }
        .btn-save:hover { background: #218838; }
    </style>

    <script>
        // Visualización y actualización automática del precio final
        function actualizarPrecio() {
            const base = parseFloat(document.getElementById('base').value) || 0;
            const iva = parseFloat(document.getElementById('iva').value) || 0;
            const total = base + (base * (iva / 100));
            document.getElementById('total_display').innerText = total.toFixed(2) + ' €';
        }
    </script>
</head>
<body onload="actualizarPrecio()">

<div class="form-container">
    <h2><?= $id ? 'Editar Producto' : 'Crear Nuevo Producto' ?></h2>
    
    <form method="POST">
        <label>Nombre del Producto:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>" required>

        <label>Descripción:</label>
        <textarea name="descripcion" rows="3"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>

        <label>Categoría:</label>
        <select name="id_categoria" required>
            <option value="">-- Selecciona una categoría --</option>
            <?php foreach($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($producto['id_categoria'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Precio Base (€):</label>
        <input type="number" step="0.01" name="precio_base" id="base" oninput="actualizarPrecio()" value="<?= $producto['precio_base'] ?? '' ?>" required>

        <label>IVA (%):</label>
        <select name="iva" id="iva" onchange="actualizarPrecio()">
            <option value="4" <?= ($producto['iva'] ?? '') == 4 ? 'selected' : '' ?>>4% (Superreducido)</option>
            <option value="10" <?= ($producto['iva'] ?? '10') == 10 ? 'selected' : '' ?>>10% (Reducido)</option>
            <option value="21" <?= ($producto['iva'] ?? '') == 21 ? 'selected' : '' ?>>21% (General)</option>
        </select>

        <div class="precio-final-card">
            <strong>Precio Final (Base + IVA): <span id="total_display">0.00 €</span></strong>
        </div>

        <label>Stock inicial:</label>
        <input type="number" name="stock" value="<?= $producto['stock'] ?? 0 ?>">

        <button type="submit" class="btn-save">Guardar Producto</button>
        <p style="text-align:center;"><a href="gestion_productos.php" style="color:#666; text-decoration:none;">← Volver al listado</a></p>
    </form>
</div>

</body>
</html>