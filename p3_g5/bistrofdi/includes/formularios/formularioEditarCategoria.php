<?php

require_once __DIR__ . '/../Formulario.php';
require_once __DIR__ . '/../negocio/CategoriaController.php';
require_once __DIR__ . '/../negocio/CategoriaDTO.php';

class FormularioEditarCategoria extends Formulario
{
    private CategoriaController $controller;
    private ?CategoriaDTO $categoria;

    public function __construct(CategoriaController $controller, ?CategoriaDTO $categoria = null)
    {
        parent::__construct('formEditarCategoria', [
            'action' => '',
            'class' => 'formulario-admin',
            'enctype' => 'multipart/form-data'
        ]);

        $this->controller = $controller;
        $this->categoria = $categoria;
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $id = htmlspecialchars((string)($datos['id'] ?? ($this->categoria->id ?? '')));
        $nombre = htmlspecialchars($datos['nombre'] ?? ($this->categoria->nombre ?? ''));
        $descripcion = htmlspecialchars($datos['descripcion'] ?? ($this->categoria->descripcion ?? ''));
        $imagenActual = htmlspecialchars($datos['imagen_actual'] ?? ($this->categoria->imagen ?? ''));

        $erroresHtml = self::generaListaErrores($this->errores);

        $imagenActualHtml = '';
        if ($this->categoria && !empty($this->categoria->imagen)) {
            $src = htmlspecialchars($this->categoria->imagen);
            $imagenActualHtml = <<<HTML
<div class="grupo-control">
    <label>Imagen actual:</label>
    <div class="preview-categoria">
        <img src="../../img/categorias/$src" alt="Imagen actual" class="img-tabla-cat">
    </div>
</div>
HTML;
        }

        return <<<HTML
$erroresHtml

<input type="hidden" name="id" value="$id">
<input type="hidden" name="imagen_actual" value="$imagenActual">

<div class="grupo-control">
    <label>Nombre:</label>
    <input type="text" name="nombre" value="$nombre" required>
</div>

<div class="grupo-control">
    <label>Descripción:</label>
    <textarea name="descripcion" rows="4">$descripcion</textarea>
</div>

<div class="grupo-control">
    <label>Nueva imagen:</label>
    <input type="file" name="imagen" accept="image/*">
</div>

$imagenActualHtml

<div class="acciones-form">
    <button type="submit" class="btn-primario">Guardar categoría</button>
    <a href="gestion_categorias.php" class="btn-secundario">Volver al listado</a>
</div>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $id = isset($datos['id']) && $datos['id'] !== '' ? (int)$datos['id'] : null;
        $imagen = $datos['imagen_actual'] ?? ($this->categoria->imagen ?? null);

        if ($nombre === '') {
            $this->errores[] = 'El nombre de la categoría es obligatorio.';
            return null;
        }

        if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'])) {
            $nombreArchivo = basename($_FILES['imagen']['name']);
            $rutaDestino = __DIR__ . '/../../img/categorias/' . $nombreArchivo;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
                $imagen = $nombreArchivo;
            } else {
                $this->errores[] = 'No se pudo subir la imagen.';
                return null;
            }
        }

        $ok = $this->controller->guardarCategoria($nombre, $descripcion, $imagen, $id);

        if (!$ok) {
            $this->errores[] = 'Error al guardar la categoría.';
            return null;
        }

        return 'gestion_categorias.php';
    }
}