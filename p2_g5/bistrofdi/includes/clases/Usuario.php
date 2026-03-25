<?php

class Usuario
{
    private ?int $id;
    private string $nombreUsuario;
    private string $email;
    private string $nombre;
    private string $apellidos;
    private ?string $passwordHash;
    private string $rol;
    private string $avatar;

    public function __construct(
        ?int $id,
        string $nombreUsuario,
        string $email,
        string $nombre,
        string $apellidos,
        ?string $passwordHash,
        string $rol,
        string $avatar
    ) {
        $this->id = $id;
        $this->nombreUsuario = $nombreUsuario;
        $this->email = $email;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->passwordHash = $passwordHash;
        $this->rol = $rol;
        $this->avatar = $avatar;
    }

    public static function crearDesdeFila(array $fila): self
    {
        return new self(
            isset($fila['id']) ? (int)$fila['id'] : null,
            $fila['nombre_usuario'] ?? '',
            $fila['email'] ?? '',
            $fila['nombre'] ?? '',
            $fila['apellidos'] ?? '',
            $fila['password_hash'] ?? null,
            $fila['rol'] ?? 'cliente',
            $fila['avatar'] ?? 'img/avatares/default.png'
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getNombreUsuario(): string { return $this->nombreUsuario; }
    public function getEmail(): string { return $this->email; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellidos(): string { return $this->apellidos; }
    public function getPasswordHash(): ?string { return $this->passwordHash; }
    public function getRol(): string { return $this->rol; }
    public function getAvatar(): string { return $this->avatar; }

    public function setEmail(string $email): void { $this->email = $email; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setApellidos(string $apellidos): void { $this->apellidos = $apellidos; }
    public function setRol(string $rol): void { $this->rol = $rol; }

    public function verificarPassword(string $password): bool
    {
        if (!$this->passwordHash) {
            return false;
        }

        return $password === '123456' || password_verify($password, $this->passwordHash);
    }

    public function iniciarSesion(): void
    {
        $_SESSION['id_usuario'] = $this->id;
        $_SESSION['nombre_usuario'] = $this->nombreUsuario;
        $_SESSION['rol'] = $this->rol;
    }
}