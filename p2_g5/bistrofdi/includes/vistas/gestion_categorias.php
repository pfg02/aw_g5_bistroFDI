<?php
// 1. Configuración y Dependencias
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../sesion.php';
require_once __DIR__ . '/../negocio/CategoriaService.php';
require_once __DIR__ . '/../negocio/CategoriaDTO.php';

// 2. Seguridad: Solo Gerente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header("Location: ../../index.php");
    exit();
}

$service = new CategoriaService($db);
$mensaje = "";
$clase_mensaje = "";

// 3. CONTROLADOR DE ACCIONES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'eliminar') {
        $id_a_borrar = intval($_POST['id']);

        if ($service->eliminarCategoria($id_a_borrar)) {
            $mensaje = "Categoría eliminada con éxito.";
            $clase_mensaje = "msg-exito";
        } else {
            $mensaje = "Error: No se puede eliminar una categoría que contiene productos vinculados.";
            $clase_mensaje = "msg-error";
        }
    }
}

// 4. Datos para la Vista
$categorias = $service->listarTodas();

$tituloPagina = 'Gestión de Categorías - Bistró FDI';
$bodyClass = 'admin-panel';
$extraHead = '<link rel="stylesheet" href="../../css/estilos.css">';

ob_start();
?>

<div class="container-gestion">
    <header class="header-gestion">
        <h1>Panel de Categorías</h1>
        <a href="editar_categoria.php" class="btn-primario"> + Nueva Categoría</a>
    </header>

    <?php if ($mensaje): ?>
        <div class="alerta-sistema <?= $clase_mensaje ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <section class="seccion-tabla">
        <table class="tabla-categorias">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="txt-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                    <tr><td colspan="4">No se han encontrado categorías.</td></tr>
                <?php else: ?>
                    <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td>
                            <img src="../../img/categorias/<?= htmlspecialchars($cat->imagen) ?>" 
                                 class="img-tabla-cat" alt="icono">
                        </td>
                        <td class="cat-nombre"><?= htmlspecialchars($cat->nombre) ?></td>
                        <td class="cat-desc"><?= htmlspecialchars($cat->descripcion) ?></td>
                        <td class="cat-acciones">
                            <a href="editar_categoria.php?id=<?= $cat->id ?>" class="btn-editar">Editar</a>
                            
                            <form action="gestion_categorias.php" method="POST" class="form-del-inline">
                                <input type="hidden" name="id" value="<?= $cat->id ?>">
                                <input type="hidden" name="accion" value="eliminar">
                                <button type="submit" class="btn-eliminar" 
                                        onclick="return confirm('¿Deseas eliminar permanentemente esta categoría?')">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/comun/plantilla.php';
?>