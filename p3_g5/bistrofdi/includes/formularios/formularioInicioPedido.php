<?php
require_once __DIR__ . '/../core/formulario.php';

class FormularioInicioPedido extends Formulario
{
    public function __construct()
    {
        parent::__construct('formInicioPedido', [
            'action' => '../tienda/catalogo.php',
            'class' => '',
        ]);
    }

    protected function generaCamposFormulario(array $datos): string
    {
        $tipo = $datos['tipo'] ?? 'Local';
        $localSelected = $tipo === 'Local' ? 'selected' : '';
        $llevarSelected = $tipo === 'Llevar' ? 'selected' : '';

        return <<<HTML
<div>
    <label class="txt-bienvenida">¿Cómo prefieres tu pedido?</label>
    <select name="tipo" id="tipo" class="select-estado">
        <option value="Local" {$localSelected}>Consumir en el local</option>
        <option value="Llevar" {$llevarSelected}>Para llevar</option>
    </select>
</div>

<div class="contenedor-botones-index">
    <button type="submit" class="btn-login"> Empezar a pedir </button>
    <a href="../../../index.php" class="btn-admin">Cancelar</a>
</div>
HTML;
    }

    protected function procesaFormulario(array $datos): ?string
    {
        return null;
    }
}