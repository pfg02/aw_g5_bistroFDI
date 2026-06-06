<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/formulario.php';

class FormularioInicioPedido extends Formulario
{
    public function __construct()
    {
        parent::__construct('formInicioPedido', [
            'action' => BASE_URL . '/includes/vistas/tienda/catalogo.php',
            'method' => 'POST',
            'class' => '',
        ]);
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $tipo = $datos['tipo'] ?? 'Local';
        $localSelected = $tipo === 'Local' ? 'selected' : '';
        $llevarSelected = $tipo === 'Llevar' ? 'selected' : '';
        $urlCancelar = BASE_URL . '/index.php';

        return <<<HTML
<div>
    <label for="tipo" class="txt-bienvenida">¿Cómo prefieres tu pedido?</label>
    <select name="tipo" id="tipo" class="select-estado" required>
        <option value="Local" {$localSelected}>Consumir en el local</option>
        <option value="Llevar" {$llevarSelected}>Para llevar</option>
    </select>
</div>

<div class="contenedor-botones-index">
    <button type="submit" class="btn-login">Empezar a pedir</button>
    <a href="{$urlCancelar}" class="btn-admin">Cancelar</a>
</div>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        return null;
    }
}