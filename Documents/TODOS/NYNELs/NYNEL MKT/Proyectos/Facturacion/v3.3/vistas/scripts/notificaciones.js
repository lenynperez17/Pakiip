var tabla;

//Función que se ejecuta al inicio
function init(){
	mostrarform(false);
	listar();

	$("#formulario").on("submit",function(e)
	{
		guardaryeditar(e);	
	})

$.post("../ajax/persona.php?op=comboclientenoti", function(r){
$("#cliente").html(r);
//$("#cliente").selectpicker('refresh');
//$("#cliente").val(r);
});

}

//Función limpiar
function limpiar()
{
	$("#idnotificacion").val("");
	$("#nombrenotificacion").val("");
	$("#contador").val("");
	document.getElementById("btnGuardar").innerHTML = "Agregar";

	var now = new Date();
    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);
    var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
    $('#fechacreacion').val(today);
    $('#fechaaviso').val(today);

$.post("../ajax/persona.php?op=comboclientenoti", function(r){
$("#cliente").html(r);
//$("#cliente").selectpicker('refresh');

});


}

	

function refrescartabla()
{
tabla.ajax.reload();
}

//Función mostrar formulario
function mostrarform(flag)
{
	
	if (flag)
	{
		$("#listadoregistros").hide();
		$("#formulario").show();
		$("#btnGuardar").prop("disabled",false);
		$("#btnagregar").hide();
	}
	else
	{
		$("#listadoregistros").show();
		$("#formulario").hide();
		$("#btnagregar").show();
		limpiar();
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
		            // 'copyHtml5',
		            // 'excelHtml5',
		            // 'csvHtml5',
		            // 'pdf'
		        ],
		"ajax":
				{
					url: '../ajax/notificaciones.php?op=listar',
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

function guardaryeditar(e)
{
	e.preventDefault(); //No se activará la acción predeterminada del evento
	$("#btnGuardar").prop("disabled",true);
	var formData = new FormData($("#formulario")[0]);

	$.ajax({
		url: "../ajax/notificaciones.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,
	
		success: function(datos)
		{                    
			  Swal.fire({
				title: 'Éxito',
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

function mostrar(idnotificacion)
{
	$.post("../ajax/notificaciones.php?op=mostrar",{idnotificacion : idnotificacion}, function(data, status)
	{
		data = JSON.parse(data);		
		mostrarform(true);
		//var conti="";
		$("#idnotificacion").val(data.idnotificacion);
		$("#cliente").val(data.idpersona);
		//$("#cliente").selectpicker('refresh');
		$("#nombrenotificacion").val(data.nombrenotificacion);
		$("#fechacreacion").val(data.fechacreacion);
		$("#fechaaviso").val(data.fechaaviso);
		$("#contador").val(data.contador);
		$('#crearnotificaciones').modal('show');
		document.getElementById("btnGuardar").innerHTML = "Actualizar";
		conti=data.continuo;
		if (conti==1) {
        document.getElementById("continuo").checked=true;
    	}else{
        document.getElementById("continuo").checked=false;
	 	}
	 	
});
}


function continuoNoti()
{
    var conti = document.getElementById("continuo").checked;
    if (conti==true) {
        $("#selconti").val("1");
    }else{
        $("#selconti").val("0");
    }
}



//Función para desactivar registros
function desactivar(idnotificacion) {
	Swal.fire({
		title: '¿Está seguro?',
		text: "¿Desea desactivar la notificación?",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Sí, desactivar!',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.isConfirmed) {
			$.post("../ajax/notificaciones.php?op=desactivar", {
				idnotificacion: idnotificacion
			}, function(e) {
				Swal.fire({
					title: 'Desactivada',
					text: e,
					icon: 'success',
					showConfirmButton: false,
  					timer: 1500
				});
				tabla.ajax.reload();
			});
		}
	})
}

//Función para activar registros
function activar(idnotificacion) {
	Swal.fire({
		title: '¿Está seguro?',
		text: "¿Desea activar la notificación?",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Sí, activar!',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.isConfirmed) {
			$.post("../ajax/notificaciones.php?op=activar", {
				idnotificacion: idnotificacion
			}, function(e) {
				Swal.fire({
					title: 'Activada',
					text: e,
					icon: 'success',
					showConfirmButton: false,
  					timer: 1500
				});
				tabla.ajax.reload();
			});
		}
	})
}


init();