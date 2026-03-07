<?php
while ($cat = $resultado->fetch_assoc()):
    // 1. Obtenemos el nombre del archivo de la base de datos
    $nombre_foto = $cat['imagen'];
    
    // 2. Definimos la ruta relativa para el navegador y la ruta física para PHP
    $ruta_mostrar = "img/categorias/" . $nombre_foto;
    
    // 3. Verificamos si el campo está vacío O si el archivo NO existe en la carpeta
    // Importante: file_exists comprueba el archivo en tu disco duro
    if (empty($nombre_foto) || !file_exists(__DIR__ . "/" . $ruta_mostrar) || $nombre_foto == 'default.jpg') {
        $foto_valida = false;
    } else {
        $foto_valida = true;
    }
?>
<tr>
    <td class="col-foto">
        <?php if ($foto_valida): ?>
            <img src="<?php echo $ruta_mostrar; ?>" 
                 alt="<?php echo htmlspecialchars($cat['nombre']); ?>" 
                 class="img-tabla-pequena">
        <?php else: ?>
            <div class="cuadro-sin-foto">Sin foto</div>
        <?php endif; ?>
    </td>
    <td class="nombre-destacado">
        <strong><?php echo htmlspecialchars($cat['nombre']); ?></strong>
    </td>
    <td><?php echo htmlspecialchars($cat['descripcion']); ?></td>
    <td class="acciones">
        <a href="editar_categoria.php?id=<?php echo $cat['id']; ?>" class="btn-editar">Editar</a>
        <a href="eliminar_categoria.php?id=<?php echo $cat['id']; ?>" 
           class="btn-borrar" 
           onclick="return confirm('¿Seguro?')">Eliminar</a>
    </td>
</tr>
<?php endwhile; ?>