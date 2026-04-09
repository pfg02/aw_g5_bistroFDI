<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = "/AW_G5_BISTROFDI/p2_g5/bistrofdi/";
$estaLogueado = isset($_SESSION['id_usuario']);
$avatar = $_SESSION['avatar'] ?? 'img/avatares/default.png';
?>

<nav class="navegacion-principal">
    <a href="<?= $base_url ?>index.php">Bistró FDI</a>

    <ul>
        <li><a href="<?= $base_url ?>index.php">Inicio</a></li>

        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente'): ?>
            <li><a href="<?= $base_url ?>includes/vistas/gestion_categorias.php">Categorías</a></li>
            <li><a href="<?= $base_url ?>includes/vistas/gestion_productos.php">Productos</a></li>
            <li><a href="<?= $base_url ?>gestionarUsuarios.php">Usuarios</a></li>
        <?php endif; ?>

        <?php if ($estaLogueado): ?>
            <li>
                <a href="<?= $base_url ?>perfil.php" class="nav-perfil-link">
                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="avatar-nav">
                    <span>Perfil</span>
                </a>
            </li>
            <li>
                <form action="<?= $base_url ?>logout.php" method="POST">
                    <li><a href="<?= $base_url ?>logout.php">Salir</a></li>
                </form>
            </li>
        <?php else: ?>
            <li><a href="<?= $base_url ?>login.php">Login</a></li>
            <li><a href="<?= $base_url ?>registro.php">Registrarse</a></li>
        <?php endif; ?>
    </ul>
</nav>