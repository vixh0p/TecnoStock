/* ==============================
   TecnoStock - Validaciones JavaScript
   Se ejecutan en el navegador antes de enviar el formulario.
   PHP repite las mismas validaciones en el servidor.
   ============================== */

document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Login ---------- */
    const formLogin = document.getElementById('formLogin');
    if (formLogin) {
        formLogin.addEventListener('submit', function (e) {
            const correo = document.getElementById('correo');
            const clave = document.getElementById('clave');
            let ok = true;

            if (!correo.value.includes('@') || correo.value.trim() === '') {
                mostrarError(correo, 'Ingrese un correo válido');
                ok = false;
            } else {
                limpiarError(correo);
            }

            if (clave.value.trim() === '') {
                mostrarError(clave, 'La clave es obligatoria');
                ok = false;
            } else {
                limpiarError(clave);
            }

            if (!ok) e.preventDefault();
        });
    }

    /* ---------- Producto: crear / editar ---------- */
    const formProducto = document.getElementById('formProducto');
    if (formProducto) {
        formProducto.addEventListener('submit', function (e) {
            let ok = true;

            const codigo = document.getElementById('codigo');
            const nombre = document.getElementById('nombre');
            const precio = document.getElementById('precio');
            const stockActual = document.getElementById('stock_actual');
            const stockMinimo = document.getElementById('stock_minimo');
            const categoria = document.getElementById('id_categoria');

            // Código: mínimo 3 caracteres, alfanumérico con guiones
            if (codigo.value.trim().length < 3) {
                mostrarError(codigo, 'El código debe tener al menos 3 caracteres');
                ok = false;
            } else {
                limpiarError(codigo);
            }

            // Nombre: mínimo 3 caracteres
            if (nombre.value.trim().length < 3) {
                mostrarError(nombre, 'El nombre debe tener al menos 3 caracteres');
                ok = false;
            } else {
                limpiarError(nombre);
            }

            // Categoría: debe seleccionarse
            if (categoria.value === '') {
                mostrarError(categoria, 'Seleccione una categoría');
                ok = false;
            } else {
                limpiarError(categoria);
            }

            // Precio: número no negativo
            const precioVal = parseFloat(precio.value);
            if (isNaN(precioVal) || precioVal < 0) {
                mostrarError(precio, 'El precio debe ser un número no negativo');
                ok = false;
            } else {
                limpiarError(precio);
            }

            // Stock actual: entero no negativo
            const saVal = parseInt(stockActual.value);
            if (isNaN(saVal) || saVal < 0) {
                mostrarError(stockActual, 'El stock actual debe ser un entero no negativo');
                ok = false;
            } else {
                limpiarError(stockActual);
            }

            // Stock mínimo: entero no negativo
            const smVal = parseInt(stockMinimo.value);
            if (isNaN(smVal) || smVal < 0) {
                mostrarError(stockMinimo, 'El stock mínimo debe ser un entero no negativo');
                ok = false;
            } else {
                limpiarError(stockMinimo);
            }

            if (!ok) e.preventDefault();
        });
    }

    /* ---------- Movimiento: registrar ---------- */
    const formMovimiento = document.getElementById('formMovimiento');
    if (formMovimiento) {
        formMovimiento.addEventListener('submit', function (e) {
            let ok = true;

            const tipo = document.getElementById('tipo');
            const cantidad = document.getElementById('cantidad');
            const idProducto = document.getElementById('id_producto');

            if (tipo.value === '') {
                mostrarError(tipo, 'Seleccione el tipo de movimiento');
                ok = false;
            } else {
                limpiarError(tipo);
            }

            const cantVal = parseInt(cantidad.value);
            if (isNaN(cantVal) || cantVal <= 0) {
                mostrarError(cantidad, 'La cantidad debe ser un entero mayor a 0');
                ok = false;
            } else {
                limpiarError(cantidad);
            }

            if (idProducto.value === '') {
                mostrarError(idProducto, 'Seleccione un producto');
                ok = false;
            } else {
                limpiarError(idProducto);
            }

            if (!ok) e.preventDefault();
        });
    }

    /* ---------- Categoría: crear / editar ---------- */
    const formCategoria = document.getElementById('formCategoria');
    if (formCategoria) {
        formCategoria.addEventListener('submit', function (e) {
            let ok = true;
            const nombre = document.getElementById('nombre');

            if (nombre.value.trim().length < 2) {
                mostrarError(nombre, 'El nombre debe tener al menos 2 caracteres');
                ok = false;
            } else {
                limpiarError(nombre);
            }

            if (!ok) e.preventDefault();
        });
    }

    /* ---------- Confirmaciones de desactivar / eliminar ---------- */
    document.querySelectorAll('.confirmar-accion').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const mensaje = btn.dataset.mensaje || '¿Confirma esta acción?';
            if (!confirm(mensaje)) {
                e.preventDefault();
            }
        });
    });
});

/* ==============================
   Funciones auxiliares de UI
   ============================== */

function mostrarError(campo, mensaje) {
    campo.classList.add('is-invalid');
    let feedback = campo.parentElement.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        campo.parentElement.appendChild(feedback);
    }
    feedback.textContent = mensaje;
}

function limpiarError(campo) {
    campo.classList.remove('is-invalid');
    const feedback = campo.parentElement.querySelector('.invalid-feedback');
    if (feedback) feedback.textContent = '';
}
