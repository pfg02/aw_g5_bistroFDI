<?php
session_start();
require_once 'config.php';

// Si alguien intenta entrar sin hacer login, lo echamos
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Bistró FDI</title>
<link rel="stylesheet" href="../css/estilos.css">
</head>
<body>

<?php include 'nav.php'; ?>

<div class="container" style="max-width: 600px;">
    <h1>Mi Perfil</h1>
    
    <div style="background: #f8f9fa; padding: 25px; border-radius: 8px; border: 1px solid #ddd; margin-top: 20px;">
        <h3 style="margin-top: 0; color: #007bff;">Datos de la cuenta</h3>
        
        <p style="font-size: 1.1em; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['username']) ?>
        </p>
        
        <p style="font-size: 1.1em; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <strong>Nivel de Acceso:</strong> 
            <span class="badge badge-success" style="text-transform: uppercase; margin-left: 10px;">
                <?= htmlspecialchars($_SESSION['rol']) ?>
            </span>
        </p>
        
        <?php if($_SESSION['rol'] === 'cliente'): ?>
            <p>Aquí aparecerá tu historial de pedidos en la próxima fase.</p>
        <?php endif; ?>
    </div>

    <div style="margin-top: 30px; display: flex; gap: 15px;">
        <a href="<?= $_SESSION['rol'] === 'gerente' ? 'gestion_productos.php' : 'index.php' ?>" class="btn btn-primary">Volver al Panel</a>
        
        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>
</div>

</body>
</html>