<?php
// includes/presentacion/ProductoController.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../negocio/ProductoService.php';

class ProductoController {
    private $service;
    public function __construct($db) { $this->service = new ProductoService($db); }

    public function manejarPeticion() {
        $accion = $_REQUEST['accion'] ?? '';
        $id = $_REQUEST['id'] ?? null;

        if ($accion === 'guardar') {
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
                $_POST['nombre'],
                $_POST['descripcion'] ?? '',
                $_POST['precio_base'],
                $_POST['stock'],
                $imagen_final,
                $_POST['id_categoria'],
                $_POST['ofertado'] ?? 1,
                $_POST['iva'] ?? 21
            );

            if ($this->service->guardarProducto($dto)) {
                header("Location: ../vistas/gestion_productos.php?msg=exito");
            } else {
                header("Location: ../vistas/editar_producto.php?id=".$_POST['id']."&error=1");
            }
            exit();
        }

        if ($accion === 'activar' || $accion === 'reactivar') {
            if ($id) $this->service->darDeAlta($id);
            header("Location: ../vistas/gestion_productos.php?msg=alta_ok");
            exit();
        }

        if ($accion === 'eliminar') {
            if ($id) $this->service->darDeBaja($id);
            header("Location: ../vistas/gestion_productos.php?msg=baja_ok");
            exit();
        }
    }
}

$controller = new ProductoController($db);
$controller->manejarPeticion();