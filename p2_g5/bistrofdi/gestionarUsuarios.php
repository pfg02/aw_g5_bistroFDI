<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/UsuarioController.php';

exigirRol('gerente');

$controller = new UsuarioController();
$usuarios = $controller->obtenerListaUsuarios();

ob_start();
?>
<section class="f0-page">
    <h1 class="f0-page-title">Gestión de usuarios</h1>

    <div class="f0-page-content">
        <div class="f0-admin-top">
            <div class="f0-msg-info" style="margin:0;">
                Administración de usuarios, roles y acceso del personal y clientes.
            </div>
            <a href="index.php" class="f0-btn-secondary">Volver al inicio</a>
        </div>

        <?php include __DIR__ . '/includes/vistas/usuarios/tablaUsuarios.php'; ?>
    </div>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Gestionar usuarios - Bistro FDI';
$bodyClass = 'f0-body';

require __DIR__ . '/includes/vistas/comun/plantilla.php';