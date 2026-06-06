<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('cliente');

$idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$idUsuario = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idPedido === false || $idPedido === null) {
    $_SESSION['mensaje_error'] = 'Pedido no válido.';
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
    $_SESSION['mensaje_error'] = 'Pedido no encontrado.';
    header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
    exit();
}

$clienteIdPedido = obtenerClienteIdPedido($pedido);

if ($clienteIdPedido === null || $clienteIdPedido !== (int) $idUsuario) {
    $_SESSION['mensaje_error'] = 'Acceso denegado.';
    header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
    exit();
}

$numeroPedido = obtenerDatoPedido($pedido, 'numero_pedido', 'getNumeroPedido');
$estadoPedido = obtenerDatoPedido($pedido, 'estado', 'getEstado');
$tipoPedido = obtenerDatoPedido($pedido, 'tipo', 'getTipo');

$tituloPagina = 'Confirmación - Bistro FDI';
$bodyClass = 'f0-body';

ob_start();
?>
<section class="contenedor-principal">
    <h1>Pedido confirmado</h1>

    <p><span>Número de pedido:</span> <?= htmlspecialchars((string) $numeroPedido) ?></p>
    <p><span>Estado actual:</span> <?= htmlspecialchars((string) $estadoPedido) ?></p>
    <p><span>Tipo:</span> <?= htmlspecialchars((string) $tipoPedido) ?></p>

    <p><a href="<?= BASE_URL ?>/index.php">Volver al inicio</a></p>
    <p><a href="<?= BASE_URL ?>/includes/vistas/pedido/mis_pedidos.php">Consultar estado de mis pedidos</a></p>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../partials/plantilla.php';

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