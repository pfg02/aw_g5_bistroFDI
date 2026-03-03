<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    die("Acceso denegado.");
}

$id = $_GET['id'] ?? null;
$producto = null;

if ($id) {
    $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
}

$res_cats = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $res_cats->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio_base'];
    $iva = $_POST['iva'];
    $cat_id = $_POST['id_categoria'];
    $stock = $_POST['stock'];
    $ofertado = $_POST['ofertado'] ?? 1;
    $descripcion = $_POST['descripcion'];

    if ($id) {
        $stmt = $db->prepare("UPDATE productos SET nombre=?, precio_base=?, iva=?, id_categoria=?, stock=?, ofertado=?, descripcion=? WHERE id=?");
        $stmt->bind_param("sdiiiisi", $nombre, $precio, $iva, $cat_id, $stock, $ofertado, $descripcion, $id);
    } else {
        $stmt = $db->prepare("INSERT INTO productos (nombre, precio_base, iva, id_categoria, stock, ofertado, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiiiis", $nombre, $precio, $iva, $cat_id, $stock, $ofertado, $descripcion);
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
    <link rel="stylesheet" href="estilos.css">
    <script>
        function actualizarPrecio() {
            const base = parseFloat(document.getElementById('base').value) || 0;
            const iva = parseFloat(document.getElementById('iva').value) || 0;
            const total = base + (base * (iva / 100));
            document.getElementById('total_display').innerText = total.toFixed(2) + ' €';
        }
    </script>
</head>
<body onload="actualizarPrecio()">

<?php include 'nav.php'; ?>

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

        <label>Estado en Carta:</label>
        <select name="ofertado">
            <option value="1" <?= ($producto['ofertado'] ?? 1) == 1 ? 'selected' : '' ?>>✅ En Carta</option>
            <option value="0" <?= ($producto['ofertado'] ?? 1) == 0 ? 'selected' : '' ?>>❌ Retirado</option>
        </select>

        <button type="submit" class="btn btn-success">Guardar Producto</button>
        <p style="text-align:center; margin-top:20px;"><a href="gestion_productos.php" style="color:#666; text-decoration:none;">← Volver al listado</a></p>
    </form>
</div>

</body>
</html>