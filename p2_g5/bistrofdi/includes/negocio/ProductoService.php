<?php
// includes/negocio/ProductoService.php
require_once __DIR__ . '/../integracion/ProductoDAO.php';
require_once __DIR__ . '/ProductoDTO.php';

class ProductoService {
    private $dao;
    public function __construct($db) { $this->dao = new ProductoDAO($db); }

    public function listarTodos() { return $this->dao->listarTodos(); }
    public function obtenerProducto($id) { return $this->dao->obtenerPorId($id) ?? new ProductoDTO(); }
    
    public function guardarProducto(ProductoDTO $dto) {
        // REGLA DE NEGOCIO: No permitir nombres vacíos, precios negativos o stock negativo
        if (empty(trim($dto->nombre)) || $dto->precio < 0 || $dto->stock < 0) {
            return false;
        }
        return $this->dao->guardar($dto);
    }

    public function cambiarEstado($id, $estado) {
        return $this->dao->actualizarEstado((int)$id, (int)$estado);
    }

    public function procesarImagenes($files) {
        $nombres = [];
        $directorio = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'productos' . DIRECTORY_SEPARATOR;
        if (!is_dir($directorio)) mkdir($directorio, 0777, true);

        for ($i = 1; $i <= 3; $i++) {
            $clave = "foto" . $i;
            if (isset($files[$clave]) && $files[$clave]['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($files[$clave]['name'], PATHINFO_EXTENSION);
                $nuevo_nombre = time() . "_img" . $i . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($files[$clave]['tmp_name'], $directorio . $nuevo_nombre)) {
                    $nombres[] = $nuevo_nombre;
                }
            }
        }
        return !empty($nombres) ? implode(',', $nombres) : null;
    }
}