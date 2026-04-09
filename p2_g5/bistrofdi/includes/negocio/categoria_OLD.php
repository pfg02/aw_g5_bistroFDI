<?php
require_once __DIR__ . '/../config.php';

class Categoria {
    public $id, $nombre, $descripcion, $imagen;

    public function __construct($fila = []) {
        $this->id = $fila['id'] ?? null;
        $this->nombre = $fila['nombre'] ?? '';
        $this->descripcion = $fila['descripcion'] ?? '';
        $this->imagen = $fila['imagen'] ?? 'default.png';
    }

    public static function todas() {
        global $db;
        $categorias = [];

        $stmt = $db->prepare("SELECT * FROM categorias ORDER BY nombre ASC");
        $stmt->execute();

        $res = $stmt->get_result();
        while ($fila = $res->fetch_assoc()) {
            $categorias[] = new Categoria($fila);
        }

        $res->free();
        $stmt->close();

        return $categorias;
    }

    public static function buscaPorId($id) {
        global $db;
        $stmt = $db->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $fila = $result->fetch_assoc();
        $result->free();

        $stmt->close();

        return $fila ? new Categoria($fila) : null;
    }

    public function guarda() {
        global $db;
        if ($this->id) {
            $stmt = $db->prepare("UPDATE categorias SET nombre=?, descripcion=?, imagen=? WHERE id=?");
            $stmt->bind_param("sssi", $this->nombre, $this->descripcion, $this->imagen, $this->id);
        } else {
            $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $this->nombre, $this->descripcion, $this->imagen);
        }

        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function borraPorId($id) {
        global $db;
        $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}