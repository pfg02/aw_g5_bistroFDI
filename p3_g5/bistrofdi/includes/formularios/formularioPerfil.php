<?php
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
            'action' => 'perfil.php',
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
        $nombreUsuario = htmlspecialchars($this->nombreUsuario);
        $email = htmlspecialchars(trim($datos['email'] ?? ''));
        $nombre = htmlspecialchars(trim($datos['nombre'] ?? ''));
        $apellidos = htmlspecialchars(trim($datos['apellidos'] ?? ''));
        $errorGlobal = !empty($this->errores)
            ? '<div class="f0-msg-error">' . htmlspecialchars($this->errores[0]) . '</div>'
            : '';

        return <<<HTML
{$errorGlobal}
<div class="f0-form-grid">
    <label>
        Nombre de usuario
        <input type="text" value="{$nombreUsuario}" disabled>
    </label>

    <label>
        Email
        <input type="email" name="email" value="{$email}" required>
    </label>

    <label>
        Nombre
        <input type="text" name="nombre" value="{$nombre}" required>
    </label>

    <label>
        Apellidos
        <input type="text" name="apellidos" value="{$apellidos}" required>
    </label>
</div>

<div class="f0-form-actions">
    <button type="submit" class="f0-btn">Guardar cambios</button>
    <a href="index.php" class="f0-btn-secondary">Volver al inicio</a>
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