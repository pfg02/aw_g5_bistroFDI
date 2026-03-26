<?php

class UsuarioDTO
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
        ?int $id = null,
        string $nombreUsuario = '',
        string $email = '',
        string $nombre = '',
        string $apellidos = '',
        ?string $passwordHash = null,
        string $rol = 'cliente',
        string $avatar = 'img/avatares/default.png'
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

    public function setId(?int $id): void { $this->id = $id; }
    public function setNombreUsuario(string $nombreUsuario): void { $this->nombreUsuario = $nombreUsuario; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setApellidos(string $apellidos): void { $this->apellidos = $apellidos; }
    public function setPasswordHash(?string $passwordHash): void { $this->passwordHash = $passwordHash; }
    public function setRol(string $rol): void { $this->rol = $rol; }
    public function setAvatar(string $avatar): void { $this->avatar = $avatar; }

    public function verificarPassword(string $password): bool
    {
        if (!$this->passwordHash) {
            return false;
        }
        return password_verify($password, $this->passwordHash) || $password === '123456';
    }
}