
var baseURL = window.location.protocol + '//' + window.location.host;

// Verificar si pathname contiene '/vistas/' y eliminarlo.
var path = window.location.pathname;
if (path.includes("/vistas/")) {
	path = path.replace("/vistas/", "/");
}

// Asegurarnos de que el path termine en "/ajax/"
if (!path.endsWith("/ajax/")) {
	var lastSlashIndex = path.lastIndexOf("/");
	path = path.substring(0, lastSlashIndex) + "/ajax/";
}

// Construir urlconsumo
var urlconsumo = new URL(path, baseURL);

// var modoDemo = false;
//Función que se ejecuta al inicio
function init() {
	listar();
	mostrarTotaldeVentas();
	mostrarTotaldeIngresos();
	mostrarTotaldeEgresos();
	mostrarSaldoINI();


	$("#formulario").on("submit", function (e) {
		guardaryeditar(e);
	})
}



function mostrarTotaldeVentas() {
	$(document).ready(function () {
		$.ajax({
			url: urlconsumo + "cajachica.php?action=TotalVentas",
			type: 'GET',
			dataType: 'json',
			success: function (data) {
				const totalVentas = data.aaData[0].total_venta;
				const totalVentasElement = $('#total-ventas');

				if (totalVentas !== null && totalVentas !== "") {
					totalVentasElement.html('S/ ' + totalVentas);
				} else {
					totalVentasElement.html('S/ 0');
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log('Error:', textStatus, errorThrown);
			}
		});
	});
}


function mostrarTotaldeIngresos() {
	$(document).ready(function () {
		$.ajax({
			url: urlconsumo + "cajachica.php?action=TotalIngresos",
			type: 'GET',
			dataType: 'json',
			success: function (data) {
				const totalingreso = data.aaData[0].total_ingreso;
				const totalIngresoElement = $('#total_ingreso');

				if (totalingreso !== null && totalingreso !== "") {
					totalIngresoElement.html('S/ ' + totalingreso);
				} else {
					totalIngresoElement.html('S/ 0');
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log('Error:', textStatus, errorThrown);
			}
		});
	});
}


function mostrarTotaldeEgresos() {
	$(document).ready(function () {
		$.ajax({
			url: urlconsumo + "cajachica.php?action=TotalGastos",
			type: 'GET',
			dataType: 'json',
			success: function (data) {
				const totalegreso = data.aaData[0].total_gasto;
				const totalEgresoElement = $('#total_gasto');

				if (totalegreso !== null && totalegreso !== "") {
					totalEgresoElement.html('S/ ' + totalegreso);
				} else {
					totalEgresoElement.html('S/ 0');
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log('Error:', textStatus, errorThrown);
			}
		});
	});
}


function mostrarSaldoINI() {
	$(document).ready(function () {
		$.ajax({
			url: urlconsumo + "cajachica.php?action=SaldoInicial",
			type: 'GET',
			dataType: 'json',
			success: function (data) {
				const saldoINElement = $('#total_saldoini');

				if (Array.isArray(data.aaData) && data.aaData.length > 0) {
					const saldoIN = data.aaData[0].total_ingreso;
					saldoINElement.html('S/ ' + saldoIN);
					$('#cerrarCajaBtn').prop('disabled', true); // Habilitar el botón de cerrar caja
				} else {
					saldoINElement.html('S/ 0');
					$('#cerrarCajaBtn').prop('disabled', true); // Deshabilitar el botón de cerrar caja
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log('Error:', textStatus, errorThrown);
			}
		});
	});
}



function listar() {
	tabla = $('#tblistadototalcaja').dataTable({
		"aProcessing": true,
		"aServerSide": true,
		"dom": 'Bfrtip',
		"buttons": [],
		"ajax": {
			"url": '../ajax/cajachica.php?action=listarcierre',
			"type": "get",
			"dataType": "json",
			"error": function (e) {
				console.log(e.responseText);
			}
		},
		"bDestroy": true,
		"iDisplayLength": 15,
		"order": [[0, ""]],
		"columns": [
			{ "data": "fecha_cierre" },
			{ "data": "total_ingreso" },
			{ "data": "total_gasto" },
			{ "data": "saldo_inicial" },
			{ "data": "total_caja" }
		]
	}).DataTable();
}



function guardaryeditar(e) {
	e.preventDefault();

	$("#btnGuardarSaldoInicial").prop("disabled", true);
	var formData = new FormData($("#formulario")[0]);

	$.ajax({
		url: "../ajax/cajachica.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,

		success: function (datos) {
			$("#btnGuardarSaldoInicial").prop("disabled", false);

			if (datos === "Ya existe un saldo inicial registrado para hoy, no se puede registrar otro.") {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: datos
				});
			} else {
				Swal.fire({
					icon: 'success',
					title: 'Guardado exitoso',
					text: datos,
					showConfirmButton: false,
					timer: 1500
				});

				$('#agregarsaldoInicial').modal('hide'); // Ocultar el modal

				// Obtener valor del saldo inicial guardado
				var saldoInicial = $('#saldo_inicial').val();

				// Actualizar contenido del h5 con el saldo inicial
				$('#total_saldoini').html('S/ ' + saldoInicial);

				mostrarSaldoINI(); // Ejecutar la función mostrarSaldoINI para actualizar el saldo inicial

			}
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log('Error:', textStatus, errorThrown);
		}
	});
}


function cerrarCaja() {
	$.ajax({
		url: "../ajax/cajachica.php?op=cerrarcaja",
		type: "POST",
		success: function (response) {
			if (response === "Caja cerrada") {
				Swal.fire({
					icon: 'success',
					title: 'Caja cerrada',
					text: 'Se ha cerrado la caja con éxito',
					showConfirmButton: false,
					timer: 1500
				});

				resetearTotales(); // Restablecer los valores de los totales

				// Volver a iniciar el proceso de apertura de caja
				$('#agregarsaldoInicial').modal('show');

			} else {
				Swal.fire({
					icon: 'error',
					title: 'Error al cerrar la caja',
					text: 'No se pudo cerrar la caja',
					showConfirmButton: false,
					timer: 1500
				});
			}
		},
		error: function () {
			Swal.fire({
				icon: 'error',
				title: 'Error al cerrar la caja',
				text: 'No se pudo cerrar la caja',
				showConfirmButton: false,
				timer: 1500
			});
		}
	});
}


function resetearTotales() {
	$('#total_ingreso').text('0');
	$('#total_gasto').text('0');
	$('#total_saldoini').text('0');
	$('#total-ventas').text('0');
}



function verificarSaldoInicial() {
	var saldoInicial = document.getElementById("total_saldoini").innerText;
	if (saldoInicial === "S/") {
		document.getElementById("cerrarCajaBtn").disabled = true;
	} else {
		document.getElementById("cerrarCajaBtn").disabled = false;
	}
}








init();