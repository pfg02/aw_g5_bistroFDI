<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/formulario.php';
require_once __DIR__ . '/../negocio/OfertasDTO.php';

class FormularioOferta extends Formulario
{
    private ?OfertaDTO $oferta;
    private array $productosDisponibles;
    private array $datosValidados = [];

    public function __construct(?OfertaDTO $oferta = null, array $productosDisponibles = [], string $action = '')
    {
        parent::__construct('formOferta', [
            'action' => $action,
            'method' => 'POST',
            'class' => 'formulario-admin',
        ]);

        $this->oferta = $oferta;
        $this->productosDisponibles = $productosDisponibles;
    }

    public function getDatosValidados(): array
    {
        return $this->datosValidados;
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $id = htmlspecialchars((string) ($datos['id'] ?? $this->obtenerDatoOferta('id') ?? ''), ENT_QUOTES, 'UTF-8');
        $nombre = htmlspecialchars((string) ($datos['nombre'] ?? $this->obtenerDatoOferta('nombre') ?? ''), ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars((string) ($datos['descripcion'] ?? $this->obtenerDatoOferta('descripcion') ?? ''), ENT_QUOTES, 'UTF-8');
        $fechaInicio = htmlspecialchars((string) ($datos['fecha_inicio'] ?? $this->formatearFechaInput($this->obtenerDatoOferta('fecha_inicio'))), ENT_QUOTES, 'UTF-8');
        $fechaFin = htmlspecialchars((string) ($datos['fecha_fin'] ?? $this->formatearFechaInput($this->obtenerDatoOferta('fecha_fin'))), ENT_QUOTES, 'UTF-8');

        $descuentoPorcentaje = htmlspecialchars((string) ($datos['descuento_porcentaje'] ?? $this->obtenerDatoOferta('descuento_porcentaje') ?? '0'), ENT_QUOTES, 'UTF-8');

        $productosSeleccionados = $this->obtenerProductosSeleccionados($datos);
        $numFilas = max(count($productosSeleccionados), 1);

        $precioPackInicial = $this->calcularPrecioPack($productosSeleccionados);

        $precioFinalInicial = $datos['precio_final']
            ?? $this->obtenerDatoOferta('precio_final')
            ?? round($precioPackInicial - ($precioPackInicial * ((float) $descuentoPorcentaje / 100)), 2);

        $precioFinalOferta = htmlspecialchars(
            number_format((float) $precioFinalInicial, 2, '.', ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $erroresHtml = self::generaListaErrores($this->errores);
        $urlListado = BASE_URL . '/includes/vistas/admin/gestion_ofertas.php';
        $rutaJs = BASE_URL . '/js/admin_ofertas.js';

        $filasProductosHtml = '';

        for ($i = 0; $i < $numFilas; $i++) {
            $productoSeleccionado = $productosSeleccionados[$i]['producto_id'] ?? '';
            $cantidadSeleccionada = $productosSeleccionados[$i]['cantidad'] ?? 1;

            $opciones = '<option value="">Selecciona un producto</option>';

            foreach ($this->productosDisponibles as $producto) {
                $idProducto = $this->obtenerDatoProducto($producto, 'id');
                $nombreProducto = $this->obtenerDatoProducto($producto, 'nombre');
                $precioBase = (float) ($this->obtenerDatoProducto($producto, 'precio') ?? 0);
                $iva = (int) ($this->obtenerDatoProducto($producto, 'iva') ?? 21);
                $precioConIva = $precioBase * (1 + ($iva / 100));

                $selected = ((string) $productoSeleccionado === (string) $idProducto) ? 'selected' : '';

                $opciones .= sprintf(
                    '<option value="%s" data-precio="%s" %s>%s</option>',
                    htmlspecialchars((string) $idProducto, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(number_format($precioConIva, 2, '.', ''), ENT_QUOTES, 'UTF-8'),
                    $selected,
                    htmlspecialchars((string) $nombreProducto, ENT_QUOTES, 'UTF-8')
                );
            }

            $cantidadSeleccionadaEscapada = htmlspecialchars((string) $cantidadSeleccionada, ENT_QUOTES, 'UTF-8');

            $filasProductosHtml .= <<<HTML
<div class="fila-pack-oferta">
    <div class="grupo-control">
        <label for="producto_id_$i">Producto</label>
        <select name="producto_id[]" id="producto_id_$i" class="js-producto-oferta" required>
            $opciones
        </select>
    </div>

    <div class="grupo-control">
        <label for="cantidad_$i">Cantidad</label>
        <input type="number" name="cantidad[]" id="cantidad_$i" min="1" step="1" value="$cantidadSeleccionadaEscapada" class="js-cantidad-oferta" required>
    </div>
</div>
HTML;
        }

        return <<<HTML
$erroresHtml

<input type="hidden" name="id" value="$id">

<div class="grupo-control">
    <label for="nombre">Nombre</label>
    <input type="text" name="nombre" id="nombre" value="$nombre" required maxlength="100">
</div>

<div class="grupo-control">
    <label for="descripcion">Descripción</label>
    <textarea name="descripcion" id="descripcion" rows="4" maxlength="2000">$descripcion</textarea>
</div>

<div class="grupo-control">
    <label for="fecha_inicio">Comienzo</label>
    <input type="datetime-local" name="fecha_inicio" id="fecha_inicio" value="$fechaInicio" required>
</div>

<div class="grupo-control">
    <label for="fecha_fin">Fin</label>
    <input type="datetime-local" name="fecha_fin" id="fecha_fin" value="$fechaFin" required>
</div>

<fieldset class="bloque-pack-oferta">
    <legend>Productos del pack</legend>

    <div id="contenedor-productos">
        $filasProductosHtml
    </div>

    <button type="button" id="btn-anadir-producto" class="btn-admin">+ Añadir otro producto</button>
</fieldset>

<div class="grupo-control">
    <label for="precio_pack_mostrado">Precio pack sin descuento</label>
    <input type="text" id="precio_pack_mostrado" value="0.00 €" readonly>
</div>

<div class="grupo-control" style="flex: 1;">
    <label for="descuento_porcentaje_dinamico">Descuento a aplicar (%)</label>
    <input type="number" name="descuento_porcentaje" id="descuento_porcentaje_dinamico" min="0" max="100" step="0.01" value="$descuentoPorcentaje" required>
</div>

<div class="grupo-control" style="flex: 1;">
    <label for="precio_final_mostrado">Precio Final Oferta</label>
    <input type="text" name="precio_final" id="precio_final_mostrado" value="$precioFinalOferta €" required>
    <input type="hidden" id="precio_final_hidden" value="$precioFinalOferta">
</div>

<div class="acciones-form">
    <button type="submit" class="btn-primario">Guardar oferta</button>
    <a href="$urlListado" class="btn-secundario">Volver al listado</a>
</div>

<script src="$rutaJs"></script>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        $this->datosValidados = [];

        $idRaw = $datos['id'] ?? null;
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        $descripcion = trim((string) ($datos['descripcion'] ?? ''));
        $fechaInicio = trim((string) ($datos['fecha_inicio'] ?? ''));
        $fechaFin = trim((string) ($datos['fecha_fin'] ?? ''));
        $descuentoRaw = $datos['descuento_porcentaje'] ?? null;

        $precioFinalRaw = trim((string) ($datos['precio_final'] ?? ''));
        $precioFinalRaw = str_replace(['€', ' '], '', $precioFinalRaw);
        $precioFinalRaw = str_replace(',', '.', $precioFinalRaw);

        $productosIds = $datos['producto_id'] ?? [];
        $cantidades = $datos['cantidad'] ?? [];

        $id = null;

        if ($idRaw !== null && $idRaw !== '') {
            $idValidado = filter_var($idRaw, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1]
            ]);

            if ($idValidado === false) {
                $this->errores[] = 'El identificador de la oferta no es válido.';
            } else {
                $id = (int) $idValidado;
            }
        }

        if ($nombre === '') {
            $this->errores[] = 'El nombre de la oferta es obligatorio.';
        } elseif (mb_strlen($nombre) > 100) {
            $this->errores[] = 'El nombre no puede superar los 100 caracteres.';
        }

        if (mb_strlen($descripcion) > 2000) {
            $this->errores[] = 'La descripción no puede superar los 2000 caracteres.';
        }

        $fechaInicioTs = strtotime($fechaInicio);
        $fechaFinTs = strtotime($fechaFin);

        if ($fechaInicio === '' || $fechaInicioTs === false) {
            $this->errores[] = 'La fecha de inicio no es válida.';
        }

        if ($fechaFin === '' || $fechaFinTs === false) {
            $this->errores[] = 'La fecha de fin no es válida.';
        }

        if ($fechaInicioTs !== false && $fechaFinTs !== false && $fechaInicioTs > $fechaFinTs) {
            $this->errores[] = 'La fecha de inicio no puede ser posterior a la fecha de fin.';
        }

        if (!is_array($productosIds) || !is_array($cantidades) || count($productosIds) !== count($cantidades)) {
            $this->errores[] = 'Los productos de la oferta no son válidos.';
        }

        $productosNormalizados = [];
        $idsUsados = [];

        if (empty($this->errores)) {
            foreach ($productosIds as $indice => $productoIdRaw) {
                $productoIdRaw = trim((string) $productoIdRaw);
                $cantidadRaw = $cantidades[$indice] ?? '';

                if ($productoIdRaw === '') {
                    continue;
                }

                $productoId = filter_var($productoIdRaw, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1]
                ]);

                $cantidad = filter_var($cantidadRaw, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1]
                ]);

                if ($productoId === false || $cantidad === false) {
                    $this->errores[] = 'Los productos o cantidades de la oferta no son válidos.';
                    break;
                }

                if (in_array((int) $productoId, $idsUsados, true)) {
                    $this->errores[] = 'No puede haber productos repetidos dentro de la misma oferta.';
                    break;
                }

                $idsUsados[] = (int) $productoId;

                $productosNormalizados[] = [
                    'producto_id' => (int) $productoId,
                    'cantidad' => (int) $cantidad,
                ];
            }
        }

        if (empty($productosNormalizados)) {
            $this->errores[] = 'Debes añadir al menos un producto al pack de la oferta.';
        }

        $precioPack = $this->calcularPrecioPack($productosNormalizados);

        if ($precioPack <= 0) {
            $this->errores[] = 'No se ha podido calcular el precio del pack.';
        }

        $descuentoPorcentaje = filter_var($descuentoRaw, FILTER_VALIDATE_FLOAT);

        if ($descuentoPorcentaje === false || $descuentoPorcentaje < 0 || $descuentoPorcentaje > 100) {
            $this->errores[] = 'El porcentaje de descuento debe estar entre 0 y 100.';
        }

        $precioFinal = filter_var($precioFinalRaw, FILTER_VALIDATE_FLOAT);

        if ($precioFinal === false || $precioFinal <= 0) {
            $this->errores[] = 'El precio final de la oferta no es válido.';
        } elseif ($precioFinal > $precioPack) {
            $this->errores[] = 'El precio final de la oferta no puede ser mayor que el precio del pack.';
        }

        if (!empty($this->errores)) {
            return null;
        }

        $oferta = new OfertaDTO();
        $oferta->setId($id);
        $oferta->setNombre($nombre);
        $oferta->setDescripcion($descripcion);
        $oferta->setFechaInicio($this->normalizarFechaBD($fechaInicio));
        $oferta->setFechaFin($this->normalizarFechaBD($fechaFin));
        $oferta->setDescuentoPorcentaje((float) $descuentoPorcentaje);
        $oferta->setProductos($productosNormalizados);

        $this->datosValidados = [
            'oferta' => $oferta,
            'precio_pack' => $precioPack,
            'precio_final' => round((float) $precioFinal, 2),
            'descuento_porcentaje' => (float) $descuentoPorcentaje,
        ];

        return null;
    }

    private function obtenerDatoOferta(string $campo)
    {
        if ($this->oferta === null) {
            return null;
        }

        $mapa = [
            'id' => 'getId',
            'nombre' => 'getNombre',
            'descripcion' => 'getDescripcion',
            'fecha_inicio' => 'getFechaInicio',
            'fecha_fin' => 'getFechaFin',
            'descuento_porcentaje' => 'getDescuentoPorcentaje',
            'precio_final' => 'getPrecioFinal',
            'productos' => 'getProductos',
        ];

        $getter = $mapa[$campo] ?? null;

        if ($getter !== null && method_exists($this->oferta, $getter)) {
            return $this->oferta->$getter();
        }

        return null;
    }

    private function obtenerDatoProducto($producto, string $campo)
    {
        $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $campo)));

        if (is_object($producto) && method_exists($producto, $getter)) {
            return $producto->$getter();
        }

        if (is_object($producto) && isset($producto->$campo)) {
            return $producto->$campo;
        }

        if (is_array($producto)) {
            return $producto[$campo] ?? null;
        }

        return null;
    }

    private function obtenerProductosSeleccionados(array $datos): array
    {
        if (
            isset($datos['producto_id'], $datos['cantidad'])
            && is_array($datos['producto_id'])
            && is_array($datos['cantidad'])
        ) {
            $resultado = [];

            foreach ($datos['producto_id'] as $i => $productoId) {
                $resultado[] = [
                    'producto_id' => $productoId,
                    'cantidad' => $datos['cantidad'][$i] ?? 1,
                ];
            }

            return $resultado;
        }

        $productosOferta = $this->obtenerDatoOferta('productos');

        return is_array($productosOferta) ? $productosOferta : [];
    }

    private function formatearFechaInput($fecha): string
    {
        if (!is_string($fecha) || trim($fecha) === '') {
            return '';
        }

        $timestamp = strtotime($fecha);

        if ($timestamp === false) {
            return '';
        }

        return date('Y-m-d\TH:i', $timestamp);
    }

    private function normalizarFechaBD(string $fecha): string
    {
        $timestamp = strtotime($fecha);

        return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : $fecha;
    }

    private function calcularPrecioPack(array $productosNormalizados): float
    {
        $mapaPrecios = [];

        foreach ($this->productosDisponibles as $producto) {
            $id = $this->obtenerDatoProducto($producto, 'id');
            $precioBase = (float) ($this->obtenerDatoProducto($producto, 'precio') ?? 0);
            $iva = (int) ($this->obtenerDatoProducto($producto, 'iva') ?? 21);

            if ($id !== null) {
                $mapaPrecios[(int) $id] = $precioBase * (1 + ($iva / 100));
            }
        }

        $total = 0.0;

        foreach ($productosNormalizados as $producto) {
            $id = (int) $producto['producto_id'];
            $cantidad = (int) $producto['cantidad'];

            if (isset($mapaPrecios[$id])) {
                $total += $mapaPrecios[$id] * $cantidad;
            }
        }

        return round($total, 2);
    }
}