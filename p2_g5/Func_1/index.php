<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Bistró FDI</title>
<link rel="stylesheet" href="../css/estilos.css">    
<style>
        /* Estilos específicos para centrar el contenido en el inicio */
        .home-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 75vh; /* Ocupa la mayor parte de la pantalla debajo del nav */
            text-align: center;
        }
        .logo-bistro {
            max-width: 350px; /* Ajusta este valor según el tamaño real de tu logo */
            height: auto;
            margin-bottom: 20px;
            /* Si tu logo tiene fondo blanco y quieres que se funda, puedes usar mix-blend-mode: multiply; */
        }
    </style>
</head>
<body>

    <?php include 'nav.php'; ?>

    <div class="home-container">
        <img src="img/logo.jpg" alt="Logo Bistró FDI" class="logo-bistro" onerror="this.src='https://via.placeholder.com/350x150?text=Logo+Bistr%C3%B3+FDI'">
        
        <h1 style="color: #343a40; margin-bottom: 5px;">Bienvenido a Bistró FDI</h1>
        <p style="color: #65676b; font-size: 1.1em;">El sistema integral de gestión de tu restaurante.</p>
    </div>

</body>
</html>