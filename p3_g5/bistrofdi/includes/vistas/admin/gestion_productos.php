<?php
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/ProductoService.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header("Location: ../auth/login.php");
    exit();
}

$service = new ProductoService($db);
$productos = $service->listarTodos();
$msg = $_GET['msg'] ?? '';

$tituloPagina = 'Gestión de Catálogo - Bistró FDI';
$bodyClass = 'admin-panel';
$extraHead = '<link rel="stylesheet" href="' . BASE_URL . '/css/estilos.css">';

ob_start();
?>

<div class="container-gestion">
    <header class="header-gestion">
        <h1>Catálogo de Productos</h1>
        <a href="editar_producto.php" class="btn-primario">+ Nuevo Producto</a>
    </header>

    <?php if ($msg === 'baja_ok'): ?>
        <div class="alerta-sistema msg-exito">Producto ocultado del catálogo correctamente.</div>
    <?php elseif ($msg === 'alta_ok'): ?>
        <div class="alerta-sistema msg-exito">Producto reactivado y visible en la carta.</div>
    <?php elseif ($msg === 'exito'): ?>
        <div class="alerta-sistema msg-exito">Producto guardado con éxito.</div>
    <?php elseif ($msg === 'error'): ?>
        <div class="alerta-sistema msg-error">Ha ocurrido un error en la base de datos.</div>
    <?php endif; ?>

    <section class="seccion-tabla">
        <div class="tabla-responsive">
            <table class="tabla-admin tabla-productos-movil">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre y Estado</th>
                        <th>Descripción</th>
                        <th>Precio (PVP)</th>
                        <th>Stock</th>
                        <th class="txt-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="6" class="txt-center">No hay productos en la base de datos.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($productos as $p): ?>
                        <tr class="<?= isset($p->ofertado) && $p->ofertado == 0 ? 'fila-inactiva' : '' ?>">
                            <td class="col-imagenes" data-label="Imagen">
                                <?php
                                    $fotos = !empty($p->imagen) ? explode(',', $p->imagen) : [];
                                    if (empty($fotos)):
                                ?>
                                    <img src="<?= BASE_URL ?>/img/productos/default.png" alt="Foto producto" class="img-mini-tabla">
                                <?php else: ?>
                                    <div class="contenedor-fotos-tabla">
                                        <?php foreach ($fotos as $f): ?>
                                            <?php if (trim($f)): ?>
                                                <img src="<?= BASE_URL ?>/img/productos/<?= htmlspecialchars(trim($f)) ?>" alt="Foto producto" class="img-mini-tabla">
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="col-nombre" data-label="Nombre y Estado">
                                <strong><?= htmlspecialchars($p->nombre) ?></strong>
                                <?php if (isset($p->ofertado) && $p->ofertado == 0): ?>
                                    <span class="badge-inactivo">INACTIVO</span>
                                <?php endif; ?>
                            </td>

                            <td class="col-desc" data-label="Descripción">
                                <?= htmlspecialchars(mb_strimwidth($p->descripcion ?? '', 0, 60, "...")) ?>
                            </td>

                            <td class="col-precio txt-right" data-label="Precio (PVP)">
                                <?php
                                    $precio_base = $p->precio ?? $p->precio_base ?? 0;
                                    $iva_aplicado = $p->iva ?? 21;
                                    $precio_final = $precio_base * (1 + ($iva_aplicado / 100));
                                ?>
                                <strong class="precio-final"><?= number_format($precio_final, 2, ',', '.') ?> €</strong>
                                <br>
                                <span class="detalle-iva">
                                    (Base: <?= number_format($precio_base, 2, ',', '.') ?>€ + <?= $iva_aplicado ?>% IVA)
                                </span>
                            </td>

                            <td class="col-stock txt-center" data-label="Stock">
                                <?= htmlspecialchars($p->stock ?? 0) ?> uds.
                            </td>

                            <td class="col-acciones txt-center" data-label="Acciones">
                                <a href="editar_producto.php?id=<?= $p->id ?>" class="btn-editar">Editar</a>

                                <?php if (!isset($p->ofertado) || $p->ofertado == 1): ?>
                                    <form action="../../negocio/ProductoController.php" method="POST" class="form-del-inline">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $p->id ?>">
                                        <button type="submit" class="btn-eliminar" onclick="return confirm('¿Ocultar este producto de la carta?')">Retirar</button>
                                    </form>
                                <?php else: ?>
                                    <form action="../../negocio/ProductoController.php" method="POST" class="form-del-inline">
                                        <input type="hidden" name="accion" value="reactivar">
                                        <input type="hidden" name="id" value="<?= $p->id ?>">
                                        <button type="submit" class="btn-reactivar">Reactivar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';
?>