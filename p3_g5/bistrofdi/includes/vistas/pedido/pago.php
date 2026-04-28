<?php
declare(strict_types=1);

/**
 * Vista de la pasarela de pago para un pedido recién creado.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';
require_once __DIR__ . '/../../formularios/FormularioPago.php';

exigirLogin();
exigirRol('cliente');

$idPedido = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$idUsuario = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idPedido === false || $idPedido === null) {
    header('Location: ' . BASE_URL . '/index.php');
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
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$clienteIdPedido = obtenerClienteIdPedido($pedido);

if ($clienteIdPedido === null || $clienteIdPedido !== (int) $idUsuario) {
    $_SESSION['mensaje_error'] = 'Acceso denegado.';
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$formularioPago = new FormularioPago((int) $idPedido);

$tituloPagina = 'Pasarela de Pago - Bistró FDI';
$bodyClass = 'f0-body';

$numeroPedido = obtenerDatoPedido($pedido, 'numero_pedido', 'getNumeroPedido');
$idPedidoMostrar = obtenerDatoPedido($pedido, 'id', 'getId');
$tipoPedido = obtenerDatoPedido($pedido, 'tipo', 'getTipo');
$totalPedido = obtenerDatoPedido($pedido, 'total', 'getTotal');

$numeroMostrar = $numeroPedido !== null ? (string) $numeroPedido : (string) $idPedidoMostrar;

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">

        <h1>Pasarela de <span>Pago</span></h1>
        <p class="lema">Elige cómo quieres abonar tu pedido</p>
        <div class="divisor"></div>

        <div class="mensaje-sesion contenedor-pago">

            <div class="resumen-pago-destacado">
                <p class="texto-resumen"><strong>Ticket:</strong> #<?= htmlspecialchars($numeroMostrar) ?></p>
                <p class="texto-resumen"><strong>Modalidad:</strong> <?= htmlspecialchars((string) $tipoPedido) ?></p>
                <div class="divisor-pago"></div>
                <p class="total-pago-container">
                    Total a pagar: <strong class="total-pago-destacado"><?= number_format((float) $totalPedido, 2) ?> €</strong>
                </p>
            </div>

            <div class="opciones-pago-container">

                <div class="caja-metodo-pago">
                    <h3 class="titulo-metodo">Pagar ahora con Tarjeta</h3>

                    <?php if (isset($_SESSION['mensaje_error'])): ?>
                        <div class="alerta alerta-error">
                            <?= htmlspecialchars($_SESSION['mensaje_error'], ENT_QUOTES, 'UTF-8') ?>
                            <?php unset($_SESSION['mensaje_error']); ?>
                        </div>
                    <?php endif; ?>

                    <?= $formularioPago->gestiona() ?>
                </div>

                <div class="caja-metodo-pago">
                    <form action="<?= BASE_URL ?>/includes/acciones/pedido/cancelar_pedido.php" method="POST" class="form-confirmar">
                        <input type="hidden" name="id_pedido" value="<?= htmlspecialchars((string) $idPedido) ?>">
                        <button type="submit" class="btn-admin">Cancelar Pedido</button>
                    </form>
                </div>

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