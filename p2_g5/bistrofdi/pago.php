<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/PedidoController.php';

exigirLogin();

$idPedido = (int)($_GET['id'] ?? 0);
$controller = PedidoController::getInstance();
$pedido = $controller->verPedido($idPedido);
$lineas = $controller->verLineasPedido($idPedido);

if (!$pedido || (int)$pedido['cliente_id'] !== (int)$_SESSION['id_usuario']) {
    die('Pedido no encontrado o sin permisos.');
}

ob_start();
?>
<section class="contenedor-principal">
    <h1>Pago del pedido</h1>

    <p><strong>Nº pedido:</strong> <?= (int)$pedido['numero_pedido'] ?></p>
    <p><strong>Tipo:</strong> <?= htmlspecialchars($pedido['tipo']) ?></p>
    <p><strong>Estado:</strong> <?= htmlspecialchars($pedido['estado']) ?></p>
    <p><strong>Total:</strong> <?= number_format($pedido['total'], 2) ?> €</p>

    <h2>Detalle</h2>
    <ul>
        <?php foreach ($lineas as $linea): ?>
            <li>
                <?= htmlspecialchars($linea['nombre']) ?> x <?= (int)$linea['cantidad'] ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Pagar con tarjeta</h2>
    <form method="post" action="procesar_pago.php">
        <input type="hidden" name="id_pedido" value="<?= (int)$idPedido ?>">

        <label>Titular:
            <input type="text" name="titular" required>
        </label><br><br>

        <label>Número de tarjeta:
            <input type="text" name="tarjeta" pattern="[0-9]{16}" maxlength="16" required>
        </label><br><br>

        <label>Caducidad:
            <input type="text" name="caducidad" placeholder="MM/AA" required>
        </label><br><br>

        <label>CVV:
            <input type="text" name="cvv" pattern="[0-9]{3}" maxlength="3" required>
        </label><br><br>

        <button type="submit">Confirmar pago</button>
    </form>

    <hr>

    <h2>Pagar al camarero</h2>
    <p>El pedido quedará en estado <strong>Recibido</strong> hasta que un camarero confirme el cobro.</p>
    <p><a href="confirmacion.php?id=<?= (int)$idPedido ?>">Continuar</a></p>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Pago - Bistro FDI';
require __DIR__ . '/includes/vistas/comun/plantilla.php';