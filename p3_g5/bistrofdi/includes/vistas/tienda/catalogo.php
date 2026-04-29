<?php
declare(strict_types=1);

/**
 * Catálogo de productos para añadir a un pedido.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../integracion/ProductoDAO.php';
require_once __DIR__ . '/../../integracion/OfertasDAO.php';

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
        header('Location: ../pedido/pedido_inicio.php');
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

$ofertaDAO = new OfertaDAO(Application::getInstance()->conexionBd());
$ofertasDisponibles = $ofertaDAO->obtenerOfertasActivas();

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
            <a href="../tienda/carrito.php" class="btn-admin">
                Ver mi carrito
                <?php if ($itemsEnCarrito > 0): ?>
                    <span class="badge-carrito"><?= (int) $itemsEnCarrito ?></span>
                <?php endif; ?>
            </a>

            <a href="../pedido/pedido_inicio.php" class="btn-admin btn-cancelar">
                Cambiar tipo de pedido
            </a>
			<button type="button" class="btn-admin" onclick="abrirModalOfertas()">
        		Ver Ofertas
			</button>
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

                                        $stock = (int) ($p->stock ?? 0);
                                        $categoriaTexto = (string) ($p->categoria_nombre ?? $categoria ?? 'Otros');
                                        $requiereCocina = (int) ($p->requiere_cocina ?? 1);

                                        /*
                                         * En la BD puedes guardar varias imágenes en el campo imagen separadas por coma:
                                         * hamburguesa1.jpg,hamburguesa2.jpg,hamburguesa3.jpg
                                         */
                                        $imagenes = !empty($p->imagen) ? explode(',', (string) $p->imagen) : [];

                                        $imagenesModal = [];

                                        foreach ($imagenes as $img) {
                                            $img = trim((string) $img);

                                            if ($img !== '') {
                                                $imagenesModal[] = '../../../img/productos/' . $img;
                                            }
                                        }

                                        if (empty($imagenesModal)) {
                                            $imagenesModal[] = '../../../img/productos/default.png';
                                        }

                                        $fotoPrincipal = $imagenesModal[0];

                                        $imagenesJson = htmlspecialchars(
                                            json_encode($imagenesModal, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );

                                        $nombreBusqueda = mb_strtolower($nombre, 'UTF-8');
                                    ?>

                                    <article
                                        class="tarjeta-producto"
                                        id="producto-<?= $productoId ?>"
                                        data-nombre="<?= htmlspecialchars($nombreBusqueda, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <div class="imagen-producto">
                                            <img
                                                src="<?= htmlspecialchars($fotoPrincipal, ENT_QUOTES, 'UTF-8') ?>"
                                                alt="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                                                class="img-catalogo"
                                                onerror="this.src='../../../img/productos/default.png'"
                                            >
                                        </div>

                                        <div class="info-producto">
                                            <h4><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></h4>
                                            <p><?= number_format($precioConIva, 2) ?> €</p>

                                            <button
                                                type="button"
                                                class="btn-abrir-modal"
                                                data-nombre="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
                                                data-descripcion="<?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>"
                                                data-precio="<?= htmlspecialchars(number_format($precioConIva, 2), ENT_QUOTES, 'UTF-8') ?>"
                                                data-precio-base="<?= htmlspecialchars(number_format($precioBase, 2), ENT_QUOTES, 'UTF-8') ?>"
                                                data-stock="<?= htmlspecialchars((string) $stock, ENT_QUOTES, 'UTF-8') ?>"
                                                data-iva="<?= htmlspecialchars((string) $iva, ENT_QUOTES, 'UTF-8') ?>"
                                                data-categoria="<?= htmlspecialchars($categoriaTexto, ENT_QUOTES, 'UTF-8') ?>"
                                                data-requiere-cocina="<?= htmlspecialchars((string) $requiereCocina, ENT_QUOTES, 'UTF-8') ?>"
                                                data-imagenes="<?= $imagenesJson ?>"
                                            >
                                                Ver producto
                                            </button>
                                        </div>

                                        <form
                                            action="../../acciones/carrito/anadir_producto.php"
                                            method="POST"
                                            class="form-anadir-carrito"
                                            data-producto-id="<?= $productoId ?>"
                                        >
                                            <input type="hidden" name="accion" value="agregar">
                                            <input type="hidden" name="id_producto" value="<?= $productoId ?>">

                                            <input
                                                type="number"
                                                name="cantidad"
                                                value="1"
                                                min="1"
                                                max="20"
                                                required
                                            >

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

        <div id="modalGaleria" class="modal-galeria"></div>

        <p id="modalDescripcion">Descripción del producto irá aquí...</p>

        <div class="modal-detalles-extra">
            <p><strong>Categoría:</strong> <span id="modalCategoria">-</span></p>
            <p><strong>IVA:</strong> <span id="modalIva">-</span>%</p>
        </div>


        <div class="modal-footer">
            <span class="precio-modal">
                Precio final: <span id="modalPrecio">0.00</span> €
            </span>
        </div>
    </div>
</div>

<div id="modalOfertas" class="modal-fondo">
    <div class="modal-contenido">
        <span class="modal-cerrar" onclick="cerrarModalOfertas()">&times;</span>
        
        <h2>Detalles de las Promociones</h2>
        
        <div id="lista-detalles-ofertas">
            <?php if (!empty($ofertasDisponibles)): ?>
                <?php foreach($ofertasDisponibles as $of): ?>
                    <div class="detalle-oferta-item">
                        <h4><?= htmlspecialchars($of->getNombre(), ENT_QUOTES, 'UTF-8') ?></h4>
                        <p><?= htmlspecialchars($of->getDescripcion(), ENT_QUOTES, 'UTF-8') ?></p>
                        
                        <ul>
                            <?php 
                            // Ojo: Usamos tu $ofertaDAO que ya está instanciado arriba en carrito.php
                            $prods = $ofertaDAO->obtenerProductosDeOferta($of->getId());
                            foreach($prods as $p): ?>
                                <li><?= (int)$p['cantidad'] ?>x <?= htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay promociones activas en este momento.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../../../js/modal.js"></script>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';
?>