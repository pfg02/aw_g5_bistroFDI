<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('cocinero', 'gerente');

$rolUsuario = $_SESSION['rol'] ?? null;
$esGerente = ($rolUsuario === 'gerente');
$esCocinero = ($rolUsuario === 'cocinero');

$idUsuarioSesion = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idUsuarioSesion === false || $idUsuarioSesion === null) {
    $_SESSION['mensaje_error'] = 'Sesión no válida.';
    header('Location: ' . BASE_URL . '/includes/vistas/auth/login.php');
    exit();
}

$controller = PedidoController::getInstance();

/*
 * Pedidos en preparación con productos de cocina.
 * No aparecen pedidos solo de bebidas/cafés.
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

/*
 * Para gerente: seguimiento de pedidos que ya están siendo cocinados.
 */
$pedidosCocinandoGerente = [];

if ($esGerente) {
    $pedidosCocinandoGerente = array_filter(
        $controller->verPedidosPorEstado('Cocinando'),
        function ($pedido) use ($controller) {
            $idPedido = (int) (obtenerDatoPedido($pedido, 'id', 'getId') ?? 0);

            if ($idPedido <= 0) {
                return false;
            }

            return pedidoTieneProductosDeCocina($controller, $idPedido);
        }
    );
}

/*
 * Solo el cocinero tiene una mesa de trabajo propia.
 * El gerente entra en modo consulta.
 */
$miPedido = null;

if ($esCocinero) {
    $miPedido = $controller->obtenerPedidoActivoDeCocinero((int) $idUsuarioSesion);

    if ($miPedido) {
        $idPedidoActivoSesion = obtenerDatoPedido($miPedido, 'id', 'getId');

        if ($idPedidoActivoSesion !== null) {
            $_SESSION['pedido_activo_cocinero'][(int) $idUsuarioSesion] = (int) $idPedidoActivoSesion;
        }
    } elseif (isset($_SESSION['pedido_activo_cocinero'][(int) $idUsuarioSesion])) {
        unset($_SESSION['pedido_activo_cocinero'][(int) $idUsuarioSesion]);
    }
}

$platos = [];
$todosPreparados = false;
$idPedidoActivo = null;

if ($miPedido) {
    $idPedidoActivo = obtenerDatoPedido($miPedido, 'id', 'getId');

    if ($idPedidoActivo !== null) {
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

$tituloPagina = $esGerente ? 'Bistró FDI - Supervisión Cocina' : 'Bistró FDI - Cocina';
$bodyClass = 'f0-body';

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        <h1>Panel de <span><?= $esGerente ? 'Supervisión de Cocina' : 'Cocina' ?></span></h1>
        <p class="lema">
            <?= $esGerente ? 'Consulta del estado de comandas y productos en preparación' : 'Gestión de comandas y preparación' ?>
        </p>

        <div class="divisor"></div>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="error-msg">
                <?= htmlspecialchars($_SESSION['mensaje_error'], ENT_QUOTES, 'UTF-8') ?>
                <?php unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alerta alerta-exito">
                <?= htmlspecialchars($_SESSION['mensaje_exito'], ENT_QUOTES, 'UTF-8') ?>
                <?php unset($_SESSION['mensaje_exito']); ?>
            </div>
        <?php endif; ?>

        <div class="seccion-camarero">
            <h2 class="titulo-seccion">
                <?= $esGerente ? 'Comandas pendientes de asignar' : 'Comandas Nuevas' ?>
            </h2>

            <?php if (empty($pedidosNuevos)): ?>
                <p class="p-vacio">No hay comandas pendientes.</p>
            <?php else: ?>
                <table class="tabla-pedidos tabla-cocina-movil">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Tipo</th>
                            <th>Hora</th>
                            <th>Productos</th>
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

                                $productosPedidoNuevo = [];

                                if ($idPedido > 0) {
                                    $productosPedidoNuevo = obtenerProductosCocinaPedido($controller, $idPedido);
                                }

                                $resumenProgreso = calcularResumenProductos($productosPedidoNuevo);
                            ?>

                            <tr>
                                <td data-label="Ticket">
                                    <strong>#<?= htmlspecialchars((string) $numeroPedido, ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>

                                <td data-label="Tipo">
                                    <?= htmlspecialchars($tipoPedido, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td data-label="Hora">
                                    <?= htmlspecialchars($horaFormateada, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td data-label="Productos">
                                    <?php if (empty($productosPedidoNuevo)): ?>
                                        <span class="txt-sin-platos">Sin productos de cocina</span>
                                    <?php else: ?>
                                        <details class="details-progreso">
                                            <summary class="summary-progreso">
                                                Ver productos (<?= $resumenProgreso['listos'] ?>/<?= $resumenProgreso['total'] ?> listos)
                                            </summary>

                                            <ul class="ul-progreso">
                                                <?php foreach ($productosPedidoNuevo as $productoPedido): ?>
                                                    <?php
                                                        $cantidadProducto = (int) ($productoPedido['cantidad'] ?? 0);
                                                        $nombreProducto = (string) ($productoPedido['nombre'] ?? '');
                                                        $preparadoProducto = ((int) ($productoPedido['preparado'] ?? 0) === 1);
                                                    ?>

                                                    <li class="li-progreso <?= $preparadoProducto ? 'listo' : 'pte' ?>">
                                                        <strong class="<?= $preparadoProducto ? 'texto-tachado' : '' ?>">
                                                            <?= $cantidadProducto ?>x
                                                        </strong>

                                                        <span class="<?= $preparadoProducto ? 'texto-tachado' : '' ?>">
                                                            <?= htmlspecialchars($nombreProducto, ENT_QUOTES, 'UTF-8') ?>
                                                        </span>

                                                        <?php if ($preparadoProducto): ?>
                                                            <small>Listo</small>
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
                                    <?php if ($esCocinero): ?>
                                        <form action="<?= BASE_URL ?>/includes/acciones/cocina/procesar_cocina.php" method="POST">
                                            <input type="hidden" name="accion" value="reclamar">
                                            <input type="hidden" name="id_pedido" value="<?= htmlspecialchars((string) $idPedido, ENT_QUOTES, 'UTF-8') ?>">

                                            <button
                                                type="submit"
                                                class="btn-accion btn-cobrar <?= $miPedido ? 'btn-cocinar-bloqueado' : '' ?>"
                                                <?= $miPedido ? 'disabled title="Termina tu pedido actual primero"' : '' ?>
                                            >
                                                Cocinar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="txt-sin-asignar">Solo consulta</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php if ($esGerente): ?>
            <div class="divisor"></div>

            <div class="seccion-camarero">
                <h2 class="titulo-seccion">Pedidos actualmente en cocina</h2>

                <?php if (empty($pedidosCocinandoGerente)): ?>
                    <p class="p-vacio">No hay pedidos cocinándose ahora mismo.</p>
                <?php else: ?>
                    <table class="tabla-pedidos tabla-cocina-movil">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Tipo</th>
                                <th>Cocinero</th>
                                <th>Estado</th>
                                <th>Productos</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pedidosCocinandoGerente as $p): ?>
                                <?php
                                    $idPedido = (int) (obtenerDatoPedido($p, 'id', 'getId') ?? 0);
                                    $numeroPedido = obtenerDatoPedido($p, 'numero_pedido', 'getNumeroPedido') ?? $idPedido;
                                    $tipoPedido = (string) (obtenerDatoPedido($p, 'tipo', 'getTipo') ?? 'Local');
                                    $estadoPedido = (string) (obtenerDatoPedido($p, 'estado', 'getEstado') ?? '');

                                    $nombreCocinero = trim(
                                        (string) (obtenerDatoPedido($p, 'nombre_cocinero', 'getNombreCocinero') ?? '') . ' ' .
                                        (string) (obtenerDatoPedido($p, 'apellidos_cocinero', 'getApellidosCocinero') ?? '')
                                    );

                                    $avatarCocineroDato = obtenerDatoPedido($p, 'avatar_cocinero', 'getAvatarCocinero');
                                    $avatarCocinero = !empty($avatarCocineroDato)
                                        ? BASE_URL . '/' . ltrim((string) $avatarCocineroDato, '/')
                                        : BASE_URL . '/img/avatares/default.png';

                                    $productosPedido = $idPedido > 0
                                        ? obtenerProductosCocinaPedido($controller, $idPedido)
                                        : [];

                                    $resumenProgreso = calcularResumenProductos($productosPedido);
                                ?>

                                <tr>
                                    <td data-label="Ticket">
                                        <strong>#<?= htmlspecialchars((string) $numeroPedido, ENT_QUOTES, 'UTF-8') ?></strong>
                                    </td>

                                    <td data-label="Tipo">
                                        <?= htmlspecialchars($tipoPedido, ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td data-label="Cocinero">
                                        <?php if ($nombreCocinero !== ''): ?>
                                            <div class="wrapper-cocinero">
                                                <img
                                                    src="<?= htmlspecialchars($avatarCocinero, ENT_QUOTES, 'UTF-8') ?>"
                                                    alt="Avatar del cocinero"
                                                    class="avatar-cocinero"
                                                >
                                                <div>
                                                    <strong><?= htmlspecialchars($nombreCocinero, ENT_QUOTES, 'UTF-8') ?></strong>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="txt-sin-asignar">Sin asignar</span>
                                        <?php endif; ?>
                                    </td>

                                    <td data-label="Estado">
                                        <span class="badge-success">
                                            <?= htmlspecialchars($estadoPedido, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>

                                    <td data-label="Productos">
                                        <?php if (empty($productosPedido)): ?>
                                            <span class="txt-sin-platos">Sin productos de cocina</span>
                                        <?php else: ?>
                                            <details class="details-progreso" open>
                                                <summary class="summary-progreso">
                                                    Ver productos (<?= $resumenProgreso['listos'] ?>/<?= $resumenProgreso['total'] ?> listos)
                                                </summary>

                                                <ul class="ul-progreso">
                                                    <?php foreach ($productosPedido as $productoPedido): ?>
                                                        <?php
                                                            $cantidadProducto = (int) ($productoPedido['cantidad'] ?? 0);
                                                            $nombreProducto = (string) ($productoPedido['nombre'] ?? '');
                                                            $preparadoProducto = ((int) ($productoPedido['preparado'] ?? 0) === 1);
                                                        ?>

                                                        <li class="li-progreso <?= $preparadoProducto ? 'listo' : 'pte' ?>">
                                                            <strong class="<?= $preparadoProducto ? 'texto-tachado' : '' ?>">
                                                                <?= $cantidadProducto ?>x
                                                            </strong>

                                                            <span class="<?= $preparadoProducto ? 'texto-tachado' : '' ?>">
                                                                <?= htmlspecialchars($nombreProducto, ENT_QUOTES, 'UTF-8') ?>
                                                            </span>

                                                            <?php if ($preparadoProducto): ?>
                                                                <small>Listo</small>
                                                            <?php else: ?>
                                                                <small>Pendiente</small>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </details>
                                        <?php endif; ?>
                                    </td>

                                    <td data-label="Detalle">
                                        <a href="<?= BASE_URL ?>/includes/vistas/pedido/detalle_pedido.php?id=<?= urlencode((string) $idPedido) ?>">
                                            Ver detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($esCocinero): ?>
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
                        <strong>
                            Preparando Ticket #<?= htmlspecialchars((string) $numeroMiPedido, ENT_QUOTES, 'UTF-8') ?>
                            (<?= htmlspecialchars($tipoMiPedido, ENT_QUOTES, 'UTF-8') ?>)
                        </strong>
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
                                            <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
                                        </td>

                                        <td data-label="Acción">
                                            <?php if (!$preparado): ?>
                                                <form action="<?= BASE_URL ?>/includes/acciones/cocina/procesar_cocina.php" method="POST">
                                                    <input type="hidden" name="accion" value="marcar_plato">
                                                    <input type="hidden" name="id_pedido" value="<?= htmlspecialchars((string) $idPedidoActivo, ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="id_producto" value="<?= htmlspecialchars((string) $productoId, ENT_QUOTES, 'UTF-8') ?>">
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
                                <input type="hidden" name="id_pedido" value="<?= htmlspecialchars((string) $idPedidoActivo, ENT_QUOTES, 'UTF-8') ?>">

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
        <?php endif; ?>

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

function obtenerProductosCocinaPedido($controller, int $idPedido): array
{
    $productos = $controller->obtenerProductosDePedido($idPedido);

    return array_values(array_filter(
        $productos,
        fn($producto) => (int) ($producto['requiere_cocina'] ?? 1) === 1
    ));
}

function calcularResumenProductos(array $productos): array
{
    $total = 0;
    $listos = 0;

    foreach ($productos as $producto) {
        $cantidad = (int) ($producto['cantidad'] ?? 0);
        $cantidad = max(1, $cantidad);

        $total += $cantidad;

        if ((int) ($producto['preparado'] ?? 0) === 1) {
            $listos += $cantidad;
        }
    }

    return [
        'total' => $total,
        'listos' => $listos,
    ];
}
?>