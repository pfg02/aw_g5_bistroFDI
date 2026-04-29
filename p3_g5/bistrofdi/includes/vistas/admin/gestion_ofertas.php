<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/sesion.php';
require_once __DIR__ . '/../../integracion/OfertasDAO.php';

exigirLogin();
exigirRol('gerente');

$db = Application::getInstance()->conexionBd();
$ofertaDAO = new OfertaDAO($db);

/*
 * En gestión/admin queremos ver todas las ofertas no borradas.
 * No usamos obtenerOfertasActivas(), porque esa función filtra por fechas.
 */
$ofertas = obtenerTodasLasOfertasAdmin($db);

$ahora = time();
$ofertasActuales = [];
$ofertasCaducadas = [];

foreach ($ofertas as $oferta) {
    $fechaFin = (string) obtenerDatoOferta($oferta, 'fecha_fin', 'getFechaFin');
    $timestampFin = strtotime($fechaFin);

    if ($timestampFin !== false && $timestampFin < $ahora) {
        $ofertasCaducadas[] = $oferta;
    } else {
        $ofertasActuales[] = $oferta;
    }
}

$tituloPagina = 'Gestión de Ofertas - Bistró FDI';
$bodyClass = 'f0-body';

$msg = trim((string) (filter_input(INPUT_GET, 'msg', FILTER_UNSAFE_RAW) ?? ''));

ob_start();
?>

<div class="main-bienvenida">
    <section class="tarjeta-presentacion tarjeta-ancha">
        <h1>Gestión de <span>Ofertas</span></h1>
        <p class="lema">Administra packs promocionales y descuentos</p>
        <div class="divisor"></div>

        <?php if ($msg === 'creada'): ?>
            <div class="alerta alerta-exito">La oferta se ha creado correctamente.</div>
        <?php elseif ($msg === 'editada'): ?>
            <div class="alerta alerta-exito">La oferta se ha actualizado correctamente.</div>
        <?php elseif ($msg === 'borrada'): ?>
            <div class="alerta alerta-exito">La oferta se ha eliminado correctamente.</div>
        <?php elseif ($msg === 'error'): ?>
            <div class="alerta alerta-error">Ha ocurrido un error al procesar la oferta.</div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="alerta alerta-error">
                <?= htmlspecialchars($_SESSION['mensaje_error'], ENT_QUOTES, 'UTF-8') ?>
                <?php unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['mensaje_exito'])): ?>
            <div class="alerta alerta-exito">
                <?= htmlspecialchars($_SESSION['mensaje_exito'], ENT_QUOTES, 'UTF-8') ?>
                <?php unset($_SESSION['mensaje_exito']); ?>
            </div>
        <?php endif; ?>

        <div class="contenedor-botones-index">
            <a href="<?= BASE_URL ?>/includes/vistas/admin/crear_oferta.php" class="btn-login">Crear nueva oferta</a>
            <a href="<?= BASE_URL ?>/includes/vistas/admin/gestion_productos.php" class="btn-admin">Volver a productos</a>
        </div>

        <div class="mensaje-sesion">
            <h2>Ofertas actuales</h2>

            <?php if (empty($ofertasActuales)): ?>
                <p>No hay ofertas actuales.</p>
            <?php else: ?>
                <table class="tabla-pedidos tabla-sala-movil">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Fechas</th>
                            <th>Descuento</th>
                            <th>Pack</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($ofertasActuales as $oferta): ?>
                            <?php
                                $id = obtenerDatoOferta($oferta, 'id', 'getId');
                                $nombre = (string) (obtenerDatoOferta($oferta, 'nombre', 'getNombre') ?? '');
                                $fechaInicio = (string) (obtenerDatoOferta($oferta, 'fecha_inicio', 'getFechaInicio') ?? '');
                                $fechaFin = (string) (obtenerDatoOferta($oferta, 'fecha_fin', 'getFechaFin') ?? '');
                                $descuento = (float) (obtenerDatoOferta($oferta, 'descuento_porcentaje', 'getDescuentoPorcentaje') ?? 0);

                                $productosOferta = $ofertaDAO->obtenerProductosDeOferta((int) $id);
                            ?>

                            <tr>
                                <td data-label="Nombre">
                                    <strong><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>

                                <td data-label="Fechas">
                                    <?= htmlspecialchars(formatearFecha($fechaInicio), ENT_QUOTES, 'UTF-8') ?><br>
                                    <span>hasta <?= htmlspecialchars(formatearFecha($fechaFin), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>

                                <td data-label="Descuento">
                                    <?= number_format($descuento, 2) ?> %
                                </td>

                                <td data-label="Pack">
                                    <?php if (empty($productosOferta)): ?>
                                        <span>Sin productos</span>
                                    <?php else: ?>
                                        <ul class="ul-progreso">
                                            <?php foreach ($productosOferta as $producto): ?>
                                                <li class="li-progreso">
                                                    <?= (int) ($producto['cantidad'] ?? 0) ?>x
                                                    <?= htmlspecialchars((string) ($producto['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Acciones">
                                    <a href="<?= BASE_URL ?>/includes/vistas/admin/editar_oferta.php?id=<?= urlencode((string) $id) ?>">Editar</a>
                                    <br>
                                    <form action="<?= BASE_URL ?>/includes/acciones/ofertas/borrar_oferta.php" method="POST" class="form-actualizar-estado">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn-accion btn-peligro">Borrar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="divisor"></div>

            <h2>Ofertas caducadas</h2>

            <?php if (empty($ofertasCaducadas)): ?>
                <p>No hay ofertas caducadas.</p>
            <?php else: ?>
                <table class="tabla-pedidos tabla-sala-movil">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Fechas</th>
                            <th>Descuento</th>
                            <th>Pack</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($ofertasCaducadas as $oferta): ?>
                            <?php
                                $id = obtenerDatoOferta($oferta, 'id', 'getId');
                                $nombre = (string) (obtenerDatoOferta($oferta, 'nombre', 'getNombre') ?? '');
                                $fechaInicio = (string) (obtenerDatoOferta($oferta, 'fecha_inicio', 'getFechaInicio') ?? '');
                                $fechaFin = (string) (obtenerDatoOferta($oferta, 'fecha_fin', 'getFechaFin') ?? '');
                                $descuento = (float) (obtenerDatoOferta($oferta, 'descuento_porcentaje', 'getDescuentoPorcentaje') ?? 0);

                                $productosOferta = $ofertaDAO->obtenerProductosDeOferta((int) $id);
                            ?>

                            <tr>
                                <td data-label="Nombre">
                                    <strong><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>

                                <td data-label="Fechas">
                                    <?= htmlspecialchars(formatearFecha($fechaInicio), ENT_QUOTES, 'UTF-8') ?><br>
                                    <span>hasta <?= htmlspecialchars(formatearFecha($fechaFin), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>

                                <td data-label="Descuento">
                                    <?= number_format($descuento, 2) ?> %
                                </td>

                                <td data-label="Pack">
                                    <?php if (empty($productosOferta)): ?>
                                        <span>Sin productos</span>
                                    <?php else: ?>
                                        <ul class="ul-progreso">
                                            <?php foreach ($productosOferta as $producto): ?>
                                                <li class="li-progreso">
                                                    <?= (int) ($producto['cantidad'] ?? 0) ?>x
                                                    <?= htmlspecialchars((string) ($producto['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Acciones">
                                    <a href="<?= BASE_URL ?>/includes/vistas/admin/editar_oferta.php?id=<?= urlencode((string) $id) ?>">Editar</a>
                                    <br>
                                    <form action="<?= BASE_URL ?>/includes/acciones/ofertas/borrar_oferta.php" method="POST" class="form-actualizar-estado">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn-accion btn-peligro">Borrar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require_once __DIR__ . '/../partials/plantilla.php';

function obtenerTodasLasOfertasAdmin(mysqli $db): array
{
    $sql = "
        SELECT *
        FROM ofertas
        WHERE activa = 1
        ORDER BY fecha_inicio DESC, id DESC
    ";

    $rs = $db->query($sql);

    if (!$rs) {
        return [];
    }

    $ofertas = [];

    while ($fila = $rs->fetch_assoc()) {
        $ofertas[] = $fila;
    }

    $rs->free();

    return $ofertas;
}

function obtenerDatoOferta($oferta, string $claveArray, string $getter)
{
    if (is_array($oferta)) {
        return $oferta[$claveArray] ?? null;
    }

    if (is_object($oferta) && method_exists($oferta, $getter)) {
        return $oferta->$getter();
    }

    return null;
}

function formatearFecha(string $fecha): string
{
    $timestamp = strtotime($fecha);

    if ($timestamp === false) {
        return $fecha;
    }

    return date('d/m/Y H:i', $timestamp);
}
?>