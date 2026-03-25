<?php

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../clases/RepositorioUsuarios.php';

class FormPerfil extends Formulario
{
    private Usuario $usuario;
    private bool $edicionGerente;

    public function __construct(Usuario $usuario, bool $edicionGerente = false)
    {
        $this->usuario = $usuario;
        $this->edicionGerente = $edicionGerente;

        parent::__construct('formPerfil');
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $nombreUsuario = self::h($this->usuario->getNombreUsuario());
        $email = self::h($datos['email'] ?? $this->usuario->getEmail());
        $nombre = self::h($datos['nombre'] ?? $this->usuario->getNombre());
        $apellidos = self::h($datos['apellidos'] ?? $this->usuario->getApellidos());
        $erroresGlobales = self::listaErroresGlobales($this->errores);

        $camposRol = '';
        if ($this->edicionGerente) {
            $rolActual = $datos['rol'] ?? $this->usuario->getRol();
            $roles = ['cliente', 'camarero', 'cocinero', 'gerente'];

            $camposRol .= '<label>Rol: <select name="rol">';
            foreach ($roles as $rol) {
                $selected = ($rol === $rolActual) ? 'selected' : '';
                $camposRol .= '<option value="' . self::h($rol) . '" ' . $selected . '>' . self::h(ucfirst($rol)) . '</option>';
            }
            $camposRol .= '</select></label><br><br>';
        }

        return <<<HTML
{$erroresGlobales}

<label>Nombre de usuario:
    <input type="text" value="{$nombreUsuario}" disabled>
</label><br><br>

<label>Email:
    <input type="email" name="email" value="{$email}" required>
</label><br><br>

<label>Nombre:
    <input type="text" name="nombre" value="{$nombre}" required>
</label><br><br>

<label>Apellidos:
    <input type="text" name="apellidos" value="{$apellidos}" required>
</label><br><br>

{$camposRol}

<button type="submit">Guardar cambios</button>
HTML;
    }

    protected function procesaFormulario(array &$datos): void
    {
        $email = trim($datos['email'] ?? '');
        $nombre = trim($datos['nombre'] ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');

        if ($email === '' || $nombre === '' || $apellidos === '') {
            $this->errores[] = 'Todos los campos son obligatorios.';
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = 'El email no tiene un formato válido.';
            return;
        }

        $repo = new RepositorioUsuarios();

        if ($repo->existeEmail($email, $this->usuario->getId())) {
            $this->errores[] = 'Ese email ya está en uso por otro usuario.';
            return;
        }

        $this->usuario->setEmail($email);
        $this->usuario->setNombre($nombre);
        $this->usuario->setApellidos($apellidos);

        if ($this->edicionGerente) {
            $rol = $datos['rol'] ?? $this->usuario->getRol();
            $rolesValidos = ['cliente', 'camarero', 'cocinero', 'gerente'];

            if (!in_array($rol, $rolesValidos, true)) {
                $this->errores[] = 'Rol no válido.';
                return;
            }

            $this->usuario->setRol($rol);
        }

        if (!$repo->actualizarPerfil($this->usuario)) {
            $this->errores[] = 'No se ha podido actualizar el perfil.';
            return;
        }

        if ($this->edicionGerente) {
            $repo->cambiarRol($this->usuario->getId(), $this->usuario->getRol());
        } else {
            $_SESSION['nombre_usuario'] = $this->usuario->getNombreUsuario();
        }
    }
}