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
	$("#numero").val("");
	$("#serie").val("");
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
				// 'copyHtml5',
				// 'excelHtml5',
				// 'csvHtml5',
				// 'pdf'
			],
			"ajax":
			{
				url: '../ajax/configNum.php?op=listar',
				type: "get",
				dataType: "json",
				error: function (e) {
					console.log(e.responseText);
				}
			},
			"bDestroy": true,
			"iDisplayLength": 8,//Paginación
			"order": [[0, "desc"]]//Ordenar (columna,orden)
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
		url: "../ajax/configNum.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			swal.fire({
				icon: 'success',
				title: '¡Guardado!',
				text: 'Los cambios han sido guardados satisfactoriamente.',
				showConfirmButton: false,
				timer: 1500
			});
			mostrarform(false);
			tabla.ajax.reload();
		}

	});
	limpiar();
}

function mostrar(idnumeracion) {
	$.post("../ajax/configNum.php?op=mostrar", { idnumeracion: idnumeracion }, function (data, status) {
		data = JSON.parse(data);
		mostrarform(true);

		$("#idnumeracion").val(data.idnumeracion);
		$("#tipo_documento").val(data.tipo_documento);
		$("#serie").val(data.serie);
		$("#numero").val(data.numero);
		$('#agregarserieynumero').modal('show');
		document.getElementById("btnGuardar").innerHTML = "Actualizar";


	})
}

// Función para desactivar registros
function desactivar(idnumeracion) {
	if (modoDemo) {
		Swal.fire({
			icon: 'warning',
			title: 'Modo demo',
			text: 'No puedes editar o guardar en modo demo',
		});
		return;
	}
	swal.fire({
		title: "¿Está seguro de desactivar la numeración?",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: "Sí, desactivar",
		cancelButtonText: "Cancelar",
	}).then((result) => {
		if (result.isConfirmed) {
			$.post(
				"../ajax/configNum.php?op=desactivar", {
				idnumeracion: idnumeracion
			},
				function (e) {
					swal.fire({
						icon: 'success',
						title: '¡Desactivado!',
						text: e,
						showConfirmButton: false,
						timer: 1500
					});
					tabla.ajax.reload();
				}
			);
		}
	});
}

// Función para activar registros
function activar(idnumeracion) {
	if (modoDemo) {
		Swal.fire({
			icon: 'warning',
			title: 'Modo demo',
			text: 'No puedes editar o guardar en modo demo',
		});
		return;
	}
	swal.fire({
		title: "¿Está seguro de activar la numeración?",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: "Sí, activar",
		cancelButtonText: "Cancelar",
	}).then((result) => {
		if (result.isConfirmed) {
			$.post(
				"../ajax/configNum.php?op=activar", {
				idnumeracion: idnumeracion
			},
				function (e) {
					swal.fire({
						icon: 'success',
						title: '¡Activado!',
						text: e,
						showConfirmButton: false,
						timer: 1500
					});
					tabla.ajax.reload();
				}
			);
		}
	});
}

// Función para ELIMINAR FÍSICAMENTE registros
function eliminar(idnumeracion) {
	if (modoDemo) {
		Swal.fire({
			icon: 'warning',
			title: 'Modo demo',
			text: 'No puedes editar o guardar en modo demo',
		});
		return;
	}
	swal.fire({
		title: "¿Está seguro de ELIMINAR PERMANENTEMENTE esta numeración?",
		text: "Esta acción NO se puede deshacer. Se borrará completamente de la base de datos.",
		icon: "error",
		showCancelButton: true,
		confirmButtonColor: "#d33",
		cancelButtonColor: "#3085d6",
		confirmButtonText: "Sí, eliminar permanentemente",
		cancelButtonText: "Cancelar",
	}).then((result) => {
		if (result.isConfirmed) {
			$.post(
				"../ajax/configNum.php?op=eliminar", {
				idnumeracion: idnumeracion
			},
				function (e) {
					swal.fire({
						icon: 'success',
						title: '¡Eliminado!',
						text: e,
						showConfirmButton: false,
						timer: 2000
					});
					tabla.ajax.reload();
				}
			).fail(function (xhr, status, error) {
				swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo eliminar. Puede estar siendo usada en documentos.',
					showConfirmButton: true
				});
			});
		}
	});
}


init();