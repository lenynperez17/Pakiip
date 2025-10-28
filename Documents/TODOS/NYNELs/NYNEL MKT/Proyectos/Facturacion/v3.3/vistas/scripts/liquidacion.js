var tabla;
var tablas;
var cont=0;
var detalles=0;

 toastr.options = {
                closeButton: false,
                debug: false,
                newestOnTop: false,
                progressBar: false,
                rtl: false,
                positionClass: 'toast-bottom-center',
                preventDuplicates: false,
                onclick: null
            };



//Función que se ejecuta al inicio

function init()
{

  mostrarform(false);
  listar();
  
  
  $("#formulario").on("submit",function(e)
  {
    guardaryeditar(e);  

  })



}






function seltipoliq()
  {
    opttp=$("#tservicio").val();
    var x = document.getElementById("divvuelo");
    var y = document.getElementById("divservicio");
    if (opttp=="v")
    {
        y.style.display = "none";
        x.style.display = "block";

        

    }else{
    	x.style.display = "none";
        y.style.display = "block";
        limpiar();
		detallevuelo();
       
        
    }

  }


  function detallevuelo()
  {
  	var fila='<tr class="filas" id="fila'+cont+'">'+
  	'<td><i class="fa fa-close" onclick="eliminarDetalle('+cont+')" data-toggle="tooltip" title="Eliminar item"></i></td>'+
  	'<td><input type="text" name="aerol[]" id=" aerol[]" onkeyup="mayus(this)"></td>'+
  	'<td><input type="text" name="nvuelo[]" id="nvuelo[]" ></td>'+
  	'<td><input type="date" name="fecha[]" id="fecha[]" ></td>'+
  	'<td><input type="text" name="destino[]" id="destino[]" onkeyup="mayus(this)"></td>'+
  	'<td><input type="time" name="hsalida[]" id="hsalida[]" value="08:00" max="23:59" min="00:00"></td>'+
  	'<td><input type="time" name="hretorno[]" id="hretorno[]" value="08:00" max="23:59" min="00:00" onkeypress="nextf(event, this)" ></td>'+
  	'<td><button type="button" onclick="detallevuelo()">Vuelo</button>   <button type="button" onclick="detalletl()">TL</button>  </td>'+
  	'</tr>'
  	cont++;
  	$('#detalles').append(fila);
  		detalles=detalles+1;
  }


   function detalletl()
  {
  	var fila='<tr class="filas" id="fila'+cont+'">'+
  	'<td><i class="fa fa-close" onclick="eliminarDetalle('+cont+')" data-toggle="tooltip" title="Eliminar item"></i></td>'+
  	'<td><input type="text" name="aerol[]" id=" aerol[]" onkeyup="mayus(this)"></td>'+
  	'<td colspan="5"><textarea  id="tldesc[]" name="tldesc[]" class="" cols="40" rows="2"></textarea></td>'+
  	'<td><button type="button" onclick="detallevuelo()">Vuelo</button>   <button type="button" onclick="detalletl()">TL</button>  </td>'+
  	'</tr>'
  	cont++;
  	$('#detalles').append(fila);
  		detalles=detalles+1;
  }




function nextf(e, field) {
  key = e.keyCode ? e.keyCode : e.which
  if(e.keyCode===13  && !e.shiftKey)
    {
       detallevuelo();
    }
}

  function eliminarDetalle(indice){
    $("#fila" + indice).remove();
    detalles=detalles-1;
    actualizanorden();
  }

  function actualizanorden()
{
var total = document.getElementsByName("aerol[]");
var cont=0;
 for (var i = 0; i <= total.length; i++) {
        var cont=total[i];
        //cont.value=i+1;
    }//Final de for
}

function mayus(e) {
     e.value = e.value.toUpperCase();
}






  seltipoliq();




//Función limpiar

function limpiar()

{

  $("#codigo").val("");
  $("#codigo_proveedor").val("");
  $("#nombre").val("");
  $("#costo_compra").val("");
  $("#saldo_iniu").val("");
  $("#valor_iniu").val("");
  $("#saldo_finu").val("");
  $("#valor_finu").val("");
  $("#stock").val("");
  $("#comprast").val("");
  $("#ventast").val("");
  $("#portador").val("");
  $("#merma").val("");
  $("#valor_venta").val("");
  $("#imagenmuestra").attr("src","");
  $("#imagenactual").val("");
  $("#print").hide();
  $("#idliquidacion").val("");
  $("#Nnombre").val("");
  $("#codigosunat").val("");
  $("#ccontable").val("");
  $("#precio2").val("");
  $("#precio3").val("");
    $("#cicbper").val("");
    $("#nticbperi").val("");
    $("#ctticbperi").val("");
    $("#mticbperu").val("");
    $("#lote").val("");
    $("#marca").val("");
    $("#fechafabricacion").val("");
    $("#fechavencimiento").val("");
    $("#procedencia").val("");
    $("#fabricante").val("");
    $("#registrosanitario").val("");
    $("#fechaingalm").val("");
    $("#fechafinalma").val("");
    $("#proveedor").val("");
    $("#seriefaccompra").val("");
    $("#numerofaccompra").val("");
    $("#fechafacturacompra").val("");
    $("#preview").empty();
    $("#limitestock").val("");
    $("#tipoitem").val("productos");
    $("#equivalencia").val("");
    $("#factorc").val("");
    $("#fconversion").val("");
    $("#descripcion").val("");

    $(".filas").remove();
}



function limpiaralmacen()

{

$("#nombrea").val("");

}




function mostrarcampos()

{

var x = document.getElementById("chk1").checked; 
var div = document.getElementById("masdatos");
if (x) {
  
  div.style.visibility = "visible";
}else{
  div.style.visibility = "hidden";
}


}




function limpiarcategoria()

{

$("#nombrec").val("");

}





function limpiarumedida()

{

$("#nombreu").val("");

$("#abre").val("");

$("#equivalencia2").val("");

}



//Función mostrar formulario

function mostrarform(flag)

{

  limpiar();

  if (flag)

  {

    $("#listadoregistros").hide();
    $("#listadoregistrosservicios").hide();
    $("#formularioregistros").show();
    $("#btnGuardar").prop("disabled",false);
    $("#btnagregar").hide();
    $("#preview").empty();

  }

  else

  {

    $("#listadoregistros").show();
    $("#listadoregistrosservicios").show();
    $("#formularioregistros").hide();
    $("#btnagregar").show();

  }

}



//Función cancelarform

function cancelarform()
{
  limpiar();
  mostrarform(false);
}





//Función Listar

function listar()

{
  var $idempresa=$("#idempresa").val();
  tabla=$('#tbllistado').dataTable(
  {
    "aProcessing": true,//Activamos el procesamiento del datatables
      "aServerSide": true,//Paginación y filtrado realizados por el servidor

      dom: 'Bfrtip',//Definimos los elementos del control de tabla


      buttons: [              

                {

                messageTop: "PRODUCTOS" ,  
                extend:    'copyHtml5',
                text:      '<i class="fa fa-files-o"></i>',
                titleAttr: 'Copy'

            },

            {
                extend:    'excelHtml5',
                text:      '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Excel'
            },
            {

                extend:    'csvHtml5',
                text:      '<i class="fa fa-file-text-o"></i>',
                titleAttr: 'CSV'

            },

            {
                extend:    'pdfHtml5',
                text:      '<i class="fa fa-file-pdf-o"></i>',
                titleAttr: 'PDF'

            }

            ],

    "ajax":
        {
          url: '../ajax/liquidacion.php?op=listar&idempresa='+$idempresa,
          type : "get",
          dataType : "json",            
          error: function(e){
          console.log(e.responseText);  

          }

        },

    "bDestroy": true,
    "iDisplayLength": 5,//Paginación
      "order": [[ 4, "desc" ]]//Ordenar (columna,orden)
  }).DataTable();

}











//Función para guardar o editar



function guardaryeditar(e)

{

  e.preventDefault(); //No se activará la acción predeterminada del evento
  $("#btnGuardar").prop("disabled",true);
  var formData = new FormData($("#formulario")[0]);



  $.ajax({
    url: "../ajax/liquidacion.php?op=guardaryeditar",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,

      success: function(datos)
      {                    

            bootbox.alert(datos);           
            mostrarform(false);
            tabla.ajax.reload();
      }

  });
 tabla.ajax.reload();
  limpiar();

}



function mostrar(idliquidacion)

{

  $.post("../ajax/liquidacion.php?op=mostrar",{idliquidacion : idliquidacion}, function(data, status)

  {

    data = JSON.parse(data);    

    mostrarform(true);

    $("#idliquidacion").val(data.idliquidacion);
    $("#idfamilia").val(data.idfamilia);
    $('#idfamilia').selectpicker('refresh');
    $("#idalmacen").val(data.idalmacen);
    $('#idalmacen').selectpicker('refresh');
    $("#unidad_medida").val(data.unidad_medida);
    $('#unidad_medida').selectpicker('refresh');
    $("#codigo_proveedor").val(data.codigo_proveedor);
    $("#codigo").val(data.codigo);
    $("#nombre").val(data.nombre);
    $("#unidad_medidad").val(data.unidad_medidad);
    $("#costo_compra").val(data.costo_compra);
    //$("#costo_compra").attr('readonly', true);
    $("#saldo_iniu").val(data.saldo_iniu);
    //$("#saldo_iniu").attr('readonly', true);
    $("#valor_iniu").val(data.valor_iniu);
    //$("#valor_iniu").attr('readonly', true);
    $("#saldo_finu").val(data.saldo_finu);
    //$("#saldo_finu").attr('readonly', true);
    $("#valor_finu").val(data.valor_finu);
    //$("#valor_finu").attr('readonly', true);
    $("#stock").val(data.stock);
    //$("#stock").attr('readonly', true);
    $("#comprast").val(data.comprast);
    //$("#comprast").attr('readonly', true);
    $("#ventast").val(data.ventast);
    //$("#ventast").attr('readonly', true);
    $("#portador").val(data.portador);
    $("#merma").val(data.merma);
    $("#valor_venta").val(data.precio_venta);
    $("#imagenmuestra").show();

    if (data.imagen=="") {
        $("#imagenmuestra").attr("src","../files/liquidacions/simagen.png");
        $("#imagenactual").val(data.imagen);
        $("#imagen").val("");
    }else{
        $("#imagenmuestra").attr("src","../files/liquidacions/" + data.imagen);
        $("#imagenactual").val(data.imagen);
        $("#imagen").val("");
    }



    $("#codigosunat").val(data.codigosunat);
    $("#ccontable").val(data.ccontable);
    $("#precio2").val(data.precio2);
    $("#precio3").val(data.precio3);

    $("#stockprint").val(data.stock);
    $("#codigoprint").val(data.codigo);
    $("#precioprint").val(data.precio_venta);

    //Nuevos campos

    $("#cicbper").val(data.cicbper);
    $("#nticbperi").val(data.nticbperi);
    $("#ctticbperi").val(data.ctticbperi);
    $("#mticbperu").val(data.mticbperu);
    //Nuevos campos

    $("#codigott").val(data.codigott);
    $('#codigott').selectpicker('refresh');
    $("#desctt").val(data.desctt);
    $('#desctt').selectpicker('refresh');

    $("#codigointtt").val(data.codigointtt);

    $('#codigointtt').selectpicker('refresh');

    $("#nombrett").val(data.nombrett);

    $('#nombrett').selectpicker('refresh');


    var stt=$("#stock").val();
    var fc=$("#factorc").val();

    var stfc= stt * fc;

    $("#fconversion").val(stfc);

    //Nuevos campos

    

    generarbarcode();



  })

}



function mayus(e) {

     e.value = e.value.toUpperCase();

}


document.onkeypress = stopRKey; 



function stopRKey(evt) {
var evt = (evt) ? evt : ((event) ? event : null);
var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
if ((evt.keyCode == 13) && (node.type=="text")) {return false;}
}





function focusfamil(){

  document.getElementById('idfamilia').focus();  

}




function focusnomb(e, field) {

    if(e.keyCode===13  && !e.shiftKey){

       document.getElementById('nombre').focus();  

    }

 }


function refrescartabla()

{

tabla.ajax.reload();
tablas.ajax.reload();

}








  function agregarCliente(e)
  {
    var documento=$("#dnir").val();
    if(e.keyCode===13  && !e.shiftKey){
    $.post("../ajax/liquidacion.php?op=listarClientesliqui&doc="+documento, function(data,status)
    {
       data=JSON.parse(data);
       if (data != null){
       $('#idcliente').val(data.idpersona);
       $("#datoscli").val(data.razon_social);
       
       $('#suggestions').fadeOut();
       
        }else{

       $('#idcliente').val("");
       $("#datoscli").val("No existe");

       //$("#ModalNcliente").modal('show');
       //$("#nruc").val($("#numero_documento2").val());
       $('#suggestions').fadeOut();
       
        }
    });
 }
}


 $(document).ready(function() {
    $('#datoscli').on('keyup', function() {
        var key = $(this).val();  
        var dataString = 'key='+key;
    $.ajax({
            type: "POST",
            url: "../ajax/liquidacion.php?op=buscarclientepred",
            data: dataString,
            success: function(data) {
                $('#suggestions').fadeIn().html(data);
                $('.suggest-element').on('click', function(){
                        var id = $(this).attr('id');
                        $('#dnir').val($('#'+id).attr('ndocumento'));
                        $('#datoscli').val($('#'+id).attr('nombrecli'));
                        $("#idcliente").val(id);
                        $('#suggestions').fadeOut();
                        return false;
                });
            }
        });
    });
}); 



 $(document).ready(function() {
    $('#dnir').on('keyup', function() {
        var key = $(this).val();  
        var dataString = 'key='+key;
    $.ajax({
            type: "POST",
            url: "../ajax/liquidacion.php?op=buscarclientepred",
            data: dataString,
            success: function(data) {
                $('#suggestions').fadeIn().html(data);
                $('.suggest-element').on('click', function(){
                        var id = $(this).attr('id');
                        $('#dnir').val($('#'+id).attr('ndocumento'));
                        $('#datoscli').val($('#'+id).attr('nombrecli'));
                        $("#idcliente").val(id);
                        $('#suggestions').fadeOut();
                        return false;
                });
            }
        });
    });
}); 





 function focusTest(el)
{
   el.select();
}





init();