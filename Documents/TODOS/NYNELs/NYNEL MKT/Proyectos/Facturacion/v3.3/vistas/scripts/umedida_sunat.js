var tabla;
var modoDemo = false;

//Función que se ejecuta al inicio
function init() {
	mostrarform(false);
	listar();

	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	});
}

//Función limpiar
function limpiar() {
	$("#codigo").val("");
	$("#descripcion").val("");
	$("#simbolo").val("");
	$("#notas").val("");
	$("#estado").val("1");
	$("#idsunat_um").val("");
	$("#modal-title-text").text("Agregar Unidad de Medida SUNAT");
	document.getElementById("btnGuardar").innerHTML = '<i class="fa fa-save"></i> Guardar';

	// Habilitar campo código para nuevos registros
	$("#codigo").prop("readonly", false);
}

function stopRKey(evt) {
	var evt = (evt) ? evt : ((event) ? event : null);
	var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
	if ((evt.keyCode == 13) && (node.type == "text")) { return false; }
}

//BLOQUEA ENTER
document.onkeypress = stopRKey;

//Función mostrar formulario
function mostrarform(flag) {
	limpiar();
	if (flag) {
		// No hay sección de listado y formulario separados en esta vista
		// El modal se maneja con Bootstrap
	}
}

//Función cancelarform
function cancelarform() {
	limpiar();
}

//Función Listar
function listar() {
	tabla = $('#tbllistado').dataTable(
		{
			"aProcessing": true,//Activamos el procesamiento del datatables
			"aServerSide": true,//Paginación y filtrado realizados por el servidor
			dom: 'Bfrtip',//Definimos los elementos del control de tabla
			buttons: [
				{
					extend: 'copyHtml5',
					text: '<i class="fa fa-copy"></i> Copiar',
					titleAttr: 'Copiar',
					className: 'btn btn-secondary btn-sm'
				},
				{
					extend: 'excelHtml5',
					text: '<i class="fa fa-file-excel"></i> Excel',
					titleAttr: 'Exportar a Excel',
					className: 'btn btn-success btn-sm',
					title: 'Unidades de Medida SUNAT'
				},
				{
					extend: 'csvHtml5',
					text: '<i class="fa fa-file-csv"></i> CSV',
					titleAttr: 'Exportar a CSV',
					className: 'btn btn-info btn-sm'
				},
				{
					extend: 'pdfHtml5',
					text: '<i class="fa fa-file-pdf"></i> PDF',
					titleAttr: 'Exportar a PDF',
					className: 'btn btn-danger btn-sm',
					title: 'Unidades de Medida SUNAT',
					orientation: 'landscape',
					pageSize: 'A4'
				}
			],
			"ajax":
			{
				url: '../ajax/umedida_sunat.php?op=listar',
				type: "get",
				dataType: "json",
				error: function (e) {
					console.log(e.responseText);
				},
				complete: function(data) {
					// Actualizar contador de registros
					if (data.responseJSON && data.responseJSON.data) {
						$("#total-registros").text(data.responseJSON.data.length + " registros");
					}
				}
			},
			"bDestroy": true,
			"iDisplayLength": 25,//Paginación
			"order": [[0, "asc"]],//Ordenar por código ASC
			"language": {
				"lengthMenu": "Mostrar _MENU_ registros",
				"zeroRecords": "No se encontraron resultados",
				"info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
				"infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
				"infoFiltered": "(filtrado de un total de _MAX_ registros)",
				"search": "Buscar:",
				"paginate": {
					"first": "Primero",
					"last": "Último",
					"next": "Siguiente",
					"previous": "Anterior"
				}
			}
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
		url: "../ajax/umedida_sunat.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			Swal.fire({
				title: '¡Éxito!',
				text: datos,
				icon: 'success',
				showConfirmButton: false,
				timer: 1500
			});
			$('#modalUmedidaSunat').modal('hide');
			tabla.ajax.reload();
		},
		error: function(xhr, status, error) {
			Swal.fire({
				title: 'Error',
				text: 'Ocurrió un error al guardar: ' + error,
				icon: 'error'
			});
			$("#btnGuardar").prop("disabled", false);
		}
	});

	limpiar();
}

function mostrar(idsunat_um) {
	$.post("../ajax/umedida_sunat.php?op=mostrar", { idsunat_um: idsunat_um }, function (data, status) {
		data = JSON.parse(data);

		$("#idsunat_um").val(data.idsunat_um);
		$("#codigo").val(data.codigo);
		$("#descripcion").val(data.descripcion);
		$("#simbolo").val(data.simbolo);
		$("#notas").val(data.notas);
		$("#estado").val(data.estado);

		// Deshabilitar campo código para edición (clave primaria)
		$("#codigo").prop("readonly", true);

		$("#modal-title-text").text("Editar Unidad de Medida SUNAT");
		$('#modalUmedidaSunat').modal('show');
		document.getElementById("btnGuardar").innerHTML = '<i class="fa fa-save"></i> Actualizar';
	});
}

//Función para desactivar registros
function desactivar(idsunat_um) {
	if (modoDemo) {
		Swal.fire({
			icon: 'warning',
			title: 'Modo demo',
			text: 'No puedes editar o guardar en modo demo',
		});
		return;
	}

	Swal.fire({
		title: '¿Está seguro de desactivar esta unidad de medida SUNAT?',
		text: 'No podrá ser utilizada en compras',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Sí, desactivar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.isConfirmed) {
			$.post("../ajax/umedida_sunat.php?op=desactivar", {
				idsunat_um: idsunat_um
			}, function (e) {
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
function activar(idsunat_um) {
	Swal.fire({
		title: '¿Está seguro de activar esta unidad de medida SUNAT?',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Sí, activar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.isConfirmed) {
			$.post("../ajax/umedida_sunat.php?op=activar", {
				idsunat_um: idsunat_um
			}, function (e) {
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

//Función para eliminar registros
function eliminar(idsunat_um) {
	if (modoDemo) {
		Swal.fire({
			icon: 'warning',
			title: 'Modo demo',
			text: 'No puedes editar o guardar en modo demo',
		});
		return;
	}

	Swal.fire({
		title: '¿Está seguro de eliminar esta unidad de medida SUNAT?',
		text: '¡Esta acción no se puede deshacer!',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: 'Sí, eliminar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.isConfirmed) {
			$.post("../ajax/umedida_sunat.php?op=eliminar", {
				idsunat_um: idsunat_um
			}, function (e) {
				Swal.fire({
					title: '¡Eliminado!',
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

function mayus(e) {
	e.value = e.value.toUpperCase();
}

init();
