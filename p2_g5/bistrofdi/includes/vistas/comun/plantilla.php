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
    <link rel="stylesheet" href="css/estilos.css">
    <?= $extraHead ?>
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <?php include __DIR__ . '/nav.php'; ?>

    <main>
        <?= $contenidoPrincipal ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>