<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirRol('camarero');

$controller = PedidoController::getInstance();
$pedidosRecibidos = $controller->verPedidosPorEstado('Recibido');
$pedidosListoCocina = $controller->verPedidosPorEstado('Listo cocina');
$pedidosTerminados = $controller->verPedidosPorEstado('Terminado');

	$controller = PedidoController::getInstance();
	$pedidosActivos = $controller->verPedidosActivos();

	// Clasificamos los pedidos por su estado para las vistas diferenciadas
    $pedidosRecibidos = array_filter($pedidosActivos, fn($p) => $p['estado'] == 'Recibido');
    $pedidosListosCocina = array_filter($pedidosActivos, fn($p) => $p['estado'] == 'Listo cocina');
    $pedidosTerminados = array_filter($pedidosActivos, fn($p) => $p['estado'] == 'Terminado');

	$tituloPagina = 'Panel de Sala - Bistró FDI';
	$bodyClass    = 'f0-body';

	ob_start();
?>
<section class="contenedor-principal">
    <h1>Panel de camarero</h1>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        <h1>Panel de <span>Sala</span></h1>
        <p class="lema">Gestión de flujo de pedidos</p>
        <div class="divisor"></div>

        <div class="seccion-camarero">
            <h2 class="titulo-seccion">Pedidos Pendientes de Cobro (Recibidos)</h2>
            <?php if (empty($pedidosRecibidos)): ?>
                <p class="p-vacio">No hay pedidos por cobrar.</p>
            <?php else: ?>
                <table class="tabla-pedidos">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Cliente / Mesa</th>
                            <th>Total</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosRecibidos as $p): ?>
                            <tr>
                                <td><strong>#<?= $p['id'] ?></strong></td>
                                <td><?= htmlspecialchars($p['nombre_cliente'] . ' ' . $p['apellidos_cliente']) ?></td>
                                <td><?= number_format($p['total'], 2) ?> €</td>
                                <td>
                                    <form action="procesar_estado.php" method="POST">
                                        <input type="hidden" name="id_pedido" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="accion" value="cobrar">
                                        <button type="submit" class="btn-accion btn-cobrar">Marcar como Pagado</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

		<div class="divisor"></div>

        <div class="seccion-camarero">
            <h2 class="titulo-seccion">Listos en Cocina (Para recoger)</h2>
            <?php if (empty($pedidosListosCocina)): ?>
                <p class="p-vacio">No hay platos listos para recoger.</p>
            <?php else: ?>
                <table class="tabla-pedidos">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Productos</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosListosCocina as $p): ?>
                            <tr>
                                <td><strong>#<?= $p['id'] ?></strong></td>
                                <td>
                                    <a href="detalle_pedido.php?id=<?= $p['id'] ?>" class="link-detalle">Ver Detalle</a>
                                </td>
                                <td>
                                    <form action="procesar_estado.php" method="POST">
                                        <input type="hidden" name="id_pedido" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="accion" value="terminar">
                                        <button type="submit" class="btn-accion btn-servir">Pasar a Terminado</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

		<div class="divisor"></div>

        <div class="seccion-camarero">
            <h2 class="titulo-seccion">Pedidos Terminados (Para entregar)</h2>
            <?php if (empty($pedidosTerminados)): ?>
                <p class="p-vacio">No hay pedidos pendientes de entrega final.</p>
            <?php else: ?>
                <table class="tabla-pedidos">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Cliente</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosTerminados as $p): ?>
                            <tr>
                                <td><strong>#<?= $p['id'] ?></strong></td>
                                <td><?= htmlspecialchars($p['nombre_cliente']) ?></td>
                                <td>
                                    <form action="procesar_estado.php" method="POST">
                                        <input type="hidden" name="id_pedido" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="accion" value="entregar">
                                        <button type="submit" class="btn-accion btn-entregar">Marcar como Entregado</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="contenedor-volver">
            <a href="index.php" class="btn-login">Volver al Inicio</a>
        </div>
    </section>
</div>

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