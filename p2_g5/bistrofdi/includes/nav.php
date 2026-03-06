<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

// Detectamos la ruta base del proyecto para que las imágenes y enlaces no fallen nunca
// Si tu carpeta se llama distinto a 'bistroFDI', cámbialo aquí:
$base = "/bistroFDI/"; 

// Intentamos cargar las funciones de la F0 para poder sacar la foto de avatar
$rutaFunciones = __DIR__ . '/funcionesUsuarios.php'; // Al estar en la misma carpeta 'includes'
if (file_exists($rutaFunciones)) {
    require_once $rutaFunciones;
}

// Comprobamos si hay alguien logueado usando la variable oficial de la F0
$estaLogueado = isset($_SESSION['id_usuario']);
$avatar = 'img/avatares/default.png'; // Avatar por defecto

// Si está logueado, intentamos sacar su foto real de la base de datos
if ($estaLogueado && function_exists('obtenerUsuarioPorId')) {
    $usuario = obtenerUsuarioPorId($_SESSION['id_usuario']);
    if ($usuario && !empty($usuario['avatar'])) {
        $avatar = $usuario['avatar'];
    }
}

// Construimos la ruta final del avatar anteponiendo la base si no es una URL externa
$rutaFinalAvatar = (strpos($avatar, 'http') === 0) ? $avatar : $base . $avatar;
?>

<nav class="navegacion-principal">
    <div class="nav-contenedor">
        <a href="<?= $base ?>index.php" class="nav-logo">Bistró FDI</a>
        <ul class="nav-links">
            <li><a href="<?= $base ?>index.php">Inicio</a></li>
            
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente'): ?>
                <li><a href="<?= $base ?>gestion_categorias.php">Categorías</a></li>
                <li><a href="<?= $base ?>gestion_productos.php">Productos</a></li>
                <li><a href="<?= $base ?>gestionarUsuarios.php">Usuarios</a></li> 
            <?php endif; ?>

            <?php if ($estaLogueado): ?>
                <li>
                    <a href="<?= $base ?>perfil.php">
                        <img src="<?= htmlspecialchars($rutaFinalAvatar) ?>" alt="Avatar" 
                             style="width: 24px; height: 24px; border-radius: 50%; vertical-align: middle; margin-right: 5px; object-fit: cover;">
                        Perfil (<?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario') ?>)
                    </a>
                </li>
                <li><a href="<?= $base ?>logout.php">Salir</a></li>
            <?php else: ?>
                <li><a href="<?= $base ?>login.php">Login</a></li>
                <li><a href="<?= $base ?>registro.php">Registrarse</a></li> 
            <?php endif; ?>
        </ul>
    </div>
</nav>