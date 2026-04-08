<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirLogin();

$idPedido = (int)($_GET['id'] ?? 0);
$controller = PedidoController::getInstance();
$pedido = $controller->verPedido($idPedido);
$lineas = $controller->verLineasPedido($idPedido);

if (!$pedido) {
    die('Pedido no encontrado.');
}

$rol = $_SESSION['rol'] ?? 'cliente';
$esPropio = (int)$pedido['cliente_id'] === (int)$_SESSION['id_usuario'];
$esStaff = in_array($rol, ['camarero', 'gerente', 'cocinero'], true);

if (!$esPropio && !$esStaff) {
    die('No tienes permisos para ver este pedido.');
}

ob_start();
?>
<section class="contenedor-principal">
    <h1>Detalle del pedido</h1>

    <p><strong>Nº pedido:</strong> <?= (int)$pedido['numero_pedido'] ?></p>
    <p><strong>Cliente:</strong> <?= htmlspecialchars(($pedido['nombre_cliente'] ?? '') . ' ' . ($pedido['apellidos_cliente'] ?? '')) ?></p>
    <p><strong>Fecha:</strong> <?= htmlspecialchars($pedido['fecha']) ?></p>
    <p><strong>Tipo:</strong> <?= htmlspecialchars($pedido['tipo']) ?></p>
    <p><strong>Estado:</strong> <?= htmlspecialchars($pedido['estado']) ?></p>
    <p><strong>Total:</strong> <?= number_format($pedido['total'], 2) ?> €</p>

    <h2>Productos</h2>
    <ul>
        <?php foreach ($lineas as $linea): ?>
            <li>
                <?= htmlspecialchars($linea['nombre']) ?>
                — Cantidad: <?= (int)$linea['cantidad'] ?>
                — Precio final unidad: <?= number_format($linea['precio_base'] * (1 + $linea['iva'] / 100), 2) ?> €
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($rol === 'camarero' && $pedido['estado'] === 'Listo cocina'): ?>
        <form method="post" action="procesar_estado.php">
            <input type="hidden" name="id_pedido" value="<?= (int)$pedido['id'] ?>">
            <input type="hidden" name="accion" value="terminar">
            <button type="submit">Pasar a Terminado</button>
        </form>
    <?php endif; ?>

    <p><a href="<?= $rol === 'camarero' ? 'panel_camarero.php' : 'mis_pedidos.php' ?>">Volver</a></p>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Detalle del pedido';
require __DIR__ . '/includes/vistas/comun/plantilla.php';