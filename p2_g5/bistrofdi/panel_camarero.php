<?php
	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/config.php';
	require_once __DIR__ . '/includes/negocio/PedidoController.php';

	exigirLogin();
    exigirRol('camarero', 'gerente');

	$controller = PedidoController::getInstance();
	$pedidosActivos = $controller->verPedidosActivos();
    $esGerente = isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente';

    $pedidosRecibidos = array_filter($pedidosActivos, fn($p) => $p['estado'] == 'Recibido');
    $pedidosListosCocina = array_filter($pedidosActivos, fn($p) => $p['estado'] == 'Listo cocina');
    $pedidosTerminados = array_filter($pedidosActivos, fn($p) => $p['estado'] == 'Terminado');

	$tituloPagina = $esGerente ? 'Panel de Gerencia - Bistró FDI' : 'Panel de Sala - Bistró FDI';
	$bodyClass    = 'f0-body';

	ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        <h1>Panel de <span><?= $esGerente ? 'Gerencia' : 'Sala' ?></span></h1>
        <p class="lema"><?= $esGerente ? 'Supervisión global de pedidos pendientes' : 'Gestión de flujo de pedidos' ?></p>
        <div class="divisor"></div>

        <?php if ($esGerente): ?>
        <div class="seccion-camarero">
            <h2 class="titulo-seccion">Vista global de pedidos pendientes</h2>
            <?php 
                $pedidosPendientesGerente = array_filter(
                    $pedidosActivos,
                    fn($p) => in_array($p['estado'], ['Recibido', 'En preparación', 'Cocinando', 'Listo cocina', 'Terminado'])
                );
            ?>
            <?php if (empty($pedidosPendientesGerente)): ?>
                <p class="p-vacio">No hay pedidos pendientes ahora mismo.</p>
            <?php else: ?>
                <table class="tabla-pedidos">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th>Hora</th>
                            <th>Cocinero asignado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosPendientesGerente as $p): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($p['numero_pedido'] ?? $p['id']) ?></strong></td>
                                <td><?= htmlspecialchars(trim(($p['nombre_cliente'] ?? '') . ' ' . ($p['apellidos_cliente'] ?? ''))) ?></td>
                                <td><strong><?= htmlspecialchars($p['estado']) ?></strong></td>
                                <td><?= date('H:i', strtotime($p['fecha'])) ?></td>
                                <td>
                                    <?php if (!empty($p['avatar_cocinero']) || !empty($p['nombre_cocinero'])): ?>
                                        <div style="display:flex; align-items:center; gap:10px; justify-content:flex-start;">
                                            <img src="<?= htmlspecialchars($p['avatar_cocinero']) ?>" alt="Avatar del cocinero" style="width:52px; height:52px; border-radius:50%; object-fit:cover; border:2px solid #ddd;">
                                            <div style="text-align:left;">
                                                <strong><?= htmlspecialchars(trim(($p['nombre_cocinero'] ?? '') . ' ' . ($p['apellidos_cocinero'] ?? ''))) ?></strong>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span style="color:#777;">Sin asignar todavía</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="divisor"></div>
        <?php endif; ?>

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