<?php

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/formulario.php';
require_once __DIR__ . '/../negocio/ProductoDTO.php';

class FormularioEditarProducto extends Formulario
{
    private ProductoDTO $producto;
    private array $categorias;
    private ?string $id;

    public function __construct(ProductoDTO $producto, array $categorias, ?string $id)
    {
        parent::__construct('formEditarProducto', [
            'action' => BASE_URL . '/includes/negocio/ProductoController.php',
            'enctype' => 'multipart/form-data'
        ]);

        $this->producto = $producto;
        $this->categorias = $categorias;
        $this->id = $id;

        if (isset($_GET['error'])) {
            if ($_GET['error'] === 'stock') {
                $this->errores[] = 'El stock no puede ser negativo.';
            } elseif ($_GET['error'] === '1') {
                $this->errores[] = 'Error al guardar el producto.';
            }
        }
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $id = htmlspecialchars($datos['id'] ?? ($this->producto->id ?? ''));
        $imagenActual = htmlspecialchars($datos['imagen_actual'] ?? ($this->producto->imagen ?? ''));
        $nombre = htmlspecialchars($datos['nombre'] ?? ($this->producto->nombre ?? ''));
        $precio = htmlspecialchars((string)($datos['precio_base'] ?? ($this->producto->precio ?? 0)));
        $stock = htmlspecialchars((string)($datos['stock'] ?? ($this->producto->stock ?? 0)));
        $descripcion = htmlspecialchars($datos['descripcion'] ?? ($this->producto->descripcion ?? ''));
        $ivaSeleccionado = $datos['iva'] ?? ($this->producto->iva ?? 21);
        $categoriaSeleccionada = $datos['id_categoria'] ?? ($this->producto->id_categoria ?? 0);
        $ofertadoSeleccionado = $datos['ofertado'] ?? ($this->producto->ofertado ?? 1);

        $opcionesCategorias = '<option value="">Selecciona una categoría</option>';
        foreach ($this->categorias as $cat) {
            $selected = ((string)$categoriaSeleccionada === (string)$cat->id) ? 'selected' : '';
            $idCat = htmlspecialchars((string)$cat->id);
            $nombreCat = htmlspecialchars($cat->nombre);
            $opcionesCategorias .= "<option value=\"$idCat\" $selected>$nombreCat</option>";
        }

        $imagenesActualesHtml = '';
        if (!empty($this->producto->imagen)) {
            $imagenesActualesHtml .= '<p>Imágenes actuales:</p><div class="galeria-actual-producto">';
            $fotos = explode(',', $this->producto->imagen);
            foreach ($fotos as $f) {
                $f = trim($f);
                if ($f !== '') {
                    $src = htmlspecialchars($f);
                    $rutaImagen = BASE_URL . '/img/productos/' . $src;
                    $imagenesActualesHtml .= "<img src=\"$rutaImagen\" alt=\"Imagen actual del producto\">";
                }
            }
            $imagenesActualesHtml .= '</div>';
        }

        $erroresHtml = self::generaListaErrores($this->errores);

        $sel4 = ((string)$ivaSeleccionado === '4') ? 'selected' : '';
        $sel10 = ((string)$ivaSeleccionado === '10') ? 'selected' : '';
        $sel21 = ((string)$ivaSeleccionado === '21') ? 'selected' : '';

        $selAct = ((string)$ofertadoSeleccionado === '1') ? 'selected' : '';
        $selInact = ((string)$ofertadoSeleccionado === '0') ? 'selected' : '';

        return <<<HTML
$erroresHtml

<input type="hidden" name="accion" value="guardar">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="imagen_actual" value="$imagenActual">

<div class="grupo-control">
    <label>Nombre:</label>
    <input type="text" name="nombre" value="$nombre" required maxlength="100">
</div>

<div class="grupo-control">
    <label>Precio Base (€):</label>
    <input type="number" step="0.01" min="0" name="precio_base" value="$precio" required>
</div>

<div class="grupo-control">
    <label>IVA:</label>
    <select name="iva" required>
        <option value="4" $sel4>4%</option>
        <option value="10" $sel10>10%</option>
        <option value="21" $sel21>21%</option>
    </select>
</div>

<div class="grupo-control">
    <label>Stock:</label>
    <input type="number" name="stock" value="$stock" min="0" step="1" required>
</div>

<div class="grupo-control">
    <label>Categoría:</label>
    <select name="id_categoria" required>
        $opcionesCategorias
    </select>
</div>

<div class="grupo-control">
    <label>Descripción:</label>
    <textarea name="descripcion" rows="4" maxlength="2000">$descripcion</textarea>
</div>

<div class="grupo-control">
    <label>Estado:</label>
    <select name="ofertado" required>
        <option value="1" $selAct>Activo (En carta)</option>
        <option value="0" $selInact>Inactivo (Oculto)</option>
    </select>
</div>

<fieldset class="bloque-imagenes-producto">
    <legend>Imágenes del Producto</legend>

    <div class="bloque-inputs-imagenes">
        <label for="foto1">Imagen 1:</label>
        <input type="file" id="foto1" name="foto1" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">

        <label for="foto2">Imagen 2:</label>
        <input type="file" id="foto2" name="foto2" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">

        <label for="foto3">Imagen 3:</label>
        <input type="file" id="foto3" name="foto3" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
    </div>
</fieldset>

$imagenesActualesHtml

<div class="acciones-form">
    <button type="submit" class="btn-primario">Guardar producto</button>
    <a href="gestion_productos.php" class="btn-secundario">Volver al listado</a>
</div>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        return null;
    }
}
