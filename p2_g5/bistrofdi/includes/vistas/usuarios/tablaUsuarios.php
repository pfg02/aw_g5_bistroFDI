<?php if (empty($usuarios)): ?>
    <p>No hay usuarios registrados.</p>
<?php else: ?>
    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= htmlspecialchars((string)$usuario->getId()) ?></td>
                <td><?= htmlspecialchars($usuario->getNombreUsuario()) ?></td>
                <td><?= htmlspecialchars($usuario->getEmail()) ?></td>
                <td><?= htmlspecialchars($usuario->getNombre()) ?></td>
                <td><?= htmlspecialchars($usuario->getApellidos()) ?></td>
                <td><?= htmlspecialchars($usuario->getRol()) ?></td>
                <td>
                    <a href="cambiarRol.php?id=<?= urlencode((string)$usuario->getId()) ?>">Editar</a>
                    |
                    <a href="borrarUsuario.php?id=<?= urlencode((string)$usuario->getId()) ?>">Borrar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>