<?php
require_once __DIR__ . '/../Formulario.php';
require_once __DIR__ . '/../negocio/UsuarioController.php';

class FormularioRegistro extends Formulario
{
    private UsuarioController $controller;

    public function __construct(UsuarioController $controller)
    {
        parent::__construct('formRegistro', [
            'action' => 'registro.php',
            'class' => 'f0-form',
            'urlRedireccion' => 'login.php?registro=ok',
        ]);
        $this->controller = $controller;
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $nombreUsuario = htmlspecialchars(trim($datos['nombre_usuario'] ?? ''));
        $email = htmlspecialchars(trim($datos['email'] ?? ''));
        $nombre = htmlspecialchars(trim($datos['nombre'] ?? ''));
        $apellidos = htmlspecialchars(trim($datos['apellidos'] ?? ''));
        $errorGlobal = !empty($this->errores)
            ? '<div class="f0-msg-error">' . htmlspecialchars($this->errores[0]) . '</div>'
            : '';

        return <<<HTML
{$errorGlobal}
<label>
    Nombre de usuario
    <input type="text" name="nombre_usuario" value="{$nombreUsuario}" placeholder="Nombre de usuario" required>
</label>

<label>
    Email
    <input type="email" name="email" value="{$email}" placeholder="Correo electrónico" required>
</label>

<label>
    Nombre
    <input type="text" name="nombre" value="{$nombre}" placeholder="Nombre" required>
</label>

<label>
    Apellidos
    <input type="text" name="apellidos" value="{$apellidos}" placeholder="Apellidos" required>
</label>

<label>
    Contraseña
    <input type="password" name="password" placeholder="Contraseña" required>
</label>

<label>
    Repetir contraseña
    <input type="password" name="password2" placeholder="Repetir contraseña" required>
</label>

<div class="f0-form-actions">
    <button type="submit" class="f0-btn">Crear cuenta</button>
</div>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        [$ok, $mensaje] = $this->controller->procesarRegistro($datos);

        if (!$ok) {
            $this->errores[] = $mensaje;
        }

        return null;
    }
}