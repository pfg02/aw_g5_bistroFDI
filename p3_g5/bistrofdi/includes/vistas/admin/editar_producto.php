<?php

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/ProductoService.php';
require_once __DIR__ . '/../../negocio/CategoriaService.php';
require_once __DIR__ . '/../../core/formulario.php';
require_once __DIR__ . '/../../formularios/FormularioEditarProducto.php';

// Acceso restringido a administración.
// Esta vista permite crear o modificar productos.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header("Location: ../auth/login.php");
    exit();
}

// Si llega id por GET, se carga producto existente.
// Si no llega id, se crea un DTO vacío para alta nueva.
$id = $_GET['id'] ?? null;

$service = new ProductoService();
$catService = new CategoriaService($db);

// Carga del producto principal.
// El service devuelve un DTO vacío si el id no es válido o no encuentra el producto.
$producto = $id ? $service->obtenerProducto($id) : new ProductoDTO();

// Carga de datos auxiliares necesarios para el formulario.
// En este caso se cargan categorías para pintar el select.
$categorias = $catService->listarTodas();

// Construcción del formulario.
// Si el formulario necesita nuevas opciones auxiliares, se pueden pasar por constructor
// igual que se hace con categorías.
$formulario = new FormularioEditarProducto($producto, $categorias, $id);

$tituloPagina = ($id ? 'Editar' : 'Nuevo') . ' Producto';
$bodyClass = 'admin-panel';
$extraHead = '<link rel="stylesheet" href="../../../css/estilos.css">';

ob_start();
?>

<div class="container-gestion">
    <header class="header-gestion">
        <h1><?= $id ? 'EDITAR' : 'NUEVO' ?> PRODUCTO</h1>
    </header>

    <section class="formulario-admin">
        <?= $formulario->gestiona() ?>
    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';
?>

<?php
// Patrón para ampliar esta pantalla:
// 1. Cargar el producto principal desde el servicio.
// 2. Cargar datos auxiliares necesarios para selects o checkboxes.
// 3. Pasar esos datos al formulario.
// 4. Pintar el formulario con valores actuales si se edita.
// 5. Enviar el POST al controlador correspondiente.
// 6. Validar y guardar desde controller/service/DAO.
// 7. Redirigir a gestión con mensaje de resultado.
?>