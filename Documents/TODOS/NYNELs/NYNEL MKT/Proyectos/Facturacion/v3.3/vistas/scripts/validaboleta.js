var fecha = new Date();
var ano = fecha.getFullYear();
var mes=fecha.getMonth();
var dia=fecha.getDate();

$("#ano").val(ano);
$("#mes").val(mes+1);
$("#dia").val(dia);

$idempresa=$("#idempresa").val();


listarValidar()


function listarValidar()
{

	var $ano = $("#ano option:selected").text();
    var $mes = $("#mes option:selected").val();
    var $dia = $("#dia option:selected").val();



    tabla=$('#tbllistado').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip',//Definimos los elementos del control de tabla

        "processing": true,
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
                    url: '../ajax/boleta.php?op=listarValidar&ano='+$ano+'&mes='+$mes+'&dia='+$dia+'&idempresa='+$idempresa,
                    type : "get",
                    dataType : "json",                      
                    error: function(e){
                    console.log(e.responseText);    
                    }
                },
        "bDestroy": true,
        "iDisplayLength": 100,//Paginación
        "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
    }).DataTable();

        // setInterval( function () {
        // tabla.ajax.reload(null, false);
        // }, 50000 );
 }


 function enviarcorreo(idboleta)
 {
     swal.fire({
         title: '¿Está seguro de enviar correo al cliente?',
         icon: 'question',
         showCancelButton: true,
         confirmButtonColor: '#3085d6',
         cancelButtonColor: '#d33',
         confirmButtonText: 'Sí, enviar',
         cancelButtonText: 'Cancelar'
     }).then((result) => {
         if (result.isConfirmed) {
             $.post("../ajax/boleta.php?op=enviarcorreo", {idboleta : idboleta}, function(e){
                 swal.fire({
                     title: 'Correo enviado',
                     text: e,
                     icon: 'success',
                     confirmButtonText: 'OK'
                 });
                 tabla.ajax.reload();
             }); 
         }
     })
 }
 

function refrescartabla()
{
tabla.ajax.reload();
}

function baja(idboleta) {

    var f = new Date();
    cad = f.getHours() + ":" + f.getMinutes() + ":" + f.getSeconds();

    Swal.fire({
        title: 'Escriba el motivo de baja de la boleta.',
        input: 'textarea',
        inputPlaceholder: 'Ingrese aquí su comentario...',
        inputAttributes: {
            'aria-label': 'Ingrese aquí su comentario'
        },
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: (comentario) => {
            return $.post("../ajax/boleta.php?op=baja&comentario=" + comentario + "&hora=" + cad, { idboleta: idboleta })
                .then(response => {
                    return response;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Error: ${error}`);
                })
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(result.value);
            tabla.ajax.reload();
        }
    });
}



function botonmes(valor)
{
            
var $vv='';

switch (valor){

    case '1':
    $vv = valor;
    break

    case '2':
    $vv = valor;
    break

    case '3':
    $vv = valor;
    break

    case '4':
    $vv = valor;
    break

    case '5':
    $vv = valor;
    break

    case '6':
    $vv = valor;
    break

    case '7':
    $vv = valor;
    break

    case '8':
    $vv = valor;
    break

    case '9':
    $vv = valor;
    break

    case '10':
    $vv = valor;
    break

    case '11':
    $vv = valor;
    break

    case '12':
    $vv = valor;
    break

    case '13':
    $vv = valor;
    break

    case '14':
    $vv = valor;
    break

    case '15':
    $vv = valor;
    break

    case '16':
    $vv = valor;
    break

    case '17':
    $vv = valor;
    break

    case '18':
    $vv = valor;
    break

    case '19':
    $vv = valor;
    break

    case '20':
    $vv = valor;
    break

    case '21':
    $vv = valor;
    break

    case '22':
    $vv = valor;
    break

    case '23':
    $vv = valor;
    break

    case '24':
    $vv = valor;
    break

    case '25':
    $vv = valor;
    break

    case '26':
    $vv = valor;
    break

    case '27':
    $vv = valor;
    break

    case '28':
    $vv = valor;
    break

    case '29':
    $vv = valor;
    break

    case '30':
    $vv = valor;
    break

    case '31':
    $vv = valor;
    break

    
    default:
    $vv = '1';

    
}


    var $ano = $("#ano").val();
    var $mes = $("#mes").val();

    tabla=$('#tbllistado').dataTable(
    {
        "aProcessing": true,//Activamos el procesamiento del datatables
        "aServerSide": true,//Paginación y filtrado realizados por el servidor
        dom: 'Bfrtip',//Definimos los elementos del control de tabla
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
                    url: '../ajax/boleta.php?op=listarValidar&ano='+$ano+'&mes='+$mes+'&dia='+$vv,
                    type : "get",
                    dataType : "json",                      
                    error: function(e){
                    console.log(e.responseText);    
                    }
                },

         "rowCallback": 
         function( row, data ) {
        },

        "bDestroy": true,
        "iDisplayLength": 4,//Paginación
        "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
    }).DataTable();

}


        setInterval( function () {
        tabla.ajax.reload(null, false);
        }, 50000 );





//Funcion para enviararchivo xml a SUNAT
function mostrarxml(idboleta) {
    $.post("../ajax/boleta.php?op=mostrarxml", { idboleta: idboleta }, function(e) {
      data = JSON.parse(e);
  
      if (data.rutafirma) {
        var rutacarpeta = data.rutafirma;
        $("#modalxml").attr("src", rutacarpeta);
        $("#modalPreviewXml").modal("show");
        $("#bajaxml").attr("href", rutacarpeta);
        Swal.fire(data.cabextxml);
      } else {
        Swal.fire(data.cabextxml);
      }
    });
  }
  

//Funcion para enviararchivo xml a SUNAT
function mostrarrpta(idboleta)
{

            $.post("../ajax/boleta.php?op=mostrarrpta", {idboleta : idboleta}, function(e)
            {
                data=JSON.parse(e);
                //bootbox.alert('Se ha generardo el archivo XML: '+data.rpta);
              var rptaS=data.rutaxmlr;
              $("#modalxml").attr('src',rptaS);
              $("#modalPreviewXml").modal("show");
              $("#bajaxml").attr('href',rptaS);  

            }
            ); 
}

//Funcion para enviararchivo xml a SUNAT
function generarxml(idboleta) {
    swal.fire({
      title: '¿Está seguro de generar el archivo XML?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí',
      cancelButtonText: 'No'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('../ajax/boleta.php?op=generarxml', {idboleta: idboleta}, function(e) {
          data = JSON.parse(e);
          swal.fire({
            title: 'Se ha generado el archivo XML',
            html: 'Archivo XML: <a href="' + data.cabextxml + '" download="' + data.cabxml + '">' + data.cabxml + '</a>. De clic en el nombre para descargarlo.',
            icon: 'success'
          });
          tabla.ajax.reload();
        });
      }
    });
  }
  



//Función para enviar respuestas por correo 
function enviarxmlSUNAT(idboleta) {
    Swal.fire({
        title: '¿Está seguro de enviar archivo firmado a SUNAT?',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/boleta.php?op=enviarxmlSUNAT", {idboleta : idboleta}, function(e){
                Swal.fire({
                    title: e,
                    icon: 'success'
                });
                tabla.ajax.reload();   
            }); 
            refrescartabla();
        }
    });
}





//Funcion para enviararchivo xml a SUNAT
function regenerarxml(idboleta) {
    Swal.fire({
        title: "¿Está seguro de generar el archivo XML?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/boleta.php?op=regenerarxml", {idboleta : idboleta}, function(e) {
                data=JSON.parse(e);
                Swal.fire({
                    icon: "success",
                    title: "Archivo generado",
                    html: 'Se ha generado el archivo XML: <a href="'+data.cabextxml+'" download="'+data.cabxml+'">'+data.cabxml+'</a>. Haga clic en el nombre para descargarlo.'
                });
                tabla.ajax.reload();
            }); 
        }
    });
}



//Función para enviar respuestas por correo 
function enviarxmlSUNATbajas(idboleta) {
    Swal.fire({
      title: '¿Está seguro de enviar archivo firmado a SUNAT?',
      showCancelButton: true,
      confirmButtonText: 'Sí',
      cancelButtonText: 'No'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("../ajax/boleta.php?op=enviarxmlSUNATbajas", {idboleta : idboleta}, function(e){
          Swal.fire({
            title: e,
            icon: 'info'
          });
          tabla.ajax.reload();   
        }); 
        refrescartabla();
      }
    });
    tabla.ajax.reload();
    refrescartabla();     
  }
  




function cambiotipoenvio()
{
    var  tipo=$("#fenvio").val();
    
if (tipo=='1')
    {
      document.getElementById('formaenvio').style.display = 'none';  
    }else{
        document.getElementById('formaenvio').style.display = 'inline';  
    }
}


            function refrescartabla()
            {
            tabla.ajax.reload();
            }

        function tipoenvio()
        {
            if ($("#fenvio").val()=='0')
            {
              envioautomatico();  
            }
        }


function envioautomatico()
{

    var $ano = $("#ano option:selected").text();
    var $mes = $("#mes option:selected").val();
    var $dia = $("#dia option:selected").val();

    var idocu = document.getElementsByName("idoculto[]");
    var stocu = document.getElementsByName("estadoocu[]");
    var chfact = document.getElementsByName("chid[]");

        for (var i = 0; i < idocu.length; i++) {
             var idA=idocu[i].value;
             var ESoc=stocu[i].value;
             var Chhid=chfact[i].checked;

    $.ajax({
        url: '../ajax/boleta.php?op=regenerarxmlEA&anO='+$ano+'&meS='+$mes+'&diA='+$dia+'&idComp='+idA+'&SToc='+ESoc+'&Ch='+Chhid,
        type: "POST",
        contentType: false,
        processData: false,
        success: function(datos)
        {                    
            refrescartabla();
        }
    });

     }//Fin de for

}

function marcartn()
{

    var idocu = document.getElementsByName("idoculto[]");
    var chfact = document.getElementsByName("chid[]");

    if ($("#marcar").val()=='0') {
        for (var i = 0; i < idocu.length; i++) {
             chfact[i].checked=true; }
    }else{
        for (var i = 0; i < idocu.length; i++) {
             chfact[i].checked=false; }
    }

        

}


function consultarcdr(idboleta) {
    swal.fire({
        title: "Se consultará si existe el comprobante en SUNAT.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, continuar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/boleta.php?op=consultarcdr", { idboleta: idboleta }, function(e) {
                swal.fire({
                    title: "Resultado de la consulta",
                    text: e,
                    icon: "info"
                });
                tabla.ajax.reload();
            });
            refrescartabla();
        }
    });
    tabla.ajax.reload();
    refrescartabla();
}





function cambiartarjetadc(idboleta) {
    swal.fire({
      title: "Desea modificar pago con tarjeta",
      input: 'select',
      inputOptions: {
        '1': 'SI',
        '0': 'NO'
      },
      inputPlaceholder: 'Seleccione una opción',
      showCancelButton: true,
      confirmButtonText: 'Aceptar',
      cancelButtonText: 'Cancelar',
    }).then((result) => {
      if (result.value) {
        $.post("../ajax/boleta.php?op=cambiartarjetadc_&opcion=" + result.value, { idboleta: idboleta }, function (e) {
          swal.fire({
            title: 'Mensaje',
            text: e,
            icon: 'info',
            confirmButtonText: 'Aceptar'
          });
          tabla.ajax.reload();
        });
      }
    });
  }
  




  function montotarjetadc(idboleta)
  {
      swal.fire({
          title: "Desea modificar monto de pago con tarjeta",
          input: "number",
          inputLabel: "Nuevo monto",
          showCancelButton: true,
          inputValidator: (value) => {
              if (!value || isNaN(value)) {
                  return "Por favor, ingrese un número válido";
              }
          },
          preConfirm: (monto) => {
              return $.post("../ajax/boleta.php?op=montotarjetadc_&monto="+monto, {idboleta : idboleta})
                  .then((e) => {
                      swal.fire({
                          icon: "success",
                          title: "Monto de pago con tarjeta modificado",
                          text: e
                      });
                      tabla.ajax.reload();
                  })
                  .catch((error) => {
                      swal.fire({
                          icon: "error",
                          title: "Error al modificar el monto de pago con tarjeta",
                          text: error
                      });
                  });
          }
      });
  }
  





  function cambiartransferencia(idboleta) {
    swal.fire({
      title: "¿Desea modificar pago con transferencia?",
      input: "select",
      inputValue: "1",
      inputOptions: {
        "1": "SI",
        "0": "NO"
      },
      showCancelButton: true,
      confirmButtonText: "Aceptar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
      inputValidator: (value) => {
        if (!value) {
          return "Debe seleccionar una opción";
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("../ajax/boleta.php?op=cambiartransferencia&opcion="+result.value, {idboleta : idboleta}, function(e){
          swal.fire(e);
          tabla.ajax.reload();
        }); 
      }
    });
  }
  
  function montotransferencia(idboleta) {
    swal.fire({
      title: "¿Desea modificar monto de transferencia?",
      input: "number",
      inputValue: "0",
      showCancelButton: true,
      confirmButtonText: "Aceptar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
      inputValidator: (value) => {
        if (!value || value < 0) {
          return "Debe ingresar un valor válido";
        }
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("../ajax/boleta.php?op=montotransferencia&monto="+result.value, {idboleta : idboleta}, function(e){
          swal.fire(e);
          tabla.ajax.reload();
        }); 
      }
    });
  }
  

