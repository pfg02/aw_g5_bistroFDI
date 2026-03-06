<?php
// 1. Carga de sesión y configuración (Importante el orden)
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bistró FDI - Inicio</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="body-inicio">

    <?php include __DIR__ . '/includes/nav.php'; ?>

    <main class="contenedor-index">
        
        <section class="tarjeta-bienvenida">
            <div class="contenedor-logo">
                <img src="img/logo.jpg" alt="Logo Bistró FDI" class="logo-reducido">
            </div>
            
            <header class="header-inicio">
                <h1>Bienvenido a <span>Bistró FDI</span></h1>
            </header>
            
            <div class="contenido-accion">
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <div class="alerta-exito">
                        <p>Sesión activa como: <strong><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></strong></p>
                    </div>
                    
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente'): ?>
                        <div class="panel-admin-index">
                            <p>Panel de Administración desbloqueado</p>
                            <div class="grupo-botones">
                                <a href="gestion_productos.php" class="btn-profesional">Gestionar Catálogo</a>
                                <a href="gestionarUsuarios.php" class="btn-secundario-index">Usuarios</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <p class="mensaje-invitado">Accede al sistema para gestionar pedidos y catálogo.</p>
                    <a href="login.php" class="btn-profesional">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <footer class="footer-simple">
        <p>&copy; <?php echo date('Y'); ?> Bistró FDI - Facultad de Informática</p>
    </footer>

</body>
</html>