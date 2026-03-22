<?php
// includes/negocio/ProductoService.php
require_once __DIR__ . '/../integracion/ProductoDAO.php';
require_once __DIR__ . '/ProductoDTO.php';

class ProductoService {
    private $dao;

    public function __construct($db) {
        // El Service inicializa su propio DAO
        $this->dao = new ProductoDAO($db);
    }

    public function listarTodos() {
        // Delega totalmente en el DAO
        return $this->dao->listarTodos();
    }

    public function obtenerProducto($id) {
        return $this->dao->obtenerPorId($id) ?? new ProductoDTO();
    }

    public function guardarProducto(ProductoDTO $dto) {
        // Aquí podrías añadir lógica de negocio, ej: validar precios
        if ($dto->precio < 0) return false;
        return $this->dao->guardar($dto);
    }

    public function darDeBaja($id) {
        return $this->dao->actualizarEstado($id, 0);
    }

    public function darDeAlta($id) {
        return $this->dao->actualizarEstado($id, 1);
    }

    // He movido el procesamiento de imágenes aquí para centralizar la lógica
    public function procesarImagenes($files) {
        $nombres = [];
        foreach ($files as $f) {
            if ($f['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $nuevo_nombre = time() . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($f['tmp_name'], "../../img/productos/" . $nuevo_nombre)) {
                    $nombres[] = $nuevo_nombre;
                }
            }
        }
        return count($nombres) > 0 ? implode(',', $nombres) : null;
    }
}