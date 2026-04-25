<?php
declare(strict_types=1);

/**
 * Catálogo de productos para añadir a un pedido.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../integracion/ProductoDAO.php';

exigirLogin();
exigirRol('cliente');

$tiposPermitidos = ['Local', 'Llevar'];

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoRecibido = trim((string) (filter_input(INPUT_POST, 'tipo', FILTER_UNSAFE_RAW) ?? ''));

    if (in_array($tipoRecibido, $tiposPermitidos, true)) {
        $_SESSION['tipoPedido'] = $tipoRecibido;
    } else {
        $_SESSION['mensaje_error'] = 'El tipo de pedido seleccionado no es válido.';
        header('Location: ' . BASE_URL . '/includes/vistas/pedido/pedido_inicio.php');
        exit();
    }
}

$tipoPedido = $_SESSION['tipoPedido'] ?? 'Local';
if (!in_array($tipoPedido, $tiposPermitidos, true)) {
    $tipoPedido = 'Local';
    $_SESSION['tipoPedido'] = $tipoPedido;
}

$productoDAO = new ProductoDAO(Application::getInstance()->conexionBd());
$productosDTO = $productoDAO->listarOfertados();

$menuAgrupado = [];
foreach ($productosDTO as $producto) {
    $categoria = $producto->categoria_nombre ?? 'Otros';
    if (!is_string($categoria) || trim($categoria) === '') {
        $categoria = 'Otros';
    }
    $menuAgrupado[$categoria][] = $producto;
}

$itemsEnCarrito = 0;
if (!empty($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    $itemsEnCarrito = array_sum(array_map('intval', $_SESSION['carrito']));
}

$tituloPagina = 'Bistró FDI - Catálogo';
$bodyClass = 'f0-body';

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        <h1>Nuestro <span>Catálogo</span></h1>
        <p class="lema">
            Tipo de Pedido: <strong><?= htmlspecialchars($tipoPedido, ENT_QUOTES, 'UTF-8') ?></strong>
        </p>

        <div class="divisor"></div>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alerta alerta-error">
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

        <div>
            <input type="text" id="buscadorProductos" placeholder="Buscar producto...">
        </div>

        <div class="contenedor-botones-index">
            <a href="<?= BASE_URL ?>/includes/vistas/tienda/carrito.php" class="btn-admin">
                Ver mi carrito
                <?php if ($itemsEnCarrito > 0): ?>
                    <span class="badge-carrito"><?= (int) $itemsEnCarrito ?></span>
                <?php endif; ?>
            </a>

            <a href="<?= BASE_URL ?>/includes/vistas/pedido/pedido_inicio.php" class="btn-admin btn-cancelar">
                Cambiar tipo de pedido
            </a>
        </div>

        <div class="mensaje-sesion">
            <?php if (empty($menuAgrupado)): ?>
                <p>Lo sentimos, no hay productos disponibles en este momento.</p>
            <?php else: ?>
                <div class="catalogo-grid">
                    <?php foreach ($menuAgrupado as $categoria => $productosCat): ?>
                        <div class="bloque-categoria">
                            <h3><?= htmlspecialchars((string) $categoria, ENT_QUOTES, 'UTF-8') ?></h3>

                            <div class="lista-productos">
                                <?php foreach ($productosCat as $p): ?>
                                    <?php
                                        $productoId = (int) ($p->id ?? 0);
                                        $nombre = (string) ($p->nombre ?? '');
                                        $descripcion = (string) ($p->descripcion ?? 'Este producto no tiene descripción adicional.');
                                        $precioBase = (float) ($p->precio ?? 0);
                                        $iva = (int) ($p->iva ?? 21);
                                        $precioConIva = $precioBase * (1 + ($iva / 100));

                                        $imagenes = !empty($p->imagen) ? explode(',', (string) $p->imagen) : [];
                                        $fotoPrincipal = (!empty($imagenes) && trim((string) $imagenes[0]) !== '')
                                            ? trim((string) $imagenes[0])
                                            : 'default.png';

                                        $nombreBusqueda = mb_strtolower($nombre, 'UTF-8');
                                    ?>

                                    <article
                                        class="tarjeta-producto"
                                        id="producto-<?= $productoId ?>"
                                        data-nombre="<?= htmlspecialchars($nombreBusqueda, ENT_QUOTES, 'UTF-8') ?>">

                                        <div class="imagen-producto">
                                            <img
                                                src="<?= BASE_URL ?>/img/productos/<?= htmlspecialchars($fotoPrincipal, ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                                                class="img-catalogo"
                                                onerror="this.src='<?= BASE_URL ?>/img/productos/default.png'">
                                        </div>

                                        <div class="info-producto">
                                            <h4><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></h4>
                                            <p><?= number_format($precioConIva, 2) ?> €</p>

                                            <button
                                                type="button"
                                                class="btn-abrir-modal"
                                                data-nombre="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                                                data-descripcion="<?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>"
                                                data-precio="<?= htmlspecialchars(number_format($precioConIva, 2), ENT_QUOTES, 'UTF-8') ?>">
                                                Ver producto
                                            </button>
                                        </div>

                                        <form
                                            action="<?= BASE_URL ?>/includes/acciones/carrito/anadir_producto.php"
                                            method="POST"
                                            class="form-anadir-carrito"
                                            data-producto-id="<?= $productoId ?>">

                                            <input type="hidden" name="accion" value="agregar">
                                            <input type="hidden" name="id_producto" value="<?= $productoId ?>">
                                            <input type="number" name="cantidad" value="1" min="1" max="20" required>

                                            <div class="wrap-boton-anadir">
                                                <button type="submit" class="btn-login">Añadir</button>
                                                <span class="mensaje-anadido" id="mensaje-<?= $productoId ?>">✓ Añadido</span>
                                            </div>
                                        </form>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<div id="modalDetalle" class="modal-fondo">
    <div class="modal-contenido">
        <span id="cerrarModal" class="modal-cerrar">&times;</span>

        <h2 id="modalNombre">Nombre Producto</h2>
        <div class="divisor"></div>

        <p id="modalDescripcion">Descripción del producto irá aquí...</p>

        <div class="modal-footer">
            <span class="precio-modal">
                Precio: <span id="modalPrecio">0.00</span> €
            </span>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/catalogo.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.form-anadir-carrito');

    forms.forEach(form => {
        form.addEventListener('submit', function () {
            const productoId = this.dataset.productoId;
            if (productoId) {
                sessionStorage.setItem('ultimoProductoAnadido', productoId);
            }
        });
    });

    const ultimoProducto = sessionStorage.getItem('ultimoProductoAnadido');
    if (ultimoProducto) {
        const tarjeta = document.getElementById('producto-' + ultimoProducto);
        const mensaje = document.getElementById('mensaje-' + ultimoProducto);

        if (tarjeta) {
            tarjeta.scrollIntoView({ behavior: 'auto', block: 'center' });
        }

        if (mensaje) {
            mensaje.classList.add('activo');

            setTimeout(() => {
                mensaje.classList.remove('activo');
                sessionStorage.removeItem('ultimoProductoAnadido');
            }, 1500);
        } else {
            sessionStorage.removeItem('ultimoProductoAnadido');
        }
    }
});
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';
?>