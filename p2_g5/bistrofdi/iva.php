<?php
// Función para calcular el precio final según el IVA indicado en el guion
function calcularPrecioFinal($base, $iva) {
	return $base * (1 + ($iva / 100));
}

// Función para determinar el estado de stock visual
function obtenerEstadoStock($cantidad) {
	return ($cantidad > 0) ? "En stock" : "Sin stock";
}
?>