<?php
class Producto {
    public $id;
    public $id_categoria;
    public $nombre;
    public $descripcion;
    public $precio_base;
    public $iva;
    public $stock;
    public $ofertado;
    public $imagen; 
    public $categoria_nombre;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->id_categoria = $data['id_categoria'] ?? null;
        $this->nombre = $data['nombre'] ?? '';
        $this->descripcion = $data['descripcion'] ?? '';
        $this->precio_base = $data['precio_base'] ?? 0;
        $this->iva = $data['iva'] ?? 21;
        $this->stock = $data['stock'] ?? 0;
        $this->ofertado = $data['ofertado'] ?? 1;
        $this->imagen = $data['imagen'] ?? '';
        $this->categoria_nombre = $data['categoria_nombre'] ?? ($data['nombre_categoria'] ?? '');
    }

    public function getPrecioFinal() {
        // Aseguramos que los valores sean numéricos para evitar errores de cálculo
        return (float)$this->precio_base * (1 + ((int)$this->iva / 100));
    }

    public static function listarTodos($db) {
        // Usamos LEFT JOIN para traer el nombre de la categoría en una sola consulta
        $query = "SELECT p.*, c.nombre AS categoria_nombre 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.id_categoria = c.id 
                  ORDER BY p.id DESC";
        $res = $db->query($query);
        $productos = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $productos[] = new Producto($row);
            }
        }
        return $productos;
    }

    public static function obtenerPorId($db, $id) {
        $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? new Producto($res) : null;
    }

    public function guardar($db) {
        if ($this->id) {
            // UPDATE
            $stmt = $db->prepare("UPDATE productos SET id_categoria=?, nombre=?, descripcion=?, precio_base=?, iva=?, stock=?, ofertado=?, imagen=? WHERE id=?");
            // Corregido el orden y los tipos: i=int, s=string, d=double
            $stmt->bind_param("isssdiiii", $this->id_categoria, $this->nombre, $this->descripcion, $this->precio_base, $this->iva, $this->stock, $this->ofertado, $this->imagen, $this->id);
        } else {
            // INSERT
            $stmt = $db->prepare("INSERT INTO productos (id_categoria, nombre, descripcion, precio_base, iva, stock, ofertado, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            // ERROR CORREGIDO: Se eliminó el espacio en "isssdii s" -> ahora es "isssdiis"
            $stmt->bind_param("isssdiis", $this->id_categoria, $this->nombre, $this->descripcion, $this->precio_base, $this->iva, $this->stock, $this->ofertado, $this->imagen);
        }
        return $stmt->execute();
    }

    public static function eliminar($db, $id) {
        $stmt = $db->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}