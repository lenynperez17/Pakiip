var tabla;
var modoDemo = false;


//Función que se ejecuta al inicio

function init() {

	mostrarform(false);

	//mostrar("1");

	listar();



	$("#formulario").on("submit", function (e) {

		guardaryeditar(e);

	})

}



function limpiar() {

	$("#idempresa").val("");

	$("#razonsocial").val("");

	$("#ncomercial").val("");

	$("#domicilio").val("");

	$("#ruc").val("");

	$("#tel1").val("");

	$("#tel2").val("");

	$("#correo").val("");

	$("#web").val("");

	$("#webconsul").val("");

	$("#imagenmuestra").attr("src", "../assets/images/faces/9.jpg");

	$("#imagenactual").val("");

	$("#ubigueo").val("");

	$("#codubigueo").val("");



	$("#ciudad").val("");

	$("#distrito").val("");

	$("#interior").val("");

	$("#codigopais").val("");



	$("#igv").val("");

	$("#porDesc").val("");



	$("#razonsocial").focus()



	$("#preview").empty();

}


$("#imagenmuestra").css("display", "block");


//Función cancelarform

function cancelarform() {

	mostrarform(false);

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
	//$("#btnGuardar").prop("disabled",true);

	var formData = new FormData($("#formulario")[0]);
	$.ajax({
		url: "../ajax/empresa.php?op=guardaryeditar",
		type: "POST",
		data: formData,
		contentType: false,
		processData: false,
		success: function (datos) {
			Swal.fire({
				icon: 'success',
				title: 'Éxito',
				text: datos,
				showConfirmButton: false,
				timer: 1500
			});
			mostrarform(false);
			//var int = self.setInterval("refresh()",1000);
			listar();
		}
	});
}




function mostrarform(flag) {

	limpiar();

	if (flag) {

		$("#listadoregistros").hide();

		$("#formularioregistros").show();

		$("#btnGuardar").prop("disabled", false);

		$("#btnagregar").hide();

		$("#imagenmuestra").attr("src", "../assets/images/faces/9.jpg").show();
		var imagenMuestra = document.getElementById('imagenmuestra');
		if (!imagenMuestra.src || imagenMuestra.src == "") {
			imagenMuestra.src = '../assets/images/faces/9.jpg';
		}

		//$("#preview").empty();

	}

	else {

		$("#listadoregistros").show();

		$("#formularioregistros").hide();

		$("#btnagregar").show();

	}

}







function mostrar(idempresa) {

	$.post("../ajax/empresa.php?op=mostrar", { idempresa: idempresa }, function (data, status) {

		data = JSON.parse(data);

		mostrarform(true);

		$("#idempresa").val(data.idempresa);

		$("#razonsocial").val(data.nombre_razon_social);

		$("#ncomercial").val(data.nombre_comercial);

		$("#domicilio").val(data.domicilio_fiscal);

		$("#ruc").val(data.numero_ruc);

		$("#tel1").val(data.telefono1);

		$("#tel2").val(data.telefono2);

		$("#correo").val(data.correo);

		$("#web").val(data.web);

		$("#webconsul").val(data.webconsul);

		//$("#imagenmuestra").attr("src","../files/logo/"+data.logo);

		$("#imagenmuestra").show();



		if (data.logo == "") {

			$("#imagenmuestra").attr("src", "../files\\logo\\simagen.png");

			//$("#imagenmuestra").attr("src","c:/sfs/files/logo/simagen.png");

			$("#imagenactual").val(data.logo);

			$("#imagen").val("");

		} else {

			$("#imagenmuestra").attr("src", '../files/logo/' + data.logo);

			//$("#imagenmuestra").attr("src","c://sfs//files//logo//" + data.logo);

			$("#imagenactual").val(data.logo);

			$("#imagen").val("");

		}



		//$("#imagenactual").val(data.logo);

		$("#ubigueo").val(data.ubigueo);

		$("#codubigueo").val(data.codubigueo);



		$("#ciudad").val(data.ciudad);

		$("#distrito").val(data.distrito);

		$("#interior").val(data.interior);

		$("#codigopais").val(data.codigopais);



		//Configuraciones

		$("#igv").val(data.igv);

		$("#porDesc").val(data.porDesc);



		$("#banco1").val(data.banco1);

		$("#cuenta1").val(data.cuenta1);

		$("#banco2").val(data.banco2);

		$("#cuenta2").val(data.cuenta2);

		$("#banco3").val(data.banco3);

		$("#cuenta3").val(data.cuenta3);

		$("#banco4").val(data.banco4);

		$("#cuenta4").val(data.cuenta4);



		$("#cuentacci1").val(data.cuentacci1);

		$("#cuentacci2").val(data.cuentacci2);

		$("#cuentacci3").val(data.cuentacci3);

		$("#cuentacci4").val(data.cuentacci4);

		$("#tipoimpresion").val(data.tipoimpresion);
		$("#textolibre").val(data.textolibre);







	})

}



function listar() {

	tabla = $('#tbllistado').dataTable(

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

				url: '../ajax/empresa.php?op=listar',

				type: "get",

				dataType: "json",

				error: function (e) {

					console.log(e.responseText);

				}

			},

			"bDestroy": true,

			"iDisplayLength": 5,//Paginación

			"order": [[0, "desc"]]//Ordenar (columna,orden)

		}).DataTable();

}


function cambiarImagen() {
	var imagenInput = document.getElementById('imagen');
	imagenInput.click();
}

function removerImagen() {
	var imagenMuestra = document.getElementById('imagenmuestra');
	var imagenActualInput = document.getElementById('imagenactual');
	imagenMuestra.src = '../assets/images/faces/9.jpg';
	imagenActualInput.value = '';
}

// Esto se encarga de mostrar la imagen cuando se selecciona una nueva
document.addEventListener('DOMContentLoaded', function () {
	var imagenMuestra = document.getElementById('imagenmuestra');
	var imagenInput = document.getElementById('imagen');

	imagenInput.addEventListener('change', function () {
		if (imagenInput.files && imagenInput.files[0]) {
			var reader = new FileReader();

			reader.onload = function (e) {
				imagenMuestra.src = e.target.result;
			}

			reader.readAsDataURL(imagenInput.files[0]);
		}
	});
});


/*-----------------------------------------------------*/
//            EVENTO KEYPRESS INPUT NUMERO DOC

$('#ruc').on('input', function (e) {
	var val_numdoc = $(this).val();

	if (val_numdoc.length === 11) {
		$('#btnGuardar').prop('disabled', true); // Deshabilitar el botón de guardar
		$.ajax({
			type: 'POST',
			url: "../ajax/factura.php?op=consultaRucSunat&nroucc=" + val_numdoc,
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
					$('#razonsocial').val(data.nombre);
					$('#domicilio').val(data.direccion);
					$('#interior').val(data.departamento);
					$('#ciudad').val(data.provincia);
					$('#distrito').val(data.distrito);
					$('#codubigueo').val(data.ubigeo);
					$('#ncomercial').val(data.nombre);

					// Restaurar el botón de guardar
					$('#btnGuardar').prop('disabled', false);
				}
			},
			error: function (data) {
				swal.fire({
					title: 'Error!',
					text: 'Problemas al obtener la razón social',
					icon: "error",
					timer: 2000,
					showConfirmButton: false
				});
				// Restaurar el botón de guardar en caso de error
				$('#btnGuardar').prop('disabled', false);
			}
		});
	}
});


// document.getElementById("imagen").onchange = function (e) {

// 	// Creamos el objeto de la clase FileReader

// 	let reader = new FileReader();



// 	// Leemos el archivo subido y se lo pasamos a nuestro fileReader

// 	reader.readAsDataURL(e.target.files[0]);



// 	// Le decimos que cuando este listo ejecute el código interno

// 	reader.onload = function () {

// 		let preview = document.getElementById('preview'),

// 			image = document.createElement('img');



// 		image.src = reader.result;



// 		preview.innerHTML = '';

// 		preview.append(image);

// 	};

// }





init();