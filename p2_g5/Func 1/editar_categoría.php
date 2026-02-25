<?php
require_once 'config.php';
$id = $_GET['id'] ?? null;
if ($_POST) {
    $nombre = $_POST['nombre'];
    $desc = $_POST['descripcion'];
    if ($id) {
        $conn->prepare("UPDATE categorias SET nombre=?, descripcion=? WHERE id=?")->execute([$nombre, $desc, $id]);
    } else {
        $conn->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)")->execute([$nombre, $desc]);
    }
    header("Location: gestion_categorias.php");
}
?>
<form method="POST">
    <input type="text" name="nombre" placeholder="Nombre">
    <textarea name="descripcion" placeholder="Descripción"></textarea>
    <button type="submit">Guardar</button>
</form>