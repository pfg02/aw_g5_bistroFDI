<?php
require_once 'config.php';
$id = $_GET['id'] ?? null;
if (isset($_POST['retirar'])) {
    $conn->prepare("UPDATE productos SET ofertado = 0 WHERE id = ?")->execute([$id]);
    header("Location: gestion_productos.php");
}
// Lógica de guardado omitida por brevedad, similar a categorías
?>
<form method="POST">
    <button type="submit" name="guardar">Guardar</button>
    <?php if($id): ?><button type="submit" name="retirar">Retirar de Carta</button><?php endif; ?>
</form>