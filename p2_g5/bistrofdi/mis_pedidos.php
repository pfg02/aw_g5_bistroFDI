<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirLogin();

$controller = PedidoController::getInstance();
$pedidos = $controller->verPedidosCliente((int)$_SESSION['id_usuario']);

ob_start();
?>
<section class="contenedor-principal">
    <h1>Mis pedidos</h1>

    <?php if (empty($pedidos)): ?>
        <p>No tienes pedidos todavía.</p>
    <?php else: ?>
        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>Nº pedido</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?= (int)$pedido['numero_pedido'] ?></td>
                        <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                        <td><?= htmlspecialchars($pedido['tipo']) ?></td>
                        <td><?= htmlspecialchars($pedido['estado']) ?></td>
                        <td><?= number_format($pedido['total'], 2) ?> €</td>
                        <td>
                            <a href="detalle_pedido.php?id=<?= (int)$pedido['id'] ?>">Ver detalle</a>
                            <?php if ($pedido['estado'] === 'Recibido'): ?>
                                | <a href="cancelar_pedido.php?id=<?= (int)$pedido['id'] ?>">Cancelar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Mis pedidos - Bistro FDI';
require __DIR__ . '/includes/vistas/comun/plantilla.php';