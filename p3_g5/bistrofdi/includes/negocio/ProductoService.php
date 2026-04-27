<?php
declare(strict_types=1);

require_once __DIR__ . '/../integracion/ProductoDAO.php';
require_once __DIR__ . '/ProductoDTO.php';

class ProductoService
{
    private ProductoDAO $dao;
    private array $ultimosErrores = [];

    public function __construct(?mysqli $db = null)
    {
    $this->dao = new ProductoDAO($db ?? Application::getInstance()->conexionBd());
    }

    public function listarTodos(): array
    {
        return $this->dao->listarTodos();
    }

    public function obtenerProducto(int $id): ProductoDTO
    {
        if ($id <= 0) {
            return new ProductoDTO();
        }

        return $this->dao->obtenerPorId($id) ?? new ProductoDTO();
    }

    public function getUltimosErrores(): array
    {
        return $this->ultimosErrores;
    }

    public function guardarProducto(ProductoDTO $dto): bool
    {
        $this->ultimosErrores = [];

        $nombre = trim((string) $this->obtenerDatoProducto($dto, 'nombre'));
        $descripcion = trim((string) $this->obtenerDatoProducto($dto, 'descripcion'));
        $precio = (float) $this->obtenerDatoProducto($dto, 'precio');
        $stock = (int) $this->obtenerDatoProducto($dto, 'stock');
        $iva = (int) $this->obtenerDatoProducto($dto, 'iva');
        $ofertado = (int) $this->obtenerDatoProducto($dto, 'ofertado');
        $requiereCocina = (int) $this->obtenerDatoProducto($dto, 'requiere_cocina');
        $idCategoria = (int) $this->obtenerDatoProducto($dto, 'id_categoria');

        $ivasPermitidos = [4, 10, 21];

        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $this->ultimosErrores[] = 'El nombre del producto es obligatorio y no puede superar 100 caracteres.';
        }

        if ($precio < 0 || $precio > 9999.99) {
            $this->ultimosErrores[] = 'El precio no es válido.';
        }

        if ($stock < 0 || $stock > 9999) {
            $this->ultimosErrores[] = 'El stock no es válido.';
        }

        if (!in_array($iva, $ivasPermitidos, true)) {
            $this->ultimosErrores[] = 'El IVA seleccionado no es válido.';
        }

        if (!in_array($ofertado, [0, 1], true)) {
            $this->ultimosErrores[] = 'El estado del producto no es válido.';
        }

        if (!in_array($requiereCocina, [0, 1], true)) {
            $this->ultimosErrores[] = 'El valor de preparación en cocina no es válido.';
        }

        if ($idCategoria <= 0) {
            $this->ultimosErrores[] = 'La categoría seleccionada no es válida.';
        }

        if (mb_strlen($descripcion) > 2000) {
            $this->ultimosErrores[] = 'La descripción no puede superar 2000 caracteres.';
        }

        if (!empty($this->ultimosErrores)) {
            return false;
        }

        return $this->dao->guardar($dto);
    }

    public function cambiarEstado(int $id, int $estado): bool
    {
        if ($id <= 0 || !in_array($estado, [0, 1], true)) {
            return false;
        }

        return $this->dao->actualizarEstado($id, $estado);
    }

    public function procesarImagenes(array $files): ?string
    {
        $this->ultimosErrores = [];
        $nombres = [];

        $directorio = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'productos' . DIRECTORY_SEPARATOR;

        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            $this->ultimosErrores[] = 'No se pudo crear el directorio de imágenes.';
            return null;
        }

        $permitidas = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);

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
                $this->ultimosErrores[] = "La imagen {$i} no se pudo subir correctamente.";
                continue;
            }

            if (($archivo['size'] ?? 0) > 2 * 1024 * 1024) {
                $this->ultimosErrores[] = "La imagen {$i} supera el tamaño máximo de 2 MB.";
                continue;
            }

            $tmp = (string) ($archivo['tmp_name'] ?? '');

            if ($tmp === '' || !is_uploaded_file($tmp)) {
                $this->ultimosErrores[] = "El archivo de la imagen {$i} no es válido.";
                continue;
            }

            $mime = $finfo->file($tmp);

            if (!isset($permitidas[$mime])) {
                $this->ultimosErrores[] = "La imagen {$i} debe ser JPG, PNG, WEBP o GIF.";
                continue;
            }

            $extension = $permitidas[$mime];
            $nuevoNombre = 'prod_' . time() . '_img' . $i . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
            $rutaDestino = $directorio . $nuevoNombre;

            if (!move_uploaded_file($tmp, $rutaDestino)) {
                $this->ultimosErrores[] = "No se pudo guardar la imagen {$i}.";
                continue;
            }

            $nombres[] = $nuevoNombre;
        }

        if (!empty($this->ultimosErrores)) {
            return null;
        }

        return !empty($nombres) ? implode(',', $nombres) : null;
    }

    private function obtenerDatoProducto(ProductoDTO $dto, string $campo)
    {
        $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $campo)));

        if (method_exists($dto, $getter)) {
            return $dto->$getter();
        }

        if (isset($dto->$campo)) {
            return $dto->$campo;
        }

        return null;
    }
}