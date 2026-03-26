<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/negocio/UsuarioController.php';

exigirRol('gerente');

$controller = new UsuarioController();
$usuarios = $controller->obtenerListaUsuarios();

ob_start();
?>
<section class="contenedor-principal">
    <h1>Gestión de usuarios</h1>

    <?php include __DIR__ . '/includes/vistas/usuarios/tablaUsuarios.php'; ?>

    <p><a href="index.php">Volver al inicio</a></p>
</section>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Gestionar usuarios - Bistro FDI';

require __DIR__ . '/includes/vistas/comun/plantilla.php';