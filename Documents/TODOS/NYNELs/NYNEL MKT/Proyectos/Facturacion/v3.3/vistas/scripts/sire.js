/**
 * sire.js - Script para módulo SIRE (Sistema Integrado de Registros Electrónicos)
 * Maneja la interfaz de generación de archivos RVIE y RCE para SUNAT
 *
 * @package    Facturacion
 * @subpackage JavaScript
 * @author     Sistema de Facturación
 * @version    1.0.0
 */

// =============================================================================
// VARIABLES GLOBALES
// =============================================================================
var ultimoArchivoGenerado = null;
var tblHistorial; // DataTable del historial

// =============================================================================
// INICIALIZACIÓN AL CARGAR LA PÁGINA
// =============================================================================
$(document).ready(function() {
  // Cargar configuración existente
  cargarConfiguracion();

  // Inicializar selectores de año
  inicializarSelectorAnios();

  // Inicializar DataTable del historial
  inicializarTablaHistorial();

  // Cargar historial al cambiar al tab correspondiente
  $('#tab-historial').on('shown.bs.tab', function() {
    listarExportaciones();
  });

  // Event listeners
  inicializarEventos();

  // Actualizar vista previa de indicadores en modal de configuración
  actualizarVistaIndicadores();
});

// =============================================================================
// INICIALIZACIÓN DE EVENTOS
// =============================================================================
function inicializarEventos() {
  // Botón abrir modal de configuración
  $('#btnConfiguracionSIRE').click(function() {
    cargarConfiguracion();
    $('#modalConfiguracionSIRE').modal('show');
  });

  // Botón guardar configuración
  $('#btnGuardarConfiguracion').click(function() {
    guardarConfiguracion();
  });

  // Formulario generar RVIE
  $('#formGenerarRVIE').submit(function(e) {
    e.preventDefault();
    generarRVIE();
  });

  // Formulario generar RCE
  $('#formGenerarRCE').submit(function(e) {
    e.preventDefault();
    generarRCE();
  });

  // Formulario de filtros de historial
  $('#formFiltrosHistorial').submit(function(e) {
    e.preventDefault();
    listarExportaciones();
  });

  // Limpiar filtros
  $('#btnLimpiarFiltros').click(function() {
    $('#filtroTipo').val('');
    $('#filtroAnio').val('');
    $('#filtroMes').val('');
    listarExportaciones();
  });

  // Botón descargar último archivo
  $('#btnDescargarUltimo').click(function() {
    if (ultimoArchivoGenerado) {
      descargarArchivo(ultimoArchivoGenerado);
    }
  });

  // Actualizar vista previa de indicadores al cambiar cualquier selector
  $('#config_ind_reemplaza, #config_ind_estado, #config_ind_moneda, #config_ind_libro, #config_ind_entidad, #config_ind_sin_mov')
    .change(function() {
      actualizarVistaIndicadores();
    });
}

// =============================================================================
// INICIALIZAR SELECTOR DE AÑOS (últimos 5 años)
// =============================================================================
function inicializarSelectorAnios() {
  var anioActual = new Date().getFullYear();
  var selectoresAnio = ['#rvie_anio', '#rce_anio', '#filtroAnio'];

  selectoresAnio.forEach(function(selector) {
    var $select = $(selector);
    $select.empty();

    if (selector === '#filtroAnio') {
      $select.append('<option value="">Todos</option>');
    }

    for (var i = 0; i < 5; i++) {
      var anio = anioActual - i;
      $select.append('<option value="' + anio + '">' + anio + '</option>');
    }

    if (selector !== '#filtroAnio') {
      $select.val(anioActual);
    }
  });

  // Establecer mes actual
  var mesActual = ('0' + (new Date().getMonth() + 1)).slice(-2);
  $('#rvie_mes').val(mesActual);
  $('#rce_mes').val(mesActual);
}

// =============================================================================
// INICIALIZAR TABLA DE HISTORIAL CON DATATABLES
// =============================================================================
function inicializarTablaHistorial() {
  tblHistorial = $('#tblHistorialExportaciones').DataTable({
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
    },
    "order": [[0, "desc"]], // Ordenar por ID descendente (más recientes primero)
    "pageLength": 25,
    "responsive": true,
    "columnDefs": [
      {
        "targets": -1, // Última columna (Acciones)
        "orderable": false,
        "searchable": false
      }
    ]
  });
}

// =============================================================================
// CARGAR CONFIGURACIÓN EXISTENTE
// =============================================================================
function cargarConfiguracion() {
  $.ajax({
    url: urlconsumo + 'sire.php?op=obtenerConfiguracion',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success && response.data) {
        var config = response.data;

        // Llenar formulario de configuración
        $('#config_ruc').val(config.ruc);
        $('#config_periodo_desde').val(config.periodo_obligado_desde);
        $('#config_propuesta').val(config.generar_propuesta_aceptar);
        $('#config_moneda').val(config.moneda_principal);
        $('#config_anulados').val(config.incluir_anulados);
        $('#config_ind_reemplaza').val(config.indicador_reemplaza);
        $('#config_ind_estado').val(config.indicador_estado);
        $('#config_ind_moneda').val(config.indicador_moneda);
        $('#config_ind_libro').val(config.indicador_libro_simplif);
        $('#config_ind_entidad').val(config.indicador_entidad);
        $('#config_ind_sin_mov').val(config.indicador_genera_sin_mov);

        actualizarVistaIndicadores();

      } else if (response.requiere_config) {
        // Primera vez - abrir modal automáticamente
        console.log('No hay configuración SIRE. Requiere configuración inicial.');
      }
    },
    error: function(xhr, status, error) {
      console.error('Error al cargar configuración:', error);
    }
  });
}

// =============================================================================
// GUARDAR CONFIGURACIÓN
// =============================================================================
function guardarConfiguracion() {
  var formData = $('#formConfiguracionSIRE').serialize();
  formData += '&op=guardarConfiguracion';

  // Validar RUC
  var ruc = $('#config_ruc').val();
  if (!/^[0-9]{11}$/.test(ruc)) {
    Swal.fire({
      icon: 'error',
      title: 'RUC inválido',
      text: 'El RUC debe contener exactamente 11 dígitos numéricos',
      confirmButtonText: 'Entendido'
    });
    return;
  }

  // Mostrar loading
  Swal.fire({
    title: 'Guardando configuración...',
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  $.ajax({
    url: urlconsumo + 'sire.php',
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function(response) {
      Swal.close();

      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: '¡Configuración guardada!',
          text: response.mensaje,
          confirmButtonText: 'Aceptar'
        }).then(() => {
          $('#modalConfiguracionSIRE').modal('hide');
          cargarConfiguracion();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          text: response.error || 'No se pudo guardar la configuración',
          confirmButtonText: 'Aceptar'
        });
      }
    },
    error: function(xhr, status, error) {
      Swal.close();
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo guardar la configuración. Intente nuevamente.',
        confirmButtonText: 'Aceptar'
      });
      console.error('Error:', error);
    }
  });
}

// =============================================================================
// GENERAR ARCHIVO RVIE (REGISTRO DE VENTAS)
// =============================================================================
function generarRVIE() {
  var formData = $('#formGenerarRVIE').serialize();
  formData += '&op=generarRVIE';

  // Confirmar generación
  Swal.fire({
    title: '¿Generar archivo RVIE?',
    html: 'Se generará el archivo de Registro de Ventas e Ingresos Electrónico para el período seleccionado.<br><br>' +
          '<strong>Año:</strong> ' + $('#rvie_anio').val() + '<br>' +
          '<strong>Mes:</strong> ' + $('#rvie_mes option:selected').text(),
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, generar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#28a745'
  }).then((result) => {
    if (result.isConfirmed) {
      // Mostrar loading
      Swal.fire({
        title: 'Generando archivo RVIE...',
        html: 'Por favor espere mientras se procesa la información.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      $.ajax({
        url: urlconsumo + 'sire.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
          Swal.close();

          if (response.success) {
            // Guardar referencia del último archivo
            ultimoArchivoGenerado = response.archivo;

            // Mostrar resultado
            Swal.fire({
              icon: 'success',
              title: '¡Archivo RVIE generado!',
              html: '<strong>Archivo:</strong> ' + response.archivo + '<br>' +
                    '<strong>Total de registros:</strong> ' + response.total_registros + '<br><br>' +
                    'El archivo se ha generado correctamente.',
              confirmButtonText: 'Aceptar'
            });

            // Mostrar card de vista previa
            mostrarVistaPrevia('RVIE', response.archivo, response.total_registros);

            // Actualizar historial si está visible
            if ($('#tab-historial').hasClass('active')) {
              listarExportaciones();
            }

          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error al generar archivo',
              text: response.error || 'No se pudo generar el archivo RVIE',
              confirmButtonText: 'Aceptar'
            });
          }
        },
        error: function(xhr, status, error) {
          Swal.close();
          Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo generar el archivo. Intente nuevamente.',
            confirmButtonText: 'Aceptar'
          });
          console.error('Error:', error);
        }
      });
    }
  });
}

// =============================================================================
// GENERAR ARCHIVO RCE (REGISTRO DE COMPRAS)
// =============================================================================
function generarRCE() {
  var formData = $('#formGenerarRCE').serialize();
  formData += '&op=generarRCE';

  // Confirmar generación
  Swal.fire({
    title: '¿Generar archivo RCE?',
    html: 'Se generará el archivo de Registro de Compras Electrónico para el período seleccionado.<br><br>' +
          '<strong>Año:</strong> ' + $('#rce_anio').val() + '<br>' +
          '<strong>Mes:</strong> ' + $('#rce_mes option:selected').text(),
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, generar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#dc3545'
  }).then((result) => {
    if (result.isConfirmed) {
      // Mostrar loading
      Swal.fire({
        title: 'Generando archivo RCE...',
        html: 'Por favor espere mientras se procesa la información.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      $.ajax({
        url: urlconsumo + 'sire.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
          Swal.close();

          if (response.success) {
            // Guardar referencia del último archivo
            ultimoArchivoGenerado = response.archivo;

            // Mostrar resultado
            Swal.fire({
              icon: 'success',
              title: '¡Archivo RCE generado!',
              html: '<strong>Archivo:</strong> ' + response.archivo + '<br>' +
                    '<strong>Total de registros:</strong> ' + response.total_registros + '<br><br>' +
                    'El archivo se ha generado correctamente.',
              confirmButtonText: 'Aceptar'
            });

            // Mostrar card de vista previa
            mostrarVistaPrevia('RCE', response.archivo, response.total_registros);

            // Actualizar historial si está visible
            if ($('#tab-historial').hasClass('active')) {
              listarExportaciones();
            }

          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error al generar archivo',
              text: response.error || 'No se pudo generar el archivo RCE',
              confirmButtonText: 'Aceptar'
            });
          }
        },
        error: function(xhr, status, error) {
          Swal.close();
          Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo generar el archivo. Intente nuevamente.',
            confirmButtonText: 'Aceptar'
          });
          console.error('Error:', error);
        }
      });
    }
  });
}

// =============================================================================
// MOSTRAR VISTA PREVIA DEL ÚLTIMO ARCHIVO GENERADO
// =============================================================================
function mostrarVistaPrevia(tipo, nombreArchivo, totalRegistros) {
  $('#previewNombreArchivo').text(nombreArchivo);
  $('#previewTotalRegistros').text(totalRegistros);
  $('#previewEstado').text('GENERADO').removeClass().addClass('badge bg-success fs-6');
  $('#cardVistaPrevia').slideDown();

  // Scroll suave a la card de vista previa
  $('html, body').animate({
    scrollTop: $('#cardVistaPrevia').offset().top - 100
  }, 500);
}

// =============================================================================
// LISTAR EXPORTACIONES (HISTORIAL)
// =============================================================================
function listarExportaciones() {
  var filtroTipo = $('#filtroTipo').val();
  var filtroAnio = $('#filtroAnio').val();
  var filtroMes = $('#filtroMes').val();

  var url = urlconsumo + 'sire.php?op=listarExportaciones';
  if (filtroTipo) url += '&tipo_registro=' + filtroTipo;
  if (filtroAnio) url += '&periodo_anio=' + filtroAnio;
  if (filtroMes) url += '&periodo_mes=' + filtroMes;

  $.ajax({
    url: url,
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success && response.data) {
        // Limpiar tabla
        tblHistorial.clear();

        // Agregar filas
        response.data.forEach(function(exp) {
          var badgeEstado = '';
          switch(exp.estado_exportacion) {
            case 'GENERADO':
              badgeEstado = '<span class="badge bg-success">GENERADO</span>';
              break;
            case 'ENVIADO':
              badgeEstado = '<span class="badge bg-info">ENVIADO</span>';
              break;
            case 'ACEPTADO':
              badgeEstado = '<span class="badge bg-primary">ACEPTADO</span>';
              break;
            case 'RECHAZADO':
              badgeEstado = '<span class="badge bg-danger">RECHAZADO</span>';
              break;
            case 'ERROR':
              badgeEstado = '<span class="badge bg-warning text-dark">ERROR</span>';
              break;
            default:
              badgeEstado = '<span class="badge bg-secondary">' + exp.estado_exportacion + '</span>';
          }

          var badgeTipo = exp.tipo_registro === 'RVIE'
            ? '<span class="badge bg-success">RVIE</span>'
            : '<span class="badge bg-danger">RCE</span>';

          var periodo = exp.periodo_anio + '-' + exp.periodo_mes;

          var acciones = '<div class="btn-group btn-group-sm" role="group">' +
            '<button class="btn btn-primary" onclick="descargarArchivo(\'' + exp.nombre_archivo + '\')" title="Descargar">' +
            '<i class="fa fa-download"></i>' +
            '</button>';

          // Solo permitir eliminar si no ha sido enviado
          if (exp.estado_exportacion === 'GENERADO') {
            acciones += '<button class="btn btn-danger" onclick="eliminarExportacion(' + exp.idsire_exportacion + ')" title="Eliminar">' +
              '<i class="fa fa-trash"></i>' +
              '</button>';
          }

          acciones += '</div>';

          tblHistorial.row.add([
            exp.idsire_exportacion,
            badgeTipo,
            periodo,
            exp.codigo_oportunidad,
            '<code>' + exp.nombre_archivo + '</code>',
            exp.total_registros,
            badgeEstado,
            formatearFechaHora(exp.fecha_generacion),
            acciones
          ]);
        });

        // Redibujar tabla
        tblHistorial.draw();
      }
    },
    error: function(xhr, status, error) {
      console.error('Error al listar exportaciones:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo cargar el historial de exportaciones',
        confirmButtonText: 'Aceptar'
      });
    }
  });
}

// =============================================================================
// DESCARGAR ARCHIVO TXT
// =============================================================================
function descargarArchivo(nombreArchivo) {
  // Abrir en nueva ventana para forzar descarga
  var urlDescarga = urlconsumo + 'sire.php?op=descargarArchivo&archivo=' + encodeURIComponent(nombreArchivo);
  window.open(urlDescarga, '_blank');

  // Feedback al usuario
  Swal.fire({
    icon: 'info',
    title: 'Descargando archivo...',
    text: 'El archivo se descargará en breve',
    timer: 2000,
    showConfirmButton: false
  });
}

// =============================================================================
// ELIMINAR EXPORTACIÓN
// =============================================================================
function eliminarExportacion(idsire_exportacion) {
  Swal.fire({
    title: '¿Eliminar exportación?',
    text: 'Esta acción eliminará el archivo generado. Solo puede eliminar archivos que aún no han sido enviados a SUNAT.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#dc3545'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: urlconsumo + 'sire.php',
        type: 'POST',
        data: {
          op: 'eliminarExportacion',
          idsire_exportacion: idsire_exportacion
        },
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            Swal.fire({
              icon: 'success',
              title: '¡Eliminado!',
              text: response.mensaje,
              confirmButtonText: 'Aceptar'
            });

            // Recargar historial
            listarExportaciones();

          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: response.error || 'No se pudo eliminar la exportación',
              confirmButtonText: 'Aceptar'
            });
          }
        },
        error: function(xhr, status, error) {
          Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo eliminar la exportación',
            confirmButtonText: 'Aceptar'
          });
          console.error('Error:', error);
        }
      });
    }
  });
}

// =============================================================================
// ACTUALIZAR VISTA PREVIA DE INDICADORES
// =============================================================================
function actualizarVistaIndicadores() {
  var ind1 = $('#config_ind_reemplaza').val() || '1';
  var ind2 = $('#config_ind_estado').val() || '1';
  var ind3 = $('#config_ind_moneda').val() || '1';
  var ind4 = $('#config_ind_libro').val() || '2';
  var ind5 = $('#config_ind_entidad').val() || '1';
  var ind6 = $('#config_ind_sin_mov').val() || '0';

  var indicadores = ind1 + ind2 + ind3 + ind4 + ind5 + ind6;
  $('#vistaIndicadores').text(indicadores);
}

// =============================================================================
// FUNCIONES AUXILIARES
// =============================================================================

/**
 * Formatear fecha y hora desde formato SQL a legible
 */
function formatearFechaHora(fechaSQL) {
  if (!fechaSQL) return '-';

  var fecha = new Date(fechaSQL);
  var dia = ('0' + fecha.getDate()).slice(-2);
  var mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
  var anio = fecha.getFullYear();
  var hora = ('0' + fecha.getHours()).slice(-2);
  var minuto = ('0' + fecha.getMinutes()).slice(-2);

  return dia + '/' + mes + '/' + anio + ' ' + hora + ':' + minuto;
}

/**
 * Formatear número con separadores de miles
 */
function formatearNumero(numero) {
  return numero.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
