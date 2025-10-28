var tabla;
$idempresa=$("#idempresa").val();
//Función que se ejecuta al inicio
function init(){
	mostrarform(false);
	listar();

	$("#formulario").on("submit",function(e)
	{
		guardaryeditar(e);	
	})

	 //Carga de combo para empresa =====================
    $.post("../ajax/conexion.php?op=empresa", function(r){
            $("#empresa").html(r);
           // $('#empresa').selectpicker('refresh');
    });

}

//Función limpiar
function limpiar()
{
	$("#nombre").val("");
	document.getElementById('nombre').focus();
	document.getElementById("btnGuardar").innerHTML = "Agregar";
	
}

//Función mostrar formulario
function mostrarform(flag)
{
	limpiar();
	if (flag)
	{
		$("#listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled",false);
		$("#btnagregar").hide();
		document.getElementById('nombre').focus();
	}
	else
	{
		$("#listadoregistros").show();
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
	tabla=$('#tbllistado').dataTable(
	{
		"aProcessing": true,//Activamos el procesamiento del datatables
	    "aServerSide": true,//Paginación y filtrado realizados por el servidor
	    dom: 'Bfrtip',//Definimos los elementos del control de tabla
	    buttons: [		          
		    //           {
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
					url: '../ajax/vendedorsitio.php?op=listar&idempresa='+$idempresa,
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

//Función para desactivar registros
function desactivar(id) {
	Swal.fire({
	  title: '¿Está seguro de desactivar el vendedor?',
	  icon: 'warning',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: 'Sí, desactivar',
	  cancelButtonText: 'Cancelar'
	}).then((result) => {
	  if (result.isConfirmed) {
		$.post("../ajax/vendedorsitio.php?op=desactivar", {id : id}, function(e){
		  Swal.fire({
			title: '¡Desactivado!',
			text: e,
			icon: 'success',
			showConfirmButton: false,
  		    timer: 1500
		  });
		  tabla.ajax.reload();
		});	
	  }
	});
  }
  
  //Función para activar registros
  function activar(id) {
	Swal.fire({
	  title: '¿Está seguro de activar el vendedor?',
	  icon: 'warning',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: 'Sí, activar',
	  cancelButtonText: 'Cancelar'
	}).then((result) => {
	  if (result.isConfirmed) {
		$.post("../ajax/vendedorsitio.php?op=activar", {id : id}, function(e){
		  Swal.fire({
			title: '¡Activado!',
			text: e,
			icon: 'success',
			showConfirmButton: false,
  		    timer: 1500
		  });
		  tabla.ajax.reload();
		});	
	  }
	});
  }
  
  //Función para guardar y editar registros
  function guardaryeditar(e) {
	e.preventDefault();
	$("#btnGuardar").prop("disabled",true);
	var formData = new FormData($("#formulario")[0]);
  
	$.ajax({
	  url: "../ajax/vendedorsitio.php?op=guardaryeditar",
	  type: "POST",
	  data: formData,
	  contentType: false,
	  processData: false,
	  success: function(datos) {                    
		Swal.fire({
		  title: '¡Guardado!',
		  text: datos,
		  icon: 'success',
		  showConfirmButton: false,
  		  timer: 1500
		});
		mostrarform(false);
		tabla.ajax.reload();
	  }
	});
	limpiar();
  }
  

function mostrar(id)
{
	$.post("../ajax/vendedorsitio.php?op=mostrar",{id : id}, function(data, status)
	{
		data = JSON.parse(data);		
		mostrarform(true);

		$("#id").val(data.id);
		$("#nombre").val(data.nombre);
		$("#empresa").val(data.idempresa);
		//$("#empresa").selectpicker('refresh');
		$('#agregarvendedor').modal('show');
		document.getElementById("btnGuardar").innerHTML = "Actualizar";


		

 	})
}



function mayus(e) {
     e.value = e.value.toUpperCase();
}


init();