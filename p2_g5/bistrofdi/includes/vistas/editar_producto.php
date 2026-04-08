<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../sesion.php';
require_once __DIR__ . '/../negocio/ProductoService.php';
require_once __DIR__ . '/../negocio/CategoriaService.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header("Location: ../../login.php"); 
    exit();
}

$id = $_GET['id'] ?? null;
$service = new ProductoService($db);
$catService = new CategoriaService($db);

$producto = $id ? $service->obtenerProducto($id) : new ProductoDTO();
$categorias = $catService->listarTodas();

$tituloPagina = ($id ? 'Editar' : 'Nuevo') . ' Producto';
$bodyClass = 'admin-panel';
$extraHead = '<link rel="stylesheet" href="../../css/estilos.css">';

ob_start();
?>

<div class="container-gestion">
    <header class="header-gestion">
        <h1><?= $id ? 'EDITAR' : 'NUEVO' ?> PRODUCTO</h1>
    </header>
    
    <section class="formulario-admin">
        <form action="../negocio/ProductoController.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="guardar">
            <input type="hidden" name="id" value="<?= $producto->id ?>">
            <input type="hidden" name="imagen_actual" value="<?= $producto->imagen ?>">

            <div class="grupo-control">
                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($producto->nombre ?? '') ?>" required>
            </div>

            <div class="grupo-control">
                <label>Precio Base (€):</label>
                <input type="number" step="0.01" name="precio_base" value="<?= $producto->precio ?? 0 ?>" required>
            </div>

            <div class="grupo-control">
                <label>IVA:</label>
                <select name="iva">
                    <option value="4" <?= (($producto->iva ?? 21) == 4) ? 'selected' : '' ?>>4%</option>
                    <option value="10" <?= (($producto->iva ?? 21) == 10) ? 'selected' : '' ?>>10%</option>
                    <option value="21" <?= (($producto->iva ?? 21) == 21) ? 'selected' : '' ?>>21%</option>
                </select>
            </div>

            <div class="grupo-control">
                <label>Stock:</label>
                <input type="number" name="stock" value="<?= $producto->stock ?? 0 ?>" required>
            </div>

            <div class="grupo-control">
                <label>Categoría:</label>
                <select name="id_categoria">
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?= $cat->id ?>" <?= (($producto->id_categoria ?? 0) == $cat->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grupo-control">
                <label>Descripción:</label>
                <textarea name="descripcion" rows="4"><?= htmlspecialchars($producto->descripcion ?? '') ?></textarea>
            </div>

            <div class="grupo-control">
                <label>Estado:</label>
                <select name="ofertado">
                    <option value="1" <?= (($producto->ofertado ?? 1) == 1) ? 'selected' : '' ?>>Activo (En carta)</option>
                    <option value="0" <?= (($producto->ofertado ?? 1) == 0) ? 'selected' : '' ?>>Inactivo (Oculto)</option>
                </select>
            </div>

            <fieldset style="margin-bottom: 20px; padding: 15px; border: 1px solid #ddd;">
                <legend style="padding: 0 10px; font-weight: bold;">Imágenes del Producto</legend>
                <label>Imagen 1:</label> <input type="file" name="foto1" accept="image/*"><br><br>
                <label>Imagen 2:</label> <input type="file" name="foto2" accept="image/*"><br><br>
                <label>Imagen 3:</label> <input type="file" name="foto3" accept="image/*">
            </fieldset>

            <?php if (!empty($producto->imagen)): ?>
                <p>Imágenes actuales:</p>
                <div style="margin-bottom: 20px;">
                    <?php 
                    $fotos = explode(',', $producto->imagen);
                    foreach($fotos as $f): if(trim($f)): ?>
                        <img src="../../img/productos/<?= trim($f) ?>" width="100" style="margin-right:10px; border:1px solid #ccc; border-radius: 4px;">
                    <?php endif; endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="acciones-form">
                <button type="submit" class="btn-primario">GUARDAR PRODUCTO</button>
                <a href="gestion_productos.php" class="btn-secundario">Volver al listado</a>
            </div>
        </form>
    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/comun/plantilla.php';
?> 