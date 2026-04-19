<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/PedidoController.php';

exigirLogin();

$idPedido = (int)($_GET['id'] ?? 0);
$controller = PedidoController::getInstance();
$pedido = $controller->verPedido($idPedido);

if (!$pedido || (int)$pedido['cliente_id'] !== (int)$_SESSION['id_usuario']) {
    die('Pedido no encontrado o sin permisos.');
}

ob_start();
?>
<section class="contenedor-principal">
    <h1>Pedido confirmado</h1>

    <p><strong>Número de pedido:</strong> <?= (int)$pedido['numero_pedido'] ?></p>
    <p><strong>Estado actual:</strong> <?= htmlspecialchars($pedido['estado']) ?></p>
    <p><strong>Tipo:</strong> <?= htmlspecialchars($pedido['tipo']) ?></p>

    <p><a href="index.php">Volver al inicio</a></p>
    <p><a href="mis_pedidos.php">Consultar estado de mis pedidos</a></p>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Confirmación - Bistro FDI';
require __DIR__ . '/../partials/plantilla.php';