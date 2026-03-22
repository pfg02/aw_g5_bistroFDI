<?php
session_start();
// 1. Rutas ajustadas a tu carpeta includes
require_once 'includes/config.php';

if (!isset($_SESSION['rol']) || !tienePermiso('gerente')) {
    die("Acceso denegado.");
}

// 2. Capturar ID de GET (al entrar) o de POST (al guardar)
$id = $_POST['id'] ?? $_GET['id'] ?? null;
$producto = null;
$error_msg = ""; // Variable para el mensaje de error

if ($id) {
    $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");  // Sentencias Preparadas 
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
    $ofertado = $_POST['ofertado'] ?? 1;
    $descripcion = $_POST['descripcion'];

    // --- NUEVA VALIDACIÓN DE STOCK NEGATIVO ---
    if ($stock < 0) {
        $error_msg = "¡Error! El stock no puede ser un número negativo.";
        // Recargamos los datos actuales del producto para no perder el resto del formulario
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $producto = $stmt->get_result()->fetch_assoc();
        }
    } else {
        // Si el stock es correcto (0 o más), ejecutamos la lógica normal

        // Lógica de imágenes
        $imagenes_db = $producto['imagen'] ?? '';

        if (isset($_FILES['fotos']) && !empty(array_filter($_FILES['fotos']['name']))) {
            $nuevas_fotos = [];
            foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['fotos']['error'][$key] == 0) {
                    $nombre_f = time() . "_" . $_FILES['fotos']['name'][$key];
                    // Ajustado a la carpeta img en la raíz
                    if (move_uploaded_file($tmp_name, "img/productos/" . $nombre_f)) {
                        $nuevas_fotos[] = $nombre_f;
                    }
                }
            }
            if (!empty($nuevas_fotos)) {
                $imagenes_db = implode(',', $nuevas_fotos);
            }
        }

        // 3. ACTUALIZAR O CREAR
        if ($id) {
            $sql = "UPDATE productos SET nombre=?, precio_base=?, iva=?, id_categoria=?, stock=?, ofertado=?, descripcion=?, imagen=? WHERE id=?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sdiiiissi", $nombre, $precio, $iva, $cat_id, $stock, $ofertado, $descripcion, $imagenes_db, $id);
        } else {
            $sql = "INSERT INTO productos (nombre, precio_base, iva, id_categoria, stock, ofertado, descripcion, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("sdiiiiss", $nombre, $precio, $iva, $cat_id, $stock, $ofertado, $descripcion, $imagenes_db);
        }

        if ($stmt->execute()) {
            header("Location: gestion_productos.php?msg=guardado");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Añadir' ?> Producto</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<?php include 'includes/nav.php'; ?>

<div class="container" style="max-width: 700px; margin: 20px auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <h1><?= $id ? 'Editar Producto: ' . htmlspecialchars($producto['nombre']) : 'Añadir Nuevo Producto' ?></h1>

    <?php if (!empty($error_msg)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb; margin-bottom: 20px; font-weight: bold;">
            <?= $error_msg ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
    
    <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">

    <label>Nombre del Producto:</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>" required style="width:100%; margin-bottom:15px;">

    <label>Descripción:</label>
    <textarea name="descripcion" style="width:100%; height:80px; margin-bottom:15px;"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>

    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
    <div style="flex: 1;">
    <label>Precio Base (€):</label>
    <input type="number" step="0.01" name="precio_base" id="base" oninput="actualizarPrecio()" value="<?= $producto['precio_base'] ?? '' ?>" required style="width:100%;">
    </div>
    <div style="flex: 1;">
    <label>IVA (%):</label>
    <select name="iva" id="iva" onchange="actualizarPrecio()" style="width:100%;">
    <option value="4" <?= ($producto['iva'] ?? '') == 4 ? 'selected' : '' ?>>4%</option>
    <option value="10" <?= ($producto['iva'] ?? '10') == 10 ? 'selected' : '' ?>>10%</option>
    <option value="21" <?= ($producto['iva'] ?? '') == 21 ? 'selected' : '' ?>>21%</option>
    </select>
    </div>
    </div>

    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #eee;">
    <strong>Precio Final (con IVA): <span id="total_display" style="color: #2c3e50; font-size: 1.2em;">0.00</span> €</strong>
    </div>

    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
    <div style="flex: 1;">
    <label>Categoría:</label>
    <select name="id_categoria" style="width:100%;">
    <?php foreach($categorias as $cat): ?>
    <option value="<?= $cat['id'] ?>" <?= (isset($producto['id_categoria']) && $producto['id_categoria'] == $cat['id']) ? 'selected' : '' ?>>
    <?= htmlspecialchars($cat['nombre']) ?>
    </option>
    <?php endforeach; ?>
    </select>
    </div>
    <div style="flex: 1;">
    <label>Stock Actual:</label>
    <input type="number" name="stock" value="<?= $producto['stock'] ?? 0 ?>" min="0" style="width:100%;">
    </div>
    </div>

    <label>Estado en Carta:</label>
    <select name="ofertado" style="width:100%; margin-bottom: 15px;">
    <option value="1" <?= (!isset($producto['ofertado']) || $producto['ofertado'] == 1) ? 'selected' : '' ?>>✅ Activo (En Carta)</option>
    <option value="0" <?= (isset($producto['ofertado']) && $producto['ofertado'] == 0) ? 'selected' : '' ?>>❌ Retirado (Baja)</option>
    </select>

    <label>Fotos (puedes subir varias):</label>
    <div style="background: #f4f4f4; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 15px;">
    <input type="file" name="fotos[]" accept="image/*"><br><br>
    <input type="file" name="fotos[]" accept="image/*"><br><br>
    <input type="file" name="fotos[]" accept="image/*">
    <p><small>* Si seleccionas archivos nuevos, se reemplazarán los actuales.</small></p>
    </div>

    <?php if (!empty($producto['imagen'])): ?>
    <p><small>Imágenes actuales:</small></p>
    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
    <?php foreach(explode(',', $producto['imagen']) as $img): ?>
    <img src="img/productos/<?= trim($img) ?>" width="80" style="border-radius: 5px; border: 1px solid #ddd;">
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <button type="submit" style="width:100%; background: #28a745; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px;">
    <?= $id ? 'ACTUALIZAR DATOS' : 'CREAR PRODUCTO' ?>
    </button>
    
    <div style="text-align: center; margin-top: 15px;">
    <a href="gestion_productos.php" style="color: #666; text-decoration: none;">← Cancelar y volver a Gestión</a>
    </div>
    </form>
</div>

<script>
function actualizarPrecio() {
    let base = parseFloat(document.getElementById('base').value) || 0;
    let iva = parseFloat(document.getElementById('iva').value) || 0;
    let total = base + (base * iva / 100);
    document.getElementById('total_display').innerText = total.toFixed(2);
}
// Que se ejecute al cargar la página para que muestre el precio inicial calculado
window.onload = actualizarPrecio;
</script>

</body>
</html>
