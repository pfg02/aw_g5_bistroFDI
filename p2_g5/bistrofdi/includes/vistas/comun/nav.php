<?php
require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$estaLogueado = isset($_SESSION['id_usuario']);
$avatar = $_SESSION['avatar'] ?? 'img/avatares/default.png';
?>

<nav class="navegacion-principal">
    <a href="<?= BASE_URL ?>/index.php">Bistró FDI</a>

    <ul>
        <li><a href="<?= BASE_URL ?>/index.php">Inicio</a></li>

        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente'): ?>
            <li><a href="<?= BASE_URL ?>/includes/vistas/gestion_categorias.php">Categorías</a></li>
            <li><a href="<?= BASE_URL ?>/includes/vistas/gestion_productos.php">Productos</a></li>
            <li><a href="<?= BASE_URL ?>/gestionarUsuarios.php">Usuarios</a></li>
        <?php endif; ?>

        <?php if ($estaLogueado): ?>
            <li>
                <a href="<?= BASE_URL ?>/perfil.php" class="nav-perfil-link">
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="avatar-nav">
                    <span>Perfil</span>
                </a>
            </li>
            <li><a href="<?= BASE_URL ?>/logout.php">Salir</a></li>
        <?php else: ?>
            <li><a href="<?= BASE_URL ?>/login.php">Login</a></li>
            <li><a href="<?= BASE_URL ?>/registro.php">Registrarse</a></li>
        <?php endif; ?>
    </ul>
</nav>