var tabla;

// Función que se ejecuta al inicio
function init() {
    // Establecer la fecha actual en los campos de fecha
    document.getElementById('FechaDesdeIni').value = obtenerFechaActual();
    document.getElementById('FechaHastaFin').value = obtenerFechaActual();

    // Iniciar la tabla
    listar();
}

// Obtener la fecha actual en el formato YYYY-MM-DD
function obtenerFechaActual() {
    var fecha = new Date();
    var year = fecha.getFullYear();
    var month = (fecha.getMonth() + 1).toString().padStart(2, '0');
    var day = fecha.getDate().toString().padStart(2, '0');
    return year + '-' + month + '-' + day;
}

// Función para listar
function listar() {
    // Obtener las fechas actuales
    var fechaDesde = $('#FechaDesdeIni').val();
    var fechaHasta = $('#FechaHastaFin').val();
    var idempresa = $('#idempresa').val(); // Usar jQuery para obtener el valor
    var tmonedaa = $('#tmonedaa').val();

    // Configuración de DataTable
    tabla = $('#tbllistadoVentas').dataTable({
        "aProcessing": true,
        "aServerSide": true,
        "scrollX": true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                title: 'Reporte Excel',
                filename: 'reporte_tributario_' + fechaDesde + '_' + fechaHasta,
                text: 'Exportar a Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdfHtml5',
                title: 'Reporte PDF',
                filename: 'reporte_tributario_' + fechaDesde + '_' + fechaHasta,
                text: 'Exportar a PDF',
                className: 'btn btn-danger btn-sm',
                orientation: 'landscape',
                customize: function (doc) {
                    // Ajustar los márgenes del documento
                    doc.pageMargins = [10, 10, 10, 10];

                    // Ajustar el tamaño de las letras y establecer un factor de zoom al 80%
                    doc.defaultStyle.fontSize = 8;
                    doc.zoom = 0.7;

                    // Omitir la columna "Observación" (cambiar el índice según la posición de la columna)
                    doc.content[1].table.body.forEach(function(row) {
                        row.splice(15, 1); // Eliminar el elemento en la posición 15 (columna "Observación")
                    });
                }
            }
        ],
        "ajax": {
            url: '../ajax/ajaxReporteVenta.php?op=ListarReporteTributario',
            data: {
                FechaDesdeIni: fechaDesde,
                FechaHastaFin: fechaHasta,
                idempresa: idempresa,
                tmonedaa: tmonedaa
            },
            type: "get",
            dataType: "json",
            error: function (e) {
                console.log(e.responseText);
            }
        },
        "bDestroy": true,
        "iDisplayLength": 15,
        "order": [[0, ""]]
    }).DataTable();
}

// Añadir evento para recargar la tabla cuando cambien los campos de fecha y moneda
$('#FechaDesdeIni, #FechaHastaFin, #idempresa, #tmonedaa').change(function () {
    // Recargar la tabla con los nuevos valores
    listar();
});

init();
