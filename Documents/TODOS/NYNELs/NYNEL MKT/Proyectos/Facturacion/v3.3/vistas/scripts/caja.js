// ========================================
// MÓDULO DE CAJA DIARIA - GESTIÓN PROFESIONAL
// Sistema de apertura/cierre de caja con arqueo completo
// ========================================

// ========== VARIABLES GLOBALES ==========
let idcajaActual = null;
let dataCajaActual = null;
let tablaCajas;
let tablaMovimientos;

// ========== INICIALIZACIÓN AL CARGAR DOCUMENTO ==========
$(document).ready(function() {
    inicializarDataTables();
    verificarCajaAbierta();
    listarHistorialCajas();
    configurarEventListeners();

    // Auto-refresh cada 30 segundos para mantener datos actualizados
    setInterval(verificarCajaAbierta, 30000);
});

// ========== CONFIGURACIÓN DE DATATABLES ==========
function inicializarDataTables() {
    // DataTable para historial de cajas cerradas
    tablaCajas = $('#tblHistorialCajas').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[ 0, "desc" ]],
        "pageLength": 10,
        "responsive": true,
        "processing": true,
        "serverSide": false
    });

    // DataTable para movimientos de caja actual
    tablaMovimientos = $('#tblMovimientos').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "order": [[ 0, "desc" ]],
        "pageLength": 10,
        "responsive": true,
        "processing": true,
        "serverSide": false
    });
}

// ========== CONFIGURACIÓN DE EVENT LISTENERS ==========
function configurarEventListeners() {
    // Botón aperturar caja
    $('#btnAperturarCaja').click(function() {
        $('#formAperturarCaja')[0].reset();
        $('#turnoApertura').val('COMPLETO');
        $('#montoInicialApertura').val('0.00');
        $('#modalAperturarCaja').modal('show');
    });

    // Submit aperturar caja
    $('#formAperturarCaja').submit(function(e) {
        e.preventDefault();
        aperturarCaja();
    });

    // Botón registrar movimiento
    $('#btnRegistrarMovimiento').click(function() {
        if (idcajaActual) {
            $('#idcajaMovimiento').val(idcajaActual);
            $('#formRegistrarMovimiento')[0].reset();
            $('#tipoMovimiento').val('');
            $('#tipoPagoMovimiento').val('EFECTIVO');
            $('#modalRegistrarMovimiento').modal('show');
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe aperturar una caja primero'
            });
        }
    });

    // Submit registrar movimiento
    $('#formRegistrarMovimiento').submit(function(e) {
        e.preventDefault();
        registrarMovimiento();
    });

    // Botón cerrar caja
    $('#btnCerrarCaja').click(function() {
        if (idcajaActual && dataCajaActual) {
            prepararCierreCaja();
            $('#modalCerrarCaja').modal('show');
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'No hay caja abierta para cerrar'
            });
        }
    });

    // Submit cerrar caja
    $('#formCerrarCaja').submit(function(e) {
        e.preventDefault();
        cerrarCaja();
    });

    // Auto-cálculo del arqueo de caja
    $('.denominacion').on('input', calcularArqueoCaja);
    $('.otros-pagos').on('input', calcularArqueoCaja);
}

// ========== VERIFICAR SI HAY CAJA ABIERTA ==========
function verificarCajaAbierta() {
    $.ajax({
        url: "../ajax/caja.php?action=cajaAbierta",
        type: "GET",
        dataType: "json",
        success: function(response) {
            if (response && response.num_rows && response.num_rows > 0) {
                // Hay caja abierta
                mostrarCajaAbierta(response);
            } else {
                // No hay caja abierta
                mostrarCajaCerrada();
            }
        },
        error: function(xhr, status, error) {
            console.error("Error verificando caja abierta:", error);
        }
    });
}

// ========== MOSTRAR INTERFAZ DE CAJA ABIERTA ==========
function mostrarCajaAbierta(data) {
    idcajaActual = data.idcaja;
    dataCajaActual = data;

    // Ocultar botón aperturar, mostrar panel de caja abierta
    $('#btnAperturarCaja').hide();
    $('#resumenCaja').show();
    $('#panelCajaAbierta').show();
    $('#seccionMovimientos').show();

    // Actualizar información de la caja
    $('#cajaIdCaja').text(data.idcaja);
    $('#cajaUsuario').text(data.usuario || 'N/A');
    $('#cajaTurno').html('<span class="badge bg-info">' + (data.turno || 'N/A') + '</span>');
    $('#cajaFechaApertura').text(formatearFechaHora(data.fecha_apertura));

    // Calcular totales
    let montoInicial = parseFloat(data.monto_inicial) || 0;
    let totalIngresos = parseFloat(data.total_ingresos) || 0;
    let totalEgresos = parseFloat(data.total_egresos) || 0;
    let saldoActual = parseFloat(data.saldo_actual) || 0;

    // Actualizar cards de resumen
    $('#cajaMontoInicial').text('S/ ' + montoInicial.toFixed(2));
    $('#cajaTotalIngresos').text('S/ ' + totalIngresos.toFixed(2));
    $('#cajaTotalEgresos').text('S/ ' + totalEgresos.toFixed(2));
    $('#cajaSaldoActual').text('S/ ' + saldoActual.toFixed(2));

    // Cargar movimientos de esta caja
    cargarMovimientos(data.idcaja);
}

// ========== MOSTRAR INTERFAZ DE CAJA CERRADA ==========
function mostrarCajaCerrada() {
    idcajaActual = null;
    dataCajaActual = null;

    // Mostrar botón aperturar, ocultar panel de caja abierta
    $('#btnAperturarCaja').show();
    $('#resumenCaja').hide();
    $('#panelCajaAbierta').hide();
    $('#seccionMovimientos').hide();

    // Limpiar tabla de movimientos
    if (tablaMovimientos) {
        tablaMovimientos.clear().draw();
    }
}

// ========== CARGAR MOVIMIENTOS DE CAJA ==========
function cargarMovimientos(idcaja) {
    $.ajax({
        url: "../ajax/caja.php?action=listarMovimientos&idcaja=" + idcaja,
        type: "GET",
        dataType: "json",
        success: function(response) {
            if (tablaMovimientos) {
                tablaMovimientos.clear();

                if (response.aaData && response.aaData.length > 0) {
                    response.aaData.forEach(function(movimiento) {
                        let tipoBadge = obtenerBadgeTipoMovimiento(movimiento.tipo_movimiento);
                        let tipoPagoBadge = obtenerBadgeTipoPago(movimiento.tipo_pago);

                        tablaMovimientos.row.add([
                            formatearFechaHora(movimiento.fecha_hora),
                            tipoBadge,
                            movimiento.concepto,
                            'S/ ' + movimiento.monto,
                            tipoPagoBadge,
                            movimiento.referencia || '-',
                            movimiento.usuario
                        ]);
                    });
                }

                tablaMovimientos.draw();
            }
        },
        error: function(xhr, status, error) {
            console.error("Error cargando movimientos:", error);
        }
    });
}

// ========== LISTAR HISTORIAL DE CAJAS ==========
function listarHistorialCajas() {
    $.ajax({
        url: "../ajax/caja.php?action=listarCajas",
        type: "GET",
        dataType: "json",
        success: function(response) {
            if (tablaCajas) {
                tablaCajas.clear();

                if (response.aaData && response.aaData.length > 0) {
                    response.aaData.forEach(function(caja) {
                        let estadoBadge = caja.estado === 'ABIERTA'
                            ? '<span class="badge bg-success">ABIERTA</span>'
                            : '<span class="badge bg-secondary">CERRADA</span>';

                        let diferencia = parseFloat(caja.diferencia) || 0;
                        let diferenciaHtml = diferencia > 0
                            ? '<span class="text-success">+S/ ' + diferencia.toFixed(2) + '</span>'
                            : diferencia < 0
                            ? '<span class="text-danger">S/ ' + diferencia.toFixed(2) + '</span>'
                            : '<span class="text-muted">S/ 0.00</span>';

                        let opciones = '<button class="btn btn-sm btn-info" onclick="verDetalleCaja(' + caja.idcaja + ')">' +
                                     '<i class="fa fa-eye"></i></button>';

                        tablaCajas.row.add([
                            caja.idcaja,
                            formatearFecha(caja.fecha),
                            caja.turno,
                            caja.usuario,
                            'S/ ' + caja.monto_inicial,
                            'S/ ' + caja.monto_sistema,
                            'S/ ' + caja.monto_final,
                            diferenciaHtml,
                            estadoBadge,
                            opciones
                        ]);
                    });
                }

                tablaCajas.draw();
            }
        },
        error: function(xhr, status, error) {
            console.error("Error listando historial de cajas:", error);
        }
    });
}

// ========== APERTURAR CAJA ==========
function aperturarCaja() {
    let formData = $('#formAperturarCaja').serialize();

    // Agregar idusuario desde PHP session
    formData += '&idusuario=' + <?php echo isset($_SESSION["idusuario"]) ? $_SESSION["idusuario"] : "0"; ?>;
    formData += '&idempresa=1';

    Swal.fire({
        title: 'Aperturando caja...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "../ajax/caja.php?op=aperturarCaja",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                $('#modalAperturarCaja').modal('hide');

                // Recargar estado de caja y historial
                setTimeout(function() {
                    verificarCajaAbierta();
                    listarHistorialCajas();
                }, 500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error || 'No se pudo aperturar la caja'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor'
            });
            console.error("Error aperturando caja:", error);
        }
    });
}

// ========== REGISTRAR MOVIMIENTO ==========
function registrarMovimiento() {
    let formData = $('#formRegistrarMovimiento').serialize();

    // Agregar idusuario desde PHP session
    formData += '&idusuario=' + <?php echo isset($_SESSION["idusuario"]) ? $_SESSION["idusuario"] : "0"; ?>;

    Swal.fire({
        title: 'Registrando movimiento...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "../ajax/caja.php?op=registrarMovimiento",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Movimiento Registrado!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                $('#modalRegistrarMovimiento').modal('hide');

                // Recargar movimientos y resumen
                setTimeout(function() {
                    verificarCajaAbierta();
                }, 500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error || 'No se pudo registrar el movimiento'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor'
            });
            console.error("Error registrando movimiento:", error);
        }
    });
}

// ========== PREPARAR CIERRE DE CAJA ==========
function prepararCierreCaja() {
    if (!dataCajaActual) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No hay datos de caja disponibles'
        });
        return;
    }

    // Obtener resumen actualizado
    $.ajax({
        url: "../ajax/caja.php?action=resumenDiario&idcaja=" + idcajaActual,
        type: "GET",
        dataType: "json",
        success: function(response) {
            if (response && response.num_rows && response.num_rows > 0) {
                let montoInicial = parseFloat(response.monto_inicial) || 0;
                let totalIngresos = parseFloat(response.total_ingresos) || 0;
                let totalEgresos = parseFloat(response.total_egresos) || 0;
                let saldoSistema = parseFloat(response.saldo_sistema) || 0;

                // Actualizar modal con datos del resumen
                $('#idcajaCierre').val(response.idcaja);
                $('#cierreMontoInicial').text('S/ ' + montoInicial.toFixed(2));
                $('#cierreTotalIngresos').text('S/ ' + totalIngresos.toFixed(2));
                $('#cierreTotalEgresos').text('S/ ' + totalEgresos.toFixed(2));
                $('#cierreSaldoSistema').text('S/ ' + saldoSistema.toFixed(2));

                // Guardar saldo del sistema para cálculo de diferencia
                $('#formCerrarCaja').data('saldoSistema', saldoSistema);

                // Resetear formulario de arqueo
                resetearArqueo();
            }
        },
        error: function(xhr, status, error) {
            console.error("Error obteniendo resumen:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo obtener el resumen de la caja'
            });
        }
    });
}

// ========== RESETEAR FORMULARIO DE ARQUEO ==========
function resetearArqueo() {
    // Resetear todas las denominaciones a 0
    $('.denominacion').val(0);
    $('.otros-pagos').val(0);
    $('#observacionesCierre').val('');

    // Recalcular totales
    calcularArqueoCaja();
}

// ========== CALCULAR ARQUEO DE CAJA (AUTO-CÁLCULO) ==========
function calcularArqueoCaja() {
    let totalEfectivo = 0;

    // Calcular subtotales de cada denominación
    $('.denominacion').each(function() {
        let cantidad = parseInt($(this).val()) || 0;
        let valor = parseFloat($(this).data('valor')) || 0;
        let subtotal = cantidad * valor;

        // Actualizar subtotal en la tabla
        $(this).closest('tr').find('.subtotal').text('S/ ' + subtotal.toFixed(2));

        totalEfectivo += subtotal;
    });

    // Actualizar total efectivo
    $('#totalEfectivo').text('S/ ' + totalEfectivo.toFixed(2));

    // Sumar otros medios de pago
    let totalOtros = 0;
    $('.otros-pagos').each(function() {
        totalOtros += parseFloat($(this).val()) || 0;
    });

    // Calcular total general
    let totalGeneral = totalEfectivo + totalOtros;
    $('#totalGeneral').text('S/ ' + totalGeneral.toFixed(2));

    // Calcular diferencia con el saldo del sistema
    let saldoSistema = parseFloat($('#formCerrarCaja').data('saldoSistema')) || 0;
    let diferencia = totalGeneral - saldoSistema;

    // Actualizar card de diferencia con color
    let $cardDiferencia = $('#cardDiferencia');
    $cardDiferencia.removeClass('border-success border-danger border-warning');

    if (diferencia > 0) {
        // Sobrante
        $cardDiferencia.addClass('border-success border-4');
        $('#diferenciaCierre').html('<i class="fa fa-arrow-up"></i> S/ ' + diferencia.toFixed(2));
        $('#textoDiferencia').text('Sobrante en caja');
        $('#diferenciaCierre').removeClass('text-danger text-muted').addClass('text-success');
    } else if (diferencia < 0) {
        // Faltante
        $cardDiferencia.addClass('border-danger border-4');
        $('#diferenciaCierre').html('<i class="fa fa-arrow-down"></i> S/ ' + Math.abs(diferencia).toFixed(2));
        $('#textoDiferencia').text('Faltante en caja');
        $('#diferenciaCierre').removeClass('text-success text-muted').addClass('text-danger');
    } else {
        // Cuadrado
        $cardDiferencia.addClass('border-warning border-4');
        $('#diferenciaCierre').html('<i class="fa fa-check"></i> S/ 0.00');
        $('#textoDiferencia').text('Cuadrado exacto');
        $('#diferenciaCierre').removeClass('text-success text-danger').addClass('text-muted');
    }
}

// ========== CERRAR CAJA ==========
function cerrarCaja() {
    Swal.fire({
        title: '¿Cerrar Caja?',
        text: "Esta acción registrará el cierre con los montos declarados. ¿Continuar?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, Cerrar Caja',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarCierreCaja();
        }
    });
}

// ========== EJECUTAR CIERRE DE CAJA ==========
function ejecutarCierreCaja() {
    // Calcular total efectivo
    let totalEfectivo = 0;
    $('.denominacion').each(function() {
        let cantidad = parseInt($(this).val()) || 0;
        let valor = parseFloat($(this).data('valor')) || 0;
        totalEfectivo += cantidad * valor;
    });

    // Sumar otros medios de pago
    let totalOtros = 0;
    $('.otros-pagos').each(function() {
        totalOtros += parseFloat($(this).val()) || 0;
    });

    // Total declarado
    let totalDeclarado = totalEfectivo + totalOtros;

    // Preparar datos del formulario
    let formData = $('#formCerrarCaja').serialize();
    formData += '&monto_declarado=' + totalDeclarado.toFixed(2);

    Swal.fire({
        title: 'Cerrando caja...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Primero guardar el arqueo
    $.ajax({
        url: "../ajax/caja.php?op=guardarArqueo",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(responseArqueo) {
            if (responseArqueo.success) {
                // Arqueo guardado, ahora cerrar la caja
                $.ajax({
                    url: "../ajax/caja.php?op=cerrarCaja",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(responseCierre) {
                        if (responseCierre.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Caja Cerrada Exitosamente!',
                                text: 'El cierre se registró correctamente',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            $('#modalCerrarCaja').modal('hide');

                            // Recargar estado y historial
                            setTimeout(function() {
                                verificarCajaAbierta();
                                listarHistorialCajas();
                            }, 500);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al cerrar',
                                text: responseCierre.error || 'No se pudo cerrar la caja'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor para cerrar la caja'
                        });
                        console.error("Error cerrando caja:", error);
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al guardar arqueo',
                    text: responseArqueo.error || 'No se pudo guardar el arqueo'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor para guardar el arqueo'
            });
            console.error("Error guardando arqueo:", error);
        }
    });
}

// ========== VER DETALLE DE CAJA CERRADA ==========
function verDetalleCaja(idcaja) {
    $.ajax({
        url: "../ajax/caja.php?op=mostrar",
        type: "POST",
        data: { idcaja: idcaja },
        dataType: "json",
        success: function(response) {
            if (response && response.success) {
                let data = response.data;

                let html = '<div class="row">';
                html += '<div class="col-md-6"><p><strong>Usuario:</strong> ' + data.usuario + '</p></div>';
                html += '<div class="col-md-6"><p><strong>Turno:</strong> ' + data.turno + '</p></div>';
                html += '<div class="col-md-6"><p><strong>Fecha Apertura:</strong> ' + formatearFechaHora(data.fecha_apertura) + '</p></div>';
                html += '<div class="col-md-6"><p><strong>Fecha Cierre:</strong> ' + formatearFechaHora(data.fecha_cierre) + '</p></div>';
                html += '<div class="col-md-4"><p><strong>Monto Inicial:</strong> S/ ' + parseFloat(data.monto_inicial).toFixed(2) + '</p></div>';
                html += '<div class="col-md-4"><p><strong>Saldo Sistema:</strong> S/ ' + parseFloat(data.monto_sistema).toFixed(2) + '</p></div>';
                html += '<div class="col-md-4"><p><strong>Monto Final:</strong> S/ ' + parseFloat(data.monto_final).toFixed(2) + '</p></div>';
                html += '<div class="col-md-12"><p><strong>Diferencia:</strong> S/ ' + parseFloat(data.diferencia).toFixed(2) + '</p></div>';

                if (data.observaciones) {
                    html += '<div class="col-md-12"><p><strong>Observaciones:</strong> ' + data.observaciones + '</p></div>';
                }

                html += '</div>';

                Swal.fire({
                    title: 'Detalle de Caja #' + idcaja,
                    html: html,
                    width: '600px',
                    confirmButtonText: 'Cerrar'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo obtener el detalle de la caja'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error obteniendo detalle:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor'
            });
        }
    });
}

// ========== FUNCIONES HELPER ==========

function formatearFechaHora(fechaHora) {
    if (!fechaHora) return '-';
    let fecha = new Date(fechaHora);
    return fecha.toLocaleString('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatearFecha(fecha) {
    if (!fecha) return '-';
    let f = new Date(fecha);
    return f.toLocaleDateString('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function formatearMonto(monto) {
    return 'S/ ' + parseFloat(monto).toFixed(2);
}

function obtenerBadgeTipoMovimiento(tipo) {
    let badges = {
        'INGRESO': '<span class="badge bg-success">INGRESO</span>',
        'EGRESO': '<span class="badge bg-danger">EGRESO</span>',
        'VENTA': '<span class="badge bg-primary">VENTA</span>',
        'COMPRA': '<span class="badge bg-warning">COMPRA</span>',
        'AJUSTE': '<span class="badge bg-secondary">AJUSTE</span>'
    };
    return badges[tipo] || '<span class="badge bg-secondary">' + tipo + '</span>';
}

function obtenerBadgeTipoPago(tipo) {
    let badges = {
        'EFECTIVO': '<span class="badge bg-success">EFECTIVO</span>',
        'TARJETA': '<span class="badge bg-info">TARJETA</span>',
        'TRANSFERENCIA': '<span class="badge bg-primary">TRANSFERENCIA</span>',
        'YAPE': '<span class="badge bg-purple">YAPE</span>',
        'PLIN': '<span class="badge bg-warning">PLIN</span>',
        'OTRO': '<span class="badge bg-secondary">OTRO</span>'
    };
    return badges[tipo] || '<span class="badge bg-secondary">' + tipo + '</span>';
}
