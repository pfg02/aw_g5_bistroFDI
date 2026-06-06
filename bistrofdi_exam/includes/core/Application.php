<?php
declare(strict_types=1);

class Application
{
    private static ?Application $instancia = null;

    private array $datosConexion = [];
    private ?mysqli $conexion = null;
    private string $baseUrl = '';
    private string $basePath = '';

    private const JERARQUIA_ROLES = [
        'cliente'  => 1,
        'camarero' => 2,
        'cocinero' => 3,
        'gerente'  => 4,
    ];

    public static function getInstance(): Application
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }

        return self::$instancia;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new RuntimeException('No se puede deserializar Application.');
    }

    public function init(array $datosConexion, string $baseUrl, string $basePath): void
    {
        $this->datosConexion = $datosConexion;
        $this->baseUrl = $baseUrl;
        $this->basePath = $basePath;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function conexionBd(): mysqli
    {
        if ($this->conexion === null) {
            $host = $this->datosConexion['host'] ?? 'localhost';
            $user = $this->datosConexion['user'] ?? '';
            $pass = $this->datosConexion['pass'] ?? '';
            $bd = $this->datosConexion['bd'] ?? '';

            $this->conexion = new mysqli($host, $user, $pass, $bd);

            if ($this->conexion->connect_errno) {
                die('Error de conexión a la base de datos: ' . $this->conexion->connect_error);
            }

            if (!$this->conexion->set_charset('utf8mb4')) {
                die('Error al configurar UTF-8 en la base de datos: ' . $this->conexion->error);
            }
        }

        return $this->conexion;
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function usuarioLogueado(): bool
    {
        return isset($_SESSION['id_usuario']);
    }

    public function rolActual(): ?string
    {
        return $_SESSION['rol'] ?? null;
    }

    public function logoutUsuario(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public function tieneRolMinimo(string $rolMinimo): bool
    {
        if (!$this->usuarioLogueado()) {
            return false;
        }

        $nivelUsuario = self::JERARQUIA_ROLES[$this->rolActual() ?? ''] ?? 0;
        $nivelRequerido = self::JERARQUIA_ROLES[$rolMinimo] ?? PHP_INT_MAX;

        return $nivelUsuario >= $nivelRequerido;
    }

    public function tieneAlgunoDeLosRoles(string ...$rolesPermitidos): bool
    {
        if (!$this->usuarioLogueado()) {
            return false;
        }

        $rolUsuario = $this->rolActual();

        return $rolUsuario !== null && in_array($rolUsuario, $rolesPermitidos, true);
    }

    public function exigirLogin(): void
    {
        if (!$this->usuarioLogueado()) {
            header('Location: ' . $this->baseUrl . '/login.php');
            exit;
        }
    }

    public function exigirRol(string $rolMinimo, string ...$rolesAdicionales): void
    {
        $tienePermiso = empty($rolesAdicionales)
            ? $this->tieneRolMinimo($rolMinimo)
            : $this->tieneAlgunoDeLosRoles($rolMinimo, ...$rolesAdicionales);

        if (!$tienePermiso) {
            header('HTTP/1.1 403 Forbidden');
            echo '<p>No tienes permisos suficientes para acceder a esta página.</p>';
            exit;
        }
    }
}