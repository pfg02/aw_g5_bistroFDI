<?php

/**
	* Clase de acceso a datos para categorías.
*/

class CategoriaDAO {
    
	private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function obtenerPorId($id) {
        $sql = "SELECT id, nombre, descripcion, imagen FROM categorias WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $resultado->free();

        $stmt->close();
        return $fila; // Retorna array para que el Service lo convierta a DTO
    }

    public function listarTodas() {
        $sql = "SELECT id, nombre, descripcion, imagen FROM categorias ORDER BY nombre ASC";
        $resultado = $this->db->query($sql);
        $categorias = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $categorias[] = $fila;
            }
            $resultado->free();
        }

        return $categorias;
    }

    public function guardar($nombre, $descripcion, $imagen, $id = null) {
        if ($id) {
            // UPDATE
            $sql = "UPDATE categorias SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("sssi", $nombre, $descripcion, $imagen, $id);
        } else {
            // INSERT
            $sql = "INSERT INTO categorias (nombre, descripcion, imagen) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("sss", $nombre, $descripcion, $imagen);
        }
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function eliminar($id) {
        // Intentamos borrar. Si hay productos vinculados, la BD lanzará error (FK)
        // y el Service devolverá 'false'.
        try {
            $sql = "DELETE FROM categorias WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $id);
            $ok = $stmt->execute();
            $stmt->close();
            return $ok;
        } catch (Exception $e) {
            return false;
        }
    }
}