<?php
require_once __DIR__ . '/includes/sesion.php';
exigirLogin();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_carrito'])) {
    foreach ($_POST['cantidades'] ?? [] as $idProducto => $cantidad) {
        $idProducto = (int)$idProducto;
        $cantidad = (int)$cantidad;

        if ($cantidad <= 0) {
            unset($_SESSION['carrito'][$idProducto]);
        } else {
            $_SESSION['carrito'][$idProducto] = $cantidad;
        }
    }
    header('Location: carrito.php');
    exit;
}

$carrito = $_SESSION['carrito'];
$productos = [];
$total = 0;

if (!empty($carrito)) {
    $conn = obtenerConexionBD();
    $ids = implode(',', array_map('intval', array_keys($carrito)));

    $sql = "SELECT id, nombre, descripcion, precio_base, iva, imagen FROM productos WHERE id IN ($ids)";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $cantidad = $carrito[$row['id']];
        $precioFinal = $row['precio_base'] * (1 + $row['iva'] / 100);
        $subtotal = $precioFinal * $cantidad;

        $row['cantidad'] = $cantidad;
        $row['precio_final'] = $precioFinal;
        $row['subtotal'] = $subtotal;

        $productos[] = $row;
        $total += $subtotal;
    }
}

ob_start();
?>
<section class="contenedor-principal">
    <h1>Carrito</h1>

    <?php if (empty($productos)): ?>
        <p>Tu carrito está vacío.</p>
        <p><a href="catalogo.php">Ir al catálogo</a></p>
    <?php else: ?>
        <form method="post" action="carrito.php">
            <?php foreach ($productos as $producto): ?>
                <article style="border:1px solid #ccc; padding:10px; margin:10px 0;">
                    <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                    <p><?= htmlspecialchars($producto['descripcion']) ?></p>
                    <p>Precio unitario: <?= number_format($producto['precio_final'], 2) ?> €</p>

                    <label>Cantidad:
                        <input type="number" name="cantidades[<?= (int)$producto['id'] ?>]" min="0" value="<?= (int)$producto['cantidad'] ?>">
                    </label>

                    <p>Subtotal: <?= number_format($producto['subtotal'], 2) ?> €</p>
                    <p><a href="eliminar_del_carrito.php?id=<?= (int)$producto['id'] ?>">Eliminar producto</a></p>
                </article>
            <?php endforeach; ?>

            <button type="submit" name="actualizar_carrito" value="1">Actualizar carrito</button>
        </form>

        <h2>Total: <?= number_format($total, 2) ?> €</h2>

        <form method="post" action="confirmar_pedido.php" style="display:inline-block; margin-right:10px;">
            <button type="submit">Confirmar pedido</button>
        </form>

        <a href="cancelar_pedido.php?carrito=1">Cancelar pedido</a>
    <?php endif; ?>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Carrito - Bistro FDI';
require __DIR__ . '/includes/vistas/comun/plantilla.php';