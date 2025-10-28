var fecha = new Date();
var ano = fecha.getFullYear();
var mes=fecha.getMonth();
var dia=fecha.getDate();

$("#ano").val(ano);
$("#mes").val(mes+1);
$("#dia").val(dia);

$idempresa=$("#idempresa").val();

listarValidarComprobantes();
listarValidar();

//Función Listar
function listarValidar()
{

    var $ano = $("#ano option:selected").text();
    var $mes = $("#mes option:selected").val();
    var $dia = $("#dia option:selected").val();
   // var $dia = "1";


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
                    url: '../ajax/factura.php?op=listarValidar&ano='+$ano+'&mes='+$mes+'&dia='+$dia+'&idempresa='+$idempresa,
                    type : "get",
                    dataType : "json",                      
                    error: function(e){
                    console.log(e.responseText);    
                    }
                },

         "rowCallback": 
         function( row, data ) {
            //$(row).addClass('selected');
            //$(row).id(0).addClass('selected');
        },

        "bDestroy": true,
        "iDisplayLength": 100,//Paginación
        "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
    }).DataTable();


//Funcion para actualizar la pagina cada 20 segundos.
// setInterval( function () {
// tabla.ajax.reload(null, false);
// }, 50000 );

}


function refrescartabla()
{
tabla.ajax.reload();
}


function enviarcorreo(idfactura) {
	let mmcliente = "";
	$.post("../ajax/factura.php?op=traercorreocliente&iddff=" + idfactura, function(data, status) {
		data = JSON.parse(data);
		$("#correo").val(data.email);
	});
	mmcliente = $("#correo").val();

	Swal.fire({
		title: "Ingresa el correo para enviarlo " + mmcliente + "?",
		showCancelButton: true,
		confirmButtonText: "Enviar",
		cancelButtonText: "Cancelar",
		input: "email",
		inputValue: mmcliente,
		inputValidator: (value) => {
			if (!value) {
				return "Debe ingresar un correo válido";
			}
		},
	}).then((result) => {
		if (result.isConfirmed) {
			let correo = result.value;
			$.post("../ajax/factura.php?op=enviarcorreo&idfact=" + idfactura + "&ema=" + correo, function(e) {
				Swal.fire({
					title: "Correo enviado",
					text: e,
					icon: "success",
				});
				tabla.ajax.reload();
			});
		}
	});
}



//Función para dar de baja registros
function baja(idfactura) {

    var f = new Date();
    cad = f.getHours() + ":" + f.getMinutes() + ":" + f.getSeconds();
  
    Swal.fire({
      title: 'Escriba el motivo de baja de la factura de la factura:',
      input: 'textarea',
      inputAttributes: {
        autocapitalize: 'off'
      },
      showCancelButton: true,
      confirmButtonText: 'Aceptar',
      cancelButtonText: 'Cancelar',
      showLoaderOnConfirm: true,
      preConfirm: (comentario) => {
        return fetch(`../ajax/factura.php?op=baja&comentario=${comentario}&hora=${cad}&idfactura=${idfactura}`)
          .then(response => {
            if (!response.ok) {
              throw new Error(response.statusText)
            }
            return response.json()
          })
          .catch(error => {
            Swal.showValidationMessage(
              `Request failed: ${error}`
            )
          })
      },
      allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: result.value.mensaje
        })
        tabla.ajax.reload();
      }
    });
  }
  




function botonmes(valor)
{
            
var $dia='';

switch (valor){

    case '1':
    $dia = valor;
    break

    case '2':
    $dia = valor;
    break

    case '3':
    $dia = valor;
    break

    case '4':
    $dia = valor;
    break

    case '5':
    $dia = valor;
    break

    case '6':
    $dia = valor;
    break

    case '7':
    $dia = valor;
    break

    case '8':
    $dia = valor;
    break

    case '9':
    $dia = valor;
    break

    case '10':
    $dia = valor;
    break

    case '11':
    $dia = valor;
    break

    case '12':
    $dia = valor;
    break

    case '13':
    $dia = valor;
    break

    case '14':
    $dia = valor;
    break

    case '15':
    $dia = valor;
    break

    case '16':
    $dia = valor;
    break

    case '17':
    $dia = valor;
    break

    case '18':
    $dia = valor;
    break

    case '19':
    $dia = valor;
    break

    case '20':
    $dia = valor;
    break

    case '21':
    $dia = valor;
    break

    case '22':
    $dia = valor;
    break

    case '23':
    $dia = valor;
    break

    case '24':
    $dia = valor;
    break

    case '25':
    $dia = valor;
    break

    case '26':
    $dia = valor;
    break

    case '27':
    $dia = valor;
    break

    case '28':
    $dia = valor;
    break

    case '29':
    $dia = valor;
    break

    case '30':
    $dia = valor;
    break

    case '31':
    $dia = valor;
    break

    
    default:
    $dia = '1';

    
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
                    url: '../ajax/factura.php?op=listarValidar&ano='+$ano+'&mes='+$mes+'&dia='+$dia,
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



//Función Listar
function listarValidarComprobantes()
{
    var $estadoC = $("#estadoC").val();
    //var $estado = "1";
    tabla=$('#tbllistadoEstado').dataTable(
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
                    url: '../ajax/ventas.php?op=listarValidarComprobantes&estadoFinal='+$estadoC,
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
        "iDisplayLength": 8,//Paginación
        "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
    }).DataTable();
//Funcion para actualizar la pagina cada 20 segundos.
// setInterval( function () {
// tabla.ajax.reload(null, false);
// }, 5000 );
}


//Funcion para enviararchivo xml a SUNAT
function mostrarxml(idfactura)
{

    $.post("../ajax/factura.php?op=mostrarxml", {idfactura : idfactura}, function(e)
    {
        data=JSON.parse(e);
        
        if (data.rutafirma) {
            var rutacarpeta=data.rutafirma;
            $("#modalxml").attr('src',rutacarpeta);
            $("#modalPreviewXml").modal("show");
            $("#bajaxml").attr('href',rutacarpeta); 

            Swal.fire({
              title: 'Cabecera y detalle del XML',
              text: data.cabextxml,
              icon: 'info',
              confirmButtonText: 'OK'
            });

        }else{
            Swal.fire({
              title: 'Error',
              text: data.cabextxml,
              icon: 'error',
              confirmButtonText: 'OK'
            });
        }   

    });

}




//Funcion para enviararchivo xml a SUNAT
function mostrarrpta(idfactura)
{

            $.post("../ajax/factura.php?op=mostrarrpta", {idfactura : idfactura}, function(e)
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
function generarxml(idfactura) {
    Swal.fire({
      title: '¿Está seguro de generar el archivo XML?',
      showCancelButton: true,
      confirmButtonText: 'Sí',
      cancelButtonText: 'No'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("../ajax/factura.php?op=generarxml", {idfactura : idfactura}, function(e) {
          data=JSON.parse(e);
          Swal.fire({
            title: 'Se ha generado el archivo XML',
            html: 'Haga clic en el siguiente enlace para descargar el archivo XML:<br><a href="'+data.cabextxml+'" download="'+data.cabxml+'">'+data.cabxml+'</a>',
            icon: 'success'
          });
          tabla.ajax.reload();
        });
        refrescartabla();
      }
    })
  }
  



//Función para enviar respuestas por correo 
//Función para enviar respuestas por correo 
function enviarxmlSUNAT(idfactura)
{
    Swal.fire({
        title: '¿Está seguro de enviar archivo firmado a SUNAT?',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/factura.php?op=enviarxmlSUNAT", {idfactura : idfactura}, function(e){
                bootbox.alert(e);
                tabla.ajax.reload();   
            }); 
            refrescartabla();
        }
    });
}






//Funcion para enviararchivo xml a SUNAT
function regenerarxml(idfactura)
{
    swal.fire({
        title: '¿Está Seguro de generar el archivo XML?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'Cancelar',
        cancelButtonColor: '#d33',
        confirmButtonColor: '#3085d6'
    }).then((result) => {
        if(result.value)
        {
            $.post("../ajax/factura.php?op=regenerarxml", {idfactura : idfactura}, function(e)
            {
                data=JSON.parse(e);
                swal.fire({
                    title: 'Archivo XML generado',
                    html: 'Se ha generado el archivo XML: <a href="'+data.cabextxml+'" download="'+data.cabxml+'">ARCHIVO XML: '+data.cabxml+'</a><br>De clic en el nombre para descargarlo.',
                    icon: 'success'
                });
                tabla.ajax.reload();
            }); 
            refrescartabla();
        }
    })
}




//Función para enviar xml a sunat
function enviarxmlSUNATbajas(idfactura)
{
    swal.fire({
        title: "¿Está seguro de enviar archivo firmado a SUNAT?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, enviar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if(result.isConfirmed)
        {
            $.post("../ajax/factura.php?op=enviarxmlSUNATbajas", {idfactura : idfactura}, function(e){
                swal.fire({
                    title: e,
                    icon: "success"
                });
                tabla.ajax.reload();   
            }); 
            refrescartabla();
        }
    })
}









function envioautomatico(valor)
{

    var $ano = $("#ano option:selected").text();
    var $mes = $("#mes option:selected").val();
    var $dia = $("#dia option:selected").val();
    var opc = $("#opcion option:selected").val();

    var idocu = document.getElementsByName("idoculto[]");
    var stocu = document.getElementsByName("estadoocu[]");
    var chfact = document.getElementsByName("chid[]");

        for (var i = 0; i < idocu.length; i++) {
             var idA=idocu[i].value;
             var ESoc=stocu[i].value;
             var Chhid=chfact[i].checked;

    $.ajax({
        url: '../ajax/factura.php?op=regenerarxmlEA&anO='+$ano+'&meS='+$mes+'&diA='+$dia+'&idComp='+idA+'&SToc='+ESoc+'&Ch='+Chhid+'&opt='+opc,
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


function tipoenvio()
{
    if ($("#fenvio").val()=='0')
    {
      envioautomatico();  
    }
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



//Función para enviar respuestas por correo 
function consultarcdr(idfactura)
{
    swal.fire({
        title: "Se consultará si existe el comprobante en SUNAT.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, continuar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if(result.isConfirmed)
        {
            $.post("../ajax/factura.php?op=consultarcdr", {idfactura : idfactura}, function(e){
                //data2=JSON.parse(e);
                swal.fire({
                    title: e,
                    icon: "success",
                    confirmButtonText: "Aceptar"
                });
                tabla.ajax.reload();   
            }); 
             refrescartabla();
        }

    })
}




function cambiartarjetadc(idfactura)
{
    Swal.fire({
        title: 'Desea modificar pago con tarjeta?',
        input: 'select',
        inputOptions: {
            '1': 'SI',
            '0': 'NO'
        },
        inputPlaceholder: 'Seleccione',
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe seleccionar una opción'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/factura.php?op=cambiartarjetadc_&opcion="+result.value, {idfactura : idfactura}, function(e){
                Swal.fire({
                    title: 'Éxito!',
                    text: e,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
                tabla.ajax.reload();
            }); 
        }
    });
}



function montotarjetadc(idfactura) {
    swal.fire({
      title: "Desea modificar monto de pago con tarjeta",
      input: "number",
      inputValue: "0",
      showCancelButton: true,
      confirmButtonText: "Aceptar",
      cancelButtonText: "Cancelar",
      inputValidator: (value) => {
        if (!value || value < 0) {
          return "Por favor ingrese un valor válido.";
        }
      },
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          "../ajax/factura.php?op=montotarjetadc_&monto=" + result.value,
          { idfactura: idfactura },
          function (e) {
            swal.fire({
              title: "¡Listo!",
              text: e,
              icon: "success",
            });
            tabla.ajax.reload();
          }
        );
      }
    });
  }
  






  function cambiartransferencia(idfactura) {
    Swal.fire({
      title: '¿Desea modificar pago con transferencia?',
      input: 'select',
      inputOptions: {
        '1': 'SI',
        '0': 'NO'
      },
      inputPlaceholder: 'Seleccione una opción',
      showCancelButton: true,
      confirmButtonText: 'Aceptar',
      cancelButtonText: 'Cancelar',
      inputValidator: (value) => {
        return new Promise((resolve) => {
          if (value !== '') {
            resolve();
          } else {
            resolve('Debe seleccionar una opción');
          }
        });
      },
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('../ajax/factura.php?op=cambiartransferencia&opcion=' + result.value, { idfactura: idfactura }, function (e) {
          Swal.fire({
            icon: 'success',
            title: '¡Correcto!',
            text: e,
          });
          tabla.ajax.reload();
        });
      }
    });
  }
  




  function montotransferencia(idfactura) {
    Swal.fire({
      title: 'Desea modificar monto de transferencia',
      input: 'number',
      inputValue: '0',
      showCancelButton: true,
      confirmButtonText: 'Aceptar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("../ajax/factura.php?op=montotransferencia&monto=" + result.value, {
          idfactura: idfactura
        }, function (e) {
          Swal.fire({
            title: 'Información',
            text: e,
            icon: 'success'
          });
          tabla.ajax.reload();
        });
      }
    });
  }




