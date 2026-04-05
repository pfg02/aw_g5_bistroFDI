<?php
require_once __DIR__ . '/includes/sesion.php';

ob_start();
?>
<main class="main-bienvenida">
    <section class="tarjeta-presentacion">
        <div class="logo-wrapper">
            <img src="img/logo.jpg" alt="Logo Bistró FDI" class="logo-index-pequeno">
        </div>

        <h1>Bistró <span>FDI</span></h1>
        <p class="lema">Experiencia Gastronómica &amp; Gestión</p>

        <div class="divisor"></div>

        <div class="mensaje-sesion">
            <?php if (isset($_SESSION['id_usuario'])): ?>
                <p class="txt-bienvenida">Bienvenido de nuevo,</p>
                <p class="user-destacado"><?= htmlspecialchars($_SESSION['nombre_usuario']) ?></p>

                <div class="contenedor-botones-index">
                    <?php $rol = $_SESSION['rol'] ?? 'cliente'; ?>

                    <?php if ($rol === 'gerente'): ?>
                        <a href="includes/vistas/gestion_productos.php" class="btn-admin">Gestionar Catálogo</a>
                        <a href="includes/vistas/gestion_categorias.php" class="btn-admin">Gestionar Categorías</a>
                        <a href="gestionarUsuarios.php" class="btn-admin">Gestionar Usuarios</a>

                    <?php elseif ($rol === 'camarero'): ?>
                        <a href="panel_camarero.php" class="btn-admin">Panel de Sala (Camareros)</a>

                    <?php elseif ($rol === 'cocinero'): ?>
                        <a href="" class="btn-admin">Panel de Cocina (Cocineros)</a>

                    <?php else: ?>
                        <a href="perfil.php" class="btn-login">Ver mi Perfil</a>
                        <a href="pedido_inicio.php" class="btn-login">Hacer Pedido</a>
                        <a href="mis_pedidos.php" class="btn-login">Mis Pedidos</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>Inicia sesión para gestionar el sistema.</p>
                <div class="contenedor-botones-index">
                    <a href="login.php" class="btn-login">Acceder al Sistema</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
$contenidoPrincipal = ob_get_clean();
$tituloPagina = 'Bistró FDI - Inicio';
$bodyClass = 'f0-body';

require __DIR__ . '/includes/vistas/comun/plantilla.php';