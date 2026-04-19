<?php
require_once __DIR__ . '/../core/formulario.php';
require_once __DIR__ . '/../negocio/UsuarioController.php';

class FormularioLogin extends Formulario
{
    private UsuarioController $controller;

    public function __construct(UsuarioController $controller)
    {
        parent::__construct('formLogin', [
            'action' => 'login.php',
            'class' => 'f0-form',
            'urlRedireccion' => '../../../index.php',
        ]);
        $this->controller = $controller;
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $nombreUsuario = htmlspecialchars($datos['nombre_usuario'] ?? '');
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
    Contraseña
    <input type="password" name="password" placeholder="Contraseña" required>
</label>

<div class="f0-form-actions">
    <button type="submit" class="f0-btn">Entrar</button>
</div>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        [$ok, $mensaje] = $this->controller->procesarLogin($datos);

        if (!$ok) {
            $this->errores[] = $mensaje;
        }

        return null;
    }
}