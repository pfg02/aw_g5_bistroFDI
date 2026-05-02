<?php
declare(strict_types=1);

class ProductoDTO
{
    // Propiedades principales del producto.
    // Si se añade un nuevo campo en la tabla productos, revisar también:
    // constructor, getters, ProductoDAO::mapear(), ProductoDAO::guardar()
    // y el formulario de creación/edición.
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

    // Dato relacionado obtenido mediante JOIN con categorías.
    // No pertenece necesariamente a la tabla principal de productos,
    // pero se usa para mostrar información más completa en vistas.
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

        // Se evita guardar stock negativo dentro del objeto.
        $this->stock = max(0, $stock);

        $this->imagen = $imagen;
        $this->id_categoria = $id_categoria;
        $this->ofertado = $ofertado;
        $this->requiere_cocina = $requiere_cocina;
        $this->iva = $iva;
        $this->categoria_nombre = $categoria_nombre;
    }

    // Getters del producto.
    // Se usan desde DAO, servicios y vistas para acceder a los datos
    // sin depender directamente de cómo se construye el objeto.
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

    // Método calculado.
    // No se guarda directamente en base de datos: se obtiene a partir del precio base y el IVA.
    public function getPrecioFinal(): float
    {
        return $this->precio * (1 + ($this->iva / 100));
    }

    // Método auxiliar para mostrar estado del stock en vistas.
    public function obtenerEstadoStock(): string
    {
        return $this->stock > 0 ? 'En stock' : 'Sin stock';
    }

    // Método auxiliar para saber si el producto pasa por cocina.
    public function requiereCocina(): bool
    {
        return $this->requiere_cocina === 1;
    }

    // Patrón para ampliar el DTO:
    // 1. Añadir la propiedad.
    // 2. Añadir el parámetro al constructor con valor por defecto.
    // 3. Asignar la propiedad dentro del constructor.
    // 4. Crear getter.
    // 5. Mapear el campo en ProductoDAO::mapear().
    // 6. Añadirlo al INSERT/UPDATE si pertenece a la tabla principal.
    //
    // Para datos relacionados mediante otra tabla:
    // - se puede usar una propiedad auxiliar si solo se va a mostrar;
    // - si es una relación múltiple, normalmente se carga desde el DAO
    //   con métodos específicos y no como campo simple del producto.
}