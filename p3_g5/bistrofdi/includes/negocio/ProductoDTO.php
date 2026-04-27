<?php
declare(strict_types=1);

class ProductoDTO
{
    public ?int $id;
    public string $nombre;
    public string $descripcion;
    public float $precio;
    public int $stock;
    public ?string $imagen;
    public ?int $id_categoria;
    public int $ofertado;
    public int $requiere_cocina;
    public int $iva;
    public ?string $categoria_nombre;

    public function __construct(
        ?int $id = null,
        string $nombre = '',
        string $descripcion = '',
        float $precio = 0.0,
        int $stock = 0,
        ?string $imagen = null,
        ?int $id_categoria = null,
        int $ofertado = 1,
        int $requiere_cocina = 1,
        int $iva = 21,
        ?string $categoria_nombre = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->stock = max(0, $stock);
        $this->imagen = $imagen;
        $this->id_categoria = $id_categoria;
        $this->ofertado = $ofertado;
        $this->requiere_cocina = $requiere_cocina;
        $this->iva = $iva;
        $this->categoria_nombre = $categoria_nombre;
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

    public function getPrecio(): float
    {
        return $this->precio;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function getIdCategoria(): ?int
    {
        return $this->id_categoria;
    }

    public function getOfertado(): int
    {
        return $this->ofertado;
    }

    public function getRequiereCocina(): int
    {
        return $this->requiere_cocina;
    }

    public function getIva(): int
    {
        return $this->iva;
    }

    public function getCategoriaNombre(): ?string
    {
        return $this->categoria_nombre;
    }

    public function getPrecioFinal(): float
    {
        return $this->precio * (1 + ($this->iva / 100));
    }

    public function obtenerEstadoStock(): string
    {
        return $this->stock > 0 ? 'En stock' : 'Sin stock';
    }

    public function requiereCocina(): bool
    {
        return $this->requiere_cocina === 1;
    }
}