function init(){
  
$("#formulario").on("submit",function(e)
    {
        regcompras(e);  
    });   

var fecha = new Date();
var ano = fecha.getFullYear();
var mes=fecha.getMonth();
var dia=fecha.getDate();

$("#ano").val(ano);
$("#mes").val(mes+1);
$("#dia").val(dia);
regbajas();
}


function regbajas()
{

	var $ano = $("#ano option:selected").text();
	var $mes = $("#mes option:selected").val();
    var $dia = $("#dia option:selected").val();

    tabla=$('#tbllistado').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip',//Definimos los elementos del control de tabla

        buttons: [                
                    // 'copyHtml5',
                    // 'excelHtml5',
                    // 'csvHtml5',
                    // 'pdf'
                ],
        "ajax":
                {
                    url: '../ajax/bajanc.php?op=regbajas&ano='+$ano+'&mes='+$mes+'&dia='+$dia,
                    type : "post",
                    dataType : "json",                      
                    error: function(e){
                    console.log(e.responseText);    
                    }
               },

        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // converting to interger to find total
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
 
            // Calculando subtotal
            var subtotal = api
                .column( 4 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                // Calculando igv
            var igv = api
                .column( 5 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                // Calculando total
            var total = api
                .column( 6 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var subtotal2=currencyFormat(subtotal);
                var igv2=currencyFormat(igv);
                var total2=currencyFormat(total);
                    
            // Update footer by showing the total with the reference of the column index
            //$( api.column( 0 ).footer() ).html('Total');

            // Try-catch para manejar errores cuando DataTables no encuentra elementos footer
            try {
                var footer4 = api.column(4).footer();
                if (footer4) {
                    $(footer4).html(subtotal2);
                }
            } catch (e) {
                console.warn('Footer columna 4 no disponible:', e.message);
            }

            try {
                var footer5 = api.column(5).footer();
                if (footer5) {
                    $(footer5).html(igv2);
                }
            } catch (e) {
                console.warn('Footer columna 5 no disponible:', e.message);
            }

            try {
                var footer6 = api.column(6).footer();
                if (footer6) {
                    $(footer6).html(total2);
                }
            } catch (e) {
                console.warn('Footer columna 6 no disponible:', e.message);
            }
            },

        "bDestroy": true,
        "iDisplayLength": 5,//Paginación
        "order": [[ 0, "desc" ]]//Ordenar (columna,orden)

    }).DataTable();
}

function currencyFormat (num) {
    return "S/ " + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
}




function generarbajaxml() {
    var $ano = $("#ano option:selected").text();
    var $mes = $("#mes option:selected").val();
    var $dia = $("#dia option:selected").val();

    swal.fire({
        title: "¿Está seguro de generar el archivo XML?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, generar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../ajax/bajanc.php?op=generarbajaxml&ano='+$ano+'&mes='+$mes+'&dia='+$dia, function(e) {
                data=JSON.parse(e);
                swal.fire({
                    title: "Se ha generado el archivo XML",
                    html: 'Se ha generado el archivo XML: <a href="'+data.cabextxml+'" download="'+data.cabxml+'">" ARCHIVO XML:   '+data.cabxml+'"</a></br> Si desea enviar el archivo a SUNAT, haga clic en enviar.',
                    icon: "success",
                    confirmButtonText: "Enviar",
                }).then((result) => {
                    if (result.isConfirmed) {
                        enviarxmlbajanotacredito(data.nombrea);
                    }
                });
                tabla.ajax.reload();
            }); 
            //refrescartabla();
        }
    });
}





function enviarxmlbajanotacredito(nroxml) {
    swal.fire({
        title: '¿Está seguro de enviar el archivo firmado a SUNAT?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/bajanc.php?op=enviarxmlbajanotacredito&nombrexml="+nroxml, function(e){
                data2=JSON.parse(e);
                swal.fire({
                    title: 'Sus comprobantes han sido anulados',
                    html: 'El número de ticket es: <h1>'+data2.nroticket+'</h1><br>Su comprobante se estará dando de baja. Guarde el número de ticket para cualquier consulta.',
                    icon: 'success'
                });
                tabla.ajax.reload();
            }); 
        }
    });

    tabla=$('#tbllistadoxml').dataTable({
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        "bFilter": false,
        dom: 'Bfrtip',//Definimos los elementos del control de tabla
        buttons: [],
        "ajax": {
            url: '../ajax/bajanc.php?op=ultimoarchivoxml&ultimoxml='+nroxml,
            type : "post",
            dataType : "json",                      
            error: function(e){
                console.log(e.responseText);    
            }
        }, 
        "bDestroy": true, 
        "iDisplayLength": 5
    }).DataTable();
}










function detalle(idxml)
{
tabla=$('#tbllistadocomprobante').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        "bFilter": false,
        dom: 'Bfrtip',//Definimos los elementos del control de tabla
        buttons: [],
        "ajax":
                {
                    url: '../ajax/bajanc.php?op=detallecomprobante&idxml='+idxml,
                    type : "post",
                    dataType : "json",                      
                    error: function(e){
                    console.log(e.responseText);    
                    }
               },

        "bDestroy": true,
        "iDisplayLength": 5,//Paginación
        "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
    }).DataTable();



}





 init();