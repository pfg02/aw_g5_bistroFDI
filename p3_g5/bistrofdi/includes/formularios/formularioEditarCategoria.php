<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';
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
            'method' => 'POST',
            'class' => 'formulario-admin',
            'enctype' => 'multipart/form-data',
        ]);

        $this->controller = $controller;
        $this->categoria = $categoria;
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $idCategoria = $datos['id'] ?? $this->obtenerDatoCategoria('id') ?? '';
        $nombre = $datos['nombre'] ?? $this->obtenerDatoCategoria('nombre') ?? '';
        $descripcion = $datos['descripcion'] ?? $this->obtenerDatoCategoria('descripcion') ?? '';
        $imagenActual = $datos['imagen_actual'] ?? $this->obtenerDatoCategoria('imagen') ?? '';

        $id = htmlspecialchars((string) $idCategoria, ENT_QUOTES, 'UTF-8');
        $nombreEsc = htmlspecialchars((string) $nombre, ENT_QUOTES, 'UTF-8');
        $descripcionEsc = htmlspecialchars((string) $descripcion, ENT_QUOTES, 'UTF-8');
        $imagenActualEsc = htmlspecialchars((string) $imagenActual, ENT_QUOTES, 'UTF-8');

        $erroresHtml = self::generaListaErrores($this->errores);
        $urlListado = BASE_URL . '/includes/vistas/admin/gestion_categorias.php';

        $imagenActualHtml = '';
        $imagenCategoria = $this->obtenerDatoCategoria('imagen');

        if (is_string($imagenCategoria) && trim($imagenCategoria) !== '') {
            $src = htmlspecialchars(trim($imagenCategoria), ENT_QUOTES, 'UTF-8');
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
<input type="hidden" name="imagen_actual" value="$imagenActualEsc">

<div class="grupo-control">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" value="$nombreEsc" required maxlength="100">
</div>

<div class="grupo-control">
    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion" rows="4" maxlength="500">$descripcionEsc</textarea>
</div>

<div class="grupo-control">
    <label for="imagen">Nueva imagen:</label>
    <input type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif">
</div>

$imagenActualHtml

<div class="acciones-form">
    <button type="submit" class="btn-primario">Guardar categoría</button>
    <a href="$urlListado" class="btn-secundario">Volver al listado</a>
</div>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $descripcion = trim((string) ($datos['descripcion'] ?? ''));
        $idRaw = $datos['id'] ?? null;
        $imagen = trim((string) ($datos['imagen_actual'] ?? ($this->obtenerDatoCategoria('imagen') ?? 'default.png')));

        $id = null;
        if ($idRaw !== null && $idRaw !== '') {
            $idValidado = filter_var($idRaw, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1]
            ]);

            if ($idValidado === false) {
                $this->errores[] = 'El identificador de la categoría no es válido.';
            } else {
                $id = (int) $idValidado;
            }
        }

        if ($nombre === '') {
            $this->errores[] = 'El nombre de la categoría es obligatorio.';
        }

        if (mb_strlen($nombre) > 100) {
            $this->errores[] = 'El nombre no puede superar los 100 caracteres.';
        }

        if (mb_strlen($descripcion) > 500) {
            $this->errores[] = 'La descripción no puede superar los 500 caracteres.';
        }

        if ($imagen !== '' && preg_match('/^[a-zA-Z0-9._-]+\.(jpg|jpeg|png|webp|gif)$/i', $imagen) !== 1) {
            $this->errores[] = 'El nombre de la imagen actual no es válido.';
        }

        if (isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $archivo = $_FILES['imagen'];
            $errorArchivo = $archivo['error'] ?? UPLOAD_ERR_NO_FILE;

            if ($errorArchivo !== UPLOAD_ERR_OK) {
                $this->errores[] = 'Error al subir la imagen.';
            } elseif (($archivo['size'] ?? 0) > 2 * 1024 * 1024) {
                $this->errores[] = 'La imagen no puede superar los 2 MB.';
            } else {
                $tmp = (string) ($archivo['tmp_name'] ?? '');

                if ($tmp === '' || !is_uploaded_file($tmp)) {
                    $this->errores[] = 'El archivo subido no es válido.';
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($tmp);

                    $permitidas = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        'image/gif' => 'gif',
                    ];

                    if (!isset($permitidas[$mime])) {
                        $this->errores[] = 'La imagen debe ser JPG, PNG, WEBP o GIF.';
                    } else {
                        $directorio = dirname(dirname(__DIR__)) . '/img/categorias/';

                        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
                            $this->errores[] = 'No se pudo crear el directorio de imágenes.';
                        } else {
                            $extension = $permitidas[$mime];
                            $nombreArchivo = 'cat_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
                            $rutaDestino = $directorio . $nombreArchivo;

                            if (!move_uploaded_file($tmp, $rutaDestino)) {
                                $this->errores[] = 'No se pudo guardar la imagen.';
                            } else {
                                $imagen = $nombreArchivo;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($this->errores)) {
            return null;
        }

        $ok = $this->controller->guardarCategoria($nombre, $descripcion, $imagen, $id);

        if (!$ok) {
            $this->errores[] = 'Error al guardar la categoría.';
            return null;
        }

        return BASE_URL . '/includes/vistas/admin/gestion_categorias.php';
    }

    private function obtenerDatoCategoria(string $campo)
    {
        if ($this->categoria === null) {
            return null;
        }

        $getter = 'get' . ucfirst($campo);
        if (method_exists($this->categoria, $getter)) {
            return $this->categoria->$getter();
        }

        if (isset($this->categoria->$campo)) {
            return $this->categoria->$campo;
        }

        return null;
    }
}