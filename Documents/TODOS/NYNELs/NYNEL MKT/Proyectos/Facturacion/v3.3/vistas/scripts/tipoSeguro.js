var tabla;

//Función que se ejecuta al inicio
function init(){
	
	$("#formulario").on("submit",function(e)
	{
		guardaryeditar(e);	
	})

    limpiar();
    listar();
}




//Función limpiar
function limpiar()
{
	$("#idtipoSeguro").val("");
	$("#tipoSeguro").val("AFP");
	$("#nombreSeguro").val("");
	$("#snp").val("");
	$("#aoafp").val("");
	$("#invsob").val("");
	$("#comiafp").val("");

	 setTimeout(function(){
        document.getElementById('nombreSeguro').focus();
        },100);
	
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


//Función cancelarform
function cancelarform()
{
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
		            
		            // 'excelHtml5',
		           
		            // 'pdf'
		        ],
		"ajax":
				{
					url: '../ajax/sueldoBoleta.php?op=listarts',
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





// function guardaryeditar(e) {
// 	e.preventDefault();    //No se activará la acción predeterminada del evento
	
// 	var formData = new FormData($("#formulario")[0]);

// 	$.ajax({
// 		url: "../ajax/sueldoBoleta.php?op=guardaryeditartiposeguro",
// 	    type: "POST",
// 	    data: formData,
// 	    contentType: false,
// 	    processData: false,
// 	    success: function(datos) { 
// 	    	Swal.fire({
// 	    		icon: 'success',
// 	    		title: 'Guardado',
// 	    		text: datos,
// 				showConfirmButton: false,
//   				timer: 1500
// 	    	});
// 	    	limpiar();
// 	    	refrescartabla();
// 	    }
// 	});

// 	limpiar();
// 	refrescartabla();
// }





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

function editar(idtipoSeguro)
{
	$.post("../ajax/sueldoBoleta.php?op=mostrarts",{idtipoSeguro : idtipoSeguro}, function(data, status)
	{
		data = JSON.parse(data);		

		$("#idtipoSeguro").val(data.idtipoSeguro);
		$("#tipoSeguro").val(data.tipoSeguro);
		$("#nombreSeguro").val(data.nombreSeguro);
		$("#snp").val(data.snp);
		$("#aoafp").val(data.aoafp);
		$("#invsob").val(data.invsob);
		$("#comiafp").val(data.comiafp);

 	})
}




function mayus(e) {
     e.value = e.value.toUpperCase();
}


function guardaryeditar(e) {
    e.preventDefault(); //No se activará la acción predeterminada del evento

    var formData = new FormData($("#formulario")[0]);

    $.ajax({
        url: "../ajax/sueldoBoleta.php?op=guardaryeditartiposeguro",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,

        success: function(datos) {
            Swal.fire({
                icon: 'success',
                title: 'Guardado',
                text: datos,
				showConfirmButton: false,
  				timer: 1500
            });
            limpiar();
            refrescartabla()
        },
        error: function(error) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Algo salió mal!',
                footer: 'Inténtalo de nuevo más tarde',
				showConfirmButton: false,
  				timer: 1500
            });
        }
    });

    limpiar();
    refrescartabla()
}

function eliminar(idtipoSeguro) {
    Swal.fire({
        title: '¿Está Seguro de eliminar?',
        text: "No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminarlo!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("../ajax/sueldoBoleta.php?op=eliminartse", { idtipoSeguro: idtipoSeguro }, function(e) {
                Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    text: e,
					showConfirmButton: false,
  					timer: 1500
                });
                refrescartabla();
                listar();
            });
        }
    })
}



function refrescartabla()
{
tabla.ajax.reload();
}





init();