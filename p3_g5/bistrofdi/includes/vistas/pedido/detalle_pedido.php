<?php
declare(strict_types=1);

/**
 * Vista para mostrar el ticket detallado de un pedido.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('cliente', 'gerente', 'camarero');

$idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$idClienteContexto = filter_input(INPUT_GET, 'id_cliente', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$idUsuario = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$rolUsuario = $_SESSION['rol'] ?? null;
$esPersonal = in_array($rolUsuario, ['gerente', 'camarero'], true);

if ($idPedido === false || $idPedido === null) {
    header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
    exit();
}

if ($idUsuario === false || $idUsuario === null) {
    $_SESSION['mensaje_error'] = 'Sesión no válida.';
    header('Location: ' . BASE_URL . '/includes/vistas/auth/login.php');
    exit();
}

$controller = PedidoController::getInstance();
$pedido = $controller->verPedido((int) $idPedido);

if ($pedido === null || $pedido === false) {
    $_SESSION['mensaje_error'] = 'Acceso denegado o pedido no encontrado.';
    header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
    exit();
}

$clienteIdPedido = obtenerClienteIdPedido($pedido);

if (!$esPersonal && $clienteIdPedido !== (int) $idUsuario) {
    $_SESSION['mensaje_error'] = 'Acceso denegado o pedido no encontrado.';
    header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
    exit();
}

$lineasPedido = $controller->obtenerProductosDePedido((int) $idPedido);

$tituloPagina = 'Bistró FDI - Pedido';
$bodyClass = 'f0-body';

$numeroPedido = obtenerDatoPedido($pedido, 'numero_pedido', 'getNumeroPedido');
$idPedidoMostrar = obtenerDatoPedido($pedido, 'id', 'getId');
$fechaPedido = obtenerDatoPedido($pedido, 'fecha', 'getFecha');
$tipoPedido = obtenerDatoPedido($pedido, 'tipo', 'getTipo');
$estadoPedido = obtenerDatoPedido($pedido, 'estado', 'getEstado');
$totalPedido = obtenerDatoPedido($pedido, 'total', 'getTotal');

$numeroMostrar = $numeroPedido !== null ? (string) $numeroPedido : (string) $idPedidoMostrar;
$fechaFormateada = '';

if (is_string($fechaPedido) && $fechaPedido !== '') {
    $timestamp = strtotime($fechaPedido);
    if ($timestamp !== false) {
        $fechaFormateada = date('d/m/Y H:i', $timestamp);
    }
}

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        <h1>Detalle del <span>Pedido</span></h1>
        <div class="divisor"></div>

        <div class="mensaje-sesion">
            <div>
                <div>
                    <h3>Ticket #<?= htmlspecialchars($numeroMostrar) ?></h3>
                    <p>
                        <?= htmlspecialchars($fechaFormateada) ?> |
                        <strong><?= htmlspecialchars((string) $tipoPedido) ?></strong>
                    </p>
                </div>
                <div>
                    <span><?= htmlspecialchars((string) $estadoPedido) ?></span>
                </div>
            </div>

            <table class="tabla-pedidos tabla-detalle-pedido-movil">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio Ud.</th>
                        <th>Cant.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lineasPedido as $linea):
                        $precioBase = (float) ($linea['precio_base'] ?? 0);
                        $iva = (int) ($linea['iva'] ?? 0);
                        $cantidad = (int) ($linea['cantidad'] ?? 0);
                        $nombre = (string) ($linea['nombre'] ?? '');

                        $precioConIva = $precioBase * (1 + ($iva / 100));
                        $subtotalLinea = $precioConIva * $cantidad;
                    ?>
                    <tr>
                        <td data-label="Producto"><strong><?= htmlspecialchars($nombre) ?></strong></td>
                        <td data-label="Precio Ud."><?= number_format($precioConIva, 2) ?> €</td>
                        <td data-label="Cant."><?= $cantidad ?></td>
                        <td data-label="Subtotal"><strong><?= number_format($subtotalLinea, 2) ?> €</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="modal-footer">
                <h3>Total:</h3>
                <div class="precio-modal">
                    <?= number_format((float) $totalPedido, 2) ?> €
                </div>
            </div>

            <div class="contenedor-botones-index">
                <?php if ($esPersonal && $idClienteContexto !== false && $idClienteContexto !== null): ?>
                    <a href="<?= BASE_URL ?>/includes/vistas/pedido/mis_pedidos.php?id_cliente=<?= urlencode((string) $idClienteContexto) ?>" class="btn-login">Volver a Pedidos del Usuario</a>
                <?php elseif ($esPersonal): ?>
                    <a href="<?= BASE_URL ?>/includes/vistas/camarero/panel_camarero.php" class="btn-login">Volver al Panel</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/includes/vistas/pedido/mis_pedidos.php" class="btn-login">Volver a Mis Pedidos</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';

/**
 * Obtiene cliente_id de un pedido que puede venir como array o DTO.
 */
function obtenerClienteIdPedido($pedido): ?int
{
    if (is_array($pedido)) {
        $valor = $pedido['cliente_id'] ?? null;
        return filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
    }

    if (is_object($pedido) && method_exists($pedido, 'getClienteId')) {
        $valor = $pedido->getClienteId();
        return is_int($valor) ? $valor : null;
    }

    return null;
}

/**
 * Obtiene un dato del pedido que puede venir como array o DTO.
 */
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
?>