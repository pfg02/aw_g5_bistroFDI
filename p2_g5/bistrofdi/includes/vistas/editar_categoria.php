<?php
// 1. Carga de dependencias (Subimos un nivel desde vistas/)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../sesion.php';
require_once __DIR__ . '/../negocio/CategoriaService.php';

// 2. Seguridad: Solo el gerente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header("Location: ../../index.php");
    exit();
}

$service = new CategoriaService($db);
$mensaje_error = "";

// 3. Detectar si es EDICIÓN o NUEVA
$id = $_GET['id'] ?? null;
$categoria = $id ? $service->obtenerPorId($id) : null; // Devuelve un objeto CategoriaDTO o null

// 4. PROCESAMIENTO DEL FORMULARIO (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_post = $_POST['id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    
    // Gestión de imagen: Si no suben una nueva, mantenemos la que ya tiene el objeto
    $nombre_imagen = ($categoria) ? $categoria->imagen : 'default.png'; 

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $nombre_imagen = time() . "_" . basename($_FILES['foto']['name']);
        $ruta_destino = "../../img/categorias/" . $nombre_imagen;
        
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
            $mensaje_error = "Error al subir la imagen.";
        }
    }

    if (empty($mensaje_error)) {
        // USAMOS POO: El Service recibe datos y decide si insertar o actualizar
        if ($service->guardarCategoria($nombre, $descripcion, $nombre_imagen, $id_post)) {
            header("Location: gestion_categorias.php?msg=ok");
            exit();
        } else {
            $mensaje_error = "Error al guardar en la base de datos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Nueva' ?> Categoría - Bistró FDI</title>
    <link rel="stylesheet" href="../../css/estilos.css">
</head>
<body class="admin-panel">
    <?php include __DIR__ . '/comun/nav.php'; ?>

    <main class="container-gestion">
        <div class="form-card">
            <h1><?= $id ? 'Editar Categoría' : 'Nueva Categoría' ?></h1>
            
            <?php if ($mensaje_error): ?>
                <div class="alerta-sistema alerta-error"><?= htmlspecialchars($mensaje_error) ?></div>
            <?php endif; ?>

            <form action="editar_categoria.php<?= $id ? '?id='.$id : '' ?>" method="POST" enctype="multipart/form-data" class="admin-form">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>">

                <div class="grupo-control">
                    <label for="nombre">Nombre de la categoría</label>
                    <input type="text" id="nombre" name="nombre" 
                           value="<?= htmlspecialchars($categoria->nombre ?? '') ?>" required>
                </div>

                <div class="grupo-control">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required><?= htmlspecialchars($categoria->descripcion ?? '') ?></textarea>
                </div>

                <div class="grupo-control">
                    <label>Imagen / Icono</label>
                    <?php if ($id && !empty($categoria->imagen)): ?>
                        <div class="current-img" style="margin-bottom: 10px;">
                            <img src="../../img/categorias/<?= htmlspecialchars($categoria->imagen) ?>" alt="Actual" width="80">
                            <p style="font-size: 0.8em; color: #666;">Imagen actual</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="foto" accept="image/*">
                </div>

                <div class="acciones-form">
                    <button type="submit" class="btn-primario">
                        <?= $id ? 'Actualizar Categoría' : 'Crear Categoría' ?>
                    </button>
                    <a href="gestion_categorias.php" class="btn-secundario">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/comun/footer.php'; ?>
</body>
</html>