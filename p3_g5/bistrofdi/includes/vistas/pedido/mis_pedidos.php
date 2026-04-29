<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('cliente', 'gerente');

$controller = PedidoController::getInstance();

$rolUsuario = $_SESSION['rol'] ?? null;
$esGerente = ($rolUsuario === 'gerente');

$idUsuarioSesion = filter_var($_SESSION['id_usuario'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$idClienteParam = filter_input(INPUT_GET, 'id_cliente', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($idUsuarioSesion === false || $idUsuarioSesion === null) {
    $_SESSION['mensaje_error'] = 'Sesión no válida.';
    header('Location: ' . BASE_URL . '/includes/vistas/auth/login.php');
    exit();
}

/*
 * Si es gerente y no viene un id_cliente concreto, no debe ver "mis pedidos":
 * lo mandamos a la gestión de usuarios.
 */
if ($esGerente && ($idClienteParam === false || $idClienteParam === null)) {
    header('Location: ' . BASE_URL . '/includes/vistas/admin/gestionarUsuarios.php');
    exit();
}

if ($esGerente) {
    $idCliente = (int) $idClienteParam;
    $subtituloListado = 'Historial de pedidos del usuario seleccionado';
} else {
    $idCliente = (int) $idUsuarioSesion;
    $subtituloListado = 'Historial de tus compras';
}

$historialPedidos = $controller->verPedidosCliente($idCliente);

$tituloPagina = 'Mis Pedidos - Bistró FDI';
$bodyClass = 'f0-body';

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">

        <h1><?= $esGerente ? 'Pedidos del <span>Usuario</span>' : 'Mis <span>Pedidos</span>' ?></h1>
        <p class="lema"><?= htmlspecialchars($subtituloListado, ENT_QUOTES, 'UTF-8') ?></p>

        <div class="divisor"></div>

        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="error-msg">
                <?= htmlspecialchars($_SESSION['mensaje_exito'], ENT_QUOTES, 'UTF-8') ?>
                <?php unset($_SESSION['mensaje_exito']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="error-msg">
                <?= htmlspecialchars($_SESSION['mensaje_error'], ENT_QUOTES, 'UTF-8') ?>
                <?php unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <div class="mensaje-sesion">
            <?php if (empty($historialPedidos)): ?>
                <p>No hay pedidos para mostrar.</p>

                <?php if (!$esGerente): ?>
                    <a href="<?= BASE_URL ?>/includes/vistas/pedido/pedido_inicio.php" class="btn-login">
                        Hacer un Pedido
                    </a>
                <?php endif; ?>

            <?php else: ?>
                <table class="tabla-pedidos tabla-mis-pedidos-movil">
                    <thead>
                        <tr>
                            <th>Nº Pedido</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($historialPedidos as $pedido): ?>
                            <?php
                                $pedidoId = obtenerDatoPedido($pedido, 'id', 'getId');
                                $numeroPedido = obtenerDatoPedido($pedido, 'numero_pedido', 'getNumeroPedido');
                                $fechaPedido = obtenerDatoPedido($pedido, 'fecha', 'getFecha');
                                $tipoPedido = obtenerDatoPedido($pedido, 'tipo', 'getTipo');
                                $estadoPedido = obtenerDatoPedido($pedido, 'estado', 'getEstado');
                                $totalPedido = obtenerDatoPedido($pedido, 'total', 'getTotal');

                                $numeroMostrar = $numeroPedido !== null ? (string) $numeroPedido : (string) $pedidoId;

                                $fechaFormateada = '';
                                if (is_string($fechaPedido) && $fechaPedido !== '') {
                                    $timestamp = strtotime($fechaPedido);
                                    if ($timestamp !== false) {
                                        $fechaFormateada = date('d/m/Y H:i', $timestamp);
                                    }
                                }

                                /*
                                 * Regla oficial:
                                 * Solo se puede cancelar si está en Nuevo o Recibido.
                                 * Si ya está pagado o avanzado, no se muestra el botón.
                                 */
                                $estadoPedidoNormalizado = trim((string) $estadoPedido);

                                $sePuedeCancelar = !$esGerente
                                    && in_array($estadoPedidoNormalizado, ['Nuevo', 'Recibido'], true);
                            ?>

                            <tr>
                                <td data-label="Nº Pedido">
                                    <strong>#<?= htmlspecialchars($numeroMostrar, ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>

                                <td data-label="Fecha">
                                    <?= htmlspecialchars($fechaFormateada, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td data-label="Tipo">
                                    <?= htmlspecialchars((string) $tipoPedido, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td data-label="Estado">
                                    <span class="badge-success">
                                        <?= htmlspecialchars((string) $estadoPedido, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <td data-label="Total">
                                    <strong><?= number_format((float) $totalPedido, 2) ?> €</strong>
                                </td>

                                <td data-label="Acciones">
                                    <?php if ($esGerente): ?>
                                        <a href="<?= BASE_URL ?>/includes/vistas/pedido/detalle_pedido.php?id=<?= urlencode((string) $pedidoId) ?>&id_cliente=<?= urlencode((string) $idCliente) ?>">
                                            Ver Detalle
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>/includes/vistas/pedido/detalle_pedido.php?id=<?= urlencode((string) $pedidoId) ?>">
                                            Ver Detalle
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($sePuedeCancelar): ?>
                                        <br>
                                        <form action="<?= BASE_URL ?>/includes/acciones/pedido/cancelar_pedido.php" method="POST" class="form-actualizar-estado">
                                            <input type="hidden" name="id_pedido" value="<?= htmlspecialchars((string) $pedidoId, ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn-accion btn-peligro">
                                                Cancelar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="contenedor-volver">
            <?php if ($esGerente): ?>
                <a href="<?= BASE_URL ?>/includes/vistas/admin/gestionarUsuarios.php" class="btn-login">
                    Volver a Usuarios
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/index.php" class="btn-login">
                    Volver al Inicio
                </a>
            <?php endif; ?>
        </div>

    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';

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