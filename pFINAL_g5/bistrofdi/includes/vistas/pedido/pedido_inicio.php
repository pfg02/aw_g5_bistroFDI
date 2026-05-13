<?php
declare(strict_types=1);

/**
 * Vista para iniciar un nuevo pedido.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../formularios/formularioInicioPedido.php';

exigirLogin();
exigirRol('cliente');

$formularioInicio = new FormularioInicioPedido();

$tituloPagina = 'Bistró FDI - Nuevo Pedido';
$bodyClass = 'f0-body';

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion">

        <div class="logo-wrapper">
            <img src="<?= BASE_URL ?>/img/logo.jpg" alt="Logo Bistró FDI" class="logo-index-pequeno">
        </div>

        <h1>Nuevo <span>Pedido</span></h1>
        <p class="lema">¡Prepara tu paladar para disfrutar!</p>

        <div class="divisor"></div>

        <div class="mensaje-sesion">
            <?= $formularioInicio->gestiona() ?>
        </div>

    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';
?>