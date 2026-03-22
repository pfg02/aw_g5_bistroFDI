<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// URL base para el navegador
$base_url = "/aw_g5_bistroFDI/p2_g5/bistrofdi/"; 

// Ajuste de ruta física: subimos de 'comun' a 'vistas' y de 'vistas' a 'includes'
$ruta_fisica_base = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;

// Cargamos funciones de usuario
$rutaFunciones = $ruta_fisica_base . 'funcionesUsuarios.php';
if (file_exists($rutaFunciones)) {
    require_once $rutaFunciones;
}

$estaLogueado = isset($_SESSION['id_usuario']);
$avatar = 'img/avatares/default.png'; 

if ($estaLogueado && function_exists('obtenerUsuarioPorId')) {
    $usuario = obtenerUsuarioPorId($_SESSION['id_usuario']);
    if ($usuario && !empty($usuario['avatar'])) {
        $avatar = $usuario['avatar'];
    }
}

// Limpiamos la ruta del avatar para que la concatenación sea perfecta
$avatarLimpio = ltrim($avatar, '/');
$rutaFinalAvatar = (strpos($avatarLimpio, 'http') === 0) ? $avatarLimpio : $base_url . $avatarLimpio;
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
                        <img src="<?= htmlspecialchars($rutaFinalAvatar) ?>" alt="Avatar" 
                             style="width: 24px; height: 24px; border-radius: 50%; vertical-align: middle; margin-right: 5px; object-fit: cover;">
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