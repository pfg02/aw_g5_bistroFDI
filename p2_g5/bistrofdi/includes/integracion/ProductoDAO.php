<?php
// includes/integracion/ProductoDAO.php
require_once __DIR__ . '/../negocio/ProductoDTO.php';

class ProductoDAO {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function listarTodos() {
        $productos = [];
        $sql = "SELECT p.*, c.nombre as cat_nombre FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id ORDER BY p.nombre ASC";
        $res = $this->db->query($sql);
        while ($row = $res->fetch_assoc()) {
            $p = new ProductoDTO(
                $row['id'], $row['nombre'], $row['descripcion'], 
                $row['precio_base'], $row['stock'], $row['imagen'], 
                $row['id_categoria'], $row['ofertado'], $row['iva'] ?? 21
            );
            $p->categoria_nombre = $row['cat_nombre'] ?? 'Sin categoría';
            $productos[] = $p;
        }
        return $productos;
    }

    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            return new ProductoDTO(
                $row['id'], $row['nombre'], $row['descripcion'], 
                $row['precio_base'], $row['stock'], $row['imagen'], 
                $row['id_categoria'], $row['ofertado'], $row['iva'] ?? 21
            );
        }
        return null;
    }

    public function guardar(ProductoDTO $p) {
        if ($p->id) {
            $sql = "UPDATE productos SET nombre=?, descripcion=?, precio_base=?, stock=?, imagen=?, id_categoria=?, ofertado=?, iva=? WHERE id=?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssdisiiii", $p->nombre, $p->descripcion, $p->precio, $p->stock, $p->imagen, $p->id_categoria, $p->ofertado, $p->iva, $p->id);
        } else {
            $sql = "INSERT INTO productos (nombre, descripcion, precio_base, stock, imagen, id_categoria, ofertado, iva) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssdisiii", $p->nombre, $p->descripcion, $p->precio, $p->stock, $p->imagen, $p->id_categoria, $p->ofertado, $p->iva);
        }
        return $stmt->execute();
    }

    public function actualizarEstado($id, $estado) {
        $stmt = $this->db->prepare("UPDATE productos SET ofertado = ? WHERE id = ?");
        $stmt->bind_param("ii", $estado, $id);
        return $stmt->execute();
    }
}