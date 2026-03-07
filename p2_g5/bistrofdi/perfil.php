<?php
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/funcionesUsuarios.php';

exigirLogin(); // solo usuarios logueados

$idUsuario = $_SESSION['id_usuario'];
$usuario   = obtenerUsuarioPorId($idUsuario);

$mensajeOk = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $nombre     = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');

    if (actualizarPerfil($idUsuario, $email, $nombre, $apellidos)) {
        $mensajeOk = 'Perfil actualizado correctamente.';
        $usuario   = obtenerUsuarioPorId($idUsuario); // recargar datos
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil - Bistro FDI</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="body-gestion">

    <?php include __DIR__ . '/includes/nav.php'; ?>

    <main class="main-content">
        <header class="header-gestion">
            <h1>Mi Perfil</h1>
        </header>

        <section class="contenedor-tabla-profesional">
            
            <?php if ($mensajeOk): ?>
                <div class="alerta-ok">
                    <?php echo htmlspecialchars($mensajeOk); ?>
                </div>
            <?php endif; ?>

            <div class="perfil-layout">
                <div class="perfil-sidebar">
                    <div class="perfil-avatar-container">
                       
                        <div class="avatar-wrapper">
                            <img src="<?php echo htmlspecialchars($usuario['avatar']); ?>" alt="Avatar">
                        </div>
                        <a href="cambiarAvatar.php" class="btn-cambiar-avatar">
                            Cambiar avatar
                        </a>
                    </div>
                    
                    <div class="info-usuario-fija">
                        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['nombre_usuario']); ?></p>
                        <p><strong>Rol:</strong> <span class="tag-rol"><?php echo htmlspecialchars($usuario['rol']); ?></span></p>
                    </div>
                </div>

                <div class="perfil-main">
                    <h2>Datos personales</h2>
                    <form method="post" action="perfil.php" class="form-profesional">
                        <div class="campo">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>

                        <div class="campo">
                            <label>Nombre:</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                        </div>

                        <div class="campo">
                            <label>Apellidos:</label>
                            <input type="text" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
                        </div>

                        <div class="botones-form">
                            <button type="submit" class="btn-anadir">Guardar cambios</button>
                            <a href="logout.php" class="btn-borrar" style="text-decoration:none;">Cerrar sesión</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="index.php" style="color: #666; text-decoration: none;">← Volver al inicio</a>
            </div>
        </section>
    </main>

</body>
</html>