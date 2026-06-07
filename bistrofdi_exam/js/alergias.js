document.addEventListener('DOMContentLoaded', () => {
    // Leer LocalStorage al cargar la página
    let misAlergias = JSON.parse(localStorage.getItem('alergiasCliente')) || [];
    
    const botonesAlergenos = document.querySelectorAll('.alergeno-seleccionable');
    
    actualizarVista();

    // Añadir eventos click a la leyenda de alérgenos
    botonesAlergenos.forEach(boton => {
        boton.addEventListener('click', function() {
            const idAlergeno = parseInt(this.getAttribute('data-id'));
            
            // Si ya lo tiene, lo quita. Si no, lo añade
            const index = misAlergias.indexOf(idAlergeno);
            if (index > -1) {
                misAlergias.splice(index, 1);
            } else {
                misAlergias.push(idAlergeno);
            }
            
            localStorage.setItem('alergiasCliente', JSON.stringify(misAlergias));
            actualizarVista();
        });
    });

    function actualizarVista() {
        botonesAlergenos.forEach(boton => {
            const idAlergeno = parseInt(boton.getAttribute('data-id'));
            // Si esta seleccionado se marca en rojo, sino, se deja normal
			if (misAlergias.includes(idAlergeno)) {
                boton.style.border = "2px solid red";
                boton.style.backgroundColor = "#ffe6e6";
            } else {
                boton.style.border = "none";
                boton.style.backgroundColor = "white";
            }
        });

        // Bloquear los productos que contengan alérgenos peligrosos
        const tarjetas = document.querySelectorAll('.tarjeta-producto');
        
        tarjetas.forEach(tarjeta => {
            const alergenosString = tarjeta.getAttribute('data-alergenos');
            const botonAnadir = tarjeta.querySelector('.btn-anadir-catalogo');
            
            if (!botonAnadir) return;

            const alergenosProducto = alergenosString 
                ? alergenosString.split(',').map(num => parseInt(num)) 
                : [];
            
            // Si alguno de los alérgenos del producto está en misAlergias, es peligroso
            const esPeligroso = alergenosProducto.some(id => misAlergias.includes(id));

			// Si es peligroso, se bloquea el producto visualmente y se deshabilita el botón
            if (esPeligroso) {
                tarjeta.style.opacity = "0.6";
                tarjeta.style.border = "2px solid red";
                botonAnadir.disabled = true;
                botonAnadir.style.backgroundColor = "#ccc";
                botonAnadir.style.cursor = "not-allowed";
                botonAnadir.innerText = "No Apto";
            } else {
                // Restaurar estado normal
                tarjeta.style.opacity = "1";
                tarjeta.style.border = "1px solid #eee"; 
                botonAnadir.disabled = false;
                botonAnadir.style.backgroundColor = "";
                botonAnadir.style.cursor = "pointer";
                botonAnadir.innerText = "Añadir";
            }
        });
    }
});