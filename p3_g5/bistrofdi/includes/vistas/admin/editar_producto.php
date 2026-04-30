<?php

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/ProductoService.php';
require_once __DIR__ . '/../../negocio/CategoriaService.php';
require_once __DIR__ . '/../../core/formulario.php';
require_once __DIR__ . '/../../formularios/formularioEditarProducto.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'] ?? null;
$service = new ProductoService();
$catService = new CategoriaService($db);

$producto = $id ? $service->obtenerProducto($id) : new ProductoDTO();
$categorias = $catService->listarTodas();

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