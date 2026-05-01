<?php
declare(strict_types=1);

// 1. Configuración y Dependencias
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/CategoriaService.php';
require_once __DIR__ . '/../../negocio/CategoriaDTO.php';

// 2. Seguridad: Solo Gerente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header("Location: ../../../index.php");
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
            $mensaje = "No se puede eliminar esta categoría porque tiene productos asociados.";
            $clase_mensaje = "msg-error";
        }
    }
}

// 4. Datos para la Vista
$categorias = $service->listarTodas();

$tituloPagina = 'Gestión de Categorías - Bistró FDI';
$bodyClass = 'admin-panel';
$extraHead = '<link rel="stylesheet" href="../../../css/estilos.css">';

ob_start();
?>

<div class="container-gestion">
    <header class="header-gestion">
        <h1>Panel de Categorías</h1>
        <a href="editar_categoria.php" class="btn-primario"> + Nueva Categoría</a>
    </header>

    <?php if ($mensaje): ?>
        <div class="alerta-sistema <?= htmlspecialchars($clase_mensaje, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <section class="seccion-tabla">
        <div class="tabla-responsive">
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
                        <tr>
                            <td colspan="4">No se han encontrado categorías.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $cat): ?>
                            <?php
                                $rutaImagenesWeb = '../../../img/categorias/';
                                $rutaImagenesFisica = __DIR__ . '/../../../img/categorias/';
                                $imagenPorDefecto = $rutaImagenesWeb . 'default.png';

                                $imagenCategoria = trim((string) ($cat->imagen ?? ''));
                                $fotoCategoria = $imagenPorDefecto;

                                if ($imagenCategoria !== '') {
                                    $nombreArchivo = basename($imagenCategoria);
                                    $rutaFisica = $rutaImagenesFisica . $nombreArchivo;

                                    if (is_file($rutaFisica)) {
                                        $fotoCategoria = $rutaImagenesWeb . $nombreArchivo;
                                    }
                                }

                                $nombreCategoria = (string) ($cat->nombre ?? '');
                                $descripcionCategoria = (string) ($cat->descripcion ?? '');
                            ?>

                            <tr>
                                <td data-label="Imagen">
                                    <img
                                        src="<?= htmlspecialchars($fotoCategoria, ENT_QUOTES, 'UTF-8') ?>"
                                        class="img-tabla-cat"
                                        alt="<?= htmlspecialchars($nombreCategoria, ENT_QUOTES, 'UTF-8') ?>"
                                        loading="lazy"
                                        onerror="this.onerror=null; this.src='../../../img/categorias/default.png';"
                                    >
                                </td>

                                <td data-label="Nombre" class="cat-nombre">
                                    <?= htmlspecialchars($nombreCategoria, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td data-label="Descripción" class="cat-desc">
                                    <?= htmlspecialchars($descripcionCategoria, ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td data-label="Acciones" class="cat-acciones">
                                    <a href="editar_categoria.php?id=<?= (int) $cat->id ?>" class="btn-editar">
                                        Editar
                                    </a>

                                    <form action="gestion_categorias.php" method="POST" class="form-del-inline">
                                        <input type="hidden" name="id" value="<?= (int) $cat->id ?>">
                                        <input type="hidden" name="accion" value="eliminar">

                                        <button
                                            type="submit"
                                            class="btn-eliminar"
                                            onclick="return confirm('¿Deseas eliminar permanentemente esta categoría?')"
                                        >
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';
?>