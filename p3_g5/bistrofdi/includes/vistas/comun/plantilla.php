<?php
$tituloPagina = $tituloPagina ?? 'Bistró FDI';
$contenidoPrincipal = $contenidoPrincipal ?? '';
$extraHead = $extraHead ?? '';
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/estilos.css">
    <?= $extraHead ?>
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <?php include __DIR__ . '/nav.php'; ?>

    <main>
        <?= $contenidoPrincipal ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>

    <script>
document.addEventListener('DOMContentLoaded', function () {
    const nav = document.querySelector('.navegacion-principal');
    const boton = document.querySelector('.nav-toggle');

    if (!nav || !boton) return;

    boton.addEventListener('click', function () {
        nav.classList.toggle('abierto');

        const expandido = boton.getAttribute('aria-expanded') === 'true';
        boton.setAttribute('aria-expanded', (!expandido).toString());
    });
});

</script>
</body>
</html>