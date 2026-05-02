<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';
require_once __DIR__ . '/../usuarios/tablaUsuarios.php';

exigirRol('gerente');

// Vista de administración.
// La vista no debe ejecutar SQL directamente.
// Los datos se piden al controlador y la presentación se delega en componentes o tablas.
$controller = new UsuarioController();

// Listado principal de usuarios.
// Si se necesitan datos relacionados, crear un método específico en controller/service/DAO
// y sustituir este listado por uno más completo.
$usuarios = $controller->obtenerListaUsuarios();

// Componente encargado de renderizar la tabla.
// Si hay que añadir acciones por fila, normalmente se añaden dentro de TablaUsuarios.
$tablaUsuarios = new TablaUsuarios($usuarios);

ob_start();
?>
<section class="f0-page">
    <h1 class="f0-page-title">Gestión de usuarios</h1>

    <div class="f0-page-content">
        <div class="f0-admin-top">
            <div class="f0-msg-info">
                Administración de usuarios, roles y acceso del personal y clientes.
            </div>
            <a href="<?= BASE_URL ?>/index.php" class="f0-btn-secondary">Volver al inicio</a>
        </div>

        <?php
        // Flujo habitual de ampliación en vistas de administración:
        // 1. Cargar registros principales desde el controlador.
        // 2. Cargar opciones auxiliares si hay selects o relaciones.
        // 3. Mostrar los datos en tabla o componente.
        // 4. Enviar cambios mediante formularios POST.
        // 5. Procesar los cambios en una acción separada.
        // 6. Redirigir de vuelta a esta vista con mensaje de resultado.
        ?>

        <?= $tablaUsuarios->render(); ?>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Gestionar usuarios - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/../partials/plantilla.php';