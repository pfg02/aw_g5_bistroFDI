<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/ProductoService.php';

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
                    // Combinamos las viejas con las nuevas
                    $lista_viejas = explode(',', $imagen_actual);
                    $lista_nuevas = explode(',', $nuevas_fotos);
                    $combinadas = array_merge($lista_viejas, $lista_nuevas);
                    // Limitamos a un máximo de 3 imágenes totales
                    $resultado = array_slice($combinadas, 0, 3);
                    $imagen_final = implode(',', $resultado);
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
                $_POST['ofertado'],
                $_POST['iva']
            );

            if ($this->service->guardarProducto($dto)) {
                header("Location: ../vistas/gestion_productos.php?msg=exito");
            } else {
                header("Location: ../vistas/editar_producto.php?id=".$_POST['id']."&error=1");
            }
            exit();
        }

        // Lógica de activar/eliminar (se mantiene igual)
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