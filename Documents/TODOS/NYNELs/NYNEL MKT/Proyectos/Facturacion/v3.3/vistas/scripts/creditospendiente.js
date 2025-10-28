var tablacredito = $('#tbllistado');

var tablaCreditoCargada = 0;

/* ---------------------------------------------------- */
//                 FUNCION LISTAR TABLA

var selectTipoComp;

function listarTablaCreditos() {

    selectTipoComp = parseInt($('#tipo_comprobante').val());
    

    if ( tablaCreditoCargada == 0 ) {

        tablacredito.DataTable({
            "Processing": true,//Activamos el procesamiento del datatables
            "ServerSide": true,
            "ajax": {
                "url": "../ajax/creditospendiente.php?op=detallecomprobante",
                "data": function (d) {
                    d.tipodocumento = selectTipoComp;
                },
                "dataSrc": function (data) {
                    console.log(data);
                    return data.aaData;
                },
            },
            "columns": [
                { "data": "tipo",
                  "render": function (data, row, cell) {
                    return (data == 0) ? 'Factura' : 'Boleta';
                  }
                },
                { "data": "idcomprobante", "visible": false },
                { "data": "idcliente", "visible": false },
                { "data": "cliente" },
                { "data": "importe_total" },
                { "data": "fechavenc" },
                { "data": "ccuotas" },
                { "data": "montocuota" },
                { "data": "t_pagado" },
                { "data": "t_restante" },
                { "data": null,
                  "render": function (data, row, cell) {
                    return '<button type="button" id="btn_cuotas" class="btn btn-info m-0" title="mostrar cuotas">' + 
                                '<i class="fa-solid fa-sack-dollar"></i></button>';
                  }
                }
            ]
        })

        tablaCreditoCargada = 1;
    } else {

        tablacredito.DataTable().ajax.reload();
    }
}

listarTablaCreditos();

/* ---------------------------------------------------- */
//                 EVENTO CHANGE FILTRO SELECT

$("#tipo_comprobante").change(function() {
    var valorSeleccionado = $(this).val(); // Obtiene el valor de la opción seleccionada
    console.log("Valor seleccionado: " + valorSeleccionado);

    listarTablaCreditos();
});

/* ---------------------------------------------------- */
//                 EVENTO LISTAR CUOTAS

// Evento click del botón
$('#tbllistado tbody').on('click', '#btn_cuotas', function() {

    limpiarModal();

    var data = tablacredito.DataTable().row($(this).closest('tr')).data();

    console.log("ID de la fila:", data);

    var idcomprobante = data.idcomprobante;

    $.ajax({
        "url": "../ajax/creditospendiente.php?op=listarcuotas",
        "type": "GET",
        "data": {
            "idcomprobante": idcomprobante,
        },
        dataType: "json", 
        success: function (data) {
            console.log(data); 

            data.forEach(function(cuota) {

                if (cuota.estadocuota == 0) {

                    $('#divmontocuotas').append(
                        '<div class="col-12 text-center mb-2" style="color: red;">' +
                        cuota.montocuota +
                        '</div>'
                    );
    
                    $('#divfechaspago').append(
                        '<div class="col-12 text-center mb-2" style="color: red;">' +
                        cuota.fechacuota +
                        '</div>'
                    );
                } else {

                    $('#divmontocuotas').append(
                        '<div class="col-12 text-center mb-2" style="color: green;">' +
                        cuota.montocuota +
                        '</div>'
                    );
    
                    $('#divfechaspago').append(
                        '<div class="col-12 text-center mb-2" style="color: green;">' +
                        cuota.fechacuota +
                        '</div>'
                    );
                }

            });

            
        },
        error: function (error) {
            console.log(error);
        }
    })

    $('#modalcuotas').modal('show');
});

/* ---------------------------------------------------- */
//                 FUNCION LIMPIAR MODAL

function limpiarModal() {
    $('#divmontocuotas').html('');
    $('#divfechaspago').html('');
}
