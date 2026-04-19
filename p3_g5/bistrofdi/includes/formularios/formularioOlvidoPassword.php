<?php
require_once __DIR__ . '/../core/formulario.php';
require_once __DIR__ . '/../negocio/UsuarioController.php';

class FormularioOlvidoPassword extends Formulario
{
    private UsuarioController $controller;

    public function __construct(UsuarioController $controller)
    {
        parent::__construct('formOlvidoPassword', [
            'action' => 'olvidoPassword.php',
            'class' => 'f0-form',
        ]);
        $this->controller = $controller;
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $email = htmlspecialchars(trim($datos['email'] ?? ''));
        $errorGlobal = !empty($this->errores)
            ? '<div class="f0-msg-error">' . htmlspecialchars($this->errores[0]) . '</div>'
            : '';

        return <<<HTML
{$errorGlobal}
<label>
    Email
    <input type="email" name="email" value="{$email}" placeholder="Correo electrónico" required>
</label>

<div class="f0-form-actions">
    <button type="submit" class="f0-btn">Enviar correo de recuperación</button>
</div>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        [$ok, $texto] = $this->controller->procesarSolicitudRecuperacion($datos);

        if ($ok) {
            $this->mensajeExito = $texto;
            return null;
        }

        $this->errores[] = $texto;
        return null;
    }

    protected function getValoresTrasExito(array $datos): array
    {
        return ['email' => ''];
    }
}