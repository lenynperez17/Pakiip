var tabla;

//Función que se ejecuta al inicio
function init(){

	mostrarform(false);
	
	$("#formulario").on("submit",function(e)
	{
		guardaryeditar(e);	
	})


	$.post("../ajax/sueldoBoleta.php?op=cargarempresas", function(r){
              $("#idempresab").html(r);
              //$('#idempresab').selectpicker('refresh');
  	});


  	$.post("../ajax/sueldoBoleta.php?op=cargarseguro", function(r){
              $("#idtipoSeguro").html(r);
              //$('#idtipoSeguro').selectpicker('refresh');
  	});



    limpiar();
    listar();
}


function mostrarform(flag)

{

  limpiar();

  if (flag)

  {

    $("#listadoregistros").hide();
    $("#formularioregistros").show();
    $("#btnGuardar").prop("disabled",false);

  }

  else

  {

    $("#listadoregistros").show();
    $("#formularioregistros").hide();

  }

}



function cancelarform()
{
  limpiar();
  mostrarform(false);
}




//Función limpiar
function limpiar()
{
	$("#idempleado").val("");
	$("#nombresE").val("");
	$("#apellidosE").val("");
	
	$("#ocupacion").val("");
	$("#tiporemuneracion").val("");
	$("#dni").val("");
	$("#autogenessa").val("");
	$("#cusspp").val("");
	$("#sueldoBruto").val("");
	$("#horasT").val("");
	$("#asigFam").val("");
	$("#trabNoct").val("");
	$("#nombreSeguro").val("");

	 document.getElementById('nombresE').focus();  
}



function focusTest(el)
{
   el.select();
}


function foco0()
{
    
       document.getElementById('descripcion').focus();  
    
}


function foco1(e)
{
    if(e.keyCode===13  && !e.shiftKey){
       document.getElementById('monto').focus();  
    }
}

function foco2(e)
{
    if(e.keyCode===13  && !e.shiftKey){
       document.getElementById('btnGuardar').focus();  
    }
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
		            
		            // 'excelHtml5',
		           
		            // 'pdf'
		        ],
		"ajax":
				{
					url: '../ajax/sueldoBoleta.php?op=listarempleado',
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
//Función para guardar o editar





function guardaryeditar(e) {
	e.preventDefault();
	var formData = new FormData($("#formulario")[0]);
	$.ajax({
	  url: "../ajax/sueldoBoleta.php?op=guardaryeditarempleado",
	  type: "POST",
	  data: formData,
	  contentType: false,
	  processData: false,
	  success: function(datos) { 
		Swal.fire({
		  title: 'Guardado',
		  text: datos,
		  icon: 'success',
		  showConfirmButton: false,
 		  timer: 1500
		});	 
		limpiar();
		refrescartabla();
		mostrarform(false);

	  }
	});
  }




function stopRKey(evt) {
var evt = (evt) ? evt : ((event) ? event : null);
var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
if ((evt.keyCode == 13) && (node.type=="text")) {return false;}
}


//Función para aceptar solo numeros con dos decimales
  function NumCheck(e, field) {
  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46
  key = e.keyCode ? e.keyCode : e.which

  if(e.keyCode===13  && !e.shiftKey)
    {
       document.getElementById('btnGuardar').focus();  
    }


  // backspace
          if (key == 8) return true;
          if (key == 9) return true;
        if (key > 44 && key < 58) {
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



//BLOQUEA ENTER 
document.onkeypress = stopRKey; 



function editar(idempleado)
{
	$.post("../ajax/sueldoBoleta.php?op=mostrarempleado",{idempleado : idempleado}, function(data, status)
	{
		data = JSON.parse(data);		
		mostrarform(true);
	$("#idempleado").val(data.idempleado);
	$("#nombresE").val(data.nombresE);
	$("#apellidosE").val(data.apellidosE);
	$("#fechaingreso").val(data.fechaingresoe);
	$("#ocupacion").val(data.ocupacion);
	$("#tiporemuneracion").val(data.tiporemuneracion);
	$("#dni").val(data.dni);
	$("#autogenessa").val(data.autogenessa);
	$("#cusspp").val(data.cusspp);
	$("#sueldoBruto").val(data.sueldoBruto);
	$("#horasT").val(data.horasT);
	$("#asigFam").val(data.asigFam);
	$("#trabNoct").val(data.trabNoct);
	$("#idtipoSeguro").val(data.idtipoSeguro);
	//$('#idtipoSeguro').selectpicker('refresh');
	$("#idempresab").val(data.idempresab);
	//$('#idempresab').selectpicker('refresh');

 	})
}






function mayus(e) {
     e.value = e.value.toUpperCase();
}


//Función para desactivar registros
function eliminar(idempleado) {
	Swal.fire({
	  title: '¿Está Seguro de eliminar?',
	  showCancelButton: true,
	  confirmButtonText: 'Eliminar',
	  cancelButtonText: 'Cancelar'
	}).then((result) => {
	  if (result.isConfirmed) {
		$.post("../ajax/sueldoBoleta.php?op=eliminarempleado", {idempleado : idempleado}, function(e){
		  Swal.fire({
			title: 'Eliminado',
			text: e,
			icon: 'success'
		  });
		  refrescartabla();
		  listar();
		});	
	  }
	});
  }


function refrescartabla()
{
tabla.ajax.reload();
}


  
/*-----------------------------------------------------*/
//            EVENTO KEYPRESS INPUT NUMERO DOC

$('#dni').keypress(function (e) {

	if (e.which === 13 && !e.shiftKey) { 

		var val_numdoc = $('#dni').val();
		
		if (val_numdoc == '') {

			swal.fire({
			title: 'Cuidado..!',
			text: "El campo DNI está vacío",
			icon: "warning",
			timer: 2000,
			showConfirmButton: false
			});

		} else {
			$.ajax({
				type: 'POST',
				url: "../ajax/boleta.php?op=consultaDniSunat&nrodni=" + val_numdoc,
				dataType: 'json',

				success: function (data) {

					if (!jQuery.isEmptyObject(data.error)) {

					swal.fire({
						title: 'Error!',
						text: data.error,
						icon: "error",
						timer: 2000,
						showConfirmButton: false
					});

					} else {
						// console.log(data);

						$('#nombresE').val(data.nombres);
						$('#apellidosE').val(data.apellidoPaterno + ' ' + data.apellidoMaterno);
					
					}
				},
				error: function (data) {
					// alert("Problemas al tratar de enviar el formulario");
					swal.fire({
					title: 'Error!',
					text: 'Problemas al obtener los datos del DNI',
					icon: "error",
					timer: 2000,
					showConfirmButton: false
					});
				}
			});
		}

	}
});
  


init();