<?php
declare(strict_types=1);

/**
 * Vista del carrito de un pedido en curso.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../integracion/ProductoDAO.php';

exigirLogin();
exigirRol('cliente');

$tipoPedido = $_SESSION['tipoPedido'] ?? null;
$tiposPermitidos = ['Local', 'Llevar'];

if (!is_string($tipoPedido) || !in_array($tipoPedido, $tiposPermitidos, true)) {
    $_SESSION['mensaje_error'] = 'Debes elegir primero el tipo de pedido.';
    header('Location: ../pedido/pedido_inicio.php');
    exit();
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

/*
 * Procesar cambio de cantidad desde esta misma vista.
 */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['accion'])
    && $_POST['accion'] === 'modificar_cantidad'
) {
    $productoId = filter_input(INPUT_POST, 'productoId', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    if ($productoId === false || $productoId === null || $cantidad === false || $cantidad === null) {
        $_SESSION['mensaje_error'] = 'Cantidad no válida.';
    } elseif (!isset($_SESSION['carrito'][(int) $productoId])) {
        $_SESSION['mensaje_error'] = 'El producto no existe en el carrito.';
    } else {
        $_SESSION['carrito'][(int) $productoId] = (int) $cantidad;
        $_SESSION['mensaje_exito'] = 'Cantidad modificada correctamente.';
    }

    header('Location: carrito.php');
    exit();
}

$carrito = $_SESSION['carrito'] ?? [];

if (!is_array($carrito)) {
    $carrito = [];
}

$productoDAO = new ProductoDAO(Application::getInstance()->conexionBd());

$productos = [];
$total = 0.0;

if (!empty($carrito)) {
    foreach ($carrito as $idProducto => $cantidad) {
        $idProductoValidado = filter_var($idProducto, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        $cantidadValidada = filter_var($cantidad, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        if ($idProductoValidado === false || $cantidadValidada === false) {
            continue;
        }

        $productoDTO = $productoDAO->obtenerPorId((int) $idProductoValidado);

        if ($productoDTO) {
            $productos[(int) $idProductoValidado] = [
                'nombre' => $productoDTO->nombre,
                'precio_base' => $productoDTO->precio,
                'iva' => $productoDTO->iva ?? 21,
            ];
        }
    }
}

$tituloPagina = 'Bistró FDI - Mi Carrito';
$bodyClass = 'f0-body';

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">

        <h1>Mi <span>Carrito</span></h1>
        <p class="lema">Revisa tu pedido (<?= htmlspecialchars($tipoPedido, ENT_QUOTES, 'UTF-8') ?>)</p>

        <div class="divisor"></div>

        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alerta alerta-exito">
                <?= htmlspecialchars($_SESSION['mensaje_exito'], ENT_QUOTES, 'UTF-8') ?>
                <?php unset($_SESSION['mensaje_exito']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alerta alerta-error">
                <?= htmlspecialchars($_SESSION['mensaje_error'], ENT_QUOTES, 'UTF-8') ?>
                <?php unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <div class="mensaje-sesion mensaje-sesion-ancho">

            <?php if (empty($carrito)): ?>
                <p>Tu carrito está vacío ahora mismo.</p>

                <div class="contenedor-botones-carrito">
                    <a href="catalogo.php" class="btn-login">
                        Volver al Catálogo
                    </a>
                </div>

            <?php else: ?>

                <table class="tabla-pedidos tabla-carrito-movil">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio (con IVA)</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Quitar</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($carrito as $productoId => $cantidad): ?>
                            <?php
                                $productoIdInt = filter_var($productoId, FILTER_VALIDATE_INT, [
                                    'options' => ['min_range' => 1]
                                ]);

                                $cantidadInt = filter_var($cantidad, FILTER_VALIDATE_INT, [
                                    'options' => ['min_range' => 1]
                                ]);

                                if (
                                    $productoIdInt === false
                                    || $cantidadInt === false
                                    || !isset($productos[(int) $productoIdInt])
                                ) {
                                    continue;
                                }

                                $producto = $productos[(int) $productoIdInt];
                                $precioBase = (float) $producto['precio_base'];
                                $porcentajeIva = (int) $producto['iva'];

                                $precioConIva = $precioBase + ($precioBase * ($porcentajeIva / 100));
                                $subtotal = $precioConIva * (int) $cantidadInt;
                                $total += $subtotal;
                            ?>

                            <tr>
                                <td data-label="Producto">
                                    <strong><?= htmlspecialchars((string) $producto['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>

                                <td data-label="Precio (con IVA)">
                                    <?= number_format($precioConIva, 2) ?> €
                                </td>

                                <td data-label="Cantidad">
                                    <form
                                        action="carrito.php"
                                        method="POST"
                                        class="form-cantidad-carrito"
                                    >
                                        <input type="hidden" name="accion" value="modificar_cantidad">
                                        <input type="hidden" name="productoId" value="<?= htmlspecialchars((string) $productoIdInt, ENT_QUOTES, 'UTF-8') ?>">

                                        <input
                                            type="number"
                                            name="cantidad"
                                            value="<?= (int) $cantidadInt ?>"
                                            min="1"
                                            class="input-cantidad-carrito"
                                        >

                                        <button type="submit" class="btn-accion">
                                            Actualizar
                                        </button>
                                    </form>
                                </td>

                                <td data-label="Subtotal">
                                    <strong><?= number_format($subtotal, 2) ?> €</strong>
                                </td>

                                <td data-label="Quitar">
                                    <form action="../../acciones/carrito/eliminar_del_carrito.php" method="POST">
                                        <input type="hidden" name="productoId" value="<?= htmlspecialchars((string) $productoIdInt, ENT_QUOTES, 'UTF-8') ?>">

                                        <button type="submit" class="btn-accion btn-peligro" title="Eliminar del carrito">
                                            x
                                        </button>
                                    </form>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="resumen-carrito">
                    <h3>Total a Pagar:</h3>
                    <div class="total-destacado"><?= number_format($total, 2) ?> €</div>
                </div>

                <div>
                    <h3>¿Cómo quieres abonar tu pedido?</h3>

                    <div class="botones-pago-horizontal">
                        <form action="../../acciones/pedido/confirmar_pedido.php" method="POST">
                            <input type="hidden" name="metodo_pago" value="tarjeta">

                            <button type="submit" class="btn-confirmar-compra">
                                Pagar con Tarjeta
                            </button>
                        </form>

                        <form action="../../acciones/pedido/confirmar_pedido.php" method="POST" class="form-confirmar">
                            <input type="hidden" name="metodo_pago" value="camarero">

                            <button type="submit" class="btn-confirmar-compra btn-camarero">
                                Solicitar cobro al personal
                            </button>
                        </form>
                    </div>
                </div>

            <?php endif; ?>

        </div>

        <?php if (!empty($carrito)): ?>
            <div class="contenedor-botones-carrito">
                <a href="catalogo.php" class="btn-admin">
                    Seguir comprando
                </a>

                <form action="../../acciones/pedido/cancelar_pedido.php" method="POST" class="form-inline-boton">
                    <input type="hidden" name="accion" value="vaciar_carrito">

                    <button type="submit" class="btn-login btn-peligro">
                        Vaciar Carrito
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';
?>