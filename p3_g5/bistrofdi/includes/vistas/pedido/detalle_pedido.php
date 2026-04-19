<?php
/**
 * Vista para mostrar el ticket detallado de un pedido.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();
exigirRol('cliente');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
    exit();
}

$id_pedido = (int)$_GET['id'];
$id_usuario = $_SESSION['id_usuario'];
$esGerente = isset($_SESSION['rol']) && ($_SESSION['rol'] === 'gerente' || $_SESSION['rol'] === 'camarero');
$idClienteContexto = isset($_GET['id_cliente']) ? (int) $_GET['id_cliente'] : null;

$controller = PedidoController::getInstance();
$pedido = $controller->verPedido($id_pedido);

// Si es cliente normal, el pedido tiene que ser suyo.
// Si es gerente/camarero, puede ver cualquier pedido.
if (!$pedido || (!$esGerente && $pedido['cliente_id'] != $id_usuario)) {
    $_SESSION['mensaje_error'] = 'Acceso denegado o pedido no encontrado.';

    if ($esGerente && $idClienteContexto) {
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php?id_cliente=' . urlencode((string)$idClienteContexto));
    } else {
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/mis_pedidos.php');
    }
    exit();
}

$lineas_pedido = $controller->obtenerProductosDePedido($id_pedido);

$tituloPagina = 'Bistró FDI - Pedido';
$bodyClass = 'f0-body';

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        
        <h1>Detalle del <span>Pedido</span></h1>
        <div class="divisor"></div>

        <div class="mensaje-sesion">
            
            <div>
                <div>
                    <h3>Ticket #<?= htmlspecialchars($pedido['numero_pedido'] ?? $pedido['id']) ?></h3>
                    <p>
                        <?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?> | <strong><?= htmlspecialchars($pedido['tipo']) ?></strong>
                    </p>
                </div>
                <div>
                    <span><?= htmlspecialchars($pedido['estado']) ?></span>
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
                    <?php foreach ($lineas_pedido as $linea): 
                        $precioConIva = $linea['precio_base'] * (1 + ($linea['iva'] / 100));
                        $subtotal_linea = $precioConIva * $linea['cantidad'];
                    ?>
                    <tr>
                        <td data-label="Producto"><strong><?= htmlspecialchars($linea['nombre']) ?></strong></td>
                        <td data-label="Precio Ud."><?= number_format($precioConIva, 2) ?> €</td>
                        <td data-label="Cant."><?= $linea['cantidad'] ?></td>
                        <td data-label="Subtotal"><strong><?= number_format($subtotal_linea, 2) ?> €</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="modal-footer">
                <h3>Total:</h3>
                <div class="precio-modal">
                    <?= number_format($pedido['total'], 2) ?> €
                </div>
            </div>

            <div class="contenedor-botones-index">
                <?php if ($esGerente && $idClienteContexto): ?>
                    <a href="<?= BASE_URL ?>/includes/vistas/pedido/mis_pedidos.php?id_cliente=<?= urlencode((string)$idClienteContexto) ?>" class="btn-login">Volver a Pedidos del Usuario</a>
                <?php elseif ($esGerente): ?>
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
?>