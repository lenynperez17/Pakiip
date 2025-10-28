// Script para controlar Modo Demo globalmente
// Este archivo permite activar/desactivar el modo demo desde la UI

// Función para abrir el modal de control de modo demo
function abrirModalModoDemo() {
    // Primero verificar el estado actual
    $.ajax({
        url: "../ajax/controlarModoDemo.php?accion=estado",
        type: "GET",
        dataType: "json",
        success: function(data) {
            if (data.success) {
                mostrarModalModoDemo(data.estado);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.mensaje
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo verificar el estado del modo demo'
            });
        }
    });
}

// Mostrar el modal con el estado actual
function mostrarModalModoDemo(estadoActual) {
    const esActivo = estadoActual === 'activo';
    const estadoTexto = esActivo ? 'ACTIVADO' : 'DESACTIVADO';
    const estadoColor = esActivo ? 'success' : 'secondary';
    const estadoIcon = esActivo ? 'bx-lock-alt' : 'bx-lock-open-alt';

    Swal.fire({
        title: '<strong>Control de Modo Demo</strong>',
        icon: 'info',
        html: `
            <div style="text-align: left; padding: 20px;">
                <p><strong>Estado actual:</strong> <span class="badge bg-${estadoColor}">${estadoTexto}</span></p>

                <hr>

                <h5><i class="bx ${estadoIcon}"></i> ¿Qué es el Modo Demo?</h5>
                <p>El modo demo permite que los usuarios naveguen y vean el sistema, pero <strong>NO pueden guardar, editar ni eliminar</strong> información.</p>

                <div class="alert alert-info">
                    <strong>Casos de uso:</strong>
                    <ul style="text-align: left; margin-top: 10px;">
                        <li>✅ Demostración a clientes</li>
                        <li>✅ Capacitación de personal</li>
                        <li>✅ Presentaciones y eventos</li>
                        <li>✅ Videos tutoriales</li>
                    </ul>
                </div>

                ${esActivo ?
                    '<div class="alert alert-warning">⚠️ <strong>Actualmente el sistema está en modo SOLO LECTURA</strong></div>' :
                    '<div class="alert alert-success">✅ <strong>Actualmente el sistema permite guardar y editar</strong></div>'
                }
            </div>
        `,
        width: '600px',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: esActivo ? '<i class="bx bx-lock-open-alt"></i> Desactivar Modo Demo' : '<i class="bx bx-lock-alt"></i> Activar Modo Demo',
        confirmButtonColor: esActivo ? '#28a745' : '#ffc107',
        denyButtonText: '<i class="bx bx-refresh"></i> Refrescar Estado',
        denyButtonColor: '#6c757d',
        cancelButtonText: 'Cerrar',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Cambiar el estado
            cambiarModoDemo(esActivo ? 'desactivar' : 'activar');
        } else if (result.isDenied) {
            // Refrescar el estado
            abrirModalModoDemo();
        }
    });
}

// Función para cambiar el modo demo
function cambiarModoDemo(accion) {
    const esActivacion = accion === 'activar';

    Swal.fire({
        title: '¿Estás seguro?',
        html: esActivacion ?
            '<p>Se activará el <strong>Modo Demo</strong>.</p><p>Los usuarios <strong>NO podrán guardar, editar ni eliminar</strong> datos.</p>' :
            '<p>Se desactivará el <strong>Modo Demo</strong>.</p><p>Los usuarios <strong>podrán guardar, editar y eliminar</strong> datos libremente.</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: esActivacion ? '#ffc107' : '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: esActivacion ? 'Sí, activar modo demo' : 'Sí, desactivar modo demo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loader
            Swal.fire({
                title: 'Modificando archivos...',
                html: 'Esto puede tardar unos segundos',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Realizar la petición AJAX
            $.ajax({
                url: `../ajax/controlarModoDemo.php?accion=${accion}`,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Cambio exitoso!',
                            html: `<p>${data.mensaje}</p><p><strong>Los cambios están activos inmediatamente.</strong></p>`,
                            confirmButtonText: 'Entendido',
                            timer: 5000
                        }).then(() => {
                            // Opcional: recargar la página para reflejar cambios
                            // location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.mensaje
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo cambiar el modo demo. Verifica los permisos de escritura en los archivos.'
                    });
                    console.error('Error:', error);
                }
            });
        }
    });
}

// Opcional: Agregar indicador visual del modo demo en el header
$(document).ready(function() {
    // Verificar estado y mostrar badge si está activo
    $.ajax({
        url: "../ajax/controlarModoDemo.php?accion=estado",
        type: "GET",
        dataType: "json",
        success: function(data) {
            if (data.success && data.estado === 'activo') {
                // Agregar badge visual en el header
                const badge = '<span id="modoDemoBadge" class="badge bg-warning ms-2" style="font-size: 12px;"><i class="bx bx-lock-alt"></i> MODO DEMO ACTIVO</span>';
                $('.header-logo').after(badge);
            }
        }
    });
});
