<?php

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../clases/RepositorioUsuarios.php';

class FormBorrarUsuario extends Formulario
{
    private Usuario $usuario;

    public function __construct(Usuario $usuario)
    {
        $this->usuario = $usuario;
        parent::__construct('formBorrarUsuario', ['urlRedireccion' => 'gestionarUsuarios.php']);
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $erroresGlobales = self::listaErroresGlobales($this->errores);
        $nombreUsuario = self::h($this->usuario->getNombreUsuario());

        return <<<HTML
{$erroresGlobales}

<p>¿Seguro que quieres borrar al usuario <strong>{$nombreUsuario}</strong>?</p>

<button type="submit">Sí, borrar usuario</button>
HTML;
    }

    protected function procesaFormulario(array &$datos): void
    {
        if ((int)$this->usuario->getId() === (int)$_SESSION['id_usuario']) {
            $this->errores[] = 'No puedes borrarte a ti mismo.';
            return;
        }

        $repo = new RepositorioUsuarios();
        if (!$repo->borrar((int)$this->usuario->getId())) {
            $this->errores[] = 'No se ha podido borrar el usuario.';
        }
    }
}