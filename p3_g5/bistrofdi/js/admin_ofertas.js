document.addEventListener('DOMContentLoaded', function () {
    const contenedor = document.getElementById('contenedor-productos');
    const btnAnadir = document.getElementById('btn-anadir-producto');
    const inputDescuento = document.getElementById('descuento_porcentaje_dinamico');
    const precioPackMostrado = document.getElementById('precio_pack_mostrado');
    const precioFinalMostrado = document.getElementById('precio_final_mostrado');
    const precioFinalHidden = document.getElementById('precio_final_hidden');

    if (!contenedor || !inputDescuento || !precioPackMostrado || !precioFinalMostrado) return;

    let actualizando = false;

    function limpiarImporte(valor) {
        return String(valor)
            .replace('€', '')
            .replace(/\s/g, '')
            .replace(',', '.')
            .trim();
    }

    function obtenerNumero(valor) {
        const numero = parseFloat(limpiarImporte(valor));
        return isNaN(numero) ? 0 : numero;
    }

    function calcularTotalPack() {
        let totalPack = 0;

        const selects = document.querySelectorAll('.js-producto-oferta');
        const cantidades = document.querySelectorAll('.js-cantidad-oferta');

        selects.forEach((select, index) => {
            const option = select.options[select.selectedIndex];
            const precio = option ? parseFloat(option.dataset.precio || '0') : 0;
            const cantidad = cantidades[index] ? parseInt(cantidades[index].value || '0', 10) : 0;

            if (!isNaN(precio) && !isNaN(cantidad) && cantidad > 0) {
                totalPack += precio * cantidad;
            }
        });

        return totalPack;
    }

    function actualizarHidden(precioFinal) {
        if (precioFinalHidden) {
            precioFinalHidden.value = precioFinal.toFixed(2);
        }
    }

    function recalcularOfertaDesdeDescuento() {
        if (actualizando) return;
        actualizando = true;

        const totalPack = calcularTotalPack();

        precioPackMostrado.value = totalPack.toFixed(2) + ' €';

        let descuento = obtenerNumero(inputDescuento.value);

        if (descuento < 0) descuento = 0;
        if (descuento > 100) descuento = 100;

        const precioFinal = totalPack - (totalPack * (descuento / 100));

        inputDescuento.value = descuento.toFixed(2);
        precioFinalMostrado.value = precioFinal.toFixed(2) + ' €';

        actualizarHidden(precioFinal);

        actualizando = false;
    }

    function recalcularDescuentoDesdePrecioFinal() {
        if (actualizando) return;
        actualizando = true;

        const totalPack = calcularTotalPack();

        precioPackMostrado.value = totalPack.toFixed(2) + ' €';

        let precioFinal = obtenerNumero(precioFinalMostrado.value);

        if (precioFinal < 0) {
            precioFinal = 0;
        }

        if (totalPack > 0 && precioFinal > totalPack) {
            precioFinal = totalPack;
        }

        let descuento = 0;

        if (totalPack > 0) {
            descuento = ((totalPack - precioFinal) / totalPack) * 100;
        }

        if (descuento < 0) descuento = 0;
        if (descuento > 100) descuento = 100;

        inputDescuento.value = descuento.toFixed(2);
        precioFinalMostrado.value = precioFinal.toFixed(2) + ' €';

        actualizarHidden(precioFinal);

        actualizando = false;
    }

    contenedor.addEventListener('change', function(e) {
        if (e.target.classList.contains('js-producto-oferta')) {
            recalcularOfertaDesdeDescuento();
        }
    });

    contenedor.addEventListener('input', function(e) {
        if (e.target.classList.contains('js-cantidad-oferta')) {
            recalcularOfertaDesdeDescuento();
        }
    });

    contenedor.addEventListener('click', function(e) {
        if (e.target.classList.contains('js-eliminar-fila')) {
            if (contenedor.querySelectorAll('.fila-pack-oferta').length > 1) {
                e.target.closest('.fila-pack-oferta').remove();
                recalcularOfertaDesdeDescuento();
            } else {
                alert("La oferta debe tener al menos un producto.");
            }
        }
    });

    inputDescuento.addEventListener('input', recalcularOfertaDesdeDescuento);
    inputDescuento.addEventListener('change', recalcularOfertaDesdeDescuento);

    precioFinalMostrado.addEventListener('focus', function () {
        precioFinalMostrado.value = limpiarImporte(precioFinalMostrado.value);
    });

    precioFinalMostrado.addEventListener('input', function () {
        if (actualizando) return;

        const totalPack = calcularTotalPack();
        let precioFinal = obtenerNumero(precioFinalMostrado.value);

        let descuento = 0;

        if (totalPack > 0) {
            descuento = ((totalPack - precioFinal) / totalPack) * 100;
        }

        if (descuento < 0) descuento = 0;
        if (descuento > 100) descuento = 100;

        precioPackMostrado.value = totalPack.toFixed(2) + ' €';
        inputDescuento.value = descuento.toFixed(2);

        actualizarHidden(precioFinal);
    });

    precioFinalMostrado.addEventListener('blur', recalcularDescuentoDesdePrecioFinal);
    precioFinalMostrado.addEventListener('change', recalcularDescuentoDesdePrecioFinal);

    if (btnAnadir) {
        btnAnadir.addEventListener('click', function() {
            const filaOriginal = contenedor.querySelector('.fila-pack-oferta');
            const nuevaFila = filaOriginal.cloneNode(true);

            nuevaFila.querySelector('.js-producto-oferta').value = "";
            nuevaFila.querySelector('.js-cantidad-oferta').value = "1";

            contenedor.appendChild(nuevaFila);
            recalcularOfertaDesdeDescuento();
        });
    }

    recalcularOfertaDesdeDescuento();
});