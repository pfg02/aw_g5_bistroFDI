<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<nav>
    <a href="index.php"><strong>Bistró FDI</strong></a>
    <ul>
        <li><a href="index.php">Inicio</a></li>
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente'): ?>
            <li><a href="gestion_categorias.php">Categorías</a></li>
            <li><a href="gestion_productos.php">Productos</a></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['username'])): ?>
            <li><a href="perfil.php">Mi Perfil (<?= $_SESSION['username'] ?>)</a></li>
            <li><a href="logout.php">Salir</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>