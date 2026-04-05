<?php if (empty($usuarios)): ?>
    <div class="f0-msg-info">No hay usuarios registrados.</div>
<?php else: ?>
    <div class="f0-table-wrap">
        <table class="f0-user-table">
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
                <?php $rolClase = 'f0-role-' . $usuario->getRol(); ?>
                <tr>
                    <td><?= htmlspecialchars((string)$usuario->getId()) ?></td>
                    <td><span class="f0-user-pill"><?= htmlspecialchars($usuario->getNombreUsuario()) ?></span></td>
                    <td><?= htmlspecialchars($usuario->getEmail()) ?></td>
                    <td><?= htmlspecialchars($usuario->getNombre()) ?></td>
                    <td><?= htmlspecialchars($usuario->getApellidos()) ?></td>
                    <td>
                        <span class="f0-role-badge <?= htmlspecialchars($rolClase) ?>">
                            <?= htmlspecialchars($usuario->getRol()) ?>
                        </span>
                    </td>
                    <td>
                        <div class="f0-actions">
                            <a href="cambiarRol.php?id=<?= urlencode((string)$usuario->getId()) ?>" class="f0-btn">Editar</a>
                            <a href="borrarUsuario.php?id=<?= urlencode((string)$usuario->getId()) ?>" class="f0-btn-danger">Borrar</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>