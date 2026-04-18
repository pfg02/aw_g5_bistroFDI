<?php
require_once __DIR__ . '/../sesion.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../negocio/CategoriaController.php';
require_once __DIR__ . '/../formularios/formularioEditarCategoria.php';

exigirLogin();
exigirRol('gerente');

$controller = new CategoriaController($db);
$categoria = null;
$esEdicion = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idCategoria = (int) $_GET['id'];
    $categoria = $controller->obtenerPorId($idCategoria);

    if (!$categoria) {
        header('Location: gestion_categorias.php');
        exit();
    }

    $esEdicion = true;
}

$form = new FormularioEditarCategoria($controller, $categoria);
$htmlForm = $form->gestiona();

$tituloPagina = $esEdicion ? 'Bistró FDI - Editar Categoría' : 'Bistró FDI - Nueva Categoría';
$bodyClass = 'fondo-madera';
$extraHead = '<link rel="stylesheet" href="' . BASE_URL . '/css/editar_categoria.css">';

ob_start();
?>

<div class="catalogo-admin">
    <div class="header-gestion">
        <h1><?= $esEdicion ? 'Editar Categoría' : 'Nueva Categoría' ?></h1>
    </div>

    <?= $htmlForm ?>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/comun/plantilla.php';