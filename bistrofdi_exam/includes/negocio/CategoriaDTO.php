<?php
declare(strict_types=1);

class CategoriaDTO
{
    public ?int $id;
    public string $nombre;
    public string $descripcion;
    public ?string $imagen;

    public function __construct(
        ?int $id = null,
        string $nombre = '',
        string $descripcion = '',
        ?string $imagen = 'default.png'
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->imagen = $imagen;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }
}