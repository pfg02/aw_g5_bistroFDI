<?php
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../negocio/UsuarioController.php';
require_once __DIR__ . '/../usuarios/tablaUsuarios.php';

exigirRol('gerente');

$controller = new UsuarioController();
$usuarios = $controller->obtenerListaUsuarios();

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

        <?= $tablaUsuarios->render(); ?>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Gestionar usuarios - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/../partials/plantilla.php';