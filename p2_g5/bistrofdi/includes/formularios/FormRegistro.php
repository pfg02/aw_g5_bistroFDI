<?php

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../clases/RepositorioUsuarios.php';

class FormRegistro extends Formulario
{
    public function __construct()
    {
        parent::__construct('formRegistro', ['urlRedireccion' => 'login.php?registro=ok']);
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $nombreUsuario = self::h($datos['nombre_usuario'] ?? '');
        $email = self::h($datos['email'] ?? '');
        $nombre = self::h($datos['nombre'] ?? '');
        $apellidos = self::h($datos['apellidos'] ?? '');
        $erroresGlobales = self::listaErroresGlobales($this->errores);

        return <<<HTML
{$erroresGlobales}
<label>Nombre de usuario:
    <input type="text" name="nombre_usuario" value="{$nombreUsuario}" required>
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

<label>Contraseña:
    <input type="password" name="password" required>
</label><br><br>

<label>Repetir contraseña:
    <input type="password" name="password2" required>
</label><br><br>

<button type="submit">Crear cuenta</button>
HTML;
    }

    protected function procesaFormulario(array &$datos): void
    {
        $nombreUsuario = trim($datos['nombre_usuario'] ?? '');
        $email = trim($datos['email'] ?? '');
        $nombre = trim($datos['nombre'] ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');
        $password = $datos['password'] ?? '';
        $password2 = $datos['password2'] ?? '';

        if ($nombreUsuario === '' || $email === '' || $nombre === '' || $apellidos === '' || $password === '' || $password2 === '') {
            $this->errores[] = 'Todos los campos son obligatorios.';
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = 'El email no tiene un formato válido.';
            return;
        }

        if ($password !== $password2) {
            $this->errores[] = 'Las contraseñas no coinciden.';
            return;
        }

        $repo = new RepositorioUsuarios();

        if ($repo->buscarPorNombreUsuario($nombreUsuario)) {
            $this->errores[] = 'Ese nombre de usuario ya existe.';
            return;
        }

        if ($repo->existeEmail($email)) {
            $this->errores[] = 'Ese email ya está registrado.';
            return;
        }

        if (!$repo->registrar($nombreUsuario, $email, $nombre, $apellidos, $password)) {
            $this->errores[] = 'No se ha podido registrar el usuario.';
        }
    }
}