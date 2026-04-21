<?php

require_once __DIR__ . '/../core/formulario.php';
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
            $rutaImagen = BASE_URL . '/img/categorias/' . $src;

            $imagenActualHtml = <<<HTML
<div class="grupo-control">
    <label>Imagen actual:</label>
    <div class="preview-categoria">
        <img src="$rutaImagen" alt="Imagen actual" class="img-tabla-cat">
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
    <input type="text" name="nombre" value="$nombre" required maxlength="100">
</div>

<div class="grupo-control">
    <label>Descripción:</label>
    <textarea name="descripcion" rows="4" maxlength="1000">$descripcion</textarea>
</div>

<div class="grupo-control">
    <label>Nueva imagen:</label>
    <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
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
        $imagen = $this->normalizarNombreImagen($datos['imagen_actual'] ?? ($this->categoria->imagen ?? null));

        if ($nombre === '') {
            $this->errores[] = 'El nombre de la categoría es obligatorio.';
        }

        if (mb_strlen($nombre) > 100) {
            $this->errores[] = 'El nombre no puede superar los 100 caracteres.';
        }

        if (mb_strlen($descripcion) > 1000) {
            $this->errores[] = 'La descripción es demasiado larga.';
        }

        if (!empty($this->errores)) {
            return null;
        }

        if (isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $nombreSubido = $this->procesarImagenSubida($_FILES['imagen']);
            if ($nombreSubido === null) {
                return null;
            }
            $imagen = $nombreSubido;
        }

        $ok = $this->controller->guardarCategoria($nombre, $descripcion, $imagen, $id);

        if (!$ok) {
            $this->errores[] = 'Error al guardar la categoría.';
            return null;
        }

        return 'gestion_categorias.php';
    }

    private function procesarImagenSubida(array $archivo): ?string
    {
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->errores[] = 'No se pudo subir la imagen.';
            return null;
        }

        if (($archivo['size'] ?? 0) > 2 * 1024 * 1024) {
            $this->errores[] = 'La imagen no puede superar los 2 MB.';
            return null;
        }

        $tmp = $archivo['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            $this->errores[] = 'Archivo de imagen no válido.';
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);

        $extensionesPermitidas = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif'
        ];

        if (!isset($extensionesPermitidas[$mime])) {
            $this->errores[] = 'La imagen debe ser JPG, PNG, WEBP o GIF.';
            return null;
        }

        $nombreArchivo = 'cat_' . uniqid('', true) . '.' . $extensionesPermitidas[$mime];
        $rutaDestino = dirname(__DIR__, 2) . '/img/categorias/' . $nombreArchivo;

        if (!move_uploaded_file($tmp, $rutaDestino)) {
            $this->errores[] = 'No se pudo guardar la imagen.';
            return null;
        }

        return $nombreArchivo;
    }

    private function normalizarNombreImagen(?string $imagen): ?string
    {
        if ($imagen === null || $imagen === '') {
            return null;
        }

        $imagen = basename($imagen);

        return preg_match('/^[a-zA-Z0-9._-]+$/', $imagen) === 1 ? $imagen : null;
    }
}
