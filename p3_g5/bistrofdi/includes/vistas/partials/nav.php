<?php
require_once __DIR__ . '/../../core/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$estaLogueado = isset($_SESSION['id_usuario']);
$avatar = $_SESSION['avatar'] ?? 'img/avatares/default.png';
?>

<nav class="navegacion-principal">
    <a href="<?= BASE_URL ?>/index.php" class="nav-logo">Bistró FDI</a>

    <button class="nav-toggle" type="button" aria-label="Abrir menú" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <ul class="nav-lista">
        <li><a href="<?= BASE_URL ?>/index.php">Inicio</a></li>

        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente'): ?>
            <li><a href="<?= BASE_URL ?>/includes/vistas/admin/gestion_categorias.php">Categorías</a></li>
            <li><a href="<?= BASE_URL ?>/includes/vistas/admin/gestion_productos.php">Productos</a></li>
            <li><a href="<?= BASE_URL ?>/includes/vistas/admin/gestionarUsuarios.php">Usuarios</a></li>
        <?php endif; ?>

        <?php if ($estaLogueado): ?>
            <li>
                <a href="<?= BASE_URL ?>/includes/vistas/perfil/perfil.php" class="nav-perfil-link">
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="avatar-nav">
                    <span>Perfil</span>
                </a>
            </li>
            <li>
                <form action="<?= BASE_URL ?>/includes/acciones/auth/logout.php" method="post" style="margin: 0;">
                    <button type="submit" class="nav-perfil-link">Salir</button>
                </form>
            </li>
        <?php else: ?>
            <li><a href="<?= BASE_URL ?>/includes/vistas/auth/login.php">Login</a></li>
            <li><a href="<?= BASE_URL ?>/includes/vistas/auth/registro.php">Registrarse</a></li>
        <?php endif; ?>
    </ul>
</nav>