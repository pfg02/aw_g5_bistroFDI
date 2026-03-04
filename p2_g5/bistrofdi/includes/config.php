<?php
// includes/config.php

// Datos de conexión a MySQL
define('BD_HOST', 'localhost');
define('BD_USER', 'root');      // Cambia esto si tu MySQL tiene otro usuario
define('BD_PASS', '');          // Cambia esto si tu MySQL tiene contraseña
define('BD_NAME', 'bistrofdi'); // Nuestra BD

function obtenerConexionBD(): mysqli {
    $conn = new mysqli(BD_HOST, BD_USER, BD_PASS, BD_NAME);
    if ($conn->connect_errno) {
        die("Error de conexión a la base de datos: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}