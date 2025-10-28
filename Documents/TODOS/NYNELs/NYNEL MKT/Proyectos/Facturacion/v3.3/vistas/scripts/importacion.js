/**
 * MÓDULO DE IMPORTACIONES CON DUA E INVOICE
 * Sistema de gestión de importaciones con cálculo automático de costos
 * Distribución proporcional de gastos y tributos aduaneros
 */

var tabla_importaciones;
var tabla_detalle;
var tabla_top_proveedores;
var tabla_productos_mas_importados;
var detalles_productos = []; // Array para almacenar productos temporalmente

// ============================================
// INICIALIZACIÓN DEL MÓDULO
// ============================================
function init() {
    mostrarForm(false);
    listarImportaciones();

    // Event listeners para tabs
    $('#tab-listado').click(function() {
        mostrarTabPanel('panelListado');
        $(this).addClass('active').siblings().removeClass('active');
    });

    $('#tab-nueva').click(function() {
        mostrarTabPanel('panelFormulario');
        $(this).addClass('active').siblings().removeClass('active');
    });

    $('#tab-estadisticas').click(function() {
        mostrarTabPanel('panelEstadisticas');
        $(this).addClass('active').siblings().removeClass('active');
        cargarEstadisticas();
    });

    // Event listeners para botones principales
    $('#btnNuevo').click(function() {
        mostrarTabPanel('panelFormulario');
        $('#tab-nueva').addClass('active').siblings().removeClass('active');
        limpiarFormulario();
    });

    $('#btnCancelar').click(function() {
        mostrarTabPanel('panelListado');
        $('#tab-listado').addClass('active').siblings().removeClass('active');
        limpiarFormulario();
    });

    // Event listener para guardar
    $('#formularioImportacion').on('submit', function(e) {
        e.preventDefault();
        guardarImportacion();
    });

    // Event listeners para cálculos automáticos
    $('#valor_fob, #valor_flete, #valor_seguro').on('input', calcularCIF);
    $('#tipo_cambio').on('input', recalcularTotales);

    // Event listener para distribuir costos
    $('#btnDistribuirCostos').click(function() {
        var idimportacion = $('#idimportacion').val();
        if (!idimportacion) {
            Swal.fire('Advertencia', 'Debe guardar la importación antes de distribuir costos', 'warning');
            return;
        }
        distribuirCostos(idimportacion);
    });

    // Event listener para agregar producto
    $('#btnAgregarProductoModal').click(function() {
        $('#modalAgregarProducto').modal('show');
        limpiarFormularioProducto();
    });

    $('#formAgregarProducto').on('submit', function(e) {
        e.preventDefault();
        agregarProducto();
    });

    // Event listener para búsqueda de partida arancelaria
    $('#producto_partida').on('blur', function() {
        var partida = $(this).val();
        if (partida.length >= 8) {
            buscarPartidaArancelaria(partida);
        }
    });

    // Event listener para filtros de estadísticas
    $('#btnGenerarEstadisticas').click(function() {
        cargarEstadisticas();
    });

    // Inicializar tabla de detalles
    inicializarTablaDetalle();
}

// ============================================
// FUNCIONES DE NAVEGACIÓN
// ============================================
function mostrarTabPanel(panel) {
    $('.tab-panel').hide();
    $('#' + panel).show();
}

function mostrarForm(flag) {
    if (flag) {
        $('#listado').hide();
        $('#formularioregistros').show();
        $('#btnNuevo').hide();
    } else {
        $('#listado').show();
        $('#formularioregistros').hide();
        $('#btnNuevo').show();
    }
}

// ============================================
// LISTADO DE IMPORTACIONES
// ============================================
function listarImportaciones() {
    tabla_importaciones = $('#tblImportaciones').DataTable({
        destroy: true,
        ajax: {
            url: urlconsumo + 'importacion.php?op=listar',
            type: 'GET',
            dataSrc: ''
        },
        columns: [
            { data: null, render: function(data, type, row, meta) {
                return meta.row + 1;
            }},
            { data: 'numero_dua' },
            { data: 'fecha_dua' },
            { data: 'numero_invoice' },
            { data: 'proveedor_extranjero' },
            { data: 'incoterm' },
            { data: 'valor_fob', render: function(data, type, row) {
                return row.moneda_invoice + ' ' + formatearMoneda(data);
            }},
            { data: 'costo_total_soles', render: function(data) {
                return 'S/ ' + formatearMoneda(data);
            }},
            { data: 'estado', render: function(data) {
                var clase = data === 'ACTIVO' ? 'success' : 'danger';
                return '<span class="badge bg-' + clase + '">' + data + '</span>';
            }},
            { data: null, render: function(data) {
                var botones = '<div class="btn-group btn-group-sm" role="group">';
                botones += '<button class="btn btn-warning" onclick="mostrarImportacion(' + data.idimportacion + ')" title="Editar"><i class="fa fa-edit"></i></button>';
                botones += '<button class="btn btn-info" onclick="verDetalle(' + data.idimportacion + ')" title="Ver Detalle"><i class="fa fa-eye"></i></button>';

                if (data.estado === 'ACTIVO' && !data.idingreso) {
                    botones += '<button class="btn btn-success" onclick="generarCompra(' + data.idimportacion + ')" title="Generar Compra"><i class="fa fa-shopping-cart"></i></button>';
                }

                if (data.estado === 'ACTIVO') {
                    botones += '<button class="btn btn-danger" onclick="anularImportacion(' + data.idimportacion + ')" title="Anular"><i class="fa fa-times"></i></button>';
                }

                botones += '</div>';
                return botones;
            }}
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        order: [[2, 'desc']],
        pageLength: 25,
        responsive: true
    });
}

// ============================================
// MOSTRAR IMPORTACIÓN PARA EDICIÓN
// ============================================
function mostrarImportacion(idimportacion) {
    $.ajax({
        url: urlconsumo + 'importacion.php?op=mostrar',
        type: 'GET',
        data: { idimportacion: idimportacion },
        dataType: 'json',
        success: function(data) {
            if (data) {
                mostrarTabPanel('panelFormulario');
                $('#tab-nueva').addClass('active').siblings().removeClass('active');

                // Llenar formulario con datos
                $('#idimportacion').val(data.idimportacion);

                // Datos del DUA
                $('#numero_dua').val(data.numero_dua);
                $('#fecha_dua').val(data.fecha_dua);
                $('#fecha_llegada').val(data.fecha_llegada);
                $('#aduana').val(data.aduana);
                $('#agente_aduanero').val(data.agente_aduanero);
                $('#regimen_aduanero').val(data.regimen_aduanero);

                // Datos del Invoice
                $('#numero_invoice').val(data.numero_invoice);
                $('#fecha_invoice').val(data.fecha_invoice);
                $('#proveedor_extranjero').val(data.proveedor_extranjero);
                $('#pais_origen').val(data.pais_origen);
                $('#moneda_invoice').val(data.moneda_invoice);

                // Valores e Incoterm
                $('#incoterm').val(data.incoterm);
                $('#valor_fob').val(data.valor_fob);
                $('#valor_flete').val(data.valor_flete);
                $('#valor_seguro').val(data.valor_seguro);
                calcularCIF();

                // Tipo de cambio
                $('#tipo_cambio').val(data.tipo_cambio);

                // Tributos aduaneros
                $('#derechos_aduaneros').val(data.derechos_aduaneros);
                $('#igv_importacion').val(data.igv_importacion);
                $('#ipm').val(data.ipm);
                $('#percepcion_igv').val(data.percepcion_igv);
                $('#otros_tributos').val(data.otros_tributos);

                // Gastos adicionales
                $('#gastos_despacho').val(data.gastos_despacho);
                $('#gastos_transporte_local').val(data.gastos_transporte_local);
                $('#gastos_almacenaje').val(data.gastos_almacenaje);
                $('#otros_gastos').val(data.otros_gastos);

                // Observaciones y documentos
                $('#observaciones').val(data.observaciones);
                $('#ruta_documentos').val(data.ruta_documentos);

                // Cargar detalle de productos
                cargarDetalleProductos(idimportacion);

                mostrarForm(true);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar importación:', error);
            Swal.fire('Error', 'No se pudo cargar la información de la importación', 'error');
        }
    });
}

// ============================================
// CARGAR DETALLE DE PRODUCTOS
// ============================================
function cargarDetalleProductos(idimportacion) {
    $.ajax({
        url: urlconsumo + 'importacion.php?op=listarDetalles',
        type: 'GET',
        data: { idimportacion: idimportacion },
        dataType: 'json',
        success: function(data) {
            detalles_productos = data;
            actualizarTablaDetalle();
        }
    });
}

// ============================================
// GUARDAR IMPORTACIÓN
// ============================================
function guardarImportacion() {
    // Validar que haya al menos un producto
    if (detalles_productos.length === 0) {
        Swal.fire('Advertencia', 'Debe agregar al menos un producto a la importación', 'warning');
        return;
    }

    // Preparar datos del formulario
    var formData = new FormData($('#formularioImportacion')[0]);
    formData.append('op', 'guardaryeditar');

    // Agregar array de productos
    formData.append('productos', JSON.stringify(detalles_productos));

    // Mostrar loading
    Swal.fire({
        title: 'Guardando importación...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: urlconsumo + 'importacion.php',
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
                    text: 'Importación guardada correctamente',
                    showConfirmButton: true
                }).then(() => {
                    // Si es nueva, actualizar ID
                    if (response.idimportacion) {
                        $('#idimportacion').val(response.idimportacion);
                    }

                    // Recargar tabla
                    tabla_importaciones.ajax.reload(null, false);

                    // Preguntar si desea distribuir costos
                    if (response.idimportacion && detalles_productos.length > 0) {
                        Swal.fire({
                            title: '¿Distribuir costos?',
                            text: '¿Desea distribuir los costos de importación entre los productos?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, distribuir',
                            cancelButtonText: 'No, después'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                distribuirCostos(response.idimportacion);
                            }
                        });
                    }
                });
            } else {
                Swal.fire('Error', response.error || 'No se pudo guardar la importación', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            Swal.fire('Error', 'Ocurrió un error al guardar la importación', 'error');
        }
    });
}

// ============================================
// DISTRIBUIR COSTOS
// ============================================
function distribuirCostos(idimportacion) {
    Swal.fire({
        title: 'Distribuyendo costos...',
        text: 'Calculando costos proporcionales',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: urlconsumo + 'importacion.php?op=distribuirCostos',
        type: 'POST',
        data: { idimportacion: idimportacion },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Costos distribuidos',
                    text: 'Los costos se distribuyeron correctamente entre los productos',
                    showConfirmButton: true
                }).then(() => {
                    // Recargar detalle
                    cargarDetalleProductos(idimportacion);
                });
            } else {
                Swal.fire('Error', response.error || 'No se pudo distribuir los costos', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Ocurrió un error al distribuir los costos', 'error');
        }
    });
}

// ============================================
// GENERAR COMPRA DESDE IMPORTACIÓN
// ============================================
function generarCompra(idimportacion) {
    Swal.fire({
        title: 'Generar Compra',
        html: `
            <div class="mb-3">
                <label class="form-label">Proveedor Local:</label>
                <select id="swal_idproveedor" class="form-select" required>
                    <option value="">Seleccione...</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo de Comprobante:</label>
                <select id="swal_tipo_comprobante" class="form-select" required>
                    <option value="Factura">Factura</option>
                    <option value="Boleta">Boleta</option>
                    <option value="Ticket">Ticket</option>
                </select>
            </div>
            <div class="row">
                <div class="col-6">
                    <label class="form-label">Serie:</label>
                    <input type="text" id="swal_serie" class="form-control" maxlength="4" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Número:</label>
                    <input type="text" id="swal_numero" class="form-control" maxlength="8" required>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Generar Compra',
        cancelButtonText: 'Cancelar',
        didOpen: () => {
            // Cargar proveedores
            $.ajax({
                url: urlconsumo + 'proveedor.php?op=selectProveedor',
                type: 'GET',
                success: function(data) {
                    $('#swal_idproveedor').html(data);
                }
            });
        },
        preConfirm: () => {
            const idproveedor = $('#swal_idproveedor').val();
            const tipo_comprobante = $('#swal_tipo_comprobante').val();
            const serie = $('#swal_serie').val();
            const numero = $('#swal_numero').val();

            if (!idproveedor || !serie || !numero) {
                Swal.showValidationMessage('Por favor complete todos los campos');
                return false;
            }

            return { idproveedor, tipo_comprobante, serie, numero };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = result.value;

            Swal.fire({
                title: 'Generando compra...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: urlconsumo + 'importacion.php?op=generarCompra',
                type: 'POST',
                data: {
                    idimportacion: idimportacion,
                    idproveedor: datos.idproveedor,
                    tipo_comprobante: datos.tipo_comprobante,
                    serie: datos.serie,
                    numero: datos.numero
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Compra generada',
                            text: 'Se generó correctamente el ingreso de compra',
                            showConfirmButton: true
                        }).then(() => {
                            tabla_importaciones.ajax.reload();
                        });
                    } else {
                        Swal.fire('Error', response.error || 'No se pudo generar la compra', 'error');
                    }
                }
            });
        }
    });
}

// ============================================
// ANULAR IMPORTACIÓN
// ============================================
function anularImportacion(idimportacion) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción anulará la importación",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: urlconsumo + 'importacion.php?op=anular',
                type: 'POST',
                data: { idimportacion: idimportacion },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Anulado', 'La importación ha sido anulada', 'success');
                        tabla_importaciones.ajax.reload();
                    } else {
                        Swal.fire('Error', response.error || 'No se pudo anular la importación', 'error');
                    }
                }
            });
        }
    });
}

// ============================================
// GESTIÓN DE PRODUCTOS
// ============================================
function inicializarTablaDetalle() {
    tabla_detalle = $('#tblDetalleProductos').DataTable({
        destroy: true,
        data: [],
        columns: [
            { data: null, render: function(data, type, row, meta) {
                return meta.row + 1;
            }},
            { data: 'nombre_articulo' },
            { data: 'partida_arancelaria' },
            { data: 'cantidad' },
            { data: 'precio_unitario_fob', render: function(data) {
                return formatearMoneda(data);
            }},
            { data: 'valor_total_fob', render: function(data) {
                return formatearMoneda(data);
            }},
            { data: 'costo_unitario_final_soles', render: function(data) {
                return data ? 'S/ ' + formatearMoneda(data) : 'Pendiente';
            }},
            { data: null, render: function(data, type, row, meta) {
                return '<button class="btn btn-sm btn-danger" onclick="eliminarProductoDetalle(' + meta.row + ')"><i class="fa fa-trash"></i></button>';
            }}
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        paging: false,
        searching: false,
        info: false
    });
}

function actualizarTablaDetalle() {
    tabla_detalle.clear();
    tabla_detalle.rows.add(detalles_productos);
    tabla_detalle.draw();
}

function agregarProducto() {
    // Obtener datos del modal
    var idarticulo = $('#producto_idarticulo').val();
    var nombre_articulo = $('#producto_descripcion').val();
    var partida = $('#producto_partida').val();
    var cantidad = parseFloat($('#producto_cantidad').val());
    var precio_fob = parseFloat($('#producto_precio_fob').val());

    // Validaciones
    if (!nombre_articulo || !partida || cantidad <= 0 || precio_fob <= 0) {
        Swal.fire('Advertencia', 'Complete todos los campos correctamente', 'warning');
        return;
    }

    // Calcular valor total
    var valor_total = cantidad * precio_fob;

    // Agregar al array
    detalles_productos.push({
        idarticulo: idarticulo || null,
        nombre_articulo: nombre_articulo,
        partida_arancelaria: partida,
        cantidad: cantidad,
        precio_unitario_fob: precio_fob,
        valor_total_fob: valor_total,
        costo_unitario_final_soles: null
    });

    // Actualizar tabla
    actualizarTablaDetalle();

    // Cerrar modal y limpiar
    $('#modalAgregarProducto').modal('hide');
    limpiarFormularioProducto();

    Swal.fire({
        icon: 'success',
        title: 'Producto agregado',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000
    });
}

function eliminarProductoDetalle(index) {
    Swal.fire({
        title: '¿Eliminar producto?',
        text: "Se quitará el producto del detalle",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            detalles_productos.splice(index, 1);
            actualizarTablaDetalle();
            Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        }
    });
}

function buscarPartidaArancelaria(partida) {
    $.ajax({
        url: urlconsumo + 'importacion.php?op=buscarPartidaArancelaria',
        type: 'GET',
        data: { partida: partida },
        dataType: 'json',
        success: function(data) {
            if (data) {
                $('#producto_descripcion').val(data.descripcion);
                Swal.fire({
                    icon: 'info',
                    title: 'Partida encontrada',
                    text: data.descripcion,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }
    });
}

// ============================================
// CÁLCULOS AUTOMÁTICOS
// ============================================
function calcularCIF() {
    var fob = parseFloat($('#valor_fob').val()) || 0;
    var flete = parseFloat($('#valor_flete').val()) || 0;
    var seguro = parseFloat($('#valor_seguro').val()) || 0;

    var cif = fob + flete + seguro;
    $('#valor_cif_calculado').val(cif.toFixed(2));
}

function recalcularTotales() {
    // Esta función se puede expandir para recalcular otros totales
    calcularCIF();
}

// ============================================
// ESTADÍSTICAS
// ============================================
function cargarEstadisticas() {
    var fecha_inicio = $('#estadistica_fecha_inicio').val();
    var fecha_fin = $('#estadistica_fecha_fin').val();

    if (!fecha_inicio || !fecha_fin) {
        Swal.fire('Advertencia', 'Seleccione el rango de fechas', 'warning');
        return;
    }

    cargarTopProveedores(fecha_inicio, fecha_fin);
    cargarProductosMasImportados(fecha_inicio, fecha_fin);
    cargarEstadisticasPeriodo(fecha_inicio, fecha_fin);
}

function cargarTopProveedores(fecha_inicio, fecha_fin) {
    $.ajax({
        url: urlconsumo + 'importacion.php?op=topProveedores',
        type: 'GET',
        data: {
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            limite: 10
        },
        dataType: 'json',
        success: function(data) {
            if (tabla_top_proveedores) {
                tabla_top_proveedores.destroy();
            }

            tabla_top_proveedores = $('#tblTopProveedores').DataTable({
                data: data,
                columns: [
                    { data: null, render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }},
                    { data: 'proveedor_extranjero' },
                    { data: 'pais_origen' },
                    { data: 'total_importaciones' },
                    { data: 'valor_total_fob', render: function(data, type, row) {
                        return 'USD ' + formatearMoneda(data);
                    }}
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                paging: false,
                searching: false
            });
        }
    });
}

function cargarProductosMasImportados(fecha_inicio, fecha_fin) {
    $.ajax({
        url: urlconsumo + 'importacion.php?op=productosMasImportados',
        type: 'GET',
        data: {
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            limite: 20
        },
        dataType: 'json',
        success: function(data) {
            if (tabla_productos_mas_importados) {
                tabla_productos_mas_importados.destroy();
            }

            tabla_productos_mas_importados = $('#tblProductosMasImportados').DataTable({
                data: data,
                columns: [
                    { data: null, render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }},
                    { data: 'nombre_articulo' },
                    { data: 'partida_arancelaria' },
                    { data: 'cantidad_total' },
                    { data: 'valor_total_fob', render: function(data) {
                        return 'USD ' + formatearMoneda(data);
                    }},
                    { data: 'costo_promedio_final', render: function(data) {
                        return 'S/ ' + formatearMoneda(data);
                    }}
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 20
            });
        }
    });
}

function cargarEstadisticasPeriodo(fecha_inicio, fecha_fin) {
    $.ajax({
        url: urlconsumo + 'importacion.php?op=estadisticasPeriodo',
        type: 'GET',
        data: {
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin
        },
        dataType: 'json',
        success: function(data) {
            if (data) {
                $('#stat_total_importaciones').text(data.total_importaciones || 0);
                $('#stat_valor_fob_total').text('USD ' + formatearMoneda(data.valor_fob_total || 0));
                $('#stat_tributos_total').text('USD ' + formatearMoneda(data.tributos_total || 0));
                $('#stat_costo_total_soles').text('S/ ' + formatearMoneda(data.costo_total_soles || 0));
            }
        }
    });
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================
function limpiarFormulario() {
    $('#formularioImportacion')[0].reset();
    $('#idimportacion').val('');
    detalles_productos = [];
    actualizarTablaDetalle();
    calcularCIF();
}

function limpiarFormularioProducto() {
    $('#formAgregarProducto')[0].reset();
    $('#producto_idarticulo').val('');
    $('#producto_cantidad').val('1');
}

function formatearMoneda(valor) {
    if (!valor) return '0.00';
    return parseFloat(valor).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function verDetalle(idimportacion) {
    // Cargar vista de detalle en modal o panel
    mostrarImportacion(idimportacion);
}

// ============================================
// INICIALIZAR AL CARGAR LA PÁGINA
// ============================================
$(document).ready(function() {
    init();
});
