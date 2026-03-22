<?php
class Categoria {
    public $id;
    public $nombre;
    public $descripcion;
    public $imagen;

    public function __construct($row = []) {
        $this->id = $row['id'] ?? null;
        $this->nombre = $row['nombre'] ?? '';
        $this->descripcion = $row['descripcion'] ?? '';
        $this->imagen = $row['imagen'] ?? 'default_cat.png';
    }

    public static function listarTodas($db) {
        $res = $db->query("SELECT * FROM categorias ORDER BY nombre ASC");
        $categorias = [];
        while ($row = $res->fetch_assoc()) {
            $categorias[] = new Categoria($row);
        }
        return $categorias;
    }

    public static function obtenerPorId($db, $id) {
        $stmt = $db->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? new Categoria($res) : null;
    }

    public function guardar($db) {
        if ($this->id) {
            $stmt = $db->prepare("UPDATE categorias SET nombre=?, descripcion=?, imagen=? WHERE id=?");
            $stmt->bind_param("sssi", $this->nombre, $this->descripcion, $this->imagen, $this->id);
        } else {
            $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $this->nombre, $this->descripcion, $this->imagen);
        }
        return $stmt->execute();
    }

    public static function eliminar($db, $id) {
        $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}