<?php

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../clases/RepositorioUsuarios.php';

class FormCambiarRol extends Formulario
{
    private Usuario $usuario;

    public function __construct(Usuario $usuario)
    {
        $this->usuario = $usuario;
        parent::__construct('formCambiarRol', ['urlRedireccion' => 'gestionarUsuarios.php']);
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $rolesValidos = ['cliente', 'camarero', 'cocinero', 'gerente'];
        $rolActual = $datos['rol'] ?? $this->usuario->getRol();
        $erroresGlobales = self::listaErroresGlobales($this->errores);

        $opciones = '';
        foreach ($rolesValidos as $rol) {
            $selected = ($rol === $rolActual) ? 'selected' : '';
            $opciones .= '<option value="' . self::h($rol) . '" ' . $selected . '>' . self::h(ucfirst($rol)) . '</option>';
        }

        $nombreUsuario = self::h($this->usuario->getNombreUsuario());

        return <<<HTML
{$erroresGlobales}

<p><strong>Usuario:</strong> {$nombreUsuario}</p>

<label>Nuevo rol:
    <select name="rol" required>
        {$opciones}
    </select>
</label><br><br>

<button type="submit">Guardar rol</button>
HTML;
    }

    protected function procesaFormulario(array &$datos): void
    {
        $nuevoRol = $datos['rol'] ?? '';
        $rolesValidos = ['cliente', 'camarero', 'cocinero', 'gerente'];

        if (!in_array($nuevoRol, $rolesValidos, true)) {
            $this->errores[] = 'Rol no válido.';
            return;
        }

        if ((int)$this->usuario->getId() === (int)$_SESSION['id_usuario'] && $nuevoRol !== 'gerente') {
            $this->errores[] = 'No puedes quitarte a ti mismo el rol de gerente.';
            return;
        }

        $repo = new RepositorioUsuarios();
        if (!$repo->cambiarRol((int)$this->usuario->getId(), $nuevoRol)) {
            $this->errores[] = 'No se ha podido cambiar el rol.';
        }
    }
}