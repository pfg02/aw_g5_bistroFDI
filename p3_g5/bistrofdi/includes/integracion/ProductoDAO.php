<?php
declare(strict_types=1);

/**
 * Clase de acceso a datos para productos.
 */

require_once __DIR__ . '/../negocio/ProductoDTO.php';

class ProductoDAO
{
    private mysqli $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

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
            isset($row['iva']) ? (int) $row['iva'] : 21
        );
    }

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
        $iva = (int) $this->obtenerDatoProducto($p, 'iva');

        if ($id !== null) {
            $sql = "UPDATE productos
                    SET nombre = ?, descripcion = ?, precio_base = ?, stock = ?, imagen = ?, id_categoria = ?, ofertado = ?, iva = ?
                    WHERE id = ?";
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
                $iva,
                $id
            );
        } else {
            $sql = "INSERT INTO productos
                    (nombre, descripcion, precio_base, stock, imagen, id_categoria, ofertado, iva)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'ssdisiii',
                $nombre,
                $descripcion,
                $precio,
                $stock,
                $imagen,
                $idCategoria,
                $ofertado,
                $iva
            );
        }

        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

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
}