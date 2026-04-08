<?php
require_once __DIR__ . '/includes/sesion.php';
exigirLogin();

$conn = obtenerConexionBD();
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT p.*, c.nombre AS categoria_nombre
                        FROM productos p
                        LEFT JOIN categorias c ON p.id_categoria = c.id
                        WHERE p.id = ? AND p.ofertado = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$producto) {
    die('Producto no encontrado.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));
    $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + $cantidad;
    header('Location: carrito.php');
    exit;
}

ob_start();
?>
<section class="contenedor-principal">
    <h1><?= htmlspecialchars($producto['nombre']) ?></h1>

    <?php if (!empty($producto['imagen'])): ?>
        <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" width="220">
    <?php endif; ?>

    <p><strong>Categoría:</strong> <?= htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría') ?></p>
    <p><?= htmlspecialchars($producto['descripcion']) ?></p>
    <p><strong>IVA:</strong> <?= (int)$producto['iva'] ?>%</p>
    <p><strong>Precio final:</strong> <?= number_format($producto['precio_base'] * (1 + $producto['iva'] / 100), 2) ?> €</p>

    <form method="post">
        <label>Cantidad:
            <input type="number" name="cantidad" min="1" value="1">
        </label>
        <button type="submit">Añadir al carrito</button>
    </form>

    <p><a href="catalogo.php">Volver al catálogo</a></p>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Detalle de producto';
require __DIR__ . '/includes/vistas/comun/plantilla.php';