<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = "/AW_G5_BISTROFDI/p2_g5/bistrofdi/";
$img_url = "/AW_G5_BISTROFDI/p2_g5/bistrofdi/img/avatares/";

$estaLogueado = isset($_SESSION['id_usuario']);
$avatar = $_SESSION['avatar'] ?? 'img/avatares/default.png';

$avatarLimpio = ltrim($avatar, '/');
$rutaFinalAvatar = (strpos($avatarLimpio, 'http://') === 0 || strpos($avatarLimpio, 'https://') === 0)
    ? $avatarLimpio
    : $img_url . $avatarLimpio;
?>

<nav class="navegacion-principal">
    <div class="nav-contenedor">
        <a href="<?= $base_url ?>index.php" class="nav-logo">Bistró FDI</a>

        <ul class="nav-links">
            <li><a href="<?= $base_url ?>index.php">Inicio</a></li>

            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente'): ?>
                <li><a href="<?= $base_url ?>includes/vistas/gestion_categorias.php">Categorías</a></li>
                <li><a href="<?= $base_url ?>includes/vistas/gestion_productos.php">Productos</a></li>
                <li><a href="<?= $base_url ?>gestionarUsuarios.php">Usuarios</a></li>
            <?php endif; ?>

            <?php if ($estaLogueado): ?>
                <li>
                    <a href="<?= $base_url ?>perfil.php">
                        <img
                            src="<?= htmlspecialchars($rutaFinalAvatar) ?>"
                            alt="Avatar"
                            style="width: 24px; height: 24px; border-radius: 50%; vertical-align: middle; margin-right: 5px; object-fit: cover;"
                        >
                        Perfil (<?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?>)
                    </a>
                </li>
                <li><a href="<?= $base_url ?>logout.php">Salir</a></li>
            <?php else: ?>
                <li><a href="<?= $base_url ?>login.php">Login</a></li>
                <li><a href="<?= $base_url ?>registro.php">Registrarse</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>