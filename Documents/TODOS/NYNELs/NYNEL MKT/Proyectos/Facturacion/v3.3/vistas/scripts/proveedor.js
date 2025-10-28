var tabla;

//Función que se ejecuta al inicio
function init(){
	//mostrarform(false);
	listar();

	$("#formulario").on("submit",function(e)
	{
		guardaryeditar(e);
	})

	// Carga de departamentos
	    $.post("../ajax/persona.php?op=selectDepartamento", function(r){
        $("#iddepartamento").html(r);
        //  $('#iddepartamento').selectpicker('refresh');
    });

}

function llenarCiudad(){
    var iddepartamento=$("#iddepartamento option:selected").val();
    $.post("../ajax/persona.php?op=selectCiudad&id="+iddepartamento, function(r){

       $("#idciudad").html(r);
       //$('#idciudad').selectpicker('refresh');
       $("#idciudad").val("");
    });
	}


function llenarDistrito(){
    var idciudad=$("#idciudad option:selected").val();
    $.post("../ajax/persona.php?op=selectDistrito&id="+idciudad, function(r){

       $("#iddistrito").html(r);
       //$('#iddistrito').selectpicker('refresh');
       $("#iddistrito").val("");
    });
	}

//Función limpiar
function limpiar()
{
	$("#nombres").val("");
	$("#apellidos").val("");
	$("#numero_documento").val("");
	$("#razon_social").val("");
	$("#domicilio_fiscal").val("");
	$("#nombre_comercial").val("");
	$("#ciudad").val("");
	$("#distrito").val("");
	$("#telefono1").val("");
	$("#telefono2").val("");
	$("#email").val("");
	$("#idpersona").val("");
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

		        ],
		"ajax":
				{
					url: '../ajax/persona.php?op=listarp',
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
  e.preventDefault(); //No se activará la acción predeterminada del evento
  $("#btnGuardar").prop("disabled", true);
  var formData = new FormData($("#formulario")[0]);

  $.ajax({
    url: "../ajax/persona.php?op=guardaryeditar",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,

    success: function (datos) {
      Swal.fire({
        title: "Guardado",
        text: datos,
        icon: "success",
        confirmButtonText: "Aceptar",
        allowOutsideClick: false,
      }).then(() => {
        mostrarform(false);
        tabla.ajax.reload();
      });
    },

    error: function () {
      Swal.fire({
        title: "Error",
        text: "No se pudo guardar los datos",
        icon: "error",
        confirmButtonText: "Aceptar",
        allowOutsideClick: false,
      });
    },
  });
  limpiar();
}


function mostrar(idpersona)
{
	$.post("../ajax/persona.php?op=mostrar",{idpersona : idpersona}, function(data, status)
	{
		data = JSON.parse(data);
		mostrarform(true);

		$("#nombres").val(data.nombres);
		$("#apellidos").val(data.apellidos);
		$("#tipo_documento").val(data.tipo_documento);
		//$("#tipo_documento").selectpicker('refresh');
		$("#numero_documento").val(data.numero_documento)
		$("#razon_social").val(data.razon_social);
		$("#nombre_comercial").val(data.nombre_comercial);
		$("#domicilio_fiscal").val(data.domicilio_fiscal);
		$("#iddepartamento").val(data.iddepartamento);
		//$('#iddepartamento').selectpicker('refresh');
		$("#idciudad").val(data.ciudad1);
		$("#iddistrito").val(data.distrito1);
		$("#telefono1").val(data.telefono1);
		$("#telefono2").val(data.telefono2);
		$("#email").val(data.email);
 		$("#idpersona").val(data.idpersona);
		$('#agregarProveedores').modal('show');
		document.getElementById("btnGuardar").innerHTML = "Actualizar";

 	})
}

//Función para desactivar registros
//Función para desactivar registros
function desactivar(idpersona) {
  Swal.fire({
    title: "¿Está seguro?",
    text: "¿Desea desactivar el proveedor?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, desactivar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    allowOutsideClick: false,
  }).then((result) => {
    if (result.isConfirmed) {
      $.post("../ajax/persona.php?op=desactivar", { idpersona: idpersona }, function (e) {
        Swal.fire({
          title: "Desactivado",
          text: e,
          icon: "success",
          confirmButtonText: "Aceptar",
          allowOutsideClick: false,
        }).then(() => {
          tabla.ajax.reload();
        });
      });
    }
  });
}

//Función para activar registros
function activar(idpersona) {
  Swal.fire({
    title: "¿Está seguro?",
    text: "¿Desea activar el proveedor?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, activar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    allowOutsideClick: false,
  }).then((result) => {
    if (result.isConfirmed) {
      $.post("../ajax/persona.php?op=activar", { idpersona: idpersona }, function (e) {
        Swal.fire({
          title: "Activado",
          text: e,
          icon: "success",
          confirmButtonText: "Aceptar",
          allowOutsideClick: false,
        }).then(() => {
          tabla.ajax.reload();
        });
      });
    }
  });
}






//Función para aceptar solo numeros con dos decimales
  function NumCheck(e, field) {
  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46
  key = e.keyCode ? e.keyCode : e.which
  // backspace
          if (key == 8) return true;
          if (key == 9) return true;
        if (key > 47 && key < 58) {
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

//=========================
//Funcion para mayusculas
function mayus(e) {
     e.value = e.value.toUpperCase();
}
//=========================

function validarProveedor(){

    var ndocumento=$("#numero_documento").val();
    $.post("../ajax/persona.php?op=ValidarProveedor&ndocumento="+ndocumento,  function(data,status){
    	data = JSON.parse(data);
    	if (data) {
    		 	$("#numero_documento").attr("style", "background-color: #FF94A0");
    		 	document.getElementById("numero_documento").focus();
    		 	}else{
    		 	$("#numero_documento").attr("style", "background-color: #A7FF64");
    		 	}

   });

	}

/*-----------------------------------------------------*/
//          EVENTO CHANGE SELECT TIPO DOCUMENTO

$('#tipo_documento').change( function () {

  $('#numero_documento').val('');

	$('#razon_social').val('');
  $('#domicilio_fiscal').val('');
  $('#iddepartamento').val('');
  $('#idciudad').val('');
  $('#iddistrito').val('');


  $('#nombres').val('');
  $('#apellidos').val('');


  if ( $('#tipo_documento').val() == 6 || $('#tipo_documento').val() == 1 ) { 
    $('#l_tipo_documento').text('Tipo Documento (Presione Enter):')
  }  else {
    $('#l_tipo_documento').text('Tipo Documento:');
  }

})

/*-----------------------------------------------------*/
//            EVENTO KEYPRESS INPUT NUMERO DOC

$('#numero_documento').keypress(function (e) {

  if (e.which === 13 && !e.shiftKey) { 

    var val_numdoc = $('#numero_documento').val();

    if (val_numdoc == '') {

      swal.fire({
        title: 'Cuidado..!',
        text: "El campo número documento está vacío",
        icon: "warning",
        timer: 2000,
        showConfirmButton: false
      });

    } else {

      if ($('#tipo_documento').val() == 6) { 

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
              // console.log(data);
              $('#razon_social').val(data.nombre);
              $('#domicilio_fiscal').val(data.direccion);

              $('#iddepartamento').val(data.departamento);
              $('#idciudad').val(data.provincia);
              $('#iddistrito').val(data.distrito);

              // $("#iddepartamento option").filter(function() {
              //   return $(this).text() === data.departamento;
              // }).prop("selected", true);

              // $('#iddepartamento').trigger('change');

              // $("#idciudad option").filter(function() {

              //   return $(this).text() === data.provincia;

              // }).prop("selected", true);

              // $('#idciudad').trigger('change');

              // $("#iddistrito option").filter(function() {

              //   return $(this).text() === data.distrito;

              // }).prop("selected", true);

            }

          },
          error: function (data) {
            // alert("Problemas al tratar de enviar el formulario");
            swal.fire({
              title: 'Error!',
              text: 'Problemas al obtener la razón social',
              icon: "error",
              timer: 2000,
              showConfirmButton: false
            });
          }
        });

      } else if ($('#tipo_documento').val() == 1) { 

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
  
              $('#nombres').val(data.nombres);
              $('#apellidos').val(data.apellidoPaterno + ' ' + data.apellidoMaterno);
            
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
  }
});




init();
