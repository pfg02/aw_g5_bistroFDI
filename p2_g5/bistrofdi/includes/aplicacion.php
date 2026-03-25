<?php

class Aplicacion
{
    private static $instancia;
    private array $bdDatosConexion = [];
    private ?mysqli $conn = null;

    public static function getInstance(): self
    {
        if (!self::$instancia instanceof self) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __construct()
    {
    }

    public function init(array $bdDatosConexion): void
    {
        $this->bdDatosConexion = $bdDatosConexion;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function shutdown(): void
    {
        if ($this->conn instanceof mysqli) {
            $this->conn->close();
            $this->conn = null;
        }
    }

    public function getConexionBd(): mysqli
    {
        if (!$this->conn instanceof mysqli) {
            $host = $this->bdDatosConexion['host'] ?? '';
            $user = $this->bdDatosConexion['user'] ?? '';
            $pass = $this->bdDatosConexion['pass'] ?? '';
            $bd   = $this->bdDatosConexion['bd'] ?? '';

            $this->conn = new mysqli($host, $user, $pass, $bd);
            if ($this->conn->connect_errno) {
                die('Error de conexión a la base de datos: ' . $this->conn->connect_error);
            }

            if (!$this->conn->set_charset('utf8mb4')) {
                die('Error al configurar UTF-8 en la base de datos: ' . $this->conn->error);
            }
        }

        return $this->conn;
    }

    public function usuarioLogueado(): bool
    {
        return isset($_SESSION['id_usuario']);
    }

    public function rolUsuario(): ?string
    {
        return $_SESSION['rol'] ?? null;
    }
}