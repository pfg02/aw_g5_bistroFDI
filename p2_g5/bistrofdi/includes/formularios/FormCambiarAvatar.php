<?php

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../clases/RepositorioUsuarios.php';

class FormCambiarAvatar extends Formulario
{
    private Usuario $usuario;
    private string $mensajeOk = '';

    public function __construct(Usuario $usuario)
    {
        $this->usuario = $usuario;
        parent::__construct('formCambiarAvatar');
    }

    public function getMensajeOk(): string
    {
        return $this->mensajeOk;
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $erroresGlobales = self::listaErroresGlobales($this->errores);

        $avataresPredefinidos = [
            'camarero.png',
            'cliente.png',
            'cocinero.png',
            'gerente.png',
            'default.png'
        ];

        $htmlAvatares = '';
        foreach ($avataresPredefinidos as $archivo) {
            $archivoSeguro = self::h($archivo);
            $htmlAvatares .= <<<HTML
<label style="display:inline-block; margin-right:10px;">
    <input type="radio" name="avatar_predefinido" value="{$archivoSeguro}">
    <img src="img/avatares/{$archivoSeguro}" width="80" alt="">
</label>
HTML;
        }

        return <<<HTML
{$erroresGlobales}

<p>Avatar actual:</p>
<img src="{$this->usuario->getAvatar()}" alt="Avatar actual" width="120">

<hr>

<h2>Seleccionar avatar predefinido</h2>
{$htmlAvatares}
<br><br>
<button type="submit" name="accion" value="predefinido">Guardar avatar predefinido</button>

<hr>

<h2>Subir imagen propia</h2>
<input type="file" name="archivo" accept="image/*">
<br><br>
<button type="submit" name="accion" value="subir">Subir y usar esta imagen</button>

<hr>

<h2>Usar avatar por defecto</h2>
<button type="submit" name="accion" value="defecto">Restaurar avatar por defecto</button>
HTML;
    }

    protected function procesaFormulario(array &$datos): void
    {
        $repo = new RepositorioUsuarios();
        $idUsuario = (int)$this->usuario->getId();
        $carpetaAvatares = 'img/avatares';

        $accion = $datos['accion'] ?? '';

        if ($accion === 'predefinido') {
            $archivo = basename($datos['avatar_predefinido'] ?? '');
            if ($archivo === '') {
                $this->errores[] = 'Debes seleccionar un avatar predefinido.';
                return;
            }

            $ruta = $carpetaAvatares . '/' . $archivo;

            if (!$repo->actualizarAvatar($idUsuario, $ruta)) {
                $this->errores[] = 'Error al actualizar el avatar.';
                return;
            }

            $_SESSION['avatar'] = $ruta;
            $this->mensajeOk = 'Avatar actualizado correctamente.';
            return;
        }

        if ($accion === 'subir') {
            if (empty($_FILES['archivo']['name'])) {
                $this->errores[] = 'No has seleccionado ninguna imagen.';
                return;
            }

            $nombreArchivo = basename($_FILES['archivo']['name']);
            $destino = $carpetaAvatares . '/' . $nombreArchivo;

            if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
                $this->errores[] = 'Error al subir la imagen.';
                return;
            }

            if (!$repo->actualizarAvatar($idUsuario, $destino)) {
                $this->errores[] = 'Error al actualizar el avatar en la BD.';
                return;
            }

            $_SESSION['avatar'] = $destino;
            $this->mensajeOk = 'Avatar subido y actualizado correctamente.';
            return;
        }

        if ($accion === 'defecto') {
            $ruta = $carpetaAvatares . '/default.png';

            if (!$repo->actualizarAvatar($idUsuario, $ruta)) {
                $this->errores[] = 'Error al actualizar el avatar por defecto.';
                return;
            }

            $_SESSION['avatar'] = $ruta;
            $this->mensajeOk = 'Avatar por defecto restaurado.';
            return;
        }

        $this->errores[] = 'Acción no válida.';
    }

    protected function generaFormulario(array $datos = []): string
    {
        $campos = $this->generaCamposFormulario($datos);

        return <<<HTML
<form method="POST" action="" enctype="multipart/form-data">
    <input type="hidden" name="formId" value="{$this->formId}">
    {$campos}
</form>
HTML;
    }
}