<?php
require_once 'config.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $u = $stmt->fetch();
    if ($u && password_verify($_POST['password'], $u['password'])) {
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['rol'] = $u['rol'];
        $_SESSION['username'] = $u['username'];
        header("Location: index.php");
    }
}
?>
<form method="POST">
    <input type="text" name="username" placeholder="Usuario" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Entrar</button>
</form>