/**
 * GESTIÓN DE SERIES Y NUMERACIÓN DE COMPROBANTES
 * Sistema flexible con control multi-empresa
 */

var tabla_series;
var tabla_asignaciones;
var tabla_alertas;
var usuario_seleccionado = 0;

// ============================================
// INICIALIZACIÓN
// ============================================
function init() {
    listarSeries();
    cargarTiposDocumento();
    cargarSelectUsuarios();
    listarAlertas();

    // Event listeners para tabs
    $('#btnNuevaSerie').click(function() {
        mostrarFormulario();
        limpiarFormulario();
    });

    $('#btnCancelarSerie').click(function() {
        $('#tab-listado').tab('show');
        limpiarFormulario();
    });

    $('#btnActualizarListado').click(function() {
        tabla_series.ajax.reload(null, false);
    });

    // Formulario de serie
    $('#formularioSerie').on('submit', function(e) {
        e.preventDefault();
        guardarSerie();
    });

    // Actualizar ejemplo de numeración al escribir
    $('#prefijo, #serie, #sufijo, #longitud_numero').on('input', actualizarEjemploNumeracion);

    // Asignaciones
    $('#btnCargarAsignaciones').click(function() {
        usuario_seleccionado = $('#selectUsuarioAsignacion').val();
        if (usuario_seleccionado) {
            listarAsignacionesUsuario(usuario_seleccionado);
        } else {
            Swal.fire('Advertencia', 'Seleccione un usuario', 'warning');
        }
    });

    $('#btnAsignarNuevaSerie').click(function() {
        usuario_seleccionado = $('#selectUsuarioAsignacion').val();
        if (usuario_seleccionado) {
            mostrarModalAsignar(usuario_seleccionado);
        } else {
            Swal.fire('Advertencia', 'Seleccione un usuario primero', 'warning');
        }
    });

    $('#btnConfirmarAsignacion').click(function() {
        asignarSerie();
    });

    // Alertas
    $('#btnActualizarAlertas').click(function() {
        listarAlertas();
    });

    $('#btnConfirmarAtenderAlerta').click(function() {
        atenderAlerta();
    });
}

// ============================================
// LISTADO DE SERIES
// ============================================
function listarSeries() {
    tabla_series = $('#tblSeries').DataTable({
        destroy: true,
        ajax: {
            url: urlconsumo + 'serie_comprobante.php?op=listar',
            type: 'GET',
            dataSrc: ''
        },
        columns: [
            { data: null, render: function(data, type, row, meta) {
                return meta.row + 1;
            }},
            { data: 'tipo_documento_nombre' },
            { data: 'serie', render: function(data, type, row) {
                var prefijo = row.prefijo || '';
                var sufijo = row.sufijo || '';
                return prefijo + data + sufijo;
            }},
            { data: null, render: function(data, type, row) {
                var hasta = row.numero_hasta ? row.numero_hasta : '∞';
                return row.numero_desde + ' - ' + hasta;
            }},
            { data: 'numero_actual', render: function(data) {
                return '<span class="badge bg-info">' + data + '</span>';
            }},
            { data: 'numeros_disponibles', render: function(data) {
                if (data === -1) {
                    return '<span class="badge bg-success">Ilimitado</span>';
                } else if (data === 0) {
                    return '<span class="badge bg-danger">Agotado</span>';
                } else if (data < 100) {
                    return '<span class="badge bg-warning text-dark">' + data + '</span>';
                } else {
                    return '<span class="badge bg-success">' + data + '</span>';
                }
            }},
            { data: 'porcentaje_usado', render: function(data) {
                if (data === null) {
                    return '-';
                }
                var clase = data >= 90 ? 'bg-danger' : (data >= 70 ? 'bg-warning' : 'bg-success');
                return '<div class="progress"><div class="progress-bar ' + clase + '" style="width: ' + data + '%">' + data + '%</div></div>';
            }},
            { data: 'estado', render: function(data) {
                var clase = '';
                switch(data) {
                    case 'ACTIVA': clase = 'bg-success'; break;
                    case 'INACTIVA': clase = 'bg-secondary'; break;
                    case 'SUSPENDIDA': clase = 'bg-warning'; break;
                    case 'AGOTADA': clase = 'bg-danger'; break;
                }
                return '<span class="badge ' + clase + '">' + data + '</span>';
            }},
            { data: 'es_electronica', render: function(data) {
                return data == 1 ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
            }},
            { data: 'usuarios_asignados', render: function(data) {
                return '<span class="badge bg-primary">' + data + '</span>';
            }},
            { data: null, render: function(data) {
                var botones = '<div class="btn-group btn-group-sm">';

                botones += '<button class="btn btn-warning" onclick="mostrarSerie(' + data.idserie_comprobante + ')" title="Editar"><i class="fa fa-edit"></i></button>';

                botones += '<button class="btn btn-info" onclick="verHistorial(' + data.idserie_comprobante + ')" title="Historial"><i class="fa fa-history"></i></button>';

                if (data.estado === 'ACTIVA') {
                    botones += '<button class="btn btn-secondary" onclick="desactivarSerie(' + data.idserie_comprobante + ')" title="Desactivar"><i class="fa fa-pause"></i></button>';
                } else if (data.estado === 'INACTIVA' || data.estado === 'AGOTADA') {
                    botones += '<button class="btn btn-success" onclick="activarSerie(' + data.idserie_comprobante + ')" title="Activar"><i class="fa fa-play"></i></button>';
                }

                if (data.numero_actual === 0) {
                    botones += '<button class="btn btn-danger" onclick="eliminarSerie(' + data.idserie_comprobante + ')" title="Eliminar"><i class="fa fa-trash"></i></button>';
                }

                botones += '</div>';
                return botones;
            }}
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        order: [[1, 'asc'], [2, 'asc']],
        pageLength: 25,
        responsive: true
    });
}

// ============================================
// FORMULARIO
// ============================================
function mostrarFormulario() {
    $('#tab-nueva').tab('show');
    $('#tituloFormulario').text('Nueva Serie de Comprobante');
}

function limpiarFormulario() {
    $('#formularioSerie')[0].reset();
    $('#idserie_comprobante').val('');
    $('#es_electronica').prop('checked', true);
    $('#es_contingencia').prop('checked', false);
    $('#requiere_autorizacion').prop('checked', false);
    $('#numero_actual').val(0);
    $('#numero_desde').val(1);
    $('#longitud_numero').val(8);
    $('#alerta_porcentaje').val(90);
    $('#codigo_establecimiento').val('0000');
    actualizarEjemploNumeracion();
}

function actualizarEjemploNumeracion() {
    var prefijo = $('#prefijo').val() || '';
    var serie = $('#serie').val() || 'F001';
    var sufijo = $('#sufijo').val() || '';
    var longitud = parseInt($('#longitud_numero').val()) || 8;

    var numero = '1'.padStart(longitud, '0');
    var ejemplo = prefijo + serie + '-' + numero + sufijo;

    $('#ejemploNumeracion').text(ejemplo);
}

function guardarSerie() {
    var formData = new FormData($('#formularioSerie')[0]);
    formData.append('op', 'guardaryeditar');

    // Convertir checkboxes a valores numéricos
    formData.set('es_electronica', $('#es_electronica').is(':checked') ? 1 : 0);
    formData.set('es_contingencia', $('#es_contingencia').is(':checked') ? 1 : 0);
    formData.set('requiere_autorizacion', $('#requiere_autorizacion').is(':checked') ? 1 : 0);

    Swal.fire({
        title: 'Guardando serie...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: urlconsumo + 'serie_comprobante.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.mensaje,
                    showConfirmButton: true
                }).then(() => {
                    $('#tab-listado').tab('show');
                    limpiarFormulario();
                    tabla_series.ajax.reload(null, false);
                });
            } else {
                Swal.fire('Error', response.error || 'No se pudo guardar la serie', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            Swal.fire('Error', 'Ocurrió un error al guardar la serie', 'error');
        }
    });
}

function mostrarSerie(idserie_comprobante) {
    $.ajax({
        url: urlconsumo + 'serie_comprobante.php?op=mostrar',
        type: 'GET',
        data: { idserie_comprobante: idserie_comprobante },
        dataType: 'json',
        success: function(data) {
            if (data) {
                mostrarFormulario();
                $('#tituloFormulario').text('Editar Serie de Comprobante');

                // Llenar formulario
                $('#idserie_comprobante').val(data.idserie_comprobante);
                $('#tipo_documento_sunat').val(data.tipo_documento_sunat);
                $('#serie').val(data.serie);
                $('#prefijo').val(data.prefijo);
                $('#sufijo').val(data.sufijo);
                $('#numero_actual').val(data.numero_actual);
                $('#numero_desde').val(data.numero_desde);
                $('#numero_hasta').val(data.numero_hasta);
                $('#longitud_numero').val(data.longitud_numero);
                $('#establecimiento').val(data.establecimiento);
                $('#codigo_establecimiento').val(data.codigo_establecimiento);
                $('#descripcion').val(data.descripcion);
                $('#fecha_inicio_uso').val(data.fecha_inicio_uso);
                $('#fecha_fin_uso').val(data.fecha_fin_uso);
                $('#alerta_porcentaje').val(data.alerta_porcentaje);

                $('#es_electronica').prop('checked', data.es_electronica == 1);
                $('#es_contingencia').prop('checked', data.es_contingencia == 1);
                $('#requiere_autorizacion').prop('checked', data.requiere_autorizacion == 1);

                actualizarEjemploNumeracion();
            }
        },
        error: function() {
            Swal.fire('Error', 'No se pudo cargar la serie', 'error');
        }
    });
}

// ============================================
// OPERACIONES DE ESTADO
// ============================================
function activarSerie(idserie_comprobante) {
    Swal.fire({
        title: '¿Activar serie?',
        text: 'La serie volverá a estar disponible para emitir comprobantes',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: urlconsumo + 'serie_comprobante.php?op=activar',
                type: 'POST',
                data: { idserie_comprobante: idserie_comprobante },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Activada', response.mensaje, 'success');
                        tabla_series.ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                }
            });
        }
    });
}

function desactivarSerie(idserie_comprobante) {
    Swal.fire({
        title: '¿Desactivar serie?',
        text: 'No se podrán emitir más comprobantes con esta serie',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: urlconsumo + 'serie_comprobante.php?op=desactivar',
                type: 'POST',
                data: { idserie_comprobante: idserie_comprobante },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Desactivada', response.mensaje, 'success');
                        tabla_series.ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                }
            });
        }
    });
}

function eliminarSerie(idserie_comprobante) {
    Swal.fire({
        title: '¿Eliminar serie?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: urlconsumo + 'serie_comprobante.php?op=eliminar',
                type: 'POST',
                data: { idserie_comprobante: idserie_comprobante },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Eliminada', response.mensaje, 'success');
                        tabla_series.ajax.reload(null, false);
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                }
            });
        }
    });
}

// ============================================
// HISTORIAL
// ============================================
function verHistorial(idserie_comprobante) {
    $.ajax({
        url: urlconsumo + 'serie_comprobante.php?op=obtenerHistorial',
        type: 'GET',
        data: { idserie_comprobante: idserie_comprobante },
        dataType: 'json',
        success: function(data) {
            var html = '';

            if (data.length === 0) {
                html = '<tr><td colspan="4" class="text-center">No hay historial disponible</td></tr>';
            } else {
                data.forEach(function(registro) {
                    html += '<tr>';
                    html += '<td>' + registro.fecha_accion + '</td>';
                    html += '<td><span class="badge bg-info">' + registro.accion + '</span></td>';
                    html += '<td>' + (registro.usuario_nombre || 'Sistema') + '</td>';
                    html += '<td>' + (registro.motivo || '-') + '</td>';
                    html += '</tr>';
                });
            }

            $('#tbodyHistorial').html(html);
            $('#modalHistorial').modal('show');
        }
    });
}

// ============================================
// ASIGNACIONES
// ============================================
function listarAsignacionesUsuario(idusuario) {
    if (tabla_asignaciones) {
        tabla_asignaciones.destroy();
    }

    tabla_asignaciones = $('#tblAsignaciones').DataTable({
        ajax: {
            url: urlconsumo + 'serie_comprobante.php?op=listarSeriesUsuario',
            type: 'GET',
            data: { idusuario: idusuario },
            dataSrc: ''
        },
        columns: [
            { data: null, render: function(data, type, row, meta) {
                return meta.row + 1;
            }},
            { data: 'serie' },
            { data: 'tipo_documento' },
            { data: 'estado_serie', render: function(data) {
                var clase = data === 'ACTIVA' ? 'bg-success' : 'bg-secondary';
                return '<span class="badge ' + clase + '">' + data + '</span>';
            }},
            { data: 'es_predeterminada', render: function(data) {
                return data == 1 ? '<i class="fa fa-star text-warning"></i>' : '-';
            }},
            { data: 'puede_emitir', render: function(data) {
                return data == 1 ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
            }},
            { data: 'puede_modificar', render: function(data) {
                return data == 1 ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
            }},
            { data: null, render: function(data) {
                return '<button class="btn btn-sm btn-danger" onclick="quitarAsignacion(' + idusuario + ', ' + data.idserie_comprobante + ')"><i class="fa fa-trash"></i></button>';
            }}
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        paging: false,
        searching: false
    });
}

function mostrarModalAsignar(idusuario) {
    $('#modal_idusuario').val(idusuario);
    $('#modal_es_predeterminada').prop('checked', false);
    $('#modal_puede_emitir').prop('checked', true);
    $('#modal_puede_modificar').prop('checked', false);

    // Cargar series disponibles
    $.ajax({
        url: urlconsumo + 'serie_comprobante.php?op=listar',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var html = '<option value="">Seleccione serie...</option>';
            data.forEach(function(serie) {
                if (serie.estado === 'ACTIVA') {
                    html += '<option value="' + serie.idserie_comprobante + '">' +
                            serie.tipo_documento_nombre + ' - ' + serie.serie + '</option>';
                }
            });
            $('#modal_idserie_comprobante').html(html);
        }
    });

    $('#modalAsignarSerie').modal('show');
}

function asignarSerie() {
    var formData = $('#formAsignarSerie').serialize();
    formData += '&op=asignarSerieAUsuario';
    formData += '&es_predeterminada=' + ($('#modal_es_predeterminada').is(':checked') ? 1 : 0);
    formData += '&puede_emitir=' + ($('#modal_puede_emitir').is(':checked') ? 1 : 0);
    formData += '&puede_modificar=' + ($('#modal_puede_modificar').is(':checked') ? 1 : 0);

    $.ajax({
        url: urlconsumo + 'serie_comprobante.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Éxito', response.mensaje, 'success');
                $('#modalAsignarSerie').modal('hide');
                listarAsignacionesUsuario(usuario_seleccionado);
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        }
    });
}

function quitarAsignacion(idusuario, idserie_comprobante) {
    Swal.fire({
        title: '¿Quitar asignación?',
        text: 'El usuario no podrá usar esta serie',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, quitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: urlconsumo + 'serie_comprobante.php?op=quitarSerieAUsuario',
                type: 'POST',
                data: {
                    idusuario: idusuario,
                    idserie_comprobante: idserie_comprobante
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Quitada', response.mensaje, 'success');
                        listarAsignacionesUsuario(usuario_seleccionado);
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                }
            });
        }
    });
}

// ============================================
// ALERTAS
// ============================================
function listarAlertas() {
    if (tabla_alertas) {
        tabla_alertas.destroy();
    }

    tabla_alertas = $('#tblAlertas').DataTable({
        ajax: {
            url: urlconsumo + 'serie_comprobante.php?op=listarAlertas',
            type: 'GET',
            dataSrc: function(json) {
                $('#badgeAlertas').text(json.length);
                return json;
            }
        },
        columns: [
            { data: null, render: function(data, type, row, meta) {
                return meta.row + 1;
            }},
            { data: 'serie' },
            { data: 'tipo_alerta' },
            { data: 'mensaje_alerta' },
            { data: 'fecha_alerta' },
            { data: 'estado_alerta', render: function(data) {
                var clase = '';
                switch(data) {
                    case 'PENDIENTE': clase = 'bg-warning'; break;
                    case 'NOTIFICADA': clase = 'bg-info'; break;
                    case 'ATENDIDA': clase = 'bg-success'; break;
                }
                return '<span class="badge ' + clase + '">' + data + '</span>';
            }},
            { data: null, render: function(data) {
                if (data.estado_alerta !== 'ATENDIDA') {
                    return '<button class="btn btn-sm btn-warning" onclick="mostrarAtenderAlerta(' + data.idserie_alerta + ')"><i class="fa fa-check"></i> Atender</button>';
                }
                return '-';
            }}
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        order: [[4, 'desc']],
        pageLength: 10
    });
}

function mostrarAtenderAlerta(idserie_alerta) {
    $('#alerta_idserie_alerta').val(idserie_alerta);
    $('#alerta_comentarios').val('');
    $('#modalAtenderAlerta').modal('show');
}

function atenderAlerta() {
    var formData = $('#formAtenderAlerta').serialize();
    formData += '&op=atenderAlerta';

    $.ajax({
        url: urlconsumo + 'serie_comprobante.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Atendida', response.mensaje, 'success');
                $('#modalAtenderAlerta').modal('hide');
                listarAlertas();
            } else {
                Swal.fire('Error', response.error, 'error');
            }
        }
    });
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================
function cargarTiposDocumento() {
    $.ajax({
        url: urlconsumo + 'serie_comprobante.php?op=listarTiposDocumento',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var html = '<option value="">Seleccione...</option>';
            data.forEach(function(tipo) {
                html += '<option value="' + tipo.codigo + '">' + tipo.codigo + ' - ' + tipo.descripcion + '</option>';
            });
            $('#tipo_documento_sunat').html(html);
        }
    });
}

function cargarSelectUsuarios() {
    $.ajax({
        url: urlconsumo + 'serie_comprobante.php?op=selectUsuarios',
        type: 'GET',
        success: function(data) {
            $('#selectUsuarioAsignacion').html(data);
        }
    });
}

// ============================================
// INICIALIZAR AL CARGAR
// ============================================
$(document).ready(function() {
    init();
});
