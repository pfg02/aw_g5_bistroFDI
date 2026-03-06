<?php
// 1. Carga de configuración y control de sesión
require_once __DIR__ . '/includes/sesion.php'; 
require_once __DIR__ . '/includes/config.php';

// No hace falta session_start() aquí porque ya debería estar en sesion.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bistró FDI - Inicio</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <?php include __DIR__ . '/includes/nav.php'; ?>

    <main class="contenedor-principal">
        
        <section class="heroe">
            <img src="img/logo.jpg" alt="Logo Bistró FDI" class="logo-inicio">
            
            <h1>Bienvenido a Bistró FDI</h1>
            
            <?php 
            // CORRECCIÓN: Usamos id_usuario que es la variable correcta que guarda el login
            if (isset($_SESSION['id_usuario'])): 
            ?>
                <p class="saludo">Hola, bienvenido de nuevo al sistema.</p>
                
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente'): ?>
                    <div class="panel-admin">
                        <p>Acceso concedido al panel de administración.</p>
                        <a href="gestion_productos.php" class="boton-primario">Gestionar Catálogo de Productos</a>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <p class="mensaje-invitado">Por favor, inicia sesión para acceder a las funciones del sistema.</p>
                <a href="login.php" class="boton-secundario">Ir al Login</a>
            <?php endif; ?>
        </section>

    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Bistró FDI - Facultad de Informática</p>
    </footer>

</body>
</html>