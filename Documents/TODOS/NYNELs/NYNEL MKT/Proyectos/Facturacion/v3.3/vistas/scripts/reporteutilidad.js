// ========================================
// MÓDULO DE REPORTE DE UTILIDAD MEJORADO
// Sistema de análisis de ingresos, egresos y utilidad
// ========================================

// ========== VARIABLES GLOBALES ==========
let tablaDetalle;
let tablaCategorias;
let tablaHistorial;
let chartUtilidad = null;
let fechasActuales = { inicio: '', fin: '' };

// ========== INICIALIZACIÓN ==========
$(document).ready(function() {
    inicializarDataTables();
    inicializarFechas();
    configurarEventListeners();
    cargarCategorias();
    listarHistorial();
});

// ========== CONFIGURACIÓN DE DATATABLES ==========
function inicializarDataTables() {
    // DataTable de detalle por período
    tablaDetalle = $('#tblDetallePeriodo').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "pageLength": 10,
        "responsive": true,
        "order": [[0, "asc"]],
        "footerCallback": function(row, data, start, end, display) {
            actualizarFooterTotales(this.api());
        }
    });

    // DataTable de detalle por categoría
    tablaCategorias = $('#tblDetalleCategoria').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "pageLength": 10,
        "responsive": true,
        "order": [[3, "desc"]]
    });

    // DataTable de historial
    tablaHistorial = $('#tblHistorialAnalisis').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "pageLength": 10,
        "responsive": true,
        "order": [[0, "desc"]]
    });
}

// ========== INICIALIZAR FECHAS POR DEFECTO ==========
function inicializarFechas() {
    let hoy = new Date();
    let primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);

    $('#fechaInicio').val(formatearFechaInput(primerDiaMes));
    $('#fechaFin').val(formatearFechaInput(hoy));
}

// ========== CONFIGURACIÓN DE EVENT LISTENERS ==========
function configurarEventListeners() {
    // Submit formulario filtros
    $('#formFiltros').submit(function(e) {
        e.preventDefault();
        generarReporte();
    });

    // Botón limpiar filtros
    $('#btnLimpiarFiltros').click(function() {
        limpiarFiltros();
    });

    // Botón nuevo análisis
    $('#btnNuevoAnalisis').click(function() {
        limpiarFiltros();
        $('html, body').animate({ scrollTop: 0 }, 300);
        $('#fechaInicio').focus();
    });

    // Exportar a Excel
    $('#btnExportarExcel').click(function() {
        exportarExcel();
    });

    // Exportar a PDF
    $('#btnExportarPDF').click(function() {
        exportarPDF();
    });
}

// ========== GENERAR REPORTE ==========
function generarReporte() {
    let fechaInicio = $('#fechaInicio').val();
    let fechaFin = $('#fechaFin').val();
    let categoria = $('#filtroCategoria').val();
    let tipoVista = $('#tipoVista').val();

    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Debe seleccionar las fechas de inicio y fin'
        });
        return;
    }

    // Validar que fecha inicio no sea mayor que fecha fin
    if (new Date(fechaInicio) > new Date(fechaFin)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'La fecha de inicio no puede ser mayor que la fecha de fin'
        });
        return;
    }

    // Guardar fechas actuales
    fechasActuales = { inicio: fechaInicio, fin: fechaFin };

    // Mostrar loading
    Swal.fire({
        title: 'Generando reporte...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Cargar datos en paralelo
    Promise.all([
        cargarResumen(fechaInicio, fechaFin, categoria),
        cargarTendencia(fechaInicio, fechaFin, tipoVista),
        cargarDetallePeriodo(fechaInicio, fechaFin, tipoVista),
        cargarDetalleCategoria(fechaInicio, fechaFin)
    ]).then(() => {
        Swal.close();
        mostrarSecciones();
    }).catch(error => {
        console.error('Error generando reporte:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo generar el reporte'
        });
    });
}

// ========== CARGAR RESUMEN ==========
function cargarResumen(fechaInicio, fechaFin, categoria) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "../ajax/reporteutilidad.php?action=resumenPeriodo",
            type: "GET",
            data: {
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                categoria: categoria
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    actualizarCardsResumen(response);
                    resolve();
                } else {
                    reject(response.error);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

// ========== ACTUALIZAR CARDS DE RESUMEN ==========
function actualizarCardsResumen(data) {
    let ingresos = parseFloat(data.total_ingresos);
    let egresos = parseFloat(data.total_egresos);
    let utilidad = parseFloat(data.utilidad);
    let margen = parseFloat(data.margen);

    $('#cardTotalIngresos').text('S/ ' + formatearMonto(ingresos));
    $('#cardCantIngresos').text(data.cant_ingresos + ' transacciones');

    $('#cardTotalEgresos').text('S/ ' + formatearMonto(egresos));
    $('#cardCantEgresos').text(data.cant_egresos + ' transacciones');

    let utilidadClass = utilidad >= 0 ? 'text-success' : 'text-danger';
    let utilidadIcono = utilidad >= 0 ? '↑' : '↓';
    $('#cardUtilidad').html('<span class="' + utilidadClass + '">' + utilidadIcono + ' S/ ' + formatearMonto(Math.abs(utilidad)) + '</span>');
    $('#cardDiferencia').text(utilidad >= 0 ? 'Ganancia' : 'Pérdida');

    let margenClass = margen >= 0 ? 'text-success' : 'text-danger';
    $('#cardMargen').html('<span class="' + margenClass + '">' + formatearMonto(margen) + '%</span>');
}

// ========== CARGAR TENDENCIA ==========
function cargarTendencia(fechaInicio, fechaFin, tipoVista) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "../ajax/reporteutilidad.php?action=datosTendencia",
            type: "GET",
            data: {
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                tipo_vista: tipoVista
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    crearGraficoTendencia(response.data);
                    resolve();
                } else {
                    reject(response.error);
                }
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

// ========== CREAR GRÁFICO DE TENDENCIA ==========
function crearGraficoTendencia(data) {
    let periodos = data.map(item => item.periodo);
    let ingresos = data.map(item => parseFloat(item.ingresos));
    let egresos = data.map(item => parseFloat(item.egresos));
    let utilidad = data.map(item => parseFloat(item.utilidad));

    // Destruir gráfico anterior si existe
    if (chartUtilidad) {
        chartUtilidad.destroy();
    }

    let ctx = document.getElementById('chartUtilidad').getContext('2d');
    chartUtilidad = new Chart(ctx, {
        type: 'line',
        data: {
            labels: periodos,
            datasets: [
                {
                    label: 'Ingresos',
                    data: ingresos,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.3
                },
                {
                    label: 'Egresos',
                    data: egresos,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.3
                },
                {
                    label: 'Utilidad',
                    data: utilidad,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'S/ ' + formatearMonto(context.parsed.y);
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + formatearMonto(value);
                        }
                    }
                }
            }
        }
    });
}

// ========== CARGAR DETALLE POR PERÍODO ==========
function cargarDetallePeriodo(fechaInicio, fechaFin, tipoVista) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "../ajax/reporteutilidad.php?action=detallePeriodo",
            type: "GET",
            data: {
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                tipo_vista: tipoVista
            },
            dataType: "json",
            success: function(response) {
                if (tablaDetalle) {
                    tablaDetalle.clear();
                    if (response.aaData && response.aaData.length > 0) {
                        tablaDetalle.rows.add(response.aaData).draw();
                    } else {
                        tablaDetalle.draw();
                    }
                }
                resolve();
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

// ========== CARGAR DETALLE POR CATEGORÍA ==========
function cargarDetalleCategoria(fechaInicio, fechaFin) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "../ajax/reporteutilidad.php?action=detalleCategoria",
            type: "GET",
            data: {
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            },
            dataType: "json",
            success: function(response) {
                if (tablaCategorias) {
                    tablaCategorias.clear();
                    if (response.aaData && response.aaData.length > 0) {
                        tablaCategorias.rows.add(response.aaData).draw();
                    } else {
                        tablaCategorias.draw();
                    }
                }
                resolve();
            },
            error: function(xhr, status, error) {
                reject(error);
            }
        });
    });
}

// ========== ACTUALIZAR FOOTER DE TOTALES ==========
function actualizarFooterTotales(api) {
    let totalIngresos = 0;
    let totalEgresos = 0;
    let totalUtilidad = 0;

    api.rows().every(function() {
        let data = this.data();
        totalIngresos += parseMontoString(data[1]);
        totalEgresos += parseMontoString(data[2]);
        totalUtilidad += parseMontoString(data[3]);
    });

    let margen = totalIngresos > 0 ? (totalUtilidad / totalIngresos) * 100 : 0;

    $('#footerIngresos').text('S/ ' + formatearMonto(totalIngresos));
    $('#footerEgresos').text('S/ ' + formatearMonto(totalEgresos));
    $('#footerUtilidad').text('S/ ' + formatearMonto(totalUtilidad));
    $('#footerMargen').text(formatearMonto(margen) + '%');
}

// ========== VER DETALLE DE TRANSACCIONES ==========
function verDetalleTransacciones(fecha, periodo) {
    $('#tituloPeriodo').text(periodo);

    // Cargar ingresos y egresos
    cargarTransaccionesPorTipo(fecha, 'ingreso', '#tblIngresosDetalle');
    cargarTransaccionesPorTipo(fecha, 'egreso', '#tblEgresosDetalle');

    $('#modalDetalleTransacciones').modal('show');
}

// ========== CARGAR TRANSACCIONES POR TIPO ==========
function cargarTransaccionesPorTipo(fecha, tipo, selectorTabla) {
    $.ajax({
        url: "../ajax/reporteutilidad.php?action=transaccionesPorFecha",
        type: "GET",
        data: {
            fecha: fecha,
            tipo: tipo
        },
        dataType: "json",
        success: function(response) {
            if (response.success) {
                let tbody = $(selectorTabla + ' tbody');
                tbody.empty();

                if (response.data.length > 0) {
                    response.data.forEach(function(item) {
                        let fila = '<tr>' +
                            '<td>' + item.fecha + '</td>' +
                            '<td>' + item.categoria + '</td>' +
                            '<td>' + item.descripcion + '</td>' +
                            '<td>' + item.acreedor + '</td>' +
                            '<td class="text-end">' + item.monto + '</td>' +
                            '</tr>';
                        tbody.append(fila);
                    });
                } else {
                    tbody.append('<tr><td colspan="5" class="text-center">No hay transacciones</td></tr>');
                }
            }
        }
    });
}

// ========== CARGAR CATEGORÍAS ==========
function cargarCategorias() {
    $.ajax({
        url: "../ajax/reporteutilidad.php?action=listarCategorias",
        type: "GET",
        dataType: "json",
        success: function(response) {
            if (response.success) {
                let select = $('#filtroCategoria');
                select.empty();
                select.append('<option value="">Todas las categorías</option>');

                response.data.forEach(function(cat) {
                    select.append('<option value="' + cat.nombre + '">' + cat.nombre + '</option>');
                });
            }
        }
    });
}

// ========== LISTAR HISTORIAL ==========
function listarHistorial() {
    $.ajax({
        url: "../ajax/reporteutilidad.php?action=listarHistorial",
        type: "GET",
        dataType: "json",
        success: function(response) {
            if (tablaHistorial) {
                tablaHistorial.clear();
                if (response.aaData && response.aaData.length > 0) {
                    tablaHistorial.rows.add(response.aaData).draw();
                } else {
                    tablaHistorial.draw();
                }
            }
        }
    });
}

// ========== APROBAR UTILIDAD ==========
function aprobarutilidad(idutilidad) {
    Swal.fire({
        title: '¿Aprobar análisis?',
        text: "Esta acción marcará el análisis como aprobado",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, Aprobar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "../ajax/reporteutilidad.php?op=aprobarAnalisis",
                type: "POST",
                data: { idutilidad: idutilidad },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Aprobado!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        listarHistorial();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error
                        });
                    }
                }
            });
        }
    });
}

// ========== ELIMINAR UTILIDAD ==========
function eliminarutilidad(idutilidad) {
    Swal.fire({
        title: '¿Eliminar análisis?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "../ajax/reporteutilidad.php?op=eliminarAnalisis",
                type: "POST",
                data: { idutilidad: idutilidad },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        listarHistorial();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error
                        });
                    }
                }
            });
        }
    });
}

// ========== RECALCULAR UTILIDAD ==========
function recalcularutilidad(idutilidad) {
    Swal.fire({
        title: 'Recalculando...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "../ajax/insumos.php?op=recalcularutilidad&iduti=" + idutilidad,
        type: "GET",
        dataType: "json",
        success: function(response) {
            Swal.close();
            Swal.fire({
                icon: 'success',
                title: '¡Recalculado!',
                text: 'El análisis ha sido actualizado',
                timer: 1500,
                showConfirmButton: false
            });
            listarHistorial();
        }
    });
}

// ========== VER REPORTE DE UTILIDAD ==========
function reporteutilidad(idutilidad) {
    let rutacarpeta = '../reportes/reportegastosvsingresossemanal.php?id=' + idutilidad;
    window.open(rutacarpeta, '_blank');
}

// ========== EXPORTAR A EXCEL ==========
function exportarExcel() {
    Swal.fire({
        icon: 'info',
        title: 'Exportando...',
        text: 'Generando archivo Excel',
        timer: 1500,
        showConfirmButton: false
    });
    // Implementar exportación con biblioteca como SheetJS o similar
}

// ========== EXPORTAR A PDF ==========
function exportarPDF() {
    let fechaInicio = $('#fechaInicio').val();
    let fechaFin = $('#fechaFin').val();
    let rutacarpeta = '../reportes/reportegastosvsingresossemanal.php?f1=' + fechaInicio + '&f2=' + fechaFin;
    window.open(rutacarpeta, '_blank');
}

// ========== MOSTRAR SECCIONES ==========
function mostrarSecciones() {
    $('#resumenCards').slideDown(300);
    $('#seccionGrafico').slideDown(300);
    $('#seccionDetalle').slideDown(300);
    $('#seccionCategorias').slideDown(300);
}

// ========== LIMPIAR FILTROS ==========
function limpiarFiltros() {
    $('#formFiltros')[0].reset();
    inicializarFechas();
    $('#filtroCategoria').val('');
    $('#tipoVista').val('diario');

    $('#resumenCards').slideUp(300);
    $('#seccionGrafico').slideUp(300);
    $('#seccionDetalle').slideUp(300);
    $('#seccionCategorias').slideUp(300);
}

// ========== FUNCIONES HELPER ==========
function formatearMonto(valor) {
    return parseFloat(valor).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function parseMontoString(str) {
    return parseFloat(str.replace(/[S/\s,]/g, '')) || 0;
}

function formatearFechaInput(fecha) {
    let year = fecha.getFullYear();
    let month = String(fecha.getMonth() + 1).padStart(2, '0');
    let day = String(fecha.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}
