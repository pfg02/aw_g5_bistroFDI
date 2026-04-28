<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('cocinero', 'gerente');

$idCocinero = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idCocinero === false || $idCocinero === null) {
    $_SESSION['mensaje_error'] = 'Sesión no válida.';
    header('Location: ' . BASE_URL . '/includes/vistas/auth/login.php');
    exit();
}

$controller = PedidoController::getInstance();

/*
 * Pedidos en preparación, pero solo si tienen productos que requieren cocina.
 * Así no aparecen comandas solo de bebidas/cafés.
 */
$pedidosNuevos = array_filter(
    $controller->verPedidosPorEstado('En preparación'),
    function ($pedido) use ($controller) {
        $idPedido = (int) (obtenerDatoPedido($pedido, 'id', 'getId') ?? 0);

        if ($idPedido <= 0) {
            return false;
        }

        return pedidoTieneProductosDeCocina($controller, $idPedido);
    }
);

// Recuperar el pedido activo desde BD para que no se pierda al cerrar sesión o salir
$miPedido = $controller->obtenerPedidoActivoDeCocinero((int) $idCocinero);

if ($miPedido) {
    $idPedidoActivoSesion = obtenerDatoPedido($miPedido, 'id', 'getId');

    if ($idPedidoActivoSesion !== null) {
        $_SESSION['pedido_activo_cocinero'][(int) $idCocinero] = (int) $idPedidoActivoSesion;
    }
} elseif (isset($_SESSION['pedido_activo_cocinero'][(int) $idCocinero])) {
    unset($_SESSION['pedido_activo_cocinero'][(int) $idCocinero]);
}

$platos = [];
$todosPreparados = false;
$idPedidoActivo = null;

if ($miPedido) {
    $idPedidoActivo = obtenerDatoPedido($miPedido, 'id', 'getId');

    if ($idPedidoActivo !== null) {
        /*
         * Cocina solo debe ver productos que requieren cocina.
         * Bebidas/cafés quedan fuera de la mesa de cocina.
         */
        $productosPedidoActivo = $controller->obtenerProductosDePedido((int) $idPedidoActivo);

        $platos = array_values(array_filter(
            $productosPedidoActivo,
            fn($producto) => (int) ($producto['requiere_cocina'] ?? 1) === 1
        ));

        if (!empty($platos)) {
            $todosPreparados = true;

            foreach ($platos as &$plato) {
                $estaPreparado = ((int) ($plato['preparado'] ?? 0) === 1);

                $plato['requiere_cocina'] = true;
                $plato['preparado'] = $estaPreparado;

                if (!$estaPreparado) {
                    $todosPreparados = false;
                }
            }

            unset($plato);
        }
    }
}

$tituloPagina = 'Bistró FDI - Cocina';
$bodyClass = 'f0-body';

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        <h1>Panel de <span>Cocina</span></h1>
        <p class="lema">Gestión de comandas y preparación</p>
        <div class="divisor"></div>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="error-msg">
                <?= htmlspecialchars($_SESSION['mensaje_error']) ?>
                <?php unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alerta alerta-exito">
                <?= htmlspecialchars($_SESSION['mensaje_exito']) ?>
                <?php unset($_SESSION['mensaje_exito']); ?>
            </div>
        <?php endif; ?>

        <div class="seccion-camarero">
            <h2 class="titulo-seccion">Comandas Nuevas</h2>

            <?php if (empty($pedidosNuevos)): ?>
                <p class="p-vacio">No hay comandas pendientes.</p>
            <?php else: ?>
                <table class="tabla-pedidos tabla-cocina-movil">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Tipo</th>
                            <th>Hora</th>
                            <th>Detalles</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidosNuevos as $p): ?>
                            <?php
                                $idPedido = (int) (obtenerDatoPedido($p, 'id', 'getId') ?? 0);
                                $numeroPedido = obtenerDatoPedido($p, 'numero_pedido', 'getNumeroPedido') ?? $idPedido;
                                $tipoPedido = (string) (obtenerDatoPedido($p, 'tipo', 'getTipo') ?? 'Local');
                                $fechaPedido = (string) (obtenerDatoPedido($p, 'fecha', 'getFecha') ?? '');

                                $horaFormateada = '';
                                if ($fechaPedido !== '') {
                                    $timestamp = strtotime($fechaPedido);
                                    if ($timestamp !== false) {
                                        $horaFormateada = date('H:i', $timestamp);
                                    }
                                }

                                /*
                                 * Solo productos que requieren cocina.
                                 * Bebidas/cafés no se muestran en cocina.
                                 */
                                $productosPedidoNuevo = [];

                                if ($idPedido > 0) {
                                    $productosPedidoNuevo = array_values(array_filter(
                                        $controller->obtenerProductosDePedido($idPedido),
                                        fn($producto) => (int) ($producto['requiere_cocina'] ?? 1) === 1
                                    ));
                                }
                            ?>
                            <tr>
                                <td data-label="Ticket">
                                    <strong>#<?= htmlspecialchars((string) $numeroPedido) ?></strong>
                                </td>

                                <td data-label="Tipo">
                                    <?= htmlspecialchars($tipoPedido) ?>
                                </td>

                                <td data-label="Hora">
                                    <?= htmlspecialchars($horaFormateada) ?>
                                </td>

                                <td data-label="Detalles">
                                    <?php if (empty($productosPedidoNuevo)): ?>
                                        <span class="txt-sin-platos">Sin productos de cocina</span>
                                    <?php else: ?>
                                        <details class="details-progreso">
                                            <summary class="summary-progreso">
                                                Ver productos
                                            </summary>

                                            <ul class="ul-progreso">
                                                <?php foreach ($productosPedidoNuevo as $productoPedido): ?>
                                                    <?php
                                                        $cantidadProducto = (int) ($productoPedido['cantidad'] ?? 0);
                                                        $nombreProducto = (string) ($productoPedido['nombre'] ?? '');
                                                        $preparadoProducto = ((int) ($productoPedido['preparado'] ?? 0) === 1);
                                                    ?>

                                                    <li class="li-progreso <?= $preparadoProducto ? 'listo' : 'pte' ?>">
                                                        <strong><?= $cantidadProducto ?>x</strong>
                                                        <span>
                                                            <?= htmlspecialchars($nombreProducto) ?>
                                                        </span>

                                                        <?php if ($preparadoProducto): ?>
                                                            <small>Ya preparado</small>
                                                        <?php else: ?>
                                                            <small>Pendiente de cocina</small>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </details>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Acción">
                                    <form action="<?= BASE_URL ?>/includes/acciones/cocina/procesar_cocina.php" method="POST">
                                        <input type="hidden" name="accion" value="reclamar">
                                        <input type="hidden" name="id_pedido" value="<?= htmlspecialchars((string) $idPedido) ?>">
                                        <button
                                            type="submit"
                                            class="btn-accion btn-cobrar <?= $miPedido ? 'btn-cocinar-bloqueado' : '' ?>"
                                            <?= $miPedido ? 'disabled title="Termina tu pedido actual primero"' : '' ?>
                                        >
                                            Cocinar
                                        </button>
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
            <h2 class="titulo-seccion">Mi Mesa de Trabajo</h2>

            <?php if (!$miPedido): ?>
                <p>
                    <br>Esperando comanda... Selecciona un ticket de arriba para empezar a cocinar.
                </p>
            <?php else: ?>
                <?php
                    $numeroMiPedido = obtenerDatoPedido($miPedido, 'numero_pedido', 'getNumeroPedido') ?? $idPedidoActivo;
                    $tipoMiPedido = (string) (obtenerDatoPedido($miPedido, 'tipo', 'getTipo') ?? 'Local');
                ?>

                <p>
                    <strong>Preparando Ticket #<?= htmlspecialchars((string) $numeroMiPedido) ?> (<?= htmlspecialchars($tipoMiPedido) ?>)</strong>
                </p>

                <?php if (empty($platos)): ?>
                    <p class="p-vacio">Este pedido no tiene productos que requieran cocina.</p>
                <?php else: ?>
                    <table class="tabla-pedidos tabla-cocina-movil">
                        <thead>
                            <tr>
                                <th>Cant.</th>
                                <th>Producto</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($platos as $plato): ?>
                                <?php
                                    $cantidad = (int) ($plato['cantidad'] ?? 0);
                                    $nombre = (string) ($plato['nombre'] ?? '');
                                    $productoId = (int) ($plato['producto_id'] ?? 0);
                                    $preparado = !empty($plato['preparado']);
                                ?>
                                <tr>
                                    <td data-label="Cantidad">
                                        <strong><?= $cantidad ?>x</strong>
                                    </td>

                                    <td data-label="Producto" class="<?= $preparado ? 'plato-preparado' : '' ?>">
                                        <?= htmlspecialchars($nombre) ?>
                                    </td>

                                    <td data-label="Acción">
                                        <?php if (!$preparado): ?>
                                            <form action="<?= BASE_URL ?>/includes/acciones/cocina/procesar_cocina.php" method="POST">
                                                <input type="hidden" name="accion" value="marcar_plato">
                                                <input type="hidden" name="id_pedido" value="<?= htmlspecialchars((string) $idPedidoActivo) ?>">
                                                <input type="hidden" name="id_producto" value="<?= htmlspecialchars((string) $productoId) ?>">
                                                <button type="submit" class="btn-accion btn-entregar">Listo</button>
                                            </form>
                                        <?php else: ?>
                                            <span>✅</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="contenedor-botones-index">
                        <form action="<?= BASE_URL ?>/includes/acciones/cocina/procesar_cocina.php" method="POST">
                            <input type="hidden" name="accion" value="finalizar_pedido">
                            <input type="hidden" name="id_pedido" value="<?= htmlspecialchars((string) $idPedidoActivo) ?>">
                            <button
                                type="submit"
                                class="btn-login btn-pasar-sala <?= $todosPreparados ? 'estado-sala-listo' : 'estado-sala-pte' ?>"
                                <?= !$todosPreparados ? 'disabled title="Marca primero los productos que requieren cocina"' : '' ?>
                            >
                                ¡Pasar a Sala!
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
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

function obtenerDatoPedido($pedido, string $claveArray, string $getter)
{
    if (is_array($pedido)) {
        return $pedido[$claveArray] ?? null;
    }

    if (is_object($pedido) && method_exists($pedido, $getter)) {
        return $pedido->$getter();
    }

    return null;
}

function pedidoTieneProductosDeCocina($controller, int $idPedido): bool
{
    $productos = $controller->obtenerProductosDePedido($idPedido);

    foreach ($productos as $producto) {
        if ((int) ($producto['requiere_cocina'] ?? 1) === 1) {
            return true;
        }
    }

    return false;
}
?>