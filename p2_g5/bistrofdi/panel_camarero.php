<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirRol('camarero');

$controller = PedidoController::getInstance();
$pedidosRecibidos = $controller->verPedidosPorEstado('Recibido');
$pedidosListoCocina = $controller->verPedidosPorEstado('Listo cocina');
$pedidosTerminados = $controller->verPedidosPorEstado('Terminado');

ob_start();
?>
<section class="contenedor-principal">
    <h1>Panel de camarero</h1>

    <p>
        <img src="<?= htmlspecialchars($_SESSION['avatar'] ?? 'img/avatares/default.png') ?>" alt="Avatar" width="48">
        <strong><?= htmlspecialchars($_SESSION['nombre_usuario'] ?? '') ?></strong>
    </p>

    <h2>Pedidos en estado "Recibido"</h2>
    <?php if (empty($pedidosRecibidos)): ?>
        <p>No hay pedidos en estado Recibido.</p>
    <?php else: ?>
        <?php foreach ($pedidosRecibidos as $pedido): ?>
            <article style="border:1px solid #ccc; padding:10px; margin:10px 0;">
                <p><strong>Nº pedido:</strong> <?= (int)$pedido['numero_pedido'] ?></p>
                <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['nombre_cliente'] . ' ' . $pedido['apellidos_cliente']) ?></p>
                <p><strong>Total:</strong> <?= number_format($pedido['total'], 2) ?> €</p>
                <p><a href="detalle_pedido.php?id=<?= (int)$pedido['id'] ?>">Ver detalle</a></p>

                <form method="post" action="procesar_estado.php">
                    <input type="hidden" name="id_pedido" value="<?= (int)$pedido['id'] ?>">
                    <input type="hidden" name="accion" value="cobrar">
                    <button type="submit">Cobrar y pasar a "En preparación"</button>
                </form>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2>Pedidos en estado "Listo cocina"</h2>
    <?php if (empty($pedidosListoCocina)): ?>
        <p>No hay pedidos en estado Listo cocina.</p>
    <?php else: ?>
        <?php foreach ($pedidosListoCocina as $pedido): ?>
            <article style="border:1px solid #ccc; padding:10px; margin:10px 0;">
                <p><strong>Nº pedido:</strong> <?= (int)$pedido['numero_pedido'] ?></p>
                <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['nombre_cliente'] . ' ' . $pedido['apellidos_cliente']) ?></p>
                <p><a href="detalle_pedido.php?id=<?= (int)$pedido['id'] ?>">Consultar detalle y pasar a "Terminado"</a></p>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2>Pedidos en estado "Terminado"</h2>
    <?php if (empty($pedidosTerminados)): ?>
        <p>No hay pedidos en estado Terminado.</p>
    <?php else: ?>
        <?php foreach ($pedidosTerminados as $pedido): ?>
            <article style="border:1px solid #ccc; padding:10px; margin:10px 0;">
                <p><strong>Nº pedido:</strong> <?= (int)$pedido['numero_pedido'] ?></p>
                <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['nombre_cliente'] . ' ' . $pedido['apellidos_cliente']) ?></p>

                <form method="post" action="procesar_estado.php">
                    <input type="hidden" name="id_pedido" value="<?= (int)$pedido['id'] ?>">
                    <input type="hidden" name="accion" value="entregar">
                    <button type="submit">Marcar como "Entregado"</button>
                </form>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Panel camarero';
require __DIR__ . '/includes/vistas/comun/plantilla.php';