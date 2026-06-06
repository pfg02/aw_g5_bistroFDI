<?php

declare(strict_types=1);

require_once __DIR__ . '/../../negocio/UsuarioDTO.php';

class TablaUsuarios
{
    /** @var UsuarioDTO[] */
    private array $usuarios;

    public function __construct(array $usuarios)
    {
        $this->usuarios = $usuarios;
    }

    // Renderiza la tabla completa de usuarios.
    // Si en el futuro se necesitan columnas adicionales, se añaden en el <thead>
    // y también dentro del método renderFila().
    public function render(): string
    {
        if (empty($this->usuarios)) {
            return '<div class="f0-msg-info">No hay usuarios registrados.</div>';
        }

        $html = <<<HTML
<div class="f0-table-wrap">
    <table class="f0-user-table f0-user-table-mobile">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
HTML;

        foreach ($this->usuarios as $usuario) {
            if ($usuario instanceof UsuarioDTO) {
                $html .= $this->renderFila($usuario);
            }
        }

        $html .= <<<HTML
        </tbody>
    </table>
</div>
HTML;

        return $html;
    }

    // Renderiza una fila de la tabla.
    // Aquí se preparan los datos de cada usuario antes de mostrarlos.
    // Si se añaden datos relacionados, normalmente se obtienen desde el DTO
    // o desde un listado específico cargado previamente desde el controlador.
    private function renderFila(UsuarioDTO $usuario): string
    {
        $id = $this->esc((string)$usuario->getId());
        $idUrl = urlencode((string)$usuario->getId());

        $nombreUsuario = $this->esc($usuario->getNombreUsuario());
        $email = $this->esc($usuario->getEmail());
        $nombre = $this->esc($usuario->getNombre());
        $apellidos = $this->esc($usuario->getApellidos());
        $rol = $this->esc($usuario->getRol());
        $rolClase = $this->esc('f0-role-' . $usuario->getRol());

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';

        return <<<HTML
            <tr>
                <td data-label="ID">{$id}</td>
                <td data-label="Usuario">
                    <span class="f0-user-pill">{$nombreUsuario}</span>
                </td>
                <td data-label="Email">{$email}</td>
                <td data-label="Nombre">{$nombre}</td>
                <td data-label="Apellidos">{$apellidos}</td>
                <td data-label="Rol">
                    <span class="f0-role-badge {$rolClase}">
                        {$rol}
                    </span>
                </td>
                <td data-label="Acciones">
                    <div class="f0-actions">
                        <a href="{$baseUrl}/includes/vistas/admin/cambiarRol.php?id={$idUrl}" class="f0-btn">Editar</a>
                        <a href="{$baseUrl}/includes/vistas/pedido/mis_pedidos.php?id_cliente={$idUrl}" class="f0-btn">Ver Pedidos</a>
                        <a href="{$baseUrl}/includes/vistas/admin/borrarUsuario.php?id={$idUrl}" class="f0-btn-danger">Borrar</a>
                    </div>
                </td>
            </tr>
HTML;
    }

    // Escapa cualquier valor antes de mostrarlo en HTML.
    // Usar siempre este método al pintar datos que vienen de usuarios o base de datos.
    private function esc(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function __toString(): string
    {
        return $this->render();
    }

    // Patrón habitual para ampliar tablas de administración:
    // 1. Añadir la nueva columna en el <thead>.
    // 2. Obtener el dato en renderFila().
    // 3. Escapar el valor antes de imprimirlo.
    // 4. Si hay que modificar datos, crear formulario POST por fila.
    // 5. Procesar la actualización en una acción separada.
    // 6. Redirigir de vuelta a la vista principal con mensaje.
}