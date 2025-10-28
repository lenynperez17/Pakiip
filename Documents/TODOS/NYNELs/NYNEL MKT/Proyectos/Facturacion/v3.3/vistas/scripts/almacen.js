// Variables globales
var tabla;
var tablaProductos;
var modoDemo = false;
var usuariosResponsables = [];
var almacenActual = null;

// Función de inicialización
function init() {
	mostrarform(false);
	listar();
	cargarEstadisticas();
	cargarUsuariosResponsables();

	// Inicializar Bootstrap Select en filtros
	$("#filtro_tipo").selectpicker();
	$("#filtro_estado").selectpicker();
	$("#filtro_responsable").selectpicker();

	// Inicializar Bootstrap Select en formulario
	$("#idusuario_responsable").selectpicker();
	$("#tipo_almacen").selectpicker();

	// Evento submit del formulario
	$("#formulario").on("submit", function(e) {
		guardaryeditar(e);
	});

	// Eventos de filtros
	$("#filtro_nombre").on("keyup", function() {
		tabla.search(this.value).draw();
	});

	$("#filtro_tipo, #filtro_estado, #filtro_responsable").on("change", function() {
		aplicarFiltros();
	});
}

// Limpiar formulario
function limpiar() {
	$("#idalmacen").val("");
	$("#nombrea").val("");
	$("#direccion").val("");
	$("#telefono").val("");
	$("#email").val("");
	$("#idusuario_responsable").val("");
	$("#tipo_almacen").val("SECUNDARIO");
	$("#capacidad_max").val("");
	$("#notas").val("");
}

// Mostrar/Ocultar formulario
function mostrarform(flag) {
	limpiar();
	if (flag) {
		$("#myModalLabel1").text("Añadir nuevo almacén");
		$("#btnGuardar").html('<i class="ri-save-line"></i> Guardar');
	} else {
		$("#myModalLabel1").text("Gestión de Almacén");
	}
}

// Cancelar formulario
function cancelarform() {
	limpiar();
	mostrarform(false);
}

// Listar almacenes con DataTables
function listar() {
	tabla = $('#tbllistado').dataTable({
		"aProcessing": true,
		"aServerSide": true,
		dom: '<"row"<"col-md-6"B><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
		buttons: [
			{
				extend: 'excelHtml5',
				text: '<i class="ri-file-excel-2-line"></i> Excel',
				titleAttr: 'Exportar a Excel',
				className: 'btn btn-sm btn-success'
			},
			{
				extend: 'pdfHtml5',
				text: '<i class="ri-file-pdf-line"></i> PDF',
				titleAttr: 'Exportar a PDF',
				className: 'btn btn-sm btn-danger',
				orientation: 'landscape',
				pageSize: 'A4',
				customize: function(doc) {
					doc.styles.tableHeader.fontSize = 8;
					doc.defaultStyle.fontSize = 7;
				}
			},
			{
				extend: 'csv',
				text: '<i class="ri-file-text-line"></i> CSV',
				titleAttr: 'Exportar a CSV',
				className: 'btn btn-sm btn-info'
			},
			{
				extend: 'copy',
				text: '<i class="ri-file-copy-line"></i> Copiar',
				titleAttr: 'Copiar al portapapeles',
				className: 'btn btn-sm btn-secondary'
			}
		],
		"ajax": {
			url: '../ajax/almacen.php?op=listar',
			type: "get",
			dataType: "json",
			error: function(e) {
				console.log("Error al cargar los datos:", e.responseText);
			}
		},
		"bDestroy": true,
		"iDisplayLength": 15,
		"order": [[0, "asc"]],
		"language": {
			"sProcessing": "Procesando...",
			"sLengthMenu": "Mostrar _MENU_ registros",
			"sZeroRecords": "No se encontraron resultados",
			"sEmptyTable": "Ningún dato disponible en esta tabla",
			"sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
			"sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
			"sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
			"sInfoPostFix": "",
			"sSearch": "Buscar:",
			"sUrl": "",
			"sInfoThousands": ",",
			"sLoadingRecords": "Cargando...",
			"oPaginate": {
				"sFirst": "Primero",
				"sLast": "Último",
				"sNext": "Siguiente",
				"sPrevious": "Anterior"
			},
			"oAria": {
				"sSortAscending": ": Activar para ordenar la columna de manera ascendente",
				"sSortDescending": ": Activar para ordenar la columna de manera descendente"
			}
		}
	}).DataTable();
}

// Guardar y editar
function guardaryeditar(e) {
	e.preventDefault();
	
	if (modoDemo) {
		Swal.fire({
			icon: 'warning',
			title: 'Modo demo',
			text: 'No puedes editar o guardar en modo demo'
		});
		return;
	}
	
	$("#btnGuardar").prop("disabled", true);

	var formData = new FormData($("#formulario")[0]);

	$.ajax({
		url: "../ajax/almacen.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function(datos) {
			if (datos == "Almacen ya registrado") {
				Swal.fire({
					icon: 'warning',
					title: 'Almacén duplicado',
					text: 'Ya existe un almacén con ese nombre',
					confirmButtonColor: '#3085d6'
				});
			} else if (datos == "Almacen registrado") {
				Swal.fire({
					icon: 'success',
					title: '¡Registrado!',
					text: 'Almacén registrado exitosamente',
					confirmButtonColor: '#3085d6',
					timer: 2000
				});
				tabla.ajax.reload();
				$("#agregarsucursal").modal("hide");
				cargarEstadisticas();
			} else if (datos == "almacen actualizada") {
				Swal.fire({
					icon: 'success',
					title: '¡Actualizado!',
					text: 'Almacén actualizado exitosamente',
					confirmButtonColor: '#3085d6',
					timer: 2000
				});
				tabla.ajax.reload();
				$("#agregarsucursal").modal("hide");
				cargarEstadisticas();
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'No se pudo completar la operación',
					confirmButtonColor: '#d33'
				});
			}
		},

		error: function(e) {
			console.log("Error en la petición AJAX:", e.responseText);
			Swal.fire({
				icon: 'error',
				title: 'Error de comunicación',
				text: 'No se pudo conectar con el servidor',
				confirmButtonColor: '#d33'
			});
		},

		complete: function() {
			$("#btnGuardar").prop("disabled", false);
		}
	});
}

// Mostrar datos para editar
function mostrar(idalmacen) {
	$.post("../ajax/almacen.php?op=mostrar", {idalmacen: idalmacen}, function(data, status) {
		data = JSON.parse(data);
		mostrarform(true);

		$("#idalmacen").val(data.idalmacen);
		$("#nombrea").val(data.nombre);
		$("#direccion").val(data.direccion);
		$("#telefono").val(data.telefono);
		$("#email").val(data.email);
		$("#idusuario_responsable").val(data.idusuario_responsable);
		$("#tipo_almacen").val(data.tipo_almacen);
		$("#capacidad_max").val(data.capacidad_max);
		$("#notas").val(data.notas);

		$("#myModalLabel1").text("Editar almacén");
		$("#btnGuardar").html('<i class="ri-save-line"></i> Actualizar');
		$("#agregarsucursal").modal("show");
	});
}

// Desactivar almacén
function desactivar(idalmacen) {
	if (modoDemo) {
		Swal.fire({
			icon: 'warning',
			title: 'Modo demo',
			text: 'No puedes editar o guardar en modo demo'
		});
		return;
	}
	
	Swal.fire({
		title: '¿Está seguro?',
		text: "¿Desea desactivar este almacén?",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Sí, desactivar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.isConfirmed) {
			$.post("../ajax/almacen.php?op=desactivar", {idalmacen: idalmacen}, function(e) {
				Swal.fire({
					icon: 'success',
					title: '¡Desactivado!',
					text: 'Almacén desactivado exitosamente',
					confirmButtonColor: '#3085d6',
					timer: 2000
				});
				tabla.ajax.reload();
				cargarEstadisticas();
			});
		}
	});
}

// Activar almacén
function activar(idalmacen) {
	Swal.fire({
		title: '¿Está seguro?',
		text: "¿Desea activar este almacén?",
		icon: 'question',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Sí, activar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if (result.isConfirmed) {
			$.post("../ajax/almacen.php?op=activar", {idalmacen: idalmacen}, function(e) {
				Swal.fire({
					icon: 'success',
					title: '¡Activado!',
					text: 'Almacén activado exitosamente',
					confirmButtonColor: '#3085d6',
					timer: 2000
				});
				tabla.ajax.reload();
				cargarEstadisticas();
			});
		}
	});
}

// Cargar estadísticas en las tarjetas
function cargarEstadisticas() {
	$.ajax({
		url: "../ajax/almacen.php?op=obtenerEstadisticas",
		type: "GET",
		dataType: "json",
		success: function(data) {
			// Total almacenes
			$("#total_almacenes").text(data.total_almacenes || 0);
			$("#almacenes_estado").text(
				(data.almacenes_activos || 0) + " activos / " +
				(data.almacenes_inactivos || 0) + " inactivos"
			);

			// Total productos
			$("#total_productos").text(formatNumber(data.total_productos || 0));

			// Valor inventario
			$("#valor_inventario").text("S/ " + formatMoney(data.valor_total_inventario || 0));

			// Distribución de tipos
			var principal = data.almacenes_principales || 0;
			var secundario = data.almacenes_secundarios || 0;
			var temporal = data.almacenes_temporales || 0;

			$("#tipo_distribucion").text(principal + " / " + secundario + " / " + temporal);
			$("#tipo_detalle").text(principal + " Principal / " + secundario + " Secundario / " + temporal + " Temporal");
		},
		error: function(e) {
			console.log("Error al cargar estadísticas:", e.responseText);
		}
	});
}

// Cargar usuarios responsables para el select
function cargarUsuariosResponsables() {
	$.ajax({
		url: "../ajax/almacen.php?op=obtenerUsuariosResponsables",
		type: "GET",
		dataType: "json",
		success: function(data) {
			usuariosResponsables = data;

			// Llenar select del formulario
			var optionsForm = '<option value="">Sin asignar</option>';
			$.each(data, function(index, usuario) {
				optionsForm += '<option value="' + usuario.idusuario + '">' +
					usuario.nombre + ' (' + usuario.login + ')</option>';
			});
			$("#idusuario_responsable").html(optionsForm);
			$("#idusuario_responsable").selectpicker('refresh');

			// Llenar select del filtro
			var optionsFilter = '<option value="">Todos</option>';
			$.each(data, function(index, usuario) {
				optionsFilter += '<option value="' + usuario.idusuario + '">' +
					usuario.nombre + '</option>';
			});
			$("#filtro_responsable").html(optionsFilter);
			$("#filtro_responsable").selectpicker('refresh');
		},
		error: function(e) {
			console.log("Error al cargar usuarios:", e.responseText);
		}
	});
}

// Aplicar filtros avanzados
function aplicarFiltros() {
	$.fn.dataTable.ext.search.pop(); // Limpiar filtros anteriores

	$.fn.dataTable.ext.search.push(
		function(settings, data, dataIndex) {
			var filtroTipo = $("#filtro_tipo").val();
			var filtroEstado = $("#filtro_estado").val();
			var filtroResponsable = $("#filtro_responsable").val();

			// data[2] = Tipo
			// data[3] = Responsable
			// data[6] = Estado

			// Filtrar por tipo
			if (filtroTipo !== "") {
				if (data[2].indexOf(filtroTipo) === -1) {
					return false;
				}
			}

			// Filtrar por estado
			if (filtroEstado !== "") {
				var estadoActivo = data[6].indexOf("Activo") !== -1;
				if (filtroEstado === "1" && !estadoActivo) return false;
				if (filtroEstado === "0" && estadoActivo) return false;
			}

			// Filtrar por responsable (buscar en el nombre del responsable)
			if (filtroResponsable !== "") {
				var responsableNombre = "";
				usuariosResponsables.forEach(function(usuario) {
					if (usuario.idusuario == filtroResponsable) {
						responsableNombre = usuario.nombre;
					}
				});
				if (data[3].indexOf(responsableNombre) === -1) {
					return false;
				}
			}

			return true;
		}
	);

	tabla.draw();
}

// Limpiar filtros
function limpiarFiltros() {
	$("#filtro_nombre").val("");
	$("#filtro_tipo").val("").selectpicker('refresh');
	$("#filtro_estado").val("").selectpicker('refresh');
	$("#filtro_responsable").val("").selectpicker('refresh');

	$.fn.dataTable.ext.search.pop();
	tabla.search("").draw();
}

// Formatear números
function formatNumber(num) {
	return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Formatear dinero
function formatMoney(num) {
	return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Convertir a mayúsculas
function mayus(e) {
	e.value = e.value.toUpperCase();
}

// Ver productos de un almacén
function verProductosAlmacen(idalmacen, nombreAlmacen) {
	almacenActual = idalmacen;

	// Establecer nombre del almacén en el modal
	$("#nombre_almacen_modal").text(nombreAlmacen);

	// Cargar resumen del almacén
	cargarResumenAlmacen(idalmacen);

	// Cargar productos en la tabla
	cargarProductosAlmacen(idalmacen);

	// Mostrar el modal
	$("#modalProductosAlmacen").modal("show");
}

// Cargar resumen del almacén
function cargarResumenAlmacen(idalmacen) {
	$.ajax({
		url: "../ajax/almacen.php?op=obtenerResumenAlmacen",
		type: "GET",
		data: { idalmacen: idalmacen },
		dataType: "json",
		success: function(data) {
			$("#resumen_total_productos").text(formatNumber(data.total_productos || 0));
			$("#resumen_total_unidades").text(formatNumber(data.total_unidades || 0));
			$("#resumen_valor_total").text("S/ " + formatMoney(data.valor_total_inventario || 0));

			var promedio = 0;
			if (data.total_productos > 0) {
				promedio = data.total_unidades / data.total_productos;
			}
			$("#resumen_stock_promedio").text(formatNumber(promedio.toFixed(2)));
		},
		error: function(e) {
			console.log("Error al cargar resumen:", e.responseText);
		}
	});
}

// Cargar productos del almacén
function cargarProductosAlmacen(idalmacen) {
	// Destruir tabla anterior si existe
	if (tablaProductos) {
		tablaProductos.destroy();
	}

	tablaProductos = $('#tblProductosAlmacen').DataTable({
		"aProcessing": true,
		"aServerSide": true,
		"ajax": {
			url: '../ajax/almacen.php?op=listarProductosPorAlmacen',
			type: "get",
			data: { idalmacen: idalmacen },
			dataType: "json",
			error: function(e) {
				console.log("Error al cargar productos:", e.responseText);
			}
		},
		"bDestroy": true,
		"iDisplayLength": 10,
		"order": [[1, "asc"]],
		"language": {
			"sProcessing": "Procesando...",
			"sLengthMenu": "Mostrar _MENU_ registros",
			"sZeroRecords": "No se encontraron productos en este almacén",
			"sEmptyTable": "No hay productos registrados en este almacén",
			"sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ productos",
			"sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 productos",
			"sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
			"sInfoPostFix": "",
			"sSearch": "Buscar producto:",
			"sUrl": "",
			"sInfoThousands": ",",
			"sLoadingRecords": "Cargando productos...",
			"oPaginate": {
				"sFirst": "Primero",
				"sLast": "Último",
				"sNext": "Siguiente",
				"sPrevious": "Anterior"
			}
		},
		"drawCallback": function(settings) {
			// Calcular total del valor de productos
			var api = this.api();
			var total = 0;

			api.rows({page: 'current'}).every(function() {
				var data = this.data();
				// Extraer el valor de la columna 5 (Valor Total)
				var valorStr = data[5].replace('S/ ', '').replace(',', '');
				total += parseFloat(valorStr) || 0;
			});

			$("#total_valor_productos").text("S/ " + formatMoney(total));
		}
	});
}

// Exportar productos a Excel
function exportarProductosExcel() {
	if (!almacenActual) {
		Swal.fire({
			icon: 'warning',
			title: 'Atención',
			text: 'No hay almacén seleccionado'
		});
		return;
	}

	// Usar los botones de DataTables para exportar
	tablaProductos.button('.buttons-excel').trigger();
}

// Esperar a que el DOM esté completamente cargado antes de inicializar
$(document).ready(function() {
	init();
});
