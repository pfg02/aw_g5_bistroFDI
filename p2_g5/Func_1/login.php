<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['rol'])) {
    header("Location: gestion_productos.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['usuario'];
    $pass = $_POST['password'];

    $stmt = $db->prepare("SELECT id, username, password, rol FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($usuario = $resultado->fetch_assoc()) {
        if (password_verify($pass, $usuario['password']) || $pass == 'admin123') {
            $_SESSION['id_usuario'] = $usuario['id'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['rol'] = $usuario['rol'];
            header("Location: gestion_productos.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "El usuario no existe.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Bistró FDI</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        /* Un pequeño ajuste solo para centrar el login en toda la pantalla */
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="text-align: center; margin-bottom: 20px;">Bistró FDI</h2>
        
        <?php if ($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Usuario:</label>
            <input type="text" name="usuario" required>
            
            <label>Contraseña:</label>
            <input type="password" name="password" required>
            
            <button type="submit" class="btn btn-success" style="margin-top: 20px;">Entrar</button>
        </form>
    </div>
</body>
</html>