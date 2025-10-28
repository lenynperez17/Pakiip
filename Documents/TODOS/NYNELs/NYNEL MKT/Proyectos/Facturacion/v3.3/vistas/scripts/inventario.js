var tabla;
$idempresa = $("#idempresa").val();
xocdTods = "";

function init() {

   $("#formulario").on("submit", function (e) {
      regcompras(e);
   });
   regvenagruxdia();

   var fecha = new Date();
   var ano = fecha.getFullYear();
   $("#ano").val(ano);

   var mes = fecha.getMonth() + 1;
   var dia = fecha.getDate();
   $("#ano").val(ano);
   $("#mes").val(String(mes).padStart(2, '0')); // Formato 01-12
   $("#dia").val(dia);



   var anoactual = ano + "-01-01";
   $("#fecha1").val(anoactual);

   //var today = fecha.getFullYear()+"-"+(mes)+"-"+(dia);
   //$("#fecha2").val(today);

   //Obtenemos la fecha actual
   $("#fecha2").prop("disabled", false);
   var now = new Date();
   var day = ("0" + now.getDate()).slice(-2);
   var month = ("0" + (now.getMonth() + 1)).slice(-2);
   var today = now.getFullYear() + "-" + (month) + "-" + (day);
   $('#fecha2').val(today);



   $.post("../ajax/inventarios.php?op=selectAlm", function (r) {
      $("#almacenlista").html(r);
      //$('#almacenlista').selectpicker('refresh');

   });

   // alma=$("#almacenlista").val();
   // $.post("../ajax/articulo.php?op=comboarticulo&anor="+ano+"&aml="+alma, function(r){
   // $("#codigoInterno").html(r);
   // $("#codigoInterno").selectpicker('refresh');
   // });
   // });


   $.post("../ajax/articulo.php?op=comboarticulokardex", function (r) {
      $("#codigoInterno").html(r);
      //$("#codigoInterno").selectpicker('refresh');
   });

   regventas();

}




// Obtener la fecha actual en formato ISO (YYYY-MM-DD)
// Obtener la fecha actual en formato ISO (YYYY-MM-DD) en la zona horaria de Lima, Perú (UTC-5)
var today = new Date();
today.setHours(today.getHours() - 5); // Restar 5 horas para ajustar a UTC-5
var yyyy = today.getFullYear();
var mm = String(today.getMonth() + 1).padStart(2, '0'); // Enero es 0, entonces sumamos 1
var dd = String(today.getDate()).padStart(2, '0');
var fechaActual = yyyy + '-' + mm + '-' + dd;

// Establecer la fecha actual en el campo de entrada FechaDesde
var fechaDesdeElement = document.getElementById('fechaDesde');
if (fechaDesdeElement) {
   fechaDesdeElement.value = fechaActual;
}

// Para FechaHasta, puedes ajustarla también si deseas un día más
var fechaManana = new Date(today.getTime() + 24 * 60 * 60 * 1000);
var yyyyManana = fechaManana.getFullYear();
var mmManana = String(fechaManana.getMonth() + 1).padStart(2, '0');
var ddManana = String(fechaManana.getDate()).padStart(2, '0');
var fechaHasta = yyyyManana + '-' + mmManana + '-' + ddManana;
var fechaHastaElement = document.getElementById('FechaHasta');
if (fechaHastaElement) {
   fechaHastaElement.value = fechaHasta;
}


// function cambioano() 
// {
// $.post("../ajax/articulo.php?op=comboarticulokardex", function(r){
// $("#codigoInterno").html(r);
// $("#codigoInterno").selectpicker('refresh');
// });

// }


function actualizarartialma() {
   alma = $("#almacenlista").val();
   $.post("../ajax/articulo.php?op=comboarticulo&anor=" + ano + "&aml=" + alma, function (r) {
      $("#codigoInterno").html(r);
      //$("#codigoInterno").selectpicker('refresh');
   });


}


var onOff = true;
function selectO() {
   if (!onOff) {

      onOff = true;
      xocdTods = "xcod";

      resetearvalores();

   } else {

      onOff = false;

      xocdTods = "xtod";
      resetearvalores();


   }
}





function mostrarumedidas() {

   var codigo = $("#codigoInterno").val();
   //var codigo = "RR50";

   $.post("../ajax/inventarios.php?op=selectUnidadum1&codaum1=" + codigo, function (data, status) {
      data = JSON.parse(data);
      $("#cbox1").val(data.abre);
      $("#umedida1").text(data.nombreum);
   });


   $.post("../ajax/inventarios.php?op=selectUnidadum2&codaum2=" + codigo, function (data, status) {
      data = JSON.parse(data);
      $("#cbox2").val(data.abre);
      $("#umedida2").text(data.nombreum);
   });

}


function checkcambioum() {
   var ch1 = document.getElementById("cbox1").checked;
   //var ch2 = document.getElementById("cbox2").checked; 
}


function checkcambioum2() {
   var ch2 = document.getElementById("cbox2").checked;

   if (ch2 == "true") {
      $("#cbox1").attr("checked", false);
   }
}






function enviar() {

   document.formEnviar.action = "../reportes/RegistroVentas";
   document.formEnviar.target = "_blank";
   document.formEnviar.submit();

   document.formEnviar.action = "../reportes/PDF_MC_Table";
   document.formEnviar.target = "_self";
   document.formEnviar.submit();

   return true;

}

function enviarKardex() {

   document.formEnviar.action = "../reportes/kardexArticulo";
   document.formEnviar.target = "_blank";
   document.formEnviar.submit();

   document.formEnviar.action = "../reportes/PDF_MC_Table5";
   document.formEnviar.target = "_self";
   document.formEnviar.submit();

   return true;

}



function regcompras() {

   var $ano = $("#ano option:selected").text();
   var $mes = $("#mes option:selected").val();
   var $moneda = $("#moneda option:selected").val();

   tabla = $('#tbllistado').dataTable(
      {
         "aProcessing": true,//Activamos el procesamiento del datatables
         "aServerSide": true,//Paginación y filtrado realizados por el servidor
         dom: 'Bfrtip',//Definimos los elementos del control de tabla

         "language": {
            'loadingRecords': '&nbsp;',
            'processing': '<i class="fa fa-spinner fa-spin"></i> Procesando datos'
         },

         buttons: [
            //          {
            //     extend:    'copyHtml5',
            //     text:      '<i class="fa fa-files-o"></i>',
            //     titleAttr: 'Copy'
            // },
            // {
            //     extend:    'excelHtml5',
            //     text:      '<i class="fa fa-file-excel-o"></i>',
            //     titleAttr: 'Excel'
            // },
            // {
            //     extend:    'csvHtml5',
            //     text:      '<i class="fa fa-file-text-o"></i>',
            //     titleAttr: 'CSV'
            // },
            // {
            //     extend:    'pdfHtml5',
            //     text:      '<i class="fa fa-file-pdf-o"></i>',
            //     titleAttr: 'PDF'
            // }
         ],
         "ajax":
         {
            url: '../ajax/inventarios.php?op=regcompras&ano=' + $ano + '&mes=' + $mes + '&moneda=' + $moneda + '&idempresa=' + $idempresa,
            type: "post",
            dataType: "json",
            error: function (e) {
               console.log(e.responseText);
            }
         },

         "footerCallback": function (row, data, start, end, display) {
            var api = this.api(), data;

            // converting to interger to find total
            var intVal = function (i) {
               return typeof i === 'string' ?
                  i.replace(/[\$,]/g, '') * 1 :
                  typeof i === 'number' ?
                     i : 0;
            };

            // Calculando subtotal
            var subtotal = api
               .column(6)
               .data()
               .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
               }, 0);

            // Calculando igv
            var igv = api
               .column(7)
               .data()
               .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
               }, 0);

            // Calculando total
            var total = api
               .column(8)
               .data()
               .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
               }, 0);


            var subtotal2 = subtotal;
            var igv2 = igv;
            var total2 = total;


            // Update footer by showing the total with the reference of the column index
            //$( api.column( 0 ).footer() ).html('Total');
            // Try-catch para manejar error cuando DataTables no encuentra elemento footer
            try {
               var footer6 = api.column(6).footer();
               if (footer6) {
                  $(footer6).html(subtotal2);
               }
            } catch (e) {
               console.warn('Footer columna 6 no disponible:', e.message);
            }

            try {
               var footer7 = api.column(7).footer();
               if (footer7) {
                  $(footer7).html(igv2);
               }
            } catch (e) {
               console.warn('Footer columna 7 no disponible:', e.message);
            }

            try {
               var footer8 = api.column(8).footer();
               if (footer8) {
                  $(footer8).html(total2);
               }
            } catch (e) {
               console.warn('Footer columna 8 no disponible:', e.message);
            }
         },

         "bDestroy": true,
         "iDisplayLength": 5,//Paginación
         "order": [[0, "asc"]]//Ordenar (columna,orden)

      }).DataTable();
}





//Funcioón para registro de ventas en menu VENTAS
function regventas() //ventasxdia.php
{

   $("#fila0").remove();
   $("#fila1").remove();
   $("#fila2").remove();
   $("#fila3").remove();
   $("#fila4").remove();
   $("#fila5").remove();

   $("#fila6").remove();
   $("#fila7").remove();
   $("#fila8").remove();
   $("#fila9").remove();
   $("#fila10").remove();
   $("#fila11").remove();



   var $ano = $("#ano option:selected").text();
   var $mes = $("#mes option:selected").val();
   var $dia = $("#dia option:selected").val();
   var $tmoneda = $("#tmonedaa option:selected").val();
   //Para prodcutos=================================================================================
   tabla = $('#tbllistado').dataTable(
      {
         "aProcessing": true,//Activamos el procesamiento del datatables
         "aServerSide": true,//Paginación y filtrado realizados por el servidor
         dom: 'Bfrtip',//Definimos los elementos del control de tabla

         "language": {
            'loadingRecords': '&nbsp;',
            'processing': '<i class="fa fa-spinner fa-spin"></i> Procesando datos'
         },


         buttons: [

            // {
            //     extend:    'copyHtml5',
            //     text:      '<i class="fa fa-files-o"></i>',
            //     titleAttr: 'Copy'
            // },
            // {
            //     extend:    'excelHtml5',
            //     text:      '<i class="fa fa-file-excel-o"></i>',
            //     titleAttr: 'Excel'
            // },
            // {
            //     extend:    'csvHtml5',
            //     text:      '<i class="fa fa-file-text-o"></i>',
            //     titleAttr: 'CSV'
            // },
            // {
            //     extend:    'pdfHtml5',
            //     text:      '<i class="fa fa-file-pdf-o"></i>',
            //     titleAttr: 'PDF'
            // }
         ],
         "ajax":
         {
            url: '../ajax/inventarios.php?op=regventas&ano=' + $ano + '&mes=' + $mes + '&dia=' + $dia + '&idempresa=' + $idempresa + '&tmon=' + $tmoneda,
            type: "post",
            dataType: "json",
            error: function (e) {
               console.log(e.responseText);
            }
         },

         "footerCallback": function (row, data, start, end, display) {
            var api = this.api(), data;
            var intVal = function (i) {
               return typeof i === 'string' ?
                  i.replace(/[\$,]/g, '') * 1 :
                  typeof i === 'number' ?
                     i : 0;
            };
            var subtotal = api
               .column(2)
               .data()
               .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
               }, 0);
            var igv = api
               .column(3)
               .data()
               .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
               }, 0);

            // Calculando total
            var total = api
               .column(4)
               .data()
               .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
               }, 0);
            var subtotal2 = currencyFormat(subtotal);
            var igv2 = currencyFormat(igv);
            var total2 = currencyFormat(total);

            // Try-catch para manejar error cuando DataTables no encuentra elemento footer
            try {
               var footer2 = api.column(2).footer();
               if (footer2) {
                  $(footer2).html(subtotal2);
               }
            } catch (e) {
               console.warn('Footer columna 2 no disponible:', e.message);
            }

            try {
               var footer3 = api.column(3).footer();
               if (footer3) {
                  $(footer3).html(igv2);
               }
            } catch (e) {
               console.warn('Footer columna 3 no disponible:', e.message);
            }

            try {
               var footer4 = api.column(4).footer();
               if (footer4) {
                  $(footer4).html(total2);
               }
            } catch (e) {
               console.warn('Footer columna 4 no disponible:', e.message);
            }
         },
         "bDestroy": true,
         "iDisplayLength": 5,//Paginación
         "order": [[0, "asc"]]//Ordenar (columna,orden)
      }).DataTable();
   //Para prodcutos=================================================================================

   //Para servicios=================================================================================
   tabla = $('#tbllistadoservicio').dataTable(
      {
         "aProcessing": true,//Activamos el procesamiento del datatables
         "aServerSide": true,//Paginación y filtrado realizados por el servidor
         dom: 'Bfrtip',//Definimos los elementos del control de tabla
         buttons: [

            //   'copyHtml5',
            //   'excelHtml5',
            //   'pdf'            
         ],
         "ajax":
         {
            url: '../ajax/inventarios.php?op=regventas&ano=' + $ano + '&mes=' + $mes + '&dia=' + $dia + '&idempresa=' + $idempresa,
            type: "post",
            dataType: "json",
            error: function (e) {
               console.log(e.responseText);
            }
         },

         "footerCallback": function (row, data, start, end, display) {
            var api = this.api(), data;
            var intVal = function (i) {
               return typeof i === 'string' ?
                  i.replace(/[\$,]/g, '') * 1 :
                  typeof i === 'number' ?
                     i : 0;
            };
            var subtotal = api
               .column(3)
               .data()
               .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
               }, 0);
            var igv = api
               .column(4)
               .data()
               .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
               }, 0);

            // Calculando total
            var total = api
               .column(5)
               .data()
               .reduce(function (a, b) {
                  return intVal(a) + intVal(b);
               }, 0);
            var subtotal2 = currencyFormat(subtotal);
            var igv2 = currencyFormat(igv);
            var total2 = currencyFormat(total);

            // Try-catch para manejar error cuando DataTables no encuentra elemento footer
            try {
               var footer3 = api.column(3).footer();
               if (footer3) {
                  $(footer3).html(subtotal2);
               }
            } catch (e) {
               console.warn('Footer columna 3 no disponible:', e.message);
            }

            try {
               var footer4 = api.column(4).footer();
               if (footer4) {
                  $(footer4).html(igv2);
               }
            } catch (e) {
               console.warn('Footer columna 4 no disponible:', e.message);
            }

            try {
               var footer5 = api.column(5).footer();
               if (footer5) {
                  $(footer5).html(total2);
               }
            } catch (e) {
               console.warn('Footer columna 5 no disponible:', e.message);
            }
         },
         "bDestroy": true,
         "iDisplayLength": 5,//Paginación
         "order": [[0, "asc"]]//Ordenar (columna,orden)
      }).DataTable();
   //Para servicios=================================================================================

   regventasmes();
}

// Función para establecer valores iniciales al cargar la página
function setInitialValues() {
   var today = new Date();

   // Establecer el año actual
   var currentYear = today.getFullYear();
   $("#ano").val(currentYear);

   // Establecer el mes actual (recuerda que en JavaScript los meses empiezan desde 0, es decir, enero es 0 y diciembre es 11)
   var currentMonth = today.getMonth() + 1; // +1 porque queremos que enero sea 1 y diciembre 12
   $("#mes").val(currentMonth);

   // Después de establecer estos valores, puedes llamar a tu función para cargar la data:
   regvenagruxdia();
}

// Llamar la función al cargar la página
$(document).ready(function () {
   setInitialValues();
});


$(document).ready(function () {
   // Este evento se activará cada vez que se modifique un elemento select dentro del formulario
   $("#formulario select").change(function () {
      regvenagruxdia();
   });
});


function renderEstado(data, type, row) {
   let estados = {
      5: { text: "Aceptado", color: "green" },
      4: { text: "Enviando a sunat", color: "orange" },
      3: { text: "Anulado", color: "red" },
      0: { text: "Error Anular y Volverlo hacer", color: "red" }
   };

   let estadoInfo = estados[data] || { text: "Estado desconocido", color: "black" };

   if (type === 'display') {  // Para DataTables
      return '<span style="color:' + estadoInfo.color + ';">' + estadoInfo.text + '</span>';
   } else if (type === 'export') {  // Para la exportación
      return estadoInfo.text;
   }
   return estadoInfo.text;  // Por defecto
}

function fechasCompletas() {
   var fechaDesde = $("#fechaDesde").val();
   var fechaHasta = $("#fechaHasta").val();

   return fechaDesde !== '' && fechaHasta !== '';
}


function regvenagruxdia() 
{ 

    var $ano = $("#ano option:selected").text();
    var $mes = $("#mes option:selected").val();
    var $dia = $("#dia option:selected").val();
    var $tm = $("#tmonedaa option:selected").val();
    console.log({
        ano: $ano,
        mes: $mes,
        dia: $dia,
        tm: $tm
    })

    tabla=$('#tbllistado').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip',//Definimos los elementos del control de tabla

        "language": { 
            'loadingRecords': '&nbsp;',
            'processing': '<i class="fa fa-spinner fa-spin"></i> Procesando datos'
        },

        buttons: [     

                  //    'copyHtml5',
                  //   'excelHtml5',
                  //   'pdf'           
                    
                ],
        "ajax":
                {
                    url: '../ajax/inventarios.php?op=regvenagruxdia&ano='+$ano+'&mes='+$mes+'&dia='+$dia+'&idempresa='+$idempresa+'&tmn='+$tm,
                    type : "post",
                    dataType : "json", 
                    error: function(e){
                        console.log("hay un error en ventas agrupadas sql.")
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
                .column( 1 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                // Calculando igv
            var igv = api
                .column( 2 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                // Calculando total
            var total = api
                .column( 3 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );

                var subtotal2=currencyFormat(subtotal);
                var igv2=currencyFormat(igv);
                var total2=currencyFormat(total);
                    
            // Update footer by showing the total with the reference of the column index 
            //$( api.column( 0 ).footer() ).html('Total');
            $( api.column( 1 ).footer() ).html(subtotal2);
            $( api.column( 2 ).footer() ).html(igv2);
            $( api.column( 3 ).footer() ).html(total2);
            },

        "bDestroy": true,
        "iDisplayLength": 5,//Paginación
        "order": [[ 0, "asc" ]]//Ordenar (columna,orden)

    }).DataTable();
}

$(document).ready(function () {
   // Agrega el evento change a los elementos de entrada de fecha
   $("#ano, #mes").change(function () {
       console.log("cambio");
      if (fechasCompletas()) {
         regvenagruxdia(); // Llama a la función para actualizar la tabla cuando ambas fechas están completas
      }
   });
});




function currencyFormat(num) {
   return "" + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
}


function regcompra(e) {
   e.preventDefault(); //No se activará la acción predeterminada del evento
   //$("#btnGuardar").prop("disabled",true);

   var formData = new FormData($("#formulario")[0]);

   $.ajax({
      url: "../ajax/inventarios.php?op=regcompras",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,

      success: function (datos) {

         listar();

      }

   });

}



function calcularkardex() {
   var $codigo = $("#codigoInterno").val();
   var $ano = $("#ano").val();
   var $xcod = $("#xcodigot").val();
   var almlist = $("#almacenlista").val();


   tabla = $('#tbllistadokardex').dataTable(

      {

         "aProcessing": true,//Activamos el procesamiento del datatables
         "aServerSide": true,//Paginación y filtrado realizados por el servidor
         dom: 'Bfrtip',//Definimos los elementos del control de tabla
         // "processing": true,
         // "language": { 
         //     'loadingRecords': '&nbsp;',
         //     'processing': '<i class="fa fa-spinner fa-spin"></i> Procesando datos.. espere'
         // },
         //"scrollX": true,
         "scrollY": "200px",
         "scrollCollapse": true,
         "paging": false,


         buttons: [

            'copyHtml5',
            'excelHtml5',
            'pdf'

         ],
         "ajax":
         {
            url: '../ajax/inventarios.php?op=consultakardexcodigo&cod=' + $codigo + '&ano=' + $ano + '&xct=' + $xcod + '&aalml=' + almlist,
            type: "post",
            dataType: "json",
            error: function (e) {
               console.log(e.responseText);

            }
         },

         "bDestroy": true,
         "iDisplayLength": 10,//Paginación
         "order": [[0, "asc"]]//Ordenar (columna,orden)
      }
   ).DataTable();

   tabla.ajax.reload();

}



function cargarumedida() {

}





init();











//Función ListarArticulos
function listarArticulos() {
   $tipoprecio = $('#tipoprecio').val();
   tablaArti = $('#tblarticulos').dataTable(
      {
         retrieve: true,
         "aProcessing": true,//Activamos el procesamiento del datatables
         "aServerSide": true,//Paginación y filtrado realizados por el servidor
         dom: 'Bfrtip',//Definimos los elementos del control de tabla

         "language": {
            'loadingRecords': '&nbsp;',
            'processing': '<i class="fa fa-spinner fa-spin"></i> Procesando datos'
         },


         buttons: [
         ],
         "ajax":
         {
            url: '../ajax/factura.php?op=listarArticulosfacturaItem&tipoprecio=' + $tipoprecio,
            type: "get",
            dataType: "json",
            error: function (e) {
               console.log(e.responseText);
            }
         },
         //Para cambiar el color del stock cuando es 0
         "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            if (aData[5] == "0.00") {
               $('td', nRow).css('background-color', '#fd96a9');
            }
            else {
               $('td', nRow).css('background-color', '');
            }
         },
         "bDestroy": true,
         "iDisplayLength": 5,//Paginación
         "order": [[0, "desc"]]//Ordenar (columna,orden)

      }
   ).DataTable();

   $('#tblarticulos').DataTable().ajax.reload();

   //controlastock();
}



function consultacomprobantes() {

   var $ano = $("#ano option:selected").text();
   var $mes = $("#mes option:selected").val();
   var $dia = $("#dia option:selected").val();
   var $comprobante = $("#comprobante option:selected").val();
   var $estado = $("#estadoC option:selected").val();

   tabla = $('#tbllistado').dataTable(
      {
         "aProcessing": true,//Activamos el procesamiento del datatables
         "aServerSide": true,//Paginación y filtrado realizados por el servidor
         dom: 'Bfrtip',//Definimos los elementos del control de tabla

         "language": {
            'loadingRecords': '&nbsp;',
            'processing': '<i class="fa fa-spinner fa-spin"></i> Procesando datos'
         },



         buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdf'
         ],
         "ajax":
         {
            url: '../ajax/inventarios.php?op=descargarcomprobante&ano=' + $ano + '&mes=' + $mes + '&dia=' + $dia + '&comprobante=' + $comprobante + '&estado=' + $estado,
            type: "post",
            dataType: "json",
            error: function (e) {
               console.log(e.responseText);
            }
         },

         "bDestroy": true,
         "iDisplayLength": 5,//Paginación
         "order": [[0, "desc"]]//Ordenar (columna,orden)

      }).DataTable();
}



function regventasmes() // FORMULARIO DE REGISTRO DE VENTA X DÍA
{
   $("#fila0").remove();
   $("#fila1").remove();
   $("#fila2").remove();
   $("#fila3").remove();
   $("#fila4").remove();
   $("#fila5").remove();

   $("#fila6").remove();
   $("#fila7").remove();
   $("#fila8").remove();
   $("#fila9").remove();
   $("#fila10").remove();
   $("#fila11").remove();


   var $ano = $("#ano option:selected").text();
   var $mes = $("#mes option:selected").val();
   var $dia = $("#dia option:selected").val();
   var $tmoneda = $("#tmonedaa option:selected").val();
   //Para prodcutos=================================================================================


   $.post("../ajax/inventarios.php?op=totalpordiames&ano=" + $ano + '&mes=' + $mes + '&mmon=' + $tmoneda, function (data, status) {

      data = JSON.parse(data);
      t1 = 0; t2 = 0; t3 = 0; t4 = 0; t5 = 0; t6 = 0; t7 = 0; t8 = 0; t9 = 0; t10 = 0; t11 = 0; t12 = 0; t13 = 0; t14 = 0; t15 = 0; t16 = 0; t17 = 0; t18 = 0; t19 = 0;
      t20 = 0; t21 = 0; t22 = 0; t23 = 0; t24 = 0; t25 = 0; t26 = 0; t27 = 0; t28 = 0; t29 = 0; t30 = 0; t31 = 0;
      $.each(data["aaData"], function (i, item) {
         //alert(item[1]);
         diaventa = item[1];

         nombredia = item[2];


         switch (diaventa) {
            case '1':
               t1 = item[0];
               break;
            case '2':
               t2 = item[0];
               break;
            case '3':
               t3 = item[0];
               break;
            case '4':
               t4 = item[0];
               break;
            case '5':
               t5 = item[0];
               break;
            case '6':
               t6 = item[0];
               break;
            case '7':
               t7 = item[0];
               break;
            case '8':
               t8 = item[0];
               break;
            case '9':
               t9 = item[0];
               break;
            case '10':
               t10 = item[0];
               break;
            case '11':
               t11 = item[0];
               break;
            case '12':
               t12 = item[0];
               break;
            case '13':
               t13 = item[0];
               break;
            case '14':
               t14 = item[0];
               break;
            case '15':
               t15 = item[0];
               break;
            case '16':
               t16 = item[0];
               break;
            case '17':
               t17 = item[0];
               break;
            case '18':
               t18 = item[0];
               break;
            case '19':
               t19 = item[0];
               break;
            case '20':
               t20 = item[0];
               break;
            case '21':
               t21 = item[0];
               break;
            case '22':
               t22 = item[0];
               break;
            case '23':
               t23 = item[0];
               break;
            case '24':
               t24 = item[0];
               break;
            case '25':
               t25 = item[0];
               break;
            case '26':
               t26 = item[0];
               break;
            case '27':
               t27 = item[0];
               break;
            case '28':
               t28 = item[0];
               break;
            case '29':
               t29 = item[0];
               break;
            case '30':
               t30 = item[0];
               break;
            case '31':
               t31 = item[0];
               break;
         }
      });

      resutot = parseFloat(t1) + parseFloat(t2) + parseFloat(t3) + parseFloat(t4) + parseFloat(t5) + parseFloat(t6) + parseFloat(t7) + parseFloat(t8) + parseFloat(t9) + parseFloat(t10) + parseFloat(t11) + parseFloat(t12) + parseFloat(t13) + parseFloat(t14) + parseFloat(t15) + parseFloat(t16) + parseFloat(t17) + parseFloat(t18) + parseFloat(t19) + parseFloat(t20) + parseFloat(t21) + parseFloat(t22) + parseFloat(t23) + parseFloat(t24) + parseFloat(t25) + parseFloat(t26) + parseFloat(t27) + parseFloat(t28) + parseFloat(t29) + parseFloat(t30) + parseFloat(t31);


      $.post("../ajax/inventarios.php?op=totalmesfactura&ano=" + $ano + '&mes=' + $mes + '&mmon=' + $tmoneda, function (data, status) {
         data = JSON.parse(data);
         tf1 = 0; tf2 = 0; tf3 = 0; tf4 = 0; tf5 = 0; tf6 = 0; tf7 = 0; tf8 = 0; tf9 = 0; tf10 = 0; tf11 = 0; tf12 = 0; tf13 = 0; tf14 = 0; tf15 = 0; tf16 = 0; tf17 = 0; tf18 = 0; tf19 = 0;
         tf20 = 0; tf21 = 0; tf22 = 0; tf23 = 0; tf24 = 0; tf25 = 0; tf26 = 0; tf27 = 0; tf28 = 0; tf29 = 0; tf30 = 0; tf31 = 0;
         $.each(data["aaData"], function (i, item) {
            //alert(item[1]);
            diaventa = item[1];
            switch (diaventa) {
               case '1':
                  tf1 = item[0];
                  break;
               case '2':
                  tf2 = item[0];
                  break;
               case '3':
                  tf3 = item[0];
                  break;
               case '4':
                  tf4 = item[0];
                  break;
               case '5':
                  tf5 = item[0];
                  break;
               case '6':
                  tf6 = item[0];
                  break;
               case '7':
                  tf7 = item[0];
                  break;
               case '8':
                  tf8 = item[0];
                  break;
               case '9':
                  tf9 = item[0];
                  break;
               case '10':
                  tf10 = item[0];
                  break;
               case '11':
                  tf11 = item[0];
                  break;
               case '12':
                  tf12 = item[0];
                  break;
               case '13':
                  tf13 = item[0];
                  break;
               case '14':
                  tf14 = item[0];
                  break;
               case '15':
                  tf15 = item[0];
                  break;
               case '16':
                  tf16 = item[0];
                  break;
               case '17':
                  tf17 = item[0];
                  break;
               case '18':
                  tf18 = item[0];
                  break;
               case '19':
                  tf19 = item[0];
                  break;
               case '20':
                  tf20 = item[0];
                  break;
               case '21':
                  tf21 = item[0];
                  break;
               case '22':
                  tf22 = item[0];
                  break;
               case '23':
                  tf23 = item[0];
                  break;
               case '24':
                  tf24 = item[0];
                  break;
               case '25':
                  tf25 = item[0];
                  break;
               case '26':
                  tf26 = item[0];
                  break;
               case '27':
                  tf27 = item[0];
                  break;
               case '28':
                  tf28 = item[0];
                  break;
               case '29':
                  tf29 = item[0];
                  break;
               case '30':
                  tf30 = item[0];
                  break;
               case '31':
                  tf31 = item[0];
                  break;
            }

            //resufac=parseFloat(item[0]);
         });
         //resufac++
         resufac = parseFloat(tf1) + parseFloat(tf2) + parseFloat(tf3) + parseFloat(tf4) + parseFloat(tf5) + parseFloat(tf6) + parseFloat(tf7) + parseFloat(tf8) + parseFloat(tf9) + parseFloat(tf10) + parseFloat(tf11) + parseFloat(tf12) + parseFloat(tf13) + parseFloat(tf14) + parseFloat(tf15) + parseFloat(tf16) + parseFloat(tf17) + parseFloat(tf18) + parseFloat(tf19) + parseFloat(tf20) + parseFloat(tf21) + parseFloat(tf22) + parseFloat(tf23) + parseFloat(tf24) + parseFloat(tf25) + parseFloat(tf26) + parseFloat(tf27) + parseFloat(tf28) + parseFloat(tf29) + parseFloat(tf30) + parseFloat(tf31);



         $.post("../ajax/inventarios.php?op=totalmesboleta&ano=" + $ano + '&mes=' + $mes + '&mmon=' + $tmoneda, function (data, status) {
            data = JSON.parse(data);
            tb1 = 0; tb2 = 0; tb3 = 0; tb4 = 0; tb5 = 0; tb6 = 0; tb7 = 0; tb8 = 0; tb9 = 0; tb10 = 0; tb11 = 0; tb12 = 0; tb13 = 0; tb14 = 0; tb15 = 0; tb16 = 0; tb17 = 0; tb18 = 0; tb19 = 0;
            tb20 = 0; tb21 = 0; tb22 = 0; tb23 = 0; tb24 = 0; tb25 = 0; tb26 = 0; tb27 = 0; tb28 = 0; tb29 = 0; tb30 = 0; tb31 = 0;
            $.each(data["aaData"], function (i, item) {
               //alert(item[1]);
               diaventa = item[1];


               switch (diaventa) {
                  case '1':
                     tb1 = item[0];
                     break;
                  case '2':
                     tb2 = item[0];
                     break;
                  case '3':
                     tb3 = item[0];
                     break;
                  case '4':
                     tb4 = item[0];
                     break;
                  case '5':
                     tb5 = item[0];
                     break;
                  case '6':
                     tb6 = item[0];
                     break;
                  case '7':
                     tb7 = item[0];
                     break;
                  case '8':
                     tb8 = item[0];
                     break;
                  case '9':
                     tb9 = item[0];
                     break;
                  case '10':
                     tb10 = item[0];
                     break;
                  case '11':
                     tb11 = item[0];
                     break;
                  case '12':
                     tb12 = item[0];
                     break;
                  case '13':
                     tb13 = item[0];
                     break;
                  case '14':
                     tb14 = item[0];
                     break;
                  case '15':
                     tb15 = item[0];
                     break;
                  case '16':
                     tb16 = item[0];
                     break;
                  case '17':
                     tb17 = item[0];
                     break;
                  case '18':
                     tb18 = item[0];
                     break;
                  case '19':
                     tb19 = item[0];
                     break;
                  case '20':
                     tb20 = item[0];
                     break;
                  case '21':
                     tb21 = item[0];
                     break;
                  case '22':
                     tb22 = item[0];
                     break;
                  case '23':
                     tb23 = item[0];
                     break;
                  case '24':
                     tb24 = item[0];
                     break;
                  case '25':
                     tb25 = item[0];
                     break;
                  case '26':
                     tb26 = item[0];
                     break;
                  case '27':
                     tb27 = item[0];
                     break;
                  case '28':
                     tb28 = item[0];
                     break;
                  case '29':
                     tb29 = item[0];
                     break;
                  case '30':
                     tb30 = item[0];
                     break;
                  case '31':
                     tb31 = item[0];
                     break;
               }
            });

            resubol = parseFloat(tb1) + parseFloat(tb2) + parseFloat(tb3) + parseFloat(tb4) + parseFloat(tb5) + parseFloat(tb6) + parseFloat(tb7) + parseFloat(tb8) + parseFloat(tb9) + parseFloat(tb10) + parseFloat(tb11) + parseFloat(tb12) + parseFloat(tb13) + parseFloat(tb14) + parseFloat(tb15) + parseFloat(tb16) + parseFloat(tb17) + parseFloat(tb18) + parseFloat(tb19) + parseFloat(tb20) + parseFloat(tb21) + parseFloat(tb22) + parseFloat(tb23) + parseFloat(tb24) + parseFloat(tb25) + parseFloat(tb26) + parseFloat(tb27) + parseFloat(tb28) + parseFloat(tb29) + parseFloat(tb30) + parseFloat(tb31);


            var $mes2 = $("#mes option:selected").text();

            // $('#tbllistadoventaxdia').append(
            //     '<tr id="fila0"  style="font-family: Arial; font-size: 0.9em; font-size: 1.5em;"><th style="color:black;" colspan="7">MES: '+$mes2+' FACTURAS Y BOLETAS ELECTRÓNICAS</th></tr>');

            // $('#tbllistadoventaxdia').append(
            //     '<tr id="fila1"><th style="font-family: Arial; font-size: 0.9em; font-size: 0.9em; background-color: #081A51; ">DIA 01  <br>T. FACTURA: '+tf1+'<br>T. BOLETA: '+tb1+' <br>TOTAL DIA: '+t1+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 02  <br>T. FACTURA: '+tf2+'<br>T. BOLETA: '+tb2+' <br>TOTAL DIA: '+t2+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 03 <br>T. FACTURA: '+tf3+'<br>T. BOLETA: '+tb3+' <br>TOTAL DIA: '+t3+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 04 <br>T. FACTURA: '+tf4+'<br>T. BOLETA: '+tb4+' <br>TOTAL DIA: '+t4+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 05 <br>T. FACTURA: '+tf5+'<br>T. BOLETA: '+tb5+' <br>TOTAL DIA: '+t5+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 06 <br>T. FACTURA: '+tf6+'<br>T. BOLETA: '+tb6+' <br>TOTAL DIA: '+t6+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 07 <br>T. FACTURA: '+tf7+'<br>T. BOLETA: '+tb7+' <br>TOTAL DIA: '+t7+'</th></tr>');

            // $('#tbllistadoventaxdia').append(
            // '<tr id="fila2"><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 08  <br>T. FACTURA: '+tf8+'<br>T. BOLETA: '+tb8+' <br>TOTAL DIA: '+t8+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 09  <br>T. FACTURA: '+tf9+'<br>T. BOLETA: '+tb9+' <br>TOTAL DIA: '+t9+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 10 <br>T. FACTURA: '+tf10+'<br>T. BOLETA: '+tb10+' <br>TOTAL DIA: '+t10+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 11 <br>T. FACTURA: '+tf11+'<br>T. BOLETA: '+tb11+' <br>TOTAL DIA: '+t11+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 12 <br>T. FACTURA: '+tf12+'<br>T. BOLETA: '+tb12+' <br>TOTAL DIA: '+t12+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 13 <br>T. FACTURA: '+tf13+'<br>T. BOLETA: '+tb13+' <br>TOTAL DIA: '+t13+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 14 <br>T. FACTURA: '+tf14+'<br>T. BOLETA: '+tb14+' <br>TOTAL DIA: '+t14+'</th></tr>');

            // $('#tbllistadoventaxdia').append(
            // '<tr id="fila3"><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 15  <br>T. FACTURA: '+tf15+'<br>T. BOLETA: '+tb15+' <br>TOTAL DIA: '+t15+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 16  <br>T. FACTURA: '+tf16+'<br>T. BOLETA: '+tb16+' <br>TOTAL DIA: '+t16+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 17 <br>T. FACTURA: '+tf17+'<br>T. BOLETA: '+tb17+' <br>TOTAL DIA: '+t17+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 18 <br>T. FACTURA: '+tf18+'<br>T. BOLETA: '+tb18+' <br>TOTAL DIA: '+t18+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 19 <br>T. FACTURA: '+tf19+'<br>T. BOLETA: '+tb19+' <br>TOTAL DIA: '+t19+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 20 <br>T. FACTURA: '+tf20+'<br>T. BOLETA: '+tb20+' <br>TOTAL DIA: '+t20+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 21 <br>T. FACTURA: '+tf21+'<br>T. BOLETA: '+tb21+' <br>TOTAL DIA: '+t21+'</th></tr>');

            // $('#tbllistadoventaxdia').append(
            // '<tr id="fila4"><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 22  <br>T. FACTURA: '+tf22+'<br>T. BOLETA: '+tb22+' <br>TOTAL DIA: '+t22+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 23  <br>T. FACTURA: '+tf23+'<br>T. BOLETA: '+tb23+' <br>TOTAL DIA: '+t23+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 24 <br>T. FACTURA: '+tf24+'<br>T. BOLETA: '+tb24+' <br>TOTAL DIA: '+t24+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 25 <br>T. FACTURA: '+tf25+'<br>T. BOLETA: '+tb25+' <br>TOTAL DIA: '+t25+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 26 <br>T. FACTURA: '+tf26+'<br>T. BOLETA: '+tb26+' <br>TOTAL DIA: '+t26+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 27 <br>T. FACTURA: '+tf27+'<br>T. BOLETA: '+tb27+' <br>TOTAL DIA: '+t27+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 28 <br>T. FACTURA: '+tf28+'<br>T. BOLETA: '+tb28+' <br>TOTAL DIA: '+t28+'</th></tr>');

            // $('#tbllistadoventaxdia').append(
            // '<tr id="fila5"><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 29  <br>T. FACTURA: '+tf29+'<br>T. BOLETA: '+tb29+' <br>TOTAL DIA: '+t29+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 30  <br>T. FACTURA: '+tf30+'<br>T. BOLETA: '+tb30+' <br>TOTAL DIA: '+t30+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 31 <br>T. FACTURA: '+tf31+'<br>T. BOLETA: '+tb31+' <br>TOTAL DIA: '+t31+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">RESUMEN <br>T. FACTURA: '+resufac+'<br>T. BOLETA: '+resubol+' <br>TOTAL MES: '+resutot+'</th></tr>');






            $.post("../ajax/inventarios.php?op=totalpordiamesnotaped&ano=" + $ano + '&mes=' + $mes, function (data, status) {

               data = JSON.parse(data);
               np1 = 0; np2 = 0; np3 = 0; np4 = 0; np5 = 0; np6 = 0; np7 = 0; np8 = 0; np9 = 0; np10 = 0; np11 = 0; np12 = 0; np13 = 0; np14 = 0; np15 = 0; np16 = 0; np17 = 0; np18 = 0; np19 = 0;
               np20 = 0; np21 = 0; np22 = 0; np23 = 0; np24 = 0; np25 = 0; np26 = 0; np27 = 0; np28 = 0; np29 = 0; np30 = 0; np31 = 0;
               $.each(data["aaData"], function (i, item) {
                  diaventa = item[1];

                  switch (diaventa) {
                     case '1':
                        np1 = item[0];
                        break;
                     case '2':
                        np2 = item[0];
                        break;
                     case '3':
                        np3 = item[0];
                        break;
                     case '4':
                        np4 = item[0];
                        break;
                     case '5':
                        np5 = item[0];
                        break;
                     case '6':
                        np6 = item[0];
                        break;
                     case '7':
                        np7 = item[0];
                        break;
                     case '8':
                        np8 = item[0];
                        break;
                     case '9':
                        np9 = item[0];
                        break;
                     case '10':
                        np10 = item[0];
                        break;
                     case '11':
                        np11 = item[0];
                        break;
                     case '12':
                        np12 = item[0];
                        break;
                     case '13':
                        np13 = item[0];
                        break;
                     case '14':
                        np14 = item[0];
                        break;
                     case '15':
                        np15 = item[0];
                        break;
                     case '16':
                        np16 = item[0];
                        break;
                     case '17':
                        np17 = item[0];
                        break;
                     case '18':
                        np18 = item[0];
                        break;
                     case '19':
                        np19 = item[0];
                        break;
                     case '20':
                        np20 = item[0];
                        break;
                     case '21':
                        np21 = item[0];
                        break;
                     case '22':
                        np22 = item[0];
                        break;
                     case '23':
                        np23 = item[0];
                        break;
                     case '24':
                        np24 = item[0];
                        break;
                     case '25':
                        np25 = item[0];
                        break;
                     case '26':
                        np26 = item[0];
                        break;
                     case '27':
                        np27 = item[0];
                        break;
                     case '28':
                        np28 = item[0];
                        break;
                     case '29':
                        np29 = item[0];
                        break;
                     case '30':
                        np30 = item[0];
                        break;
                     case '31':
                        np31 = item[0];
                        break;
                  }
               });

               // resutotnp=parseFloat(np1)+parseFloat(np2)+parseFloat(np3)+parseFloat(np4)+parseFloat(np5)+parseFloat(np6)+parseFloat(np7)+parseFloat(np8)+parseFloat(np9)+parseFloat(np10)+parseFloat(np11)+parseFloat(np12)+parseFloat(np13)+parseFloat(np14)+parseFloat(np15)+parseFloat(np16)+parseFloat(np17)+parseFloat(np18)+parseFloat(np19)+parseFloat(np20)+parseFloat(np21)+parseFloat(np22)+parseFloat(np23)+parseFloat(np24)+parseFloat(np25)+parseFloat(np26)+parseFloat(np27)+parseFloat(np28)+parseFloat(np29)+parseFloat(np30)+parseFloat(np31);
               // var $mes2 = $("#mes option:selected").text();

               // $('#tbllistadoventaxdianotapedido').append(
               //     '<tr id="fila6"  style="font-family: Arial; font-size: 0.9em; font-size: 1.5em;"><th style="color:black;" colspan="7">MES: '+$mes2+' NOTAS DE PEDIDO</th></tr>');

               // $('#tbllistadoventaxdianotapedido').append(
               //    '<tr id="fila7"><th style="font-family: Arial; font-size: 0.9em; font-size: 0.9em; background-color: #28a745; ">DIA 01 <br> TOTAL DIA: '+np1+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 02  <br> TOTAL DIA: '+np2+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 03 <br> TOTAL DIA: '+np3+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 04  <br> TOTAL DIA: '+np4+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 05  <br> TOTAL DIA: '+np5+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 06  <br> TOTAL DIA: '+np6+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 07  <br>  TOTAL DIA: '+np7+'</th></tr>');

               //  $('#tbllistadoventaxdianotapedido').append(
               //  '<tr id="fila8"><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 08 <br> TOTAL DIA: '+np8+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 09  <br> TOTAL DIA: '+np9+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 10 <br> TOTAL DIA: '+np10+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 11 <br> TOTAL DIA: '+np11+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 12 <br> TOTAL DIA: '+np12+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 13 <br> TOTAL DIA: '+np13+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 14 <br> TOTAL DIA: '+np14+'</th></tr>');

               //  $('#tbllistadoventaxdianotapedido').append(
               //  '<tr id="fila9"><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 15  <br>TOTAL DIA: '+np15+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 16  <br>TOTAL DIA: '+np16+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 17 <br>TOTAL DIA: '+np17+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 18 <br>TOTAL DIA: '+np18+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 19 <br>TOTAL DIA: '+np19+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 20 <br>TOTAL DIA: '+np20+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 21 <br>TOTAL DIA: '+np21+'</th></tr>');

               //  $('#tbllistadoventaxdianotapedido').append(
               //  '<tr id="fila10"><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 22  <br>TOTAL DIA: '+np22+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 23  <br>TOTAL DIA: '+np23+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 24 <br>TOTAL DIA: '+np24+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 25 <br>TOTAL DIA: '+np25+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 26 <br>TOTAL DIA: '+np26+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 27 <br>TOTAL DIA: '+np27+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 28 <br>TOTAL DIA: '+np28+'</th></tr>');

               //  $('#tbllistadoventaxdianotapedido').append(
               //  '<tr id="fila11"><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 29  <br>TOTAL DIA: '+np29+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">DIA 30  <br>TOTAL DIA: '+np30+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #28a745; ">DIA 31 <br>TOTAL DIA: '+np31+'</th><th style="font-family: Arial; font-size: 0.9em; background-color: #081A51; ">RESUMEN <br>TOTAL MES: '+resutotnp+'</th></tr>');

            });

         });

      });

   });







}






function calcularkardexentrefechas() {
   var $fecha1 = $("#fecha1").val();
   var $fecha2 = $("#fecha2").val();
   var xcodigo = $("#codigoInterno").val();
   tabla = $('#tbllistadokardex').dataTable(
      {

         "aProcessing": true,//Activamos el procesamiento del datatables
         "aServerSide": true,//Paginación y filtrado realizados por el servidor
         dom: 'Bfrtip',//Definimos los elementos del control de tabla

         "processing": true,
         "language": {
            'loadingRecords': '&nbsp;',
            'processing': '<i class="fa fa-spinner fa-spin"></i> Procesando datos.. espere'
         },

         buttons: [],
         "ajax":
         {
            url: '../ajax/inventarios.php?op=kardexporfechas&f1=' + $fecha1 + '&f2=' + $fecha2 + '&Opc=' + xocdTods + '&xcd=' + xcodigo,
            type: "post",
            dataType: "json",
            error: function (e) {
               console.log(e.responseText);

            }
         },

         "bDestroy": true,
         "iDisplayLength": 10,//Paginación
         "order": [[0, "asc"]]//Ordenar (columna,orden)
      }
   ).DataTable();

   tabla.ajax.reload();
}


function mostraractual() {
   var $fecha1 = $("#fecha1").val();
   var $fecha2 = $("#fecha2").val();

   tabla = $('#tbllistadokardex').dataTable(
      {

         "aProcessing": true,//Activamos el procesamiento del datatables
         "aServerSide": true,//Paginación y filtrado realizados por el servidor
         dom: 'Bfrtip',//Definimos los elementos del control de tabla

         "processing": true,
         "language": {
            'loadingRecords': '&nbsp;',
            'processing': '<i class="fa fa-spinner fa-spin"></i> Procesando datos.. espere'
         },

         buttons: [],
         "ajax":
         {
            url: "../ajax/inventarios.php?op=mostraractual&f1_=" + $fecha1 + "&f2_=" + $fecha2,
            type: "post",
            dataType: "json",
            error: function (e) {
               console.log(e.responseText);

            }
         },

         "bDestroy": true,
         "iDisplayLength": 5,//Paginación
         "order": [[0, "asc"]]//Ordenar (columna,orden)
      }
   ).DataTable();

   tabla.ajax.reload();
}



function resetearvalores() {

   var $codigo = $("#codigoInterno").val();
   var $ano = $("#ano").val();

   tabla = $('#tbllistadokardex').dataTable(
      {
         "aProcessing": true,//Activamos el procesamiento del datatables
         "aServerSide": true,//Paginación y filtrado realizados por el servidor
         dom: 'Bfrtip',//Definimos los elementos del control de tabla

         buttons: [

         ],
         "ajax":
         {
            url: '../ajax/inventarios.php?op=resetearvalores&cod=' + $codigo + '&ano=' + $ano,
            type: "post",
            dataType: "json",
            error: function (e) {
               console.log(e.responseText);
            }
         },

         "bDestroy": true,
         "iDisplayLength": 5,//Paginación
         "order": [[0, "asc"]]//Ordenar (columna,orden)

      }).DataTable();

   tabla.ajax.reload();
}


function guardarregistro(idregistro) {
   var $fecha1 = $("#fecha1").val();
   var $fecha2 = $("#fecha2").val();

   $.post("../ajax/inventarios.php?op=registrarxcodigo&idregval=" + idregistro, function (r) {
      alert(r);
   })
}


// document.getElementById('btnReportePDFMes').addEventListener('click', function () {
//    let ano = document.getElementById('ano').value;
//    let mes = document.getElementById('mes').value;
//    let tmonedaa = document.getElementById('tmonedaa').value;

//    // Luego hacer la solicitud AJAX con estos valores
//    fetchReport(ano, mes, tmonedaa);
// });



// function fetchReport(ano, mes, tmonedaa) {
//    let url = `../ajax/ajaxReporteVenta.php?action=ListarReporteMes&ano=${ano}&mes=${mes}&idempresa=1&tmon=${tmonedaa}`;

//    fetch(url)
//       .then(response => response.json())
//       .then(data => {
//          generatePDF(data.aaData);
//       });
// }

// function mesEnTexto(mesNumero) {
//    const meses = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];
//    return meses[mesNumero - 1]; // -1 porque los arrays empiezan en 0 y enero sería el mes 1.
// }

// function drawBoldText(doc, text, x, y) {
//    const offset = 0.1; // Este es el desplazamiento. Puede ajustarlo según su necesidad.
//    doc.text(text, x, y);
//    doc.text(text, x + offset, y);
//    doc.text(text, x, y + offset);
//    doc.text(text, x + offset, y + offset);
// }

// let someThreshold = 50;
// function generatePDF(data) {
//    const { jsPDF } = window.jspdf;
//    let doc = new jsPDF('landscape');
//    let mesSeleccionadoNumero = parseInt(document.getElementById('mes').value, 10); // Asegurarse de que es un número
//    let mesSeleccionadoTexto = mesEnTexto(mesSeleccionadoNumero);
//    let anoSeleccionado = document.getElementById('ano').value;
//    // Construir el título
//    let titulo = `REPORTE DE VENTAS MENSUAL DEL MES DE ${mesSeleccionadoTexto} DEL AÑO ${anoSeleccionado}`;
//    // Calcular la posición x para centrar el título
//    let pageWidth = doc.internal.pageSize.getWidth();
//    let titleWidth = doc.getStringUnitWidth(titulo) * doc.internal.getFontSize() / doc.internal.scaleFactor;
//    let titleX = (pageWidth - titleWidth) / 1.7;
//    let currentY = 10; // Inicia en 10 por el título.
//    // Dibujar el título en el PDF (puedes ajustar la posición Y según lo necesites)
//    doc.setFontSize(14); // Aumenta el tamaño de la fuente para el título
//    doc.text(titulo, titleX, currentY);
//    currentY += 20; // Restablecer el tamaño de la fuente a 8 para el resto del documento
//    doc.setFontSize(7);
//    // Posiciones x para los encabezados
//    const headersXPositions = [8, 16, 45, 75, 117, 158, 175, 189, 205, 216, 234, 246, 263, 276];
//    // Encabezados de las columnas
//    const headers = ["Dia", "Doc.", "Cliente", "N° Doc", "Productos", "Efectivo", "Visa", "Yape", "Plin", "masterC.", "Dep.", "Sub. T.", "Igv", "Total"];
//    // Posición inicial para los encabezados
//    const headersY = 25;
//    // Incrementar ligeramente el tamaño de la fuente para los encabezados
//    doc.setFontSize(8);  // Puede ajustar según su gusto
//    // Dibuja los encabezados en el PDF
//    headers.forEach((header, index) => {
//       drawBoldText(doc, header, headersXPositions[index], headersY);
//       const headerWidth = doc.getTextWidth(header);
//       doc.line(headersXPositions[index], headersY + 2, headersXPositions[index] + headerWidth, headersY + 2);
//    });
//    currentY = headersY + 15;
//    // Restablecer el tamaño de la fuente para los datos
//    doc.setFontSize(8);
//    // Ajusta la posición inicial de los datos para que estén debajo de los encabezados.
//    let initialDataY = headersY + 10; // Ajusta según necesites
//    const maxWidth = 60;
//    let yOffset = 0;
//    // 1. Cálculo de Totales
//    let totalEfectivo = 0;
//    let totalVisa = 0;
//    let totalYape = 0;
//    let totalPlin = 0;
//    let totalMastercard = 0;
//    let totalDeposito = 0;
//    let totalGeneral = 0;

//    data.forEach(item => {
//       totalEfectivo += parseFloat(item.efectivo);
//       totalVisa += parseFloat(item.visa);
//       totalYape += parseFloat(item.yape);
//       totalPlin += parseFloat(item.plin);
//       totalMastercard += parseFloat(item.mastercard);
//       totalDeposito += parseFloat(item.deposito);
//       totalGeneral += parseFloat(item.total);
//    });

//    data.forEach((item, index) => {
//       let initialY = initialDataY + (index * 7) + yOffset;   // Para calcular la posición vertical
//       currentY = initialY + 7;
//       if (doc.internal.pageSize.getHeight() - currentY < someThreshold) {  // `someThreshold` es el espacio necesario para añadir los totales y la siguiente fila
//          // Añadir los totales al final de esta página
//          addTotalsToPage(doc, totalEfectivo, totalVisa, totalYape, totalPlin, totalMastercard, totalDeposito, totalGeneral, currentY);
//          doc.addPage();
//       }

//       doc.text(8, initialY, item.fecha);                // Fecha
//       doc.text(16, initialY, item.documento);            // Documento
//       const RazonSocial = doc.splitTextToSize(item.razon_social, maxWidth)
//       doc.text(30, initialY, RazonSocial);         // Razón Social (Asumo que puede ser largo)
//       if (RazonSocial.length > 1) {
//          yOffset += (RazonSocial.length - 1) * 2;  // 7 es aproximado al alto de una línea, ajusta según necesites
//       }
//       doc.text(75, initialY, item.numero_documento);    // N° Doc (Ajustado para dar más espacio a Razón Social)
//       const PorductosAquiridos = doc.splitTextToSize(item.productos_adquiridos, maxWidth);
//       doc.text(95, initialY, PorductosAquiridos);// Productos (Asumo que puede ser largo)
//       // Si splitTitle tiene más de 1 línea, aumenta yOffset para las siguientes filas
//       if (PorductosAquiridos.length > 1) {
//          yOffset += (PorductosAquiridos.length - 1) * 2;  // 7 es aproximado al alto de una línea, ajusta según necesites
//       }
//       doc.text(160, initialY, item.efectivo);            // Efectivo
//       doc.text(175, initialY, item.visa);                // Visa
//       doc.text(190, initialY, item.yape);                // Yape
//       doc.text(205, initialY, item.plin);                // Plin
//       doc.text(220, initialY, item.mastercard);          // Mastercard
//       doc.text(234, initialY, item.deposito);            // Deposito
//       doc.text(248, initialY, item.subtotal);            // Subtotal
//       doc.text(263, initialY, item.igv);                 // IGV
//       doc.text(276, initialY, item.total);
//       currentY = initialY + 7;            // Total
//    });
//    // Para verificar si hay espacio al final para los totales:
//    let pageHeight = doc.internal.pageSize.getHeight();
//    //let someThreshold = 50;  // El espacio necesario para agregar los totales. Ajusta según necesites.
//    if (pageHeight - currentY >= someThreshold) {
//       // Tienes suficiente espacio, añade los totales.
//       addTotalsToPage(doc, totalEfectivo, totalVisa, totalYape, totalPlin, totalMastercard, totalDeposito, totalGeneral, currentY);
//    }
//    // Convertir el PDF en un Blob
//    const blob = new Blob([doc.output('blob')], { type: 'application/pdf' });
//    // Generar una URL para el Blob
//    const url = URL.createObjectURL(blob);
//    // Abrir la URL en una nueva pestaña
//    //window.open(url, '_blank');

//    const downloadLink = document.createElement('a');
//    downloadLink.href = url;
//    downloadLink.download = `reporte_mensual_${mesSeleccionadoTexto}_${anoSeleccionado}_ventas.pdf`;
//    document.body.appendChild(downloadLink);  // Necesario para que funcione en Firefox
//    downloadLink.click();
//    document.body.removeChild(downloadLink);

// }

// function addTotalsToPage(doc, totalEfectivo, totalVisa, totalYape, totalPlin, totalMastercard, totalDeposito, totalGeneral, currentY) {
//    // Las posiciones X de tus totales deben coincidir con las posiciones X de las columnas correspondientes
//    const totalPositionsX = [157.5, 174.5, 188.5, 204.5, 219.5, 233, 275];

//    // Coordenadas de inicio y fin de la línea horizontal
//    const startX = 8; // Comienza en la misma posición X que el primer encabezado
//    const endX = 284; // Ajusta este valor para que la línea llegue hasta el final de la página o hasta donde desees
//    const lineY = currentY - 3; // Esto coloca la línea 5 unidades por encima del texto "Totales"

//    // Dibuja la línea
//    doc.line(startX, lineY, endX, lineY);

//    let posY = currentY + 5;
//    doc.setFontSize(9);
//    doc.text(`Totales:`, 9, posY);
//    // Aquí dibujas cada total en el PDF
//    doc.text(totalEfectivo.toFixed(2).toString(), totalPositionsX[0], posY);
//    doc.text(totalVisa.toFixed(2).toString(), totalPositionsX[1], posY);
//    doc.text(totalYape.toFixed(2).toString(), totalPositionsX[2], posY);
//    doc.text(totalPlin.toFixed(2).toString(), totalPositionsX[3], posY);
//    doc.text(totalMastercard.toFixed(2).toString(), totalPositionsX[4], posY);
//    doc.text(totalDeposito.toFixed(2).toString(), totalPositionsX[5], posY);
//    doc.text(totalGeneral.toFixed(2).toString(), totalPositionsX[6], posY);
// }


// $("#btnReportePDFMes").click(function () {
//    var ano = $("#ano option:selected").text();
//    var mes = $("#mes option:selected").val();
//    var tm = $("#tmonedaa option:selected").val();
//    var idempresa = $("#idempresa").val();

//    $.ajax({
//       url: `https://wfacx.com/sistema/ajax/ajaxReporteVenta.php?action=ListarReporteMes&ano=${ano}&mes=${mes}&idempresa=${idempresa}&tmon=${tm}`,
//       type: "GET",
//       dataType: "json",
//       success: function (data) {
//          var docDefinition = {
//             pageSize: 'A4',
//             pageOrientation: 'landscape',
//             content: [{
//                table: {
//                   headerRows: 1,
//                   body: [
//                      ['Fecha', 'Documento', 'Cliente', 'N° Doc', 'Productos', 'Efectivo', 'Visa', 'Yape', 'Plin', 'Mastercard', 'Depósito', 'Subtotal', 'IGV', 'Total'],
//                      ...data.aaData.map(item => [
//                         item.fecha,
//                         item.documento,
//                         item.razon_social,
//                         item.numero_documento,
//                         item.productos_adquiridos,
//                         item.efectivo,
//                         item.visa,
//                         item.yape,
//                         item.plin,
//                         item.mastercard,
//                         item.deposito,
//                         item.subtotal,
//                         item.igv,
//                         item.total
//                      ])
//                   ],
//                   columnWidths: [
//                      20, 20, 40, 20, 200, 20, 20, 20, 20, 20, 20, 20, 20, 20
//                   ]
//                }
//             }]
//          };

//          pdfMake.createPdf(docDefinition).download("ReporteMensual.pdf");
//       }
//    });
// });



