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

    // Punto de entrada para las operaciones de administración de productos.
    // Centraliza la recepción de acciones por POST y redirige según el resultado.
    public function manejarPeticion(): void
    {
        // Las operaciones que modifican datos deben llegar por POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_productos.php');
            exit();
        }

        // Acción solicitada desde el formulario o botón de administración.
        $accion = trim((string) (filter_input(INPUT_POST, 'accion', FILTER_UNSAFE_RAW) ?? ''));

        // Identificador del producto, si la operación afecta a un producto existente.
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        if ($accion === 'guardar') {
            $this->procesarGuardado($id !== false ? $id : null);
            return;
        }

        if ($accion === 'eliminar' && $id !== false && $id !== null) {
            $this->procesarCambioEstado((int) $id, 0, 'baja_ok');
            return;
        }

        if ($accion === 'reactivar' && $id !== false && $id !== null) {
            $this->procesarCambioEstado((int) $id, 1, 'alta_ok');
            return;
        }

        header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_productos.php?msg=error');
        exit();
    }

    // Procesa creación o edición de producto.
    // Flujo habitual:
    // 1. Leer datos del formulario.
    // 2. Validar valores básicos.
    // 3. Procesar imágenes si existen.
    // 4. Crear DTO.
    // 5. Delegar guardado en el servicio.
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
        $requiereCocina = filter_input(INPUT_POST, 'requiere_cocina', FILTER_VALIDATE_INT);
        $iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_INT);
        $imagenActual = trim((string) (filter_input(INPUT_POST, 'imagen_actual', FILTER_UNSAFE_RAW) ?? ''));

        // Validación inicial de campos numéricos y obligatorios.
        // Las reglas más concretas se completan en el servicio.
        if (
            $precioBase === false ||
            $stock === false ||
            $idCategoria === false ||
            $idCategoria === null ||
            $ofertado === false ||
            $requiereCocina === false ||
            $requiereCocina === null ||
            $iva === false
        ) {
            $this->redirigirEdicion($id, '1');
        }

        if ((int) $stock < 0) {
            $this->redirigirEdicion($id, 'stock');
        }

        // Validación de nombres de imágenes ya guardadas.
        // Evita conservar rutas inesperadas o nombres no permitidos.
        if (!$this->imagenesActualesValidas($imagenActual)) {
            $this->redirigirEdicion($id, '1');
        }

        // Procesamiento de nuevas imágenes subidas desde el formulario.
        $nuevasFotos = $this->service->procesarImagenes($_FILES);

        if (!empty($this->service->getUltimosErrores())) {
            $this->redirigirEdicion($id, '1');
        }

        $imagenFinal = $imagenActual;

        // Mantiene imágenes anteriores y añade nuevas sin superar el máximo establecido.
        if ($nuevasFotos !== null) {
            if ($imagenActual === '') {
                $imagenFinal = $nuevasFotos;
            } else {
                $listaViejas = array_filter(array_map('trim', explode(',', $imagenActual)));
                $listaNuevas = array_filter(array_map('trim', explode(',', $nuevasFotos)));
                $imagenFinal = implode(',', array_slice(array_merge($listaViejas, $listaNuevas), 0, 3));
            }
        }

        // Construcción del DTO con los datos principales del producto.
        // Si se añaden campos nuevos al formulario, deben incluirse aquí
        // y también validarse en el servicio y guardarse desde el DAO.
        $dto = new ProductoDTO(
            $id,
            $nombre,
            $descripcion,
            (float) $precioBase,
            (int) $stock,
            $imagenFinal,
            (int) $idCategoria,
            (int) $ofertado,
            (int) $requiereCocina,
            (int) $iva
        );

        if ($this->service->guardarProducto($dto)) {
            header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_productos.php?msg=exito');
            exit();
        }

        $this->redirigirEdicion($id, '1');
    }

    // Cambia un estado simple del producto.
    // Sirve como patrón para operaciones concretas que modifican solo un campo.
    private function procesarCambioEstado(int $id, int $estado, string $mensajeOk): void
    {
        if ($this->service->cambiarEstado($id, $estado)) {
            header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_productos.php?msg=' . rawurlencode($mensajeOk));
            exit();
        }

        header('Location: ' . BASE_URL . '/includes/vistas/admin/gestion_productos.php?msg=error');
        exit();
    }

    // Redirige de vuelta al formulario de edición conservando el id si existe.
    // Permite mostrar un mensaje de error en la vista correspondiente.
    private function redirigirEdicion(?int $id, string $error): void
    {
        $url = BASE_URL . '/includes/vistas/admin/editar_producto.php?error=' . rawurlencode($error);

        if ($id !== null) {
            $url .= '&id=' . $id;
        }

        header('Location: ' . $url);
        exit();
    }

    // Comprueba que las imágenes ya guardadas tengan nombres válidos.
    // Solo permite nombres de archivo, no rutas completas.
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

    // Patrón para ampliar guardado de productos:
    // 1. Leer el nuevo dato desde POST.
    // 2. Validarlo en el controlador o servicio.
    // 3. Añadirlo al DTO si pertenece al producto principal.
    // 4. Guardarlo en INSERT/UPDATE desde el DAO.
    // 5. Si es una relación múltiple, guardar primero el producto principal
    //    y después actualizar las asociaciones correspondientes.
}

$controller = new ProductoController();
$controller->manejarPeticion();