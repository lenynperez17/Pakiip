var tabla;
var ubd;
var ubp;
var ubdi;
var ubiparti


var ubd2;
var ubp2;
var ubdi2;
var ubiparti2
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
        ///$("#serie").selectpicker('refresh');
        });

   
     //Obtenemos la fecha actual
    $("#fecha_emision").prop("disabled",false);
    var now = new Date();
    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);
    var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
    $('#fecha_emision').val(today);
    $('#fechatraslado').val(today);
    $('#fechacomprobante').val(today);


      $.post("../ajax/articulo.php?op=selectUnidad", function(r){
              $("#umedidapbruto").html(r);
              //$('#umedidapbruto').selectpicker('refresh');
    });


       $.post("../ajax/guiaremision.php?op=selectDepartamento", function(r){
            $("#ubdepartamento1").html(r);
           // $('#ubdepartamento1').selectpicker('refresh');
    });


       $.post("../ajax/guiaremision.php?op=selectDepartamento", function(r){
            $("#ubdepartamento2").html(r);
            //$('#ubdepartamento2').selectpicker('refresh');
    });

    cont=0;
}


       function llenarprovin(){
        var iddepartamento=$("#ubdepartamento1 option:selected").val();
        $.post("../ajax/guiaremision.php?op=selectprovinc&idd="+iddepartamento, function(r){
       $("#ubprovincia1").html(r);
       //$('#ubprovincia1').selectpicker('refresh');
       $("#ubprovincia1").val(""); 
        });
        
    }


    function llenarprovin2(){
        var iddepartamento=$("#ubdepartamento2 option:selected").val();
        $.post("../ajax/guiaremision.php?op=selectprovinc&idd="+iddepartamento, function(r){
       $("#ubprovincia2").html(r);
       //$('#ubprovincia2').selectpicker('refresh');
       $("#ubprovincia2").val(""); 
        });
        
    }



    function llenardistri(){
    var iddistri=$("#ubprovincia1 option:selected").val();
    $.post("../ajax/guiaremision.php?op=selectDistrito&idc="+iddistri, function(r){
       $("#ubdistrito1").html(r);
       //$('#ubdistrito1').selectpicker('refresh');

    });
    }


        function llenardistri2(){
    var iddistri=$("#ubprovincia2 option:selected").val();
    $.post("../ajax/guiaremision.php?op=selectDistrito&idc="+iddistri, function(r){
       $("#ubdistrito2").html(r);
       //$('#ubdistrito2').selectpicker('refresh');

    });
    }



    function llenarubipp()
    {
       ubd=$("#ubdepartamento1").val();
       ubp=$("#ubdepartamento1").val();
       ubdi=$("#ubdistrito1").val();
       $("#ubigeopartida").val(ubd+ubp+ubdi); 

    }

    function llenarubipp2()
    {
       ubd2=$("#ubdepartamento2").val();
       ubp2=$("#ubdepartamento2").val();
       ubdi2=$("#ubdistrito2").val();
       $("#ubigeollegada").val(ubd2+ubp2+ubdi2); 

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
    tipocompr=$('#tipocomprobante').val();
    tabla=$('#tblacomprobante').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip',//Definimos los elementos del control de tabla
        buttons: [  
        //  {
        //         extend:    'copyHtml5',
        //         text:      '<i class="fa fa-files-o"></i>',
        //         titleAttr: 'Copy'
        //     },
        //     {
        //         extend:    'excelHtml5',
        //         text:      '<i class="fa fa-file-excel-o"></i>',
        //         titleAttr: 'Excel'
        //     },
        //     {
        //         extend:    'csvHtml5',
        //         text:      '<i class="fa fa-file-text-o"></i>',
        //         titleAttr: 'CSV'
        //     },
        //     {
        //         extend:    'pdfHtml5',
        //         text:      '<i class="fa fa-file-pdf-o"></i>',
        //         titleAttr: 'PDF'
        //     }              
                     
                ],
        "ajax":
                {
                  
                    url: '../ajax/guiaremision.php?op=listarComprobante&tip='+tipocompr,
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


function boletafactura()
{

    listarComprobante();
}






function agregarComprobante(idcomprobante,tdcliente,ndcliente,rzcliente, domcliente,tipocomp, 
    numerodoc, subtotal, igv, total, fechafactura, idpersona)
  {
    
     if (idcomprobante!="")
    {
        
        $('#idcomprobante').val(idcomprobante);
        $('#numero_comprobante').val(numerodoc);
        $('#pllegada').val(domcliente);
        $('#destinatario').val(rzcliente);
        $('#nruc').val(ndcliente);
        $('#fechacomprobante').val(fechafactura);
        $('#idpersona').val(idpersona);

    $("#btnGuardar").show();
    
    }
    else
    {
        alert("Error al ingresar el detalle, revisar los datos del cliente");
    }

 //========================================================================
    tipocompr=$('#tipocomprobante').val();
    $.post("../ajax/guiaremision.php?op=detalle&id="+idcomprobante+'&tipo2='+tipocompr,function(r){
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
              Swal.fire({
                icon: 'info',
                title: 'Notificación',
                text: datos,
                confirmButtonText: 'Aceptar'
              });
              mostrarform(false);
              listar();
              tabla.ajax.reload();
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
            //         {
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
    //$("#serie").val("");
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

            $("#fechatraslado").val(today);
            $("#rsocialtransportista").val("");
            $("#ructran").val("");
            $("#placa").val("");
            $("#marca").val("");
            $("#cinc").val("");
            $("#container").val("");
            $("#nlicencia").val("");
            $("#ncoductor").val("");
            $("#ocompra").val("");
            $("#npedido").val("");
            $("#vendedor").val("");
            $("#costmt").val("");
            $("#numero_comprobante").val("");
            $("#fechacomprobante").val(today);

            $("#observaciones").val("");
            $("#pesobruto").val("");
            $("#dniconduc").val("");





    }


//Función cancelarform
function cancelarform()
{
    limpiar();
    detalles=0;
    mostrarform(false);
}



function refrescartabla()
{
tabla.ajax.reload();
}



function generarxml(idguia)
{
    Swal.fire({
        title: '¿Está seguro de generar el archivo XML?',
        showDenyButton: true,
        confirmButtonText: `Sí`,
        denyButtonText: `No`,
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/guiaremision.php?op=generarxml", {idguia : idguia}, function(e) {
                data = JSON.parse(e);
                Swal.fire({
                    icon: 'info',
                    title: 'Archivo generado',
                    html: 'Se ha generardo el archivo XML: <a href="'+data.cabextxml+'" download=" '+data.cabxml+'">" ARCHIVO XML:   '+data.cabxml+'"</a> de clic en el nombre para descargarlo.',
                    confirmButtonText: 'Aceptar'
                });
                tabla.ajax.reload();
            }); 
            refrescartabla();
        }
    })
}




function enviarxmlS(idguia)
{
    Swal.fire({
        title: '¿Está seguro de enviar el archivo firmado a SUNAT?',
        showDenyButton: true,
        confirmButtonText: `Sí`,
        denyButtonText: `No`,
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/guiaremision.php?op=enviarxmlS", {idguia : idguia}, function(e){
                Swal.fire({
                    icon: 'info',
                    title: 'Notificación',
                    text: e,
                    confirmButtonText: 'Aceptar'
                });
                tabla.ajax.reload();   
            }); 
            refrescartabla();
        }
    }) 
}




function stopRKey(evt) {
var evt = (evt) ? evt : ((event) ? event : null);
var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
if ((evt.keyCode == 13) && (node.type=="text")) {return false;}
}


 function anular(e) {
          tecla = (document.all) ? e.keyCode : e.which;
          return (tecla != 13);
     }



init();