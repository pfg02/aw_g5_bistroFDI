<?php
declare(strict_types=1);

/**
 * Clase de acceso a datos para productos.
 * Centraliza las consultas SQL relacionadas con productos.
 */

require_once __DIR__ . '/../negocio/ProductoDTO.php';

class ProductoDAO
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    // Convierte una fila de base de datos en un ProductoDTO.
    // Si se añaden nuevos campos a productos, deben mapearse aquí
    // y también añadirse en ProductoDTO, guardar(), formularios y vistas.
    private function mapear(array $row): ProductoDTO
    {
        return new ProductoDTO(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) ($row['nombre'] ?? ''),
            $row['descripcion'] !== null ? (string) $row['descripcion'] : '',
            isset($row['precio_base']) ? (float) $row['precio_base'] : 0.0,
            isset($row['stock']) ? (int) $row['stock'] : 0,
            (string) ($row['imagen'] ?? ''),
            isset($row['id_categoria']) ? (int) $row['id_categoria'] : 0,
            isset($row['ofertado']) ? (int) $row['ofertado'] : 1,
            isset($row['requiere_cocina']) ? (int) $row['requiere_cocina'] : 1,
            isset($row['iva']) ? (int) $row['iva'] : 21
        );
    }

    // Lista todos los productos para administración.
    // Usa LEFT JOIN para mostrar también productos sin categoría asociada.
    // Si se necesitan más datos relacionados, se pueden añadir con otros JOIN.
    public function listarTodos(): array
    {
        $productos = [];

        $sql = "SELECT p.*, c.nombre AS cat_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                ORDER BY p.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $producto = $this->mapear($row);
            $producto->categoria_nombre = $row['cat_nombre'] ?? 'Sin categoría';
            $productos[] = $producto;
        }

        $res->free();
        $stmt->close();

        return $productos;
    }

    // Obtiene un producto concreto por id.
    // Se usa normalmente para cargar formularios de edición o validar operaciones.
    public function obtenerPorId(int $id): ?ProductoDTO
    {
        $sql = "SELECT * FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $result->free();
        $stmt->close();

        return $row ? $this->mapear($row) : null;
    }

    // Guarda un producto.
    // Si el DTO tiene id, actualiza. Si no tiene id, inserta.
    // Al añadir campos nuevos a productos, revisar:
    // SELECT/mapear, INSERT, UPDATE, bind_param, DTO y formulario.
    public function guardar(ProductoDTO $p): bool
    {
        $id = $this->obtenerDatoProducto($p, 'id');
        $nombre = (string) $this->obtenerDatoProducto($p, 'nombre');
        $descripcion = (string) $this->obtenerDatoProducto($p, 'descripcion');
        $precio = (float) $this->obtenerDatoProducto($p, 'precio');
        $stock = (int) $this->obtenerDatoProducto($p, 'stock');
        $imagen = (string) $this->obtenerDatoProducto($p, 'imagen');
        $idCategoria = (int) $this->obtenerDatoProducto($p, 'id_categoria');
        $ofertado = (int) $this->obtenerDatoProducto($p, 'ofertado');
        $requiereCocina = (int) $this->obtenerDatoProducto($p, 'requiere_cocina');
        $iva = (int) $this->obtenerDatoProducto($p, 'iva');

        if ($id !== null) {
            $sql = "UPDATE productos
                    SET nombre = ?, descripcion = ?, precio_base = ?, stock = ?, imagen = ?, id_categoria = ?, ofertado = ?, requiere_cocina = ?, iva = ?
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'ssdisiiiii',
                $nombre,
                $descripcion,
                $precio,
                $stock,
                $imagen,
                $idCategoria,
                $ofertado,
                $requiereCocina,
                $iva,
                $id
            );
        } else {
            $sql = "INSERT INTO productos
                    (nombre, descripcion, precio_base, stock, imagen, id_categoria, ofertado, requiere_cocina, iva)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'ssdisiiii',
                $nombre,
                $descripcion,
                $precio,
                $stock,
                $imagen,
                $idCategoria,
                $ofertado,
                $requiereCocina,
                $iva
            );
        }

        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    // Actualiza solo la visibilidad/oferta del producto.
    // Separar operaciones concretas evita modificar campos que no forman parte del cambio.
    public function actualizarEstado(int $id, int $estado): bool
    {
        $sql = "UPDATE productos SET ofertado = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $estado, $id);

        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    /**
     * Obtiene solo los productos visibles para los clientes en la carta.
     */
    public function listarOfertados(): array
    {
        $productos = [];

        $sql = "SELECT p.*, c.nombre AS cat_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.id_categoria = c.id
                WHERE p.ofertado = 1 AND p.stock > 0
                ORDER BY c.nombre ASC, p.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $producto = $this->mapear($row);
            $producto->categoria_nombre = $row['cat_nombre'] ?? 'Sin categoría';
            $productos[] = $producto;
        }

        $res->free();
        $stmt->close();

        return $productos;
    }

    // Obtiene datos del DTO usando getter si existe.
    // Permite trabajar con propiedades públicas antiguas o métodos getX().
    private function obtenerDatoProducto(ProductoDTO $dto, string $campo)
    {
        $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $campo)));

        if (method_exists($dto, $getter)) {
            return $dto->$getter();
        }

        if (isset($dto->$campo)) {
            return $dto->$campo;
        }

        return null;
    }

    // Patrón para datos auxiliares asociados a productos:
    // 1. Crear método para listar opciones disponibles.
    // 2. Crear método para obtener opciones asociadas a un producto.
    // 3. Al guardar, actualizar el producto principal.
    // 4. Borrar asociaciones anteriores si la relación es múltiple.
    // 5. Insertar de nuevo las opciones seleccionadas.
    // 6. Usar JOIN si se necesitan mostrar los datos relacionados en catálogo o administración.
}