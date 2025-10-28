var tabla;
 
//Función que se ejecuta al inicio
function init(){
    mostrarform(false);
    listar();
 
     $("#formulario").on("submit",function(e)
     {
         guardaryeditarGuia(e);  
     });

        
     $.post("../ajax/guiaremision.php?op=selectSerie", function(r)
        {
        $("#serie").html(r);
        $("#serie").selectpicker('refresh');
        });

     //Obtenemos la fecha actual
    $("#fecha_emision").prop("disabled",false);
    var now = new Date();
    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);
    var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
    $('#fecha_emision').val(today);


    cont=0;
}


function incrementarNum(){
    var serie=$("#serie option:selected").val();
    $.post("../ajax/guiaremision.php?op=autonumeracion&ser="+serie, function(r){

       var n2=pad(r,0);
       $("#numero_guia").val(n2);

       var SerieReal = $("#serie option:selected").text();
        $("#SerieReal").val(SerieReal);
    }); 
}

function numero(){

       $("#numero_guia").val("000000ssdf");
}

//Función para poner ceros antes del numero siguiente de la factura
function pad (n, length){
    var n= n.toString();
while(n.length<length)
    n="0" + n;
    return n;
}


//Función mostrar formulario
function mostrarform(flag)
{
   
    //limpiar();

    if (flag)
    {
        $("#listadoregistros").hide();
        $("#formularioregistros").show();
        $("#btnagregar").hide();
        $("#btnGuardar").hide();
        $("#btnCancelar").show();
        $("#btnAgregarArt").show();
        listarComprobante();
        incrementarNum();
    }
    else
    {
        $("#listadoregistros").show();
        $("#formularioregistros").hide();
        $("#btnagregar").show();
        

    }
}

//Función para aceptar solo numeros con dos decimales
  function NumCheck(e, field) {
  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46
  key = e.keyCode ? e.keyCode : e.which
  // backspace
          if (key == 8) return true;
          if (key == 9) return true;
        if (key > 47 && key < 58) {
          if (field.val() === "") return true;
          var existePto = (/[.]/).test(field.val());
          if (existePto === false){
              regexp = /.[0-9]{10}$/;
          }
          else {
            regexp = /.[0-9]{2}$/;
          }
          return !(regexp.test(field.val()));
        }

        if (key == 46) {
          if (field.val() === "") return false;
          regexp = /^[0-9]+$/;
          return regexp.test(field.val());
        }
        return false;
}

//Funcion para mayusculas
function mayus(e) {
     e.value = e.value.toUpperCase();
}
//=========================




//Función para mostrar en Modal las facturas 
function listarComprobante()
{

    tabla=$('#tblacomprobante').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip',//Definimos los elementos del control de tabla
        buttons: [                
                     
                ],
        "ajax":
                {
                  
                    url: '../ajax/guiaremision.php?op=listarComprobante',
                    //url: '../ajax/notac.php?op=listarComprobante',
                    type : "get",
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


function agregarComprobante(idcomprobante,tdcliente,ndcliente,rzcliente, domcliente,tipocomp, numerodoc, subtotal, igv, total)
  {
    
     if (idcomprobante!="")
    {
        
        $('#idcomprobante').val(idcomprobante);
        $('#numero_comprobante').val(numerodoc);
        $('#pllegada').val(domcliente);
        $('#destinatario').val(rzcliente);
        $('#nruc').val(ndcliente);

    $("#btnGuardar").show();
    
    }
    else
    {
        alert("Error al ingresar el detalle, revisar los datos del cliente");
    }

 //========================================================================

    $.post("../ajax/guiaremision.php?op=detalle&id="+idcomprobante,function(r){
        $("#detalles").html(r);
    });

//============================================================================
$("#myModalComprobante").modal('hide');
}

function guardaryeditarGuia(e)
{
    e.preventDefault(); //No se activará la acción predeterminada del evento
    //$("#btnGuardar").prop("disabled",true);
        
    var formData = new FormData($("#formulario")[0]);
 
    $.ajax({
        url: "../ajax/guiaremision.php?op=guardaryeditarGuia",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,

        success: function(datos)
        {                    

              bootbox.alert(datos);
              mostrarform(false);
              listar();
        }
    });
            limpiar();
    
}


//Función Listar
function listar()
{
    tabla=$('#tbllistado').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip',//Definimos los elementos del control de tabla
        buttons: [                
                    'copyHtml5',
                    'excelHtml5',
                    'csvHtml5',
                    'pdf'
                ],
        "ajax":
                {
                    url: '../ajax/guiaremision.php?op=listar',
                    type : "get",
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
 
function limpiar(){


    $("#idguia").val("");
    //$("#numero_documento").val("");
    $("#numero_guia").val("");
    $("#pllegada").val("");
    $("#destinatario").val("");
    $("#nruc").val("");
    $("#numero_comprobante").val("");
    $("#serie").val("");
    $(".filas").remove();

    //para que los cheks se deseleccionen
    var ch=document.getElementsByName("motivo");
    var i;
    for(i=0; i< ch.length; i++){
        ch[i].checked=false;
    }
    
 
    //Obtenemos la fecha actual
    $("#fecha_emision").prop("disabled",false);
    var now = new Date();
    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);
    var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
    $('#fecha_emision').val(today);
    cont=0;
    }


//Función cancelarform
function cancelarform()
{
    limpiar();
    detalles=0;
    mostrarform(false);
}



init();