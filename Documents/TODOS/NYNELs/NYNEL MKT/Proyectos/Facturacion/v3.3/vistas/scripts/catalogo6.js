var tabla;
var modoDemo = false;
//Función que se ejecuta al inicio
function init() {
	mostrarform(false);
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	})
}

//Función limpiar
function limpiar() {
	//$("#id").val("");
	$("#codigo").val("");
	$("#descripcion").val("");
	$("#abrev").val("");
	document.getElementById("btnGuardar").innerHTML = "Agregar";
}

//Función mostrar formulario
function mostrarform(flag) {
	limpiar();
	if (flag) {
		$("#listadoregistros").hide();
		$("#formularioregistros").show();
		$("#btnGuardar").prop("disabled", false);
		$("#btnagregar").hide();
	}
	else {
		$("#listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
	}
}

//Función cancelarform
function cancelarform() {
	limpiar();
	mostrarform(false);
}

//Función Listar
function listar() {
	tabla = $('#tbllistado').dataTable(
		{
			"aProcessing": true,//Activamos el procesamiento del datatables
			"aServerSide": true,//Paginación y filtrado realizados por el servidor
			dom: 'Bfrtip',//Definimos los elementos del control de tabla
			buttons: [

			],
			"ajax":
			{
				url: '../ajax/catalogo6.php?op=listar',
				type: "get",
				dataType: "json",
				error: function (e) {
					console.log(e.responseText);
				}
			},
			"bDestroy": true,
			"iDisplayLength": 5,//Paginación
			"order": [[0, ""]]//Ordenar (columna,orden)
		}).DataTable();
}
//Función para guardar o editar

function guardaryeditar(e) {
	e.preventDefault(); //No se activará la acción predeterminada del evento
	if (modoDemo) {
		Swal.fire({
			icon: 'warning',
			title: 'Modo demo',
			text: 'No puedes editar o guardar en modo demo',
		});
		return;
	}
	$("#btnGuardar").prop("disabled", true);
	var formData = new FormData($("#formulario")[0]);
	$.ajax({
		url: "../ajax/catalogo6.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			Swal.fire({
				icon: 'success',
				title: 'Guardado exitoso',
				text: datos,
				showConfirmButton: false,
				timer: 1500
			});
			mostrarform(false);
			tabla.ajax.reload();
		}

	});
	limpiar();
}

function mostrar(id) {
	$.post("../ajax/catalogo6.php?op=mostrar", { id: id }, function (data, status) {
		data = JSON.parse(data);
		mostrarform(true);
		$("#id").val(data.id);
		$("#codigo").val(data.codigo);
		$("#descripcion").val(data.descripcion);
		$("#abrev").val(data.abrev);
		$('#agregarcatalogo6sunat').modal('show');
		document.getElementById("btnGuardar").innerHTML = "Actualizar";

	})
}

function desactivar(id) {
	if (modoDemo) {
		Swal.fire({
			icon: 'warning',
			title: 'Modo demo',
			text: 'No puedes editar o guardar en modo demo',
		});
		return;
	}
	Swal.fire({
		title: '¿Está Seguro de desactivar?',
		text: "¡No podrá revertir esto!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Sí, desactivar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.isConfirmed) {
			$.post("../ajax/catalogo6.php?op=desactivar", {
				id: id
			}, function (e) {
				Swal.fire({
					icon: 'success',
					title: 'Desactivado',
					text: e,
					showConfirmButton: false,
					timer: 1500
				});
				tabla.ajax.reload();
			});
		}
	})
}

function activar(id) {
	if (modoDemo) {
		Swal.fire({
			icon: 'warning',
			title: 'Modo demo',
			text: 'No puedes editar o guardar en modo demo',
		});
		return;
	}
	Swal.fire({
		title: '¿Está Seguro de activar la numeración?',
		text: "¡No podrá revertir esto!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Sí, activar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.isConfirmed) {
			$.post("../ajax/catalogo6.php?op=activar", {
				id: id
			}, function (e) {
				Swal.fire({
					icon: 'success',
					title: 'Activado',
					text: e,
					showConfirmButton: false,
					timer: 1500
				});
				tabla.ajax.reload();
			});
		}
	})
}


init();