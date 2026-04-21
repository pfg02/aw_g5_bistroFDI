<?php
require_once __DIR__ . '/../integracion/ProductoDAO.php';
require_once __DIR__ . '/ProductoDTO.php';

class ProductoService {
    private $dao;

    public function __construct() {
        $this->dao = new ProductoDAO();
    }

    public function listarTodos() {
        return $this->dao->listarTodos();
    }

    public function obtenerProducto($id) {
        return $this->dao->obtenerPorId($id) ?? new ProductoDTO();
    }
    
    public function guardarProducto(ProductoDTO $dto) {
        $nombre = trim((string) $dto->nombre);
        $descripcion = trim((string) $dto->descripcion);
        $precio = $dto->precio;
        $stock = $dto->stock;
        $idCategoria = $dto->id_categoria;
        $ofertado = $dto->ofertado;
        $iva = $dto->iva;

        if ($nombre === '' || mb_strlen($nombre) > 100) {
            return false;
        }

        if (!is_numeric($precio) || (float) $precio < 0) {
            return false;
        }

        if (!is_int($stock) && filter_var($stock, FILTER_VALIDATE_INT) === false) {
            return false;
        }

        if ((int) $stock < 0) {
            return false;
        }

        if (mb_strlen($descripcion) > 2000) {
            return false;
        }

        if (filter_var($idCategoria, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            return false;
        }

        if (!in_array((int) $ofertado, [0, 1], true)) {
            return false;
        }

        if (!in_array((int) $iva, [4, 10, 21], true)) {
            return false;
        }

        return $this->dao->guardar($dto);
    }

    public function cambiarEstado($id, $estado) {
        return $this->dao->actualizarEstado((int)$id, (int)$estado);
    }

    public function procesarImagenes(array $files): array {
        $nombres = [];
        $directorio = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'productos' . DIRECTORY_SEPARATOR;

        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            return ['imagenes' => null, 'error' => 'No se pudo crear el directorio de imágenes.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tiposPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        for ($i = 1; $i <= 3; $i++) {
            $clave = 'foto' . $i;

            if (!isset($files[$clave])) {
                continue;
            }

            $archivo = $files[$clave];
            $error = $archivo['error'] ?? UPLOAD_ERR_NO_FILE;

            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                return ['imagenes' => null, 'error' => "Error al subir la imagen {$i}."];
            }

            if (($archivo['size'] ?? 0) > 2 * 1024 * 1024) {
                return ['imagenes' => null, 'error' => "La imagen {$i} no puede superar los 2 MB."];
            }

            $tmp = $archivo['tmp_name'] ?? '';
            if ($tmp === '' || !is_uploaded_file($tmp)) {
                return ['imagenes' => null, 'error' => "El archivo de la imagen {$i} no es válido."];
            }

            $mime = $finfo->file($tmp);
            if (!isset($tiposPermitidos[$mime])) {
                return ['imagenes' => null, 'error' => "La imagen {$i} debe ser JPG, PNG, WEBP o GIF."];
            }

            $nuevoNombre = 'prod_' . uniqid('', true) . '.' . $tiposPermitidos[$mime];
            $rutaDestino = $directorio . $nuevoNombre;

            if (!move_uploaded_file($tmp, $rutaDestino)) {
                return ['imagenes' => null, 'error' => "No se pudo guardar la imagen {$i}."];
            }

            $nombres[] = $nuevoNombre;
        }

        return ['imagenes' => !empty($nombres) ? implode(',', $nombres) : null, 'error' => null];
    }
}
