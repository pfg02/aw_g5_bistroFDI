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
            
            // Rellenamos el DTO con sus alérgenos
            $producto->setAlergenos($this->obtenerInfoAlergenosProducto((int)$producto->getId()));
            
            $productos[] = $producto;
        }

        $res->free();
        $stmt->close();

        return $productos;
    }

    // Devuelve un array de IDs de alérgenos asociados a ese producto.
    public function obtenerAlergenosProducto(int $idProducto): array
    {
        $sql = "SELECT alergeno_id FROM productos_alergenos WHERE producto_id = ?";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $idProducto);
        $stmt->execute();

        $result = $stmt->get_result();
        $alergenosIds = [];

        while ($row = $result->fetch_assoc()) {
            $alergenosIds[] = (int) $row['alergeno_id'];
        }

        $result->free();
        $stmt->close();

        return $alergenosIds;
    }

    // Obtiene la información completa de los alérgenos.
    public function obtenerInfoAlergenosProducto(int $idProducto): array
    {
        $sql = "SELECT a.id, a.nombre, a.descripcion, a.imagen
                FROM alergenos a
                INNER JOIN productos_alergenos pa ON a.id = pa.alergeno_id
                WHERE pa.producto_id = ?";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $idProducto);
        $stmt->execute();

        $result = $stmt->get_result();
        $alergenos = [];

        while ($row = $result->fetch_assoc()) {
            $alergenos[] = [
                'id' => (int) $row['id'],
                'nombre' => (string) $row['nombre'],
                'descripcion' => (string) $row['descripcion'],
                'imagen' => (string) $row['imagen']
            ];
        }

        $result->free();
        $stmt->close();

        return $alergenos;
    }

    // Listar todos los alérgenos disponibles (Para el FormularioEditarProducto)
    public function listarTodosAlergenos(): array
    {
        $sql = "SELECT * FROM alergenos ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $alergenos = [];
        
        while ($row = $result->fetch_assoc()) {
             $alergenos[] = [
                 'id' => (int) $row['id'],
                 'nombre' => (string) $row['nombre'],
                 'descripcion' => (string) $row['descripcion'],
                 'imagen' => (string) $row['imagen']
             ];
        }
        
        $result->free();
        $stmt->close();
        
        return $alergenos;
    }

    public function eliminarAlergenosProductos(int $idProducto): bool
    {
        try {
            $sql = 'DELETE FROM productos_alergenos WHERE producto_id = ?';
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $idProducto);

            $ok = $stmt->execute();
            $stmt->close();

            return $ok;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function vincularProductoAlergeno(int $idProducto, int $idAlergeno): bool
    {
        $sql = "INSERT INTO productos_alergenos
                (producto_id, alergeno_id)
                VALUES (?, ?)";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ii', $idProducto, $idAlergeno);

        $exito = $stmt->execute();

        $stmt->close();

        return $exito;
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

        if ($row) {
            $producto = $this->mapear($row);
            $producto->setAlergenos($this->obtenerAlergenosProducto($id));
            return $producto;
        }

        return null;
    }

    // Guarda un producto.
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
        
        if ($exito && $id === null) {
             $id = $this->db->insert_id;
             $p->id = $id;
        }
        $stmt->close();
        
        // Si todo salió bien, gestionamos los alérgenos
        if ($exito && $id !== null) {
            // Borramos los que ya tuviera
            $this->eliminarAlergenosProductos($id);
            // Insertamos los que haya pedido guardar
            $alergenos = $p->getAlergenos();
            foreach ($alergenos as $idAlergeno) {
                $this->vincularProductoAlergeno($id, (int)$idAlergeno);
            }
        }

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
            
            // Rellenamos el DTO con sus alérgenos
            $producto->setAlergenos($this->obtenerInfoAlergenosProducto((int)$producto->getId()));
            
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