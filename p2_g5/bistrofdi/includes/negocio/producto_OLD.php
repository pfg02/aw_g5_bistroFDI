<?php
require_once __DIR__ . '/../config.php';

class Producto {
    public $id, $id_categoria, $nombre, $descripcion, $precio_base, $iva, $stock, $ofertado, $imagen, $categoria_nombre;

    public function __construct($fila = []) {
        $this->id = $fila['id'] ?? null;
        $this->id_categoria = $fila['id_categoria'] ?? null;
        $this->nombre = $fila['nombre'] ?? '';
        $this->descripcion = $fila['descripcion'] ?? '';
        $this->precio_base = $fila['precio_base'] ?? 0;
        $this->iva = $fila['iva'] ?? 21;
        $this->stock = $fila['stock'] ?? 0;
        $this->ofertado = $fila['ofertado'] ?? 1;
        $this->imagen = $fila['imagen'] ?? null;
        $this->categoria_nombre = $fila['categoria_nombre'] ?? null;
    }

    public static function todos() {
        global $db;
        $productos = [];
        $query = "SELECT p.*, c.nombre AS categoria_nombre 
                  FROM productos p
                  LEFT JOIN categorias c ON p.id_categoria = c.id
                  ORDER BY p.nombre ASC";
        $res = $db->query($query);

        if ($res) {
            while ($fila = $res->fetch_assoc()) {
                $productos[] = new Producto($fila);
            }
            $res->free();
        }

        return $productos;
    }

    public static function buscaPorId($id) {
        global $db;
        $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $fila = $result->fetch_assoc();
        $result->free();

        $stmt->close();

        return $fila ? new Producto($fila) : null;
    }

    public function guarda() {
        global $db;
        if ($this->id) {
            $stmt = $db->prepare("UPDATE productos SET id_categoria=?, nombre=?, descripcion=?, precio_base=?, iva=?, stock=?, ofertado=?, imagen=? WHERE id=?");
            $stmt->bind_param("issdiiisi", $this->id_categoria, $this->nombre, $this->descripcion, $this->precio_base, $this->iva, $this->stock, $this->ofertado, $this->imagen, $this->id);
        } else {
            $stmt = $db->prepare("INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, iva, stock, ofertado, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdiiis", $this->id_categoria, $this->nombre, $this->descripcion, $this->precio_base, $this->iva, $this->stock, $this->ofertado, $this->imagen);
        }

        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function borraPorId($id) {
        global $db;
        $stmt = $db->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}