<?php
	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/config.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';

	exigirLogin();
    exigirRol('camarero', 'admin');

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
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosListosCocina as $p): ?>
                            <tr>
                                <td><strong>#<?= $p['id'] ?></strong></td>
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

<?php
	$contenidoPrincipal = ob_get_clean();
	require_once __DIR__ . '/includes/vistas/comun/plantilla.php';
?>