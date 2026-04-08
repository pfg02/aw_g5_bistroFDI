<?php
require_once __DIR__ . '/includes/sesion.php';
exigirLogin();

$conn = obtenerConexionBD();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipo'])) {
    $_SESSION['tipo_pedido'] = $_POST['tipo'];
}

if (empty($_SESSION['tipo_pedido'])) {
    header('Location: pedido_inicio.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_producto'])) {
    $idProducto = (int)($_POST['producto_id'] ?? 0);
    $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));

    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    $_SESSION['carrito'][$idProducto] = ($_SESSION['carrito'][$idProducto] ?? 0) + $cantidad;
    header('Location: carrito.php');
    exit;
}

$idCategoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busqueda = trim($_GET['q'] ?? '');

$categorias = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT p.*, c.nombre AS categoria_nombre
        FROM productos p
        LEFT JOIN categorias c ON p.id_categoria = c.id
        WHERE p.ofertado = 1";
$tipos = '';
$params = [];

if ($idCategoria > 0) {
    $sql .= " AND p.id_categoria = ?";
    $tipos .= 'i';
    $params[] = $idCategoria;
}

if ($busqueda !== '') {
    $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $tipos .= 'ss';
    $like = '%' . $busqueda . '%';
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY c.nombre ASC, p.nombre ASC";
$stmt = $conn->prepare($sql);
if ($tipos !== '') {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

ob_start();
?>
<section class="contenedor-principal">
    <h1>Carta de productos</h1>
    <p><strong>Tipo de pedido:</strong> <?= htmlspecialchars($_SESSION['tipo_pedido']) ?></p>

    <form method="get" action="catalogo.php">
        <label>Categoría:
            <select name="categoria">
                <option value="0">Todas</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= (int)$categoria['id'] ?>" <?= $idCategoria === (int)$categoria['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categoria['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Buscar:
            <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>">
        </label>

        <button type="submit">Filtrar</button>
    </form>

    <p><a href="carrito.php">Ver carrito</a></p>

    <?php if (empty($productos)): ?>
        <p>No hay productos disponibles con esos filtros.</p>
    <?php else: ?>
        <?php foreach ($productos as $producto): ?>
            <article style="border:1px solid #ccc; padding:12px; margin:12px 0;">
                <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                <p><strong>Categoría:</strong> <?= htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría') ?></p>
                <p><?= htmlspecialchars($producto['descripcion']) ?></p>
                <p><strong>Precio final:</strong> <?= number_format($producto['precio_base'] * (1 + $producto['iva'] / 100), 2) ?> €</p>
                <p><a href="detalle_producto.php?id=<?= (int)$producto['id'] ?>">Ver detalle</a></p>

                <form method="post" action="catalogo.php">
                    <input type="hidden" name="producto_id" value="<?= (int)$producto['id'] ?>">
                    <input type="number" name="cantidad" min="1" value="1">
                    <button type="submit" name="add_producto" value="1">Añadir al carrito</button>
                </form>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Catálogo - Bistro FDI';
require __DIR__ . '/includes/vistas/comun/plantilla.php';