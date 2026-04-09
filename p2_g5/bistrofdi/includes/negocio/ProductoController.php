<?php
// includes/presentacion/ProductoController.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../negocio/ProductoService.php';
require_once __DIR__ . '/../negocio/ProductoDTO.php';

class ProductoController {
    private $service;

    public function __construct() {
        $this->service = new ProductoService();
    }

    public function manejarPeticion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../vistas/gestion_productos.php");
            exit();
        }

        $accion = $_POST['accion'] ?? '';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

        if ($accion === 'guardar') {
            $stock = (int)($_POST['stock'] ?? 0);

            // Validación previa en el Controller para ahorrar procesamiento
            if ($stock < 0) {
                $redirectId = $_POST['id'] ?? '';
                header("Location: ../vistas/editar_producto.php?id=$redirectId&error=stock");
                exit();
            }

            $imagen_actual = $_POST['imagen_actual'] ?? '';
            $nuevas_fotos = $this->service->procesarImagenes($_FILES);
            $imagen_final = $imagen_actual;

            if ($nuevas_fotos !== null) {
                if (empty($imagen_actual)) {
                    $imagen_final = $nuevas_fotos;
                } else {
                    $lista_viejas = array_filter(explode(',', $imagen_actual));
                    $lista_nuevas = array_filter(explode(',', $nuevas_fotos));
                    $combinadas = array_merge($lista_viejas, $lista_nuevas);
                    $imagen_final = implode(',', array_slice($combinadas, 0, 3));
                }
            }

            $dto = new ProductoDTO(
                $_POST['id'] ?: null,
                $_POST['nombre'] ?? '',
                $_POST['descripcion'] ?? '',
                $_POST['precio_base'] ?? 0,
                $stock,
                $imagen_final,
                $_POST['id_categoria'] ?? null,
                $_POST['ofertado'] ?? 1,
                $_POST['iva'] ?? 21
            );

            if ($this->service->guardarProducto($dto)) {
                header("Location: ../vistas/gestion_productos.php?msg=exito");
            } else {
                $redirectId = $_POST['id'] ?? '';
                header("Location: ../vistas/editar_producto.php?id=".$redirectId."&error=1");
            }
            exit();
        }

        if ($accion === 'eliminar' && $id) {
            if ($this->service->cambiarEstado($id, 0)) {
                header("Location: ../vistas/gestion_productos.php?msg=baja_ok");
            } else {
                header("Location: ../vistas/gestion_productos.php?msg=error");
            }
            exit();
        }

        if ($accion === 'reactivar' && $id) {
            if ($this->service->cambiarEstado($id, 1)) {
                header("Location: ../vistas/gestion_productos.php?msg=alta_ok");
            } else {
                header("Location: ../vistas/gestion_productos.php?msg=error");
            }
            exit();
        }

        header("Location: ../vistas/gestion_productos.php?msg=error");
        exit();
    }
}

$controller = new ProductoController();
$controller->manejarPeticion();