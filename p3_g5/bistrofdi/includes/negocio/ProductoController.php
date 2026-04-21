<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/ProductoService.php';
require_once __DIR__ . '/ProductoDTO.php';

class ProductoController
{
    private ProductoService $service;

    public function __construct()
    {
        $this->service = new ProductoService();
    }

    public function manejarPeticion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_productos.php');
            exit();
        }

        $accion = trim((string) (filter_input(INPUT_POST, 'accion', FILTER_UNSAFE_RAW) ?? ''));
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]) ?: null;

        if ($accion === 'guardar') {
            $this->procesarGuardado($id);
            return;
        }

        if ($accion === 'eliminar' && $id !== null) {
            $this->procesarCambioEstado($id, 0, 'baja_ok');
            return;
        }

        if ($accion === 'reactivar' && $id !== null) {
            $this->procesarCambioEstado($id, 1, 'alta_ok');
            return;
        }

        header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_productos.php?msg=error');
        exit();
    }

    private function procesarGuardado(?int $id): void
    {
        $nombre = trim((string) (filter_input(INPUT_POST, 'nombre', FILTER_UNSAFE_RAW) ?? ''));
        $descripcion = trim((string) (filter_input(INPUT_POST, 'descripcion', FILTER_UNSAFE_RAW) ?? ''));
        $precioBase = filter_input(INPUT_POST, 'precio_base', FILTER_VALIDATE_FLOAT);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        $idCategoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);
        $ofertado = filter_input(INPUT_POST, 'ofertado', FILTER_VALIDATE_INT);
        $iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_INT);
        $imagenActual = trim((string) (filter_input(INPUT_POST, 'imagen_actual', FILTER_UNSAFE_RAW) ?? ''));

        if ($stock === false || $stock < 0) {
            $this->redirigirEdicion($id, 'stock');
        }

        if (
            $nombre === '' ||
            $precioBase === false || $precioBase < 0 ||
            $idCategoria === false || $idCategoria <= 0 ||
            !in_array((int) $ofertado, [0, 1], true) ||
            !in_array((int) $iva, [4, 10, 21], true) ||
            !$this->imagenesActualesValidas($imagenActual)
        ) {
            $this->redirigirEdicion($id, '1');
        }

        $resultadoImagenes = $this->service->procesarImagenes($_FILES);

        if (($resultadoImagenes['error'] ?? null) !== null) {
            $this->redirigirEdicion($id, '1');
        }

        $nuevasFotos = $resultadoImagenes['imagenes'] ?? null;
        $imagenFinal = $this->combinarImagenes($imagenActual, $nuevasFotos);

        $dto = new ProductoDTO(
            $id,
            $nombre,
            $descripcion,
            (float) $precioBase,
            (int) $stock,
            $imagenFinal,
            (int) $idCategoria,
            (int) $ofertado,
            (int) $iva
        );

        if ($this->service->guardarProducto($dto)) {
            header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_productos.php?msg=exito');
            exit();
        }

        $this->redirigirEdicion($id, '1');
    }

    private function procesarCambioEstado(int $id, int $estado, string $msgOk): void
    {
        if ($this->service->cambiarEstado($id, $estado)) {
            header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_productos.php?msg=' . rawurlencode($msgOk));
            exit();
        }

        header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_productos.php?msg=error');
        exit();
    }

    private function redirigirEdicion(?int $id, string $error): void
    {
        $url = BASE_URL . '/includes/vistas/admin/editar_producto.php?error=' . rawurlencode($error);

        if ($id !== null) {
            $url .= '&id=' . $id;
        }

        header('Location: ' . $url);
        exit();
    }

    private function combinarImagenes(string $imagenActual, ?string $nuevasFotos): string
    {
        if ($nuevasFotos === null || trim($nuevasFotos) === '') {
            return $imagenActual;
        }

        if (trim($imagenActual) === '') {
            return $nuevasFotos;
        }

        $listaViejas = array_filter(array_map('trim', explode(',', $imagenActual)));
        $listaNuevas = array_filter(array_map('trim', explode(',', $nuevasFotos)));
        $combinadas = array_merge($listaViejas, $listaNuevas);

        return implode(',', array_slice($combinadas, 0, 3));
    }

    private function imagenesActualesValidas(string $imagenes): bool
    {
        if ($imagenes === '') {
            return true;
        }

        foreach (explode(',', $imagenes) as $imagen) {
            $imagen = trim($imagen);

            if ($imagen === '') {
                continue;
            }

            if (preg_match('/^[a-zA-Z0-9._-]+\.(jpg|jpeg|png|webp|gif)$/i', $imagen) !== 1) {
                return false;
            }
        }

        return true;
    }
}

$controller = new ProductoController();
$controller->manejarPeticion();