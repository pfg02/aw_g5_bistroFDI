<?php
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('camarero');

$controller = PedidoController::getInstance();
$pedidosActivos = $controller->verPedidosActivos();
$esGerente = isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente';

$pedidosRecibidos = array_filter($pedidosActivos, fn($p) => $p['estado'] == 'Recibido');
$pedidosListosCocina = array_filter($pedidosActivos, fn($p) => $p['estado'] == 'Listo cocina');
$pedidosTerminados = array_filter($pedidosActivos, fn($p) => $p['estado'] == 'Terminado');

$tituloPagina = $esGerente ? 'Panel de Gerencia - Bistró FDI' : 'Panel de Sala - Bistró FDI';
$bodyClass = 'f0-body';

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
                    fn($p) => in_array($p['estado'], ['Recibido', 'En preparación', 'Cocinando'])
                );
            ?>
            <?php if (empty($pedidosPendientesGerente)): ?>
                <p class="p-vacio">No hay pedidos en preparación o recibidos ahora mismo.</p>
            <?php else: ?>
                <table class="tabla-pedidos tabla-sala-movil">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Tipo</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th>Hora</th>
                            <th>Cocinero asignado</th>
                            <th>Progreso de Cocina</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosPendientesGerente as $p): ?>
                            <?php
                                $idPedido = $p['id'];
                                $platos = $controller->obtenerProductosDePedido($idPedido);
                                $platosListos = 0;
                                $totalPlatos = 0;

                                if (!empty($platos)) {
                                    $totalPlatos = count($platos);
                                    foreach ($platos as &$plato) {
                                        $estaPreparado = ($plato['preparado'] == 1);
                                        $plato['preparado'] = $estaPreparado;
                                        if ($estaPreparado) {
                                            $platosListos++;
                                        }
                                    }
                                    unset($plato);
                                }

                                $avatarCocinero = !empty($p['avatar_cocinero'])
                                    ? BASE_URL . '/' . ltrim($p['avatar_cocinero'], '/')
                                    : BASE_URL . '/img/avatares/default.png';
                            ?>
                            <tr>
                                <td data-label="Ticket"><strong>#<?= htmlspecialchars($p['numero_pedido'] ?? $p['id']) ?></strong></td>
                                <td data-label="Tipo"><?= htmlspecialchars($p['tipo'] ?? 'Local') ?></td>
                                <td data-label="Cliente"><?= htmlspecialchars(trim(($p['nombre_cliente'] ?? '') . ' ' . ($p['apellidos_cliente'] ?? ''))) ?></td>
                                <td data-label="Estado">
                                    <span class="badge-success"><?= htmlspecialchars($p['estado']) ?></span>
                                </td>
                                <td data-label="Hora"><?= date('H:i', strtotime($p['fecha'])) ?></td>
                                <td data-label="Cocinero asignado">
                                    <?php if (!empty($p['nombre_cocinero']) || !empty($p['apellidos_cocinero'])): ?>
                                        <div class="wrapper-cocinero">
                                            <img src="<?= htmlspecialchars($avatarCocinero) ?>" alt="Avatar" class="avatar-cocinero">
                                            <div>
                                                <strong><?= htmlspecialchars(trim(($p['nombre_cocinero'] ?? '') . ' ' . ($p['apellidos_cocinero'] ?? ''))) ?></strong>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="txt-sin-asignar">Sin asignar</span>
                                    <?php endif; ?>
                                </td>

                                <td class="celda-progreso" data-label="Progreso de Cocina">
                                    <?php if ($totalPlatos > 0): ?>
                                        <details class="details-progreso">
                                            <summary class="summary-progreso">
                                                Ver Progreso (<?= $platosListos ?>/<?= $totalPlatos ?> listos)
                                            </summary>
                                            <ul class="ul-progreso">
                                                <?php foreach ($platos as $plato): ?>
                                                    <li class="li-progreso <?= $plato['preparado'] ? 'listo' : 'pte' ?>">
                                                        <strong class="<?= $plato['preparado'] ? 'texto-tachado' : '' ?>"><?= $plato['cantidad'] ?>x</strong>
                                                        <span class="<?= $plato['preparado'] ? 'texto-tachado' : '' ?>"><?= htmlspecialchars($plato['nombre']) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </details>
                                    <?php else: ?>
                                        <span class="txt-sin-platos">Sin platos</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Detalles">
                                    <a href="<?= BASE_URL ?>/includes/vistas/pedido/detalle_pedido.php?id=<?= urlencode((string)$p['id']) ?>">Ver Detalle</a>
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
                <table class="tabla-pedidos tabla-sala-movil">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Tipo</th>
                            <th>Cliente / Mesa</th>
                            <th>Total</th>
                            <th>Detalles</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosRecibidos as $p): ?>
                            <tr>
                                <td data-label="Ticket"><strong>#<?= htmlspecialchars($p['numero_pedido'] ?? $p['id']) ?></strong></td>
                                <td data-label="Tipo"><?= htmlspecialchars($p['tipo'] ?? 'Local') ?></td>
                                <td data-label="Cliente / Mesa"><?= htmlspecialchars(($p['nombre_cliente'] ?? '') . ' ' . ($p['apellidos_cliente'] ?? '')) ?></td>
                                <td data-label="Total"><?= number_format($p['total'], 2) ?> €</td>
                                <td data-label="Detalles">
                                    <a href="<?= BASE_URL ?>/includes/vistas/pedido/detalle_pedido.php?id=<?= urlencode((string)$p['id']) ?>">Ver Detalle</a>
                                </td>
                                <td data-label="Acción">
                                    <form action="<?= BASE_URL ?>/includes/acciones/pedido/procesar_estado.php" method="POST">
                                        <input type="hidden" name="id_pedido" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="accion" value="cobrar">
                                        <button type="submit" class="btn-accion">Marcar como Pagado</button>
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
                <table class="tabla-pedidos tabla-sala-movil">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Tipo</th>
                            <th>Detalles</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosListosCocina as $p): ?>
                            <tr>
                                <td data-label="Ticket"><strong>#<?= htmlspecialchars($p['numero_pedido'] ?? $p['id']) ?></strong></td>
                                <td data-label="Tipo"><?= htmlspecialchars($p['tipo'] ?? 'Local') ?></td>
                                <td data-label="Detalles">
                                    <a href="<?= BASE_URL ?>/includes/vistas/pedido/detalle_pedido.php?id=<?= urlencode((string)$p['id']) ?>">Ver Detalle</a>
                                </td>
                                <td data-label="Acción">
                                    <form action="<?= BASE_URL ?>/includes/acciones/pedido/procesar_estado.php" method="POST">
                                        <input type="hidden" name="id_pedido" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="accion" value="terminar">
                                        <button type="submit" class="btn-accion">Pasar a Terminado</button>
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
                <table class="tabla-pedidos tabla-sala-movil">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Tipo</th>
                            <th>Cliente</th>
                            <th>Detalles</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosTerminados as $p): ?>
                            <tr>
                                <td data-label="Ticket"><strong>#<?= htmlspecialchars($p['numero_pedido'] ?? $p['id']) ?></strong></td>
                                <td data-label="Tipo"><?= htmlspecialchars($p['tipo'] ?? 'Local') ?></td>
                                <td data-label="Cliente"><?= htmlspecialchars($p['nombre_cliente'] ?? '') ?></td>
                                <td data-label="Detalles">
                                    <a href="<?= BASE_URL ?>/includes/vistas/pedido/detalle_pedido.php?id=<?= urlencode((string)$p['id']) ?>" class="link-detalle-texto">Ver Detalle</a>
                                </td>
                                <td data-label="Acción">
                                    <form action="<?= BASE_URL ?>/includes/acciones/pedido/procesar_estado.php" method="POST">
                                        <input type="hidden" name="id_pedido" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="accion" value="entregar">
                                        <button type="submit" class="btn-accion">Marcar como Entregado</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="contenedor-volver">
            <a href="<?= BASE_URL ?>/index.php" class="btn-login">Volver al Inicio</a>
        </div>
    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';
?>