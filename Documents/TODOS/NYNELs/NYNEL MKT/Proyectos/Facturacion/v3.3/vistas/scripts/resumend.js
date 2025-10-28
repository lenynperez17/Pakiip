function init(){
  
$("#formulario").on("submit",function(e)
    {
        regbajas(e);  
    });   

var fecha = new Date();
var ano = fecha.getFullYear();
$("#ano").val(ano);
var mes = fecha.getMonth();
$("#mes").val(mes+1);
var dia = fecha.getDate();
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
                    
                ],
        "ajax":
                {
                    url: '../ajax/resumend.php?op=resumend&ano='+$ano+'&mes='+$mes+'&dia='+$dia,
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

function currencyFormat (num) {
    return "S/ " + num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
}



function generarbajaxml() {
    var $ano = $("#ano option:selected").text();
    var $mes = $("#mes option:selected").val();
    var $dia = $("#dia option:selected").val();

    Swal.fire({
        title: '¿Está seguro de generar el archivo XML?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('../ajax/resumend.php?op=generarbajaxml&ano='+$ano+'&mes='+$mes+'&dia='+$dia, function(e) {
                data = JSON.parse(e);
                Swal.fire({
                    title: 'Archivo XML generado',
                    html: '<label>Se ha creado el archivo XML: </label><a href="'+data.cabextxml+'" download="'+data.cabxml+'">" ARCHIVO XML: '+data.cabxml+'"</a></br></br><h1>1.</h1><h4>Si desea enviar el archivo a SUNAT clic en enviar</h4><button class="btn btn-danger" id="btnenviarxml" name="btnenviarxml" onclick="enviarxmlbajaboleta(data.nombrea);">ENVIAR</button></br></br><h1>2.</h1><h4>Consulte el nro de ticket.</h4><input class="form-control" id="nticket" name="nticket"></br><button class="btn btn-success" name="btnconsultaticket" id="btnconsultaticket" onclick="consultarnticket();">CONSULTAR</button>',
                    icon: 'success'
                });
                tabla.ajax.reload();
            });
        }
    });
}





function enviarxmlbajaboleta(nroxml) {
    swal.fire({
      title: "¿Está seguro de enviar archivo firmado a SUNAT?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, enviar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("../ajax/resumend.php?op=enviarxmlbajaboleta&nombrexml=" + nroxml, function(e) {
          data2 = JSON.parse(e);
          swal.fire({
            title: "El número de ticket es:",
            html: "<h1>" + data2.nroticket + "</h1>",
            icon: "success",
          });
          $("#nticket").val(data2.nroticket);
          tabla.ajax.reload();
        });
      }
    });
  
    tabla = $('#tbllistadoxml').dataTable({
      "aProcessing": true,
      "aServerSide": true,
      "bFilter": false,
      dom: 'Bfrtip',
      buttons: [],
      "ajax": {
        url: '../ajax/resumend.php?op=ultimoarchivoxml&ultimoxml=' + nroxml,
        type: "post",
        dataType: "json",
        error: function(e) {
          console.log(e.responseText);
        }
      },
      "bDestroy": true,
      "iDisplayLength": 5,
    }).DataTable();
  }
  



function consultarnticket() {
    numeroticket = $("#nticket").val();
    swal.fire({
        title: "¿Desea consultar estado ticket?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/resumend.php?op=validarticket&ntikk=" + numeroticket, function(e) {
                data2 = JSON.parse(e);
                swal.fire({
                    title: "Estado del ticket",
                    text: e,
                    icon: "info"
                });
            });
        }
    });
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
                    url: '../ajax/resumend.php?op=detallecomprobante&idxml='+idxml,
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