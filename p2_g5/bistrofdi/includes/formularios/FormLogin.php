<?php

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../clases/RepositorioUsuarios.php';

class FormLogin extends Formulario
{
    public function __construct()
    {
        parent::__construct('formLogin', ['urlRedireccion' => 'index.php']);
    }

    protected function generaCamposFormulario(array &$datos): string
    {
        $nombreUsuario = self::h($datos['nombre_usuario'] ?? '');
        $registroOk = isset($_GET['registro']) && $_GET['registro'] === 'ok';
        $mensajeOk = $registroOk
            ? '<p style="color:green;">Usuario registrado correctamente. Ya puedes iniciar sesión.</p>'
            : '';

        $erroresGlobales = self::listaErroresGlobales($this->errores);

        return <<<HTML
{$mensajeOk}
{$erroresGlobales}

<label>Nombre de usuario:
    <input type="text" name="nombre_usuario" value="{$nombreUsuario}" required>
</label><br><br>

<label>Contraseña:
    <input type="password" name="password" required>
</label><br><br>

<button type="submit">Entrar</button>
HTML;
    }

    protected function procesaFormulario(array &$datos): void
    {
        $nombreUsuario = trim($datos['nombre_usuario'] ?? '');
        $password = $datos['password'] ?? '';

        if ($nombreUsuario === '' || $password === '') {
            $this->errores[] = 'Debes rellenar usuario y contraseña.';
            return;
        }

        $repo = new RepositorioUsuarios();
        $usuario = $repo->autenticar($nombreUsuario, $password);

        if (!$usuario) {
            $this->errores[] = 'Usuario no encontrado o contraseña incorrecta.';
            return;
        }

        $usuario->iniciarSesion();
    }
}