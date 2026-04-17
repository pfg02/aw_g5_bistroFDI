<?php if (empty($usuarios)): ?>
    <div class="f0-msg-info">No hay usuarios registrados.</div>
<?php else: ?>
    <div class="f0-table-wrap">
        <table class="f0-user-table f0-user-table-mobile">
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
                    <td data-label="ID"><?= htmlspecialchars((string)$usuario->getId()) ?></td>
                    <td data-label="Usuario">
                        <span class="f0-user-pill"><?= htmlspecialchars($usuario->getNombreUsuario()) ?></span>
                    </td>
                    <td data-label="Email"><?= htmlspecialchars($usuario->getEmail()) ?></td>
                    <td data-label="Nombre"><?= htmlspecialchars($usuario->getNombre()) ?></td>
                    <td data-label="Apellidos"><?= htmlspecialchars($usuario->getApellidos()) ?></td>
                    <td data-label="Rol">
                        <span class="f0-role-badge <?= htmlspecialchars($rolClase) ?>">
                            <?= htmlspecialchars($usuario->getRol()) ?>
                        </span>
                    </td>
                    <td data-label="Acciones">
                        <div class="f0-actions">
                            <a href="cambiarRol.php?id=<?= urlencode((string)$usuario->getId()) ?>" class="f0-btn">Editar</a>
                            <a href="mis_pedidos.php?id_cliente=<?= urlencode((string)$usuario->getId()) ?>" class="f0-btn">Ver Pedidos</a>
                            <a href="borrarUsuario.php?id=<?= urlencode((string)$usuario->getId()) ?>" class="f0-btn-danger">Borrar</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>