// js/catalogo.js

document.addEventListener('DOMContentLoaded', function() {
    
    // LÓGICA DEL BUSCADOR
    const buscador = document.getElementById('buscadorProductos');
    const bloquesCategoria = document.querySelectorAll('.bloque-categoria');

    if(buscador) {
        buscador.addEventListener('keyup', function() {
            const textoBusqueda = buscador.value.toLowerCase();
            bloquesCategoria.forEach(function(bloque) {
                const tarjetas = bloque.querySelectorAll('.tarjeta-producto');
                let productosVisibles = 0;
                tarjetas.forEach(function(tarjeta) {
                    const nombreProducto = tarjeta.getAttribute('data-nombre');
                    if (nombreProducto.includes(textoBusqueda)) {
                        tarjeta.style.display = 'flex';
                        productosVisibles++;
                    } else {
                        tarjeta.style.display = 'none';
                    }
                });
                bloque.style.display = (productosVisibles === 0) ? 'none' : 'block';
            });
        });
    }

    // LÓGICA DEL MODAL
    const modal = document.getElementById('modalDetalle');
    const btnCerrar = document.getElementById('cerrarModal');
    const tituloModal = document.getElementById('modalNombre');
    const descModal = document.getElementById('modalDescripcion');
    const precioModal = document.getElementById('modalPrecio');

    // Al hacer clic en cualquier botón de "Ver detalles"
    document.querySelectorAll('.btn-abrir-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            tituloModal.textContent = this.getAttribute('data-nombre');
            descModal.textContent = this.getAttribute('data-descripcion');
            precioModal.textContent = this.getAttribute('data-precio');
            modal.style.display = 'flex'; 
        });
    });

    // Cerrar pinchando en la X
    if (btnCerrar) {
        btnCerrar.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    // Cerrar pinchando fuera de la caja blanca
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});