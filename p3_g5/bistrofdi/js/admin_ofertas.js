document.addEventListener('DOMContentLoaded', function () {
    const contenedor = document.getElementById('contenedor-productos');
    const btnAnadir = document.getElementById('btn-anadir-producto');
    const inputDescuento = document.getElementById('descuento_porcentaje_dinamico');
    const precioPackMostrado = document.getElementById('precio_pack_mostrado');
    const precioFinalMostrado = document.getElementById('precio_final_mostrado');
    const precioFinalHidden = document.getElementById('precio_final_hidden');

    if (!contenedor) return; // Seguridad por si el script carga en otra página

    function recalcularOferta() {
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

        precioPackMostrado.value = totalPack.toFixed(2) + ' €';

        let descuento = parseFloat(inputDescuento.value || '0');
        if (descuento < 0) descuento = 0;
        if (descuento > 100) descuento = 100;

        let precioFinal = totalPack - (totalPack * (descuento / 100));
        
        precioFinalMostrado.value = precioFinal.toFixed(2) + ' €';
        precioFinalHidden.value = precioFinal.toFixed(2);
    }

    // Delegación de eventos para las filas dinámicas
    contenedor.addEventListener('change', function(e) {
        if (e.target.classList.contains('js-producto-oferta')) recalcularOferta();
    });
    
    contenedor.addEventListener('input', function(e) {
        if (e.target.classList.contains('js-cantidad-oferta')) recalcularOferta();
    });

    contenedor.addEventListener('click', function(e) {
        if (e.target.classList.contains('js-eliminar-fila')) {
            if (contenedor.querySelectorAll('.fila-pack-oferta').length > 1) {
                e.target.closest('.fila-pack-oferta').remove();
                recalcularOferta();
            } else {
                alert("La oferta debe tener al menos un producto.");
            }
        }
    });

    inputDescuento.addEventListener('input', recalcularOferta);

    btnAnadir.addEventListener('click', function() {
        const filaOriginal = contenedor.querySelector('.fila-pack-oferta');
        const nuevaFila = filaOriginal.cloneNode(true);
        
        nuevaFila.querySelector('.js-producto-oferta').value = "";
        nuevaFila.querySelector('.js-cantidad-oferta').value = "1";
        
        contenedor.appendChild(nuevaFila);
        recalcularOferta();
    });

    // Calcular la primera vez que carga la página
    recalcularOferta();
});