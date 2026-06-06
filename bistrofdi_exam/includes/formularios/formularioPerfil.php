<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/formulario.php';
require_once __DIR__ . '/../negocio/UsuarioController.php';

class FormularioPerfil extends Formulario
{
    private UsuarioController $controller;
    private int $idUsuario;
    private array $valoresIniciales;
    private string $nombreUsuario;

    public function __construct(UsuarioController $controller, int $idUsuario, array $valoresIniciales, string $nombreUsuario)
    {
        parent::__construct('formPerfil', [
            'action' => BASE_URL . '/includes/vistas/perfil/perfil.php',
            'method' => 'POST',
            'class' => 'f0-form',
        ]);

        $this->controller = $controller;
        $this->idUsuario = $idUsuario;
        $this->valoresIniciales = $valoresIniciales;
        $this->nombreUsuario = $nombreUsuario;
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $datos = array_merge($this->valoresIniciales, $datos);

        $nombreUsuario = htmlspecialchars($this->nombreUsuario, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim((string) ($datos['email'] ?? '')), ENT_QUOTES, 'UTF-8');
        $nombre = htmlspecialchars(trim((string) ($datos['nombre'] ?? '')), ENT_QUOTES, 'UTF-8');
        $apellidos = htmlspecialchars(trim((string) ($datos['apellidos'] ?? '')), ENT_QUOTES, 'UTF-8');

        $errorGlobal = !empty($this->errores)
            ? '<div class="f0-msg-error">' . htmlspecialchars($this->errores[0], ENT_QUOTES, 'UTF-8') . '</div>'
            : '';

        $urlInicio = BASE_URL . '/index.php';

        return <<<HTML
{$errorGlobal}
<div class="f0-form-grid">
    <label for="nombre_usuario">
        Nombre de usuario
        <input type="text" id="nombre_usuario" value="{$nombreUsuario}" disabled>
    </label>

    <label for="email">
        Email
        <input type="email" id="email" name="email" value="{$email}" required maxlength="100">
    </label>

    <label for="nombre">
        Nombre
        <input type="text" id="nombre" name="nombre" value="{$nombre}" required maxlength="50">
    </label>

    <label for="apellidos">
        Apellidos
        <input type="text" id="apellidos" name="apellidos" value="{$apellidos}" required maxlength="100">
    </label>
</div>

<div class="f0-form-actions">
    <button type="submit" class="f0-btn">Guardar cambios</button>
    <a href="{$urlInicio}" class="f0-btn-secondary">Volver al inicio</a>
</div>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        [$ok, $texto] = $this->controller->procesarPerfil($this->idUsuario, $datos);

        if ($ok) {
            $this->mensajeExito = $texto;

            $usuario = $this->controller->obtenerUsuarioPorId($this->idUsuario);
            if ($usuario) {
                $this->valoresIniciales = [
                    'email' => $usuario->getEmail(),
                    'nombre' => $usuario->getNombre(),
                    'apellidos' => $usuario->getApellidos(),
                ];
                $this->nombreUsuario = $usuario->getNombreUsuario();
            }

            return null;
        }

        $this->errores[] = $texto;
        return null;
    }

    protected function getValoresTrasExito(array $datos): array
    {
        return $this->valoresIniciales;
    }
}