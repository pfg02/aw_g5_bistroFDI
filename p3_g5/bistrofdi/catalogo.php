<?php
	/**
	 * Catálogo de productos para añadir a un pedido.
	 */

	require_once __DIR__ . '/includes/sesion.php';
	require_once __DIR__ . '/includes/integracion/ProductoDAO.php';

	// Exigimos inicio de sesión y rol de cliente
	exigirLogin();
	exigirRol('cliente');

	if (!isset($_SESSION['carrito'])) {
		$_SESSION['carrito'] = [];
	}

	// Si venimos de pedido_inicio.php, guardamos si es Local o Llevar
	if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tipo"])) {
		$_SESSION["tipoPedido"] = $_POST["tipo"];
	}
	$tipo_pedido = $_SESSION['tipoPedido'] ?? 'Local';

	// ARREGLADO: Inyectamos la conexión global $db al DAO
	global $db;
	$productoDAO = new ProductoDAO($db);
	$productosDTO = $productoDAO->listarOfertados();

	// Agrupar los objetos ProductoDTO por su nombre de categoría
	$menu_agrupado = [];
	foreach ($productosDTO as $producto) {
		$categoria = $producto->categoria_nombre ?? 'Otros';
		$menu_agrupado[$categoria][] = $producto;
	}

	// Calculamos cuántos artículos hay en el carrito ahora mismo
	$itemsEnCarrito = 0;
	if (!empty($_SESSION['carrito'])) {
		$itemsEnCarrito = array_sum($_SESSION['carrito']); 
	}

	$tituloPagina = 'Bistró FDI - Catálogo';
	$bodyClass    = 'f0-body';

	ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        
        <h1>Nuestro <span>Catálogo</span></h1>
        <p class="lema">
            Tipo de Pedido: <strong><?= htmlspecialchars($tipo_pedido) ?></strong>
        </p>
        
        <div class="divisor"></div>

        <div>   
            <input type="text" id="buscadorProductos" placeholder="Buscar producto...">
        </div>

        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alerta alerta-exito">
                <?= htmlspecialchars($_SESSION['mensaje_exito']); ?>
                <?php unset($_SESSION['mensaje_exito']); ?>
            </div>
        <?php endif; ?>

        <div class="contenedor-botones-index">
            <a href="carrito.php" class="btn-admin">Ver mi carrito
                <?php if ($itemsEnCarrito > 0): ?>
                    <span class="badge-carrito"><?= $itemsEnCarrito ?></span>
                <?php endif; ?>
            </a>
            <a href="pedido_inicio.php" class="btn-admin btn-cancelar">Cambiar tipo de pedido</a>
        </div>
        
        <div class="mensaje-sesion">
            
            <?php if (empty($menu_agrupado)): ?>
                <p>Lo sentimos, no hay productos disponibles en este momento.</p>
            <?php else: ?>
                
                <div class="catalogo-grid">
                    <?php foreach ($menu_agrupado as $categoria => $productos_cat): ?>
                        
                        <div class="bloque-categoria">
                            <h3><?= htmlspecialchars($categoria) ?></h3>
                            
                            <div class="lista-productos">
                                <?php foreach ($productos_cat as $p): 
                                    $precio_base = $p->precio ?? 0;
                                    $iva = $p->iva ?? 21;
                                    $precio_con_iva = $precio_base * (1 + ($iva / 100));
                                    $fotos = !empty($p->imagen) ? explode(',', $p->imagen) : [];
                                    $fotoPrincipal = !empty($fotos) && trim($fotos[0]) !== '' ? trim($fotos[0]) : 'default.png';
                                ?>
                                    <article class="tarjeta-producto" data-nombre="<?= strtolower(htmlspecialchars($p->nombre)) ?>">
                                        
                                        <div class="imagen-producto">
                                            <img src="img/productos/<?= htmlspecialchars($fotoPrincipal) ?>"
                                                 alt="<?= htmlspecialchars($p->nombre) ?>"
                                                 class="img-catalogo"
                                                 onerror="this.src='img/productos/default.png'">
                                        </div>

										<div class="info-producto">
                                            <h4><?= htmlspecialchars($p->nombre) ?></h4>
                                            <p><?= number_format($precio_con_iva, 2) ?> €</p>
                                            
                                            <button type="button" class="btn-abrir-modal" 
                                                    data-nombre="<?= htmlspecialchars($p->nombre) ?>"
                                                    data-descripcion="<?= htmlspecialchars($p->descripcion ?? 'Este producto no tiene descripción adicional.') ?>"
                                                    data-precio="<?= number_format($precio_con_iva, 2) ?>">
                                                Ver producto
                                            </button>
                                        </div>
                                        
                                        <form action="anadir_producto.php" method="POST" class="form-anadir-carrito">
                                            <input type="hidden" name="accion" value="agregar">
                                            <input type="hidden" name="id_producto" value="<?= $p->id ?>"> 
                                            <input type="number" name="cantidad" value="1" min="1" max="20">
                                            <button type="submit" class="btn-login">Añadir</button>
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

<script src="js/catalogo.js"></script>

<?php
    $contenidoPrincipal = ob_get_clean();
    require_once __DIR__ . '/includes/vistas/comun/plantilla.php';
?> 