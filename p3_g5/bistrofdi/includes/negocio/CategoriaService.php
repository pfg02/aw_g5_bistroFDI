<?php
require_once __DIR__ . '/../integracion/CategoriaDAO.php';
require_once __DIR__ . '/../negocio/CategoriaDTO.php';

class CategoriaService {
    private $dao;

    public function __construct($db) {
        $this->dao = new CategoriaDAO($db);
    }

    // ESTE ES EL MÉTODO QUE TE DABA EL FATAL ERROR
    public function obtenerPorId($id) {
        $datos = $this->dao->obtenerPorId($id);
        if (!$datos) return null;

        // Convertimos el array de la BD en un Objeto DTO (Requisito POO)
        return new CategoriaDTO(
            $datos['id'],
            $datos['nombre'],
            $datos['descripcion'],
            $datos['imagen']
        );
    }

    public function listarTodas() {
        $listaDatos = $this->dao->listarTodas();
        $objetos = [];
        foreach ($listaDatos as $d) {
            $objetos[] = new CategoriaDTO($d['id'], $d['nombre'], $d['descripcion'], $d['imagen']);
        }
        return $objetos;
    }

    public function guardarCategoria($nombre, $descripcion, $imagen, $id = null) {
        // Aquí podrías meter lógica de negocio (ej: validar que el nombre no esté vacío)
        if (empty($nombre)) return false;
        return $this->dao->guardar($nombre, $descripcion, $imagen, $id);
    }

    public function eliminarCategoria($id) {
        // Lógica de negocio: No dejar borrar si tiene productos (opcional)
        return $this->dao->eliminar($id);
    }
}