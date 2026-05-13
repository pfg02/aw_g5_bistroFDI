<?php
declare(strict_types=1);

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
            'method' => 'POST',
            'enctype' => 'multipart/form-data',
        ]);

        $this->producto = $producto;
        $this->categorias = $categorias;
        $this->id = $id;

        // Mensajes de error recibidos por GET después de una redirección.
        // Permite informar al usuario sin procesar lógica de guardado en la vista.
        $error = trim((string) (filter_input(INPUT_GET, 'error', FILTER_UNSAFE_RAW) ?? ''));

        if ($error === 'stock') {
            $this->errores[] = 'El stock no puede ser negativo.';
        } elseif ($error === '1') {
            $this->errores[] = 'Error al guardar el producto.';
        }
    }

    // Genera los campos del formulario.
    // Los valores se cargan desde POST si hubo error, o desde el DTO si se está editando.
    protected function generaCamposFormulario(array $datos): string
    {
        $id = htmlspecialchars(
            (string) ($datos['id'] ?? $this->obtenerDatoProducto('id') ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $imagenActual = htmlspecialchars(
            (string) ($datos['imagen_actual'] ?? $this->obtenerDatoProducto('imagen') ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $nombre = htmlspecialchars(
            (string) ($datos['nombre'] ?? $this->obtenerDatoProducto('nombre') ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $precio = htmlspecialchars(
            (string) ($datos['precio_base'] ?? $this->obtenerDatoProducto('precio') ?? 0),
            ENT_QUOTES,
            'UTF-8'
        );

        $stock = htmlspecialchars(
            (string) ($datos['stock'] ?? $this->obtenerDatoProducto('stock') ?? 0),
            ENT_QUOTES,
            'UTF-8'
        );

        $descripcion = htmlspecialchars(
            (string) ($datos['descripcion'] ?? $this->obtenerDatoProducto('descripcion') ?? ''),
            ENT_QUOTES,
            'UTF-8'
        );

        // Valores seleccionados en selects.
        // Si se añaden nuevos selects, seguir este patrón:
        // valor de POST si existe, si no valor del DTO, si no valor por defecto.
        $ivaSeleccionado = (string) ($datos['iva'] ?? $this->obtenerDatoProducto('iva') ?? 21);
        $categoriaSeleccionada = (string) ($datos['id_categoria'] ?? $this->obtenerDatoProducto('id_categoria') ?? 0);
        $ofertadoSeleccionado = (string) ($datos['ofertado'] ?? $this->obtenerDatoProducto('ofertado') ?? 1);
        $requiereCocinaSeleccionado = (string) ($datos['requiere_cocina'] ?? $this->obtenerDatoProducto('requiere_cocina') ?? 1);

        // Opciones de categoría cargadas desde la vista/controlador.
        // Para otras opciones auxiliares, se puede repetir este patrón con otro array.
        $opcionesCategorias = '<option value="">Selecciona una categoría</option>';
        foreach ($this->categorias as $cat) {
            $idCatRaw = $this->obtenerDatoGenerico($cat, 'id');
            $nombreCatRaw = $this->obtenerDatoGenerico($cat, 'nombre');

            $idCat = htmlspecialchars((string) $idCatRaw, ENT_QUOTES, 'UTF-8');
            $nombreCat = htmlspecialchars((string) $nombreCatRaw, ENT_QUOTES, 'UTF-8');
            $selected = ($categoriaSeleccionada === (string) $idCatRaw) ? 'selected' : '';

            $opcionesCategorias .= "<option value=\"$idCat\" $selected>$nombreCat</option>";
        }

        // Muestra las imágenes que ya tiene el producto.
        // El campo hidden imagen_actual conserva los nombres al guardar.
        $imagenesActualesHtml = '';
        $imagenesProducto = (string) ($this->obtenerDatoProducto('imagen') ?? '');

        if (trim($imagenesProducto) !== '') {
            $imagenesActualesHtml .= '<p>Imágenes actuales:</p><div class="galeria-actual-producto">';
            $fotos = explode(',', $imagenesProducto);

            foreach ($fotos as $f) {
                $f = trim($f);
                if ($f !== '') {
                    $src = htmlspecialchars($f, ENT_QUOTES, 'UTF-8');
                    $rutaImagen = BASE_URL . '/img/productos/' . $src;
                    $imagenesActualesHtml .= "<img src=\"$rutaImagen\" alt=\"Imagen actual del producto\">";
                }
            }

            $imagenesActualesHtml .= '</div>';
        }

        $erroresHtml = self::generaListaErrores($this->errores);

        $sel4 = ($ivaSeleccionado === '4') ? 'selected' : '';
        $sel10 = ($ivaSeleccionado === '10') ? 'selected' : '';
        $sel21 = ($ivaSeleccionado === '21') ? 'selected' : '';

        $selAct = ($ofertadoSeleccionado === '1') ? 'selected' : '';
        $selInact = ($ofertadoSeleccionado === '0') ? 'selected' : '';

        $selCocinaSi = ($requiereCocinaSeleccionado === '1') ? 'selected' : '';
        $selCocinaNo = ($requiereCocinaSeleccionado === '0') ? 'selected' : '';

        $urlListado = BASE_URL . '/includes/vistas/admin/gestion_productos.php';

        return <<<HTML
$erroresHtml

<input type="hidden" name="accion" value="guardar">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="imagen_actual" value="$imagenActual">

<div class="grupo-control">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" value="$nombre" required maxlength="100">
</div>

<div class="grupo-control">
    <label for="precio_base">Precio Base (€):</label>
    <input type="number" id="precio_base" name="precio_base" value="$precio" required min="0" max="9999.99" step="0.01">
</div>

<div class="grupo-control">
    <label for="iva">IVA:</label>
    <select id="iva" name="iva" required>
        <option value="4" $sel4>4%</option>
        <option value="10" $sel10>10%</option>
        <option value="21" $sel21>21%</option>
    </select>
</div>

<div class="grupo-control">
    <label for="stock">Stock:</label>
    <input type="number" id="stock" name="stock" value="$stock" required min="0" max="9999" step="1">
</div>

<div class="grupo-control">
    <label for="id_categoria">Categoría:</label>
    <select id="id_categoria" name="id_categoria" required>
        $opcionesCategorias
    </select>
</div>

<div class="grupo-control">
    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion" rows="4" maxlength="2000">$descripcion</textarea>
</div>

<div class="grupo-control">
    <label for="ofertado">Estado:</label>
    <select id="ofertado" name="ofertado" required>
        <option value="1" $selAct>Activo (En carta)</option>
        <option value="0" $selInact>Inactivo (Oculto)</option>
    </select>
</div>

<div class="grupo-control">
    <label for="requiere_cocina">Preparación en cocina:</label>
    <select id="requiere_cocina" name="requiere_cocina" required>
        <option value="1" $selCocinaSi>Sí, se prepara en cocina</option>
        <option value="0" $selCocinaNo>No, no se prepara en cocina</option>
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
    <a href="$urlListado" class="btn-secundario">Volver al listado</a>
</div>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        // El guardado real se procesa en ProductoController mediante la acción del formulario.
        return null;
    }

    // Obtiene un dato del producto actual.
    // Centraliza el acceso para no repetir comprobaciones en todo el formulario.
    private function obtenerDatoProducto(string $campo)
    {
        return $this->obtenerDatoGenerico($this->producto, $campo);
    }

    // Obtiene un dato de un objeto usando getter o propiedad pública.
    // Permite trabajar con DTOs y objetos auxiliares de forma flexible.
    private function obtenerDatoGenerico(object $objeto, string $campo)
    {
        $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $campo)));

        if (method_exists($objeto, $getter)) {
            return $objeto->$getter();
        }

        if (isset($objeto->$campo)) {
            return $objeto->$campo;
        }

        return null;
    }

    // Patrón para ampliar el formulario:
    // 1. Cargar el valor desde POST o desde el DTO.
    // 2. Escapar el valor antes de imprimirlo.
    // 3. Añadir el input, select o textarea.
    // 4. Usar el mismo name que leerá el controlador.
    // 5. Validar el dato en ProductoController o ProductoService.
    // 6. Añadirlo al DTO y al guardado en DAO si pertenece a productos.
    //
    // Para selección múltiple:
    // - usar name="campo[]";
    // - cargar opciones desde la vista/controlador;
    // - marcar como checked las opciones ya asociadas;
    // - guardar las asociaciones después de guardar el producto principal.
}