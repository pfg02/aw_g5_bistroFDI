// js/catalogo.js

document.addEventListener('DOMContentLoaded', function () {
    // BUSCADOR
    const buscador = document.getElementById('buscadorProductos');
    const bloquesCategoria = document.querySelectorAll('.bloque-categoria');

    if (buscador) {
        buscador.addEventListener('keyup', function () {
            const textoBusqueda = buscador.value.toLowerCase();

            bloquesCategoria.forEach(function (bloque) {
                const tarjetas = bloque.querySelectorAll('.tarjeta-producto');
                let productosVisibles = 0;

                tarjetas.forEach(function (tarjeta) {
                    const nombreProducto = tarjeta.getAttribute('data-nombre') || '';

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

    // MODAL
    const modal = document.getElementById('modalDetalle');
    const btnCerrar = document.getElementById('cerrarModal');
    const tituloModal = document.getElementById('modalNombre');
    const descModal = document.getElementById('modalDescripcion');
    const precioModal = document.getElementById('modalPrecio');
    const categoriaModal = document.getElementById('modalCategoria');
    const ivaModal = document.getElementById('modalIva');
    const stockModal = document.getElementById('modalStock');
    const galeriaModal = document.getElementById('modalGaleria');

    document.querySelectorAll('.btn-abrir-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const nombre = this.getAttribute('data-nombre') || '';
            const descripcion = this.getAttribute('data-descripcion') || '';
            const precio = this.getAttribute('data-precio') || '';
            const categoria = this.getAttribute('data-categoria') || '';
            const iva = this.getAttribute('data-iva') || '';
            const stock = this.getAttribute('data-stock') || '';
            const imagenesTexto = this.getAttribute('data-imagenes') || '[]';

            let imagenes = [];

            try {
                imagenes = JSON.parse(imagenesTexto);
            } catch (e) {
                imagenes = [];
            }

            // Rellenar datos del modal
            if (tituloModal) tituloModal.textContent = nombre;
            if (descModal) descModal.textContent = descripcion;
            if (precioModal) precioModal.textContent = precio;
            if (categoriaModal) categoriaModal.textContent = categoria;
            if (ivaModal) ivaModal.textContent = iva;
            if (stockModal) stockModal.textContent = stock;

            // Rellenar galería
            if (galeriaModal) {
                galeriaModal.innerHTML = '';

                if (imagenes.length > 0) {
                    imagenes.forEach(function (ruta) {
                        const img = document.createElement('img');
                        img.src = ruta;
                        img.alt = nombre;
                        img.className = 'modal-img-galeria';
                        galeriaModal.appendChild(img);
                    });
                } else {
                    galeriaModal.innerHTML = '<p class="txt-sin-imagenes">Este producto no tiene imágenes.</p>';
                }
            }

            if (modal) {
                modal.style.display = 'flex';
            }
        });
    });

    // Cerrar modal con la X
    if (btnCerrar) {
        btnCerrar.addEventListener('click', function () {
            if (modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Cerrar modal pulsando fuera
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // MENSAJE DE "AÑADIDO" Y VOLVER A LA ÚLTIMA TARJETA
    const forms = document.querySelectorAll('.form-anadir-carrito');

    forms.forEach(function (form) {
        form.addEventListener('submit', function () {
            const productoId = this.dataset.productoId;
            if (productoId) {
                sessionStorage.setItem('ultimoProductoAnadido', productoId);
            }
        });
    });

    const ultimoProducto = sessionStorage.getItem('ultimoProductoAnadido');
    if (ultimoProducto) {
        const tarjeta = document.getElementById('producto-' + ultimoProducto);
        const mensaje = document.getElementById('mensaje-' + ultimoProducto);

        if (tarjeta) {
            tarjeta.scrollIntoView({ behavior: 'auto', block: 'center' });
        }

        if (mensaje) {
            mensaje.classList.add('activo');

            setTimeout(function () {
                mensaje.classList.remove('activo');
                sessionStorage.removeItem('ultimoProductoAnadido');
            }, 1500);
        } else {
            sessionStorage.removeItem('ultimoProductoAnadido');
        }
    }
});

function abrirModalOfertas() {
	
	const modal = document.getElementById("modalOfertas");
	if (modal) {
		modal.style.display = "flex"; 
		document.body.style.overflow = "hidden";
	} else {
		alert("Pero hay un error: no encuentro el modalOfertas en el HTML.");
	}
}

function cerrarModalOfertas() {
	const modal = document.getElementById("modalOfertas");
	if (modal) {
		modal.style.display = "none";
		document.body.style.overflow = "auto";
	}
}

window.addEventListener('click', function(event) {
	const modal = document.getElementById("modalOfertas");
	if (event.target === modal) {
		cerrarModalOfertas();
	}
});