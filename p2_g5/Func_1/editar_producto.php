<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    die("Acceso denegado.");
}

$id = $_GET['id'] ?? null;
$producto = null;

// 1. Cargar datos si es edición
if ($id) {
    $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
}

// Obtener categorías para el desplegable
$res_cats = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $res_cats->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio_base'];
    $iva = $_POST['iva'];
    $cat_id = $_POST['id_categoria'];
    $stock = $_POST['stock'];
    $ofertado = $_POST['ofertado'] ?? 1; // Aquí se recoge el nuevo campo
    $descripcion = $_POST['descripcion'];

    // --- LÓGICA DE SUBIDA DE IMÁGENES ---
    // Si estamos editando, mantenemos las fotos que ya existen por defecto
    $imagenes_db = $producto['imagen'] ?? ''; 
    $fotos_subidas = [];

    // Revisamos los 3 inputs de archivos (vienen como un array en $_FILES['fotos'])
    if (isset($_FILES['fotos'])) {
        foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['fotos']['error'][$key] == 0) {
                $nombre_archivo = time() . "_" . $key . "_" . basename($_FILES['fotos']['name'][$key]);
                $ruta_destino = "../img/productos/" . $nombre_archivo;

                if (move_uploaded_file($tmp_name, $ruta_destino)) {
                    $fotos_subidas[] = $nombre_archivo;
                }
            }
        }
    }

    // Si se subieron fotos nuevas, las guardamos separadas por comas. 
    // Si no, mantenemos las antiguas.
    if (!empty($fotos_subidas)) {
        $imagenes_db = implode(',', $fotos_subidas);
    }

    if ($id) {
        // UPDATE (Ya incluía la variable $ofertado)
        $stmt = $db->prepare("UPDATE productos SET nombre=?, precio_base=?, iva=?, id_categoria=?, stock=?, ofertado=?, descripcion=?, imagen=? WHERE id=?");
        $stmt->bind_param("sdiiiissi", $nombre, $precio, $iva, $cat_id, $stock, $ofertado, $descripcion, $imagenes_db, $id);
    } else {
        // INSERT (Ya incluía la variable $ofertado)
        $stmt = $db->prepare("INSERT INTO productos (nombre, precio_base, iva, id_categoria, stock, ofertado, descripcion, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiiiiss", $nombre, $precio, $iva, $cat_id, $stock, $ofertado, $descripcion, $imagenes_db);
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
    <link rel="stylesheet" href="../css/estilos.css">
    <script>
        function actualizarPrecio() {
            let base = parseFloat(document.getElementById('base').value) || 0;
            let iva = parseFloat(document.getElementById('iva').value) || 0;
            let total = base + (base * (iva / 100));
            document.getElementById('total_display').innerText = total.toFixed(2) + " €";
        }
    </script>
</head>
<body>
<?php include 'nav.php'; ?>

<div class="form-container">
    <h2><?= $id ? 'Editar' : 'Nuevo' ?> Producto</h2>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>" required>

        <label>Categoría:</label>
        <select name="id_categoria">
            <?php foreach($categorias as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($producto['id_categoria'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Descripción:</label>
        <textarea name="descripcion"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>

        <label>Precio Base (€):</label>
        <input type="number" step="0.01" name="precio_base" id="base" oninput="actualizarPrecio()" value="<?= $producto['precio_base'] ?? '' ?>" required>

        <label>IVA (%):</label>
        <select name="iva" id="iva" onchange="actualizarPrecio()">
            <option value="4" <?= ($producto['iva'] ?? '') == 4 ? 'selected' : '' ?>>4%</option>
            <option value="10" <?= ($producto['iva'] ?? '10') == 10 ? 'selected' : '' ?>>10%</option>
            <option value="21" <?= ($producto['iva'] ?? '') == 21 ? 'selected' : '' ?>>21%</option>
        </select>

        <p><strong>Total con IVA: <span id="total_display">0.00 €</span></strong></p>

        <label>Stock:</label>
        <input type="number" name="stock" value="<?= $producto['stock'] ?? 0 ?>">

        <label>Estado en la Carta:</label>
        <select name="ofertado" style="margin-bottom: 15px;">
            <option value="1" <?= (!isset($producto['ofertado']) || $producto['ofertado'] == 1) ? 'selected' : '' ?>>✅ Activo (En Carta)</option>
            <option value="0" <?= (isset($producto['ofertado']) && $producto['ofertado'] == 0) ? 'selected' : '' ?>>❌ Retirado (Dado de Baja)</option>
        </select>

        <label>Fotos del producto (Sube una o más sin usar Ctrl):</label>
        <div style="background: #f4f4f4; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
            <input type="file" name="fotos[]" accept="image/*"><br><br>
            <input type="file" name="fotos[]" accept="image/*"><br><br>
            <input type="file" name="fotos[]" accept="image/*">
            <p><small>* Si seleccionas archivos nuevos, se reemplazarán los actuales.</small></p>
        </div>

        <?php if (!empty($producto['imagen'])): ?>
            <p>Fotos actuales:</p>
            <div style="display: flex; gap: 10px;">
                <?php foreach(explode(',', $producto['imagen']) as $img): ?>
                    <img src="../img/productos/<?= $img ?>" width="80" style="border-radius: 5px;">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn-primary">Guardar Producto</button>
            <a href="gestion_productos.php" class="btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>actualizarPrecio();</script>
</body>
</html>