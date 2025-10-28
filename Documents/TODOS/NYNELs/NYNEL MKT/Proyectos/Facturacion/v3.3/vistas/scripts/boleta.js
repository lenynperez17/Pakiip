var tabla;
var tablaArti;
var tablaArti2;
var numero = "";
toastr.options = {
  closeButton: false,
  debug: false,
  newestOnTop: false,
  progressBar: false,
  rtl: false,
  positionClass: "toast-bottom-center",
  preventDuplicates: false,
  onclick: null,
};

$idempresa = $("#idempresa").val();
$iva = $("#iva").val();

//Función que se ejecuta al inicio

function init() {
  $("#razon_social").val("VARIOS");
  $("#numero_documento").val("VARIOS");

  // $(document).ready(function() {
  //   mayor700();
  // });

  $("#formulario").on("submit", function (e) {
    guardaryeditarBoleta(e);
  });

  $("#formularioncliente").on("submit", function (e) {
    guardaryeditarcliente(e);
  });

  $("#formularionarticulo").on("submit", function (e) {
    guardaryeditararticulo(e);
  });

  // Carga de combo para tributo =====================
  $.post("../ajax/factura.php?op=selectTributo", function (r) {
    $("#codigo_tributo_18_3").html(r);
    //$('#codigo_tributo_18_3').selectpicker('refresh');
  });

  // Carga de combo para vendedores =====================

  $.post(
    "../ajax/vendedorsitio.php?op=selectVendedorsitio&idempresa=" + $idempresa,
    function (r) {
      $("#vendedorsitio").html(r);
      //$('#vendedorsitio').selectpicker('refresh');
    }
  );

  // Carga de combo para tributo =====================
  $.post("../ajax/boleta.php?op=selectAlmacen", function (r) {
    $("#almacenlista").html(r);
    //$('#almacenlista').selectpicker('refresh');
  });

  // Carga de combo para vendedores ======================

  // Carga de unidades de medida

  // Carga tipo de cambio =====================

  // $.post("../ajax/factura.php?op=tcambiodia", function(r){
  //         $("#tcambio").val(r);
  // });

  $.post(
    "../ajax/articulo.php?op=selectAlmacen&idempresa=" + $idempresa,
    function (r) {
      $("#idalmacennarticulo").html(r);
      //$('#idalmacennarticulo').selectpicker('refresh');
    }
  );

  $.post("../ajax/articulo.php?op=selectFamilia", function (r) {
    $("#idfamilianarticulo").html(r);
    //$('#idfamilianarticulo').selectpicker('refresh');
  });

  $.post("../ajax/factura.php?op=selectunidadmedidanuevopro", function (r) {
    $("#umedidanp").html(r);
    //$('#umedidanp').selectpicker('refresh');
  });

  cont = 0;

  conNO = 1;

  mostrarform(false);
  //listarenvioautomatico();
  //onOff=false;
  //counter=setInterval(timer, 5000);
  listarenvioautomatico();
  //listar();
  //console.log(listar());
}
let count = 0;
function timer() {
  count++;
  tabla.ajax.reload(null, false);
}

//PARA ACTUALIZAR ESTADO

let onOff = true;
function pause() {
  if (onOff) {
    onOff = false;
    //counter=setInterval(timer, 5000);
    clearInterval(counter);
    //listar();
    listarenvioautomatico();
    //$("#estadofact").load(" #estadofact");
    document.getElementById("chk1").checked = false;
  } else {
    onOff = true;
    listarenvioautomatico();
    counter = setInterval(timer, 5000);
    //document.getElementById("chk1").checked = true;
    //document.getElementById("chk1").disabled = true;
  }
}

//PARA ACTUALIZAR ESTADO

function nuevoarticulo() {
  $("#modalnuevoarticulo").modal("show");
}

function incremetarNum() {
  var serie = $("#serie option:selected").val();
  $.post(
    "../ajax/boleta.php?op=autonumeracion&ser=" +
    serie +
    "&idempresa=" +
    $idempresa,
    function (r) {
      var n2 = pad(r, 0);
      $("#numero_boleta").val(n2);
      var SerieReal = $("#serie option:selected").text();
      $("#SerieReal").val(SerieReal);
    }
  );
  document.getElementById("tipo_doc_ide").focus();
}

//Función para poner ceros antes del numero siguiente de la factura

function pad(n, length) {
  var n = n.toString();
  while (n.length < length) n = "0" + n;
  return n;
}

//Fin de Función

//Función limpiar

function limpiar() {
  $("#idcliente").val("N");
  $("#numero_guia").val("");
  $("#cliente").val("");
  $("#ipagado_input").val("");
  //$("#serie").val("");
  $("#numero_boleta").val("");
  $("#impuesto").val("0");
  $("#total_boleta").val("");
  $("#subtotal_boleta").val("");
  $("#total_igv").val("");

  $(".filas").remove();
  $("#total").html("0");
  $("#tcambio").val("0");
  document.getElementById("mensaje700").style.display = "none";
  $("#tipo_doc_ide").val("0");
  $.post("../ajax/factura.php?op=selectTributo", function (r) {
    $("#codigo_tributo_18_3").html(r);
    //$('#codigo_tributo_18_3').selectpicker('refresh');
  });

  $("#codigo_tributo_h").val($("#codigo_tributo_18_3 option:selected").val());
  $("#total").val("");
  $("#nroreferencia").val("");
  $("#total_final").val("");

  $("#ipagado").html("0");
  $("#saldo").html("0");

  $("#ipagado_final").val("");
  $("#saldo_saldo").val("");
  $("#tipoboleta").val("productos");

  $("#montocuota").val("");
  $("#tipopago").val("Contado");
  $("#ccuotas").val("0");

  //Obtenemos la fecha actual
  $("#fecha_emision_01").prop("disabled", false);

  const inputFechaVenc = document.getElementById("fechavenc");

  // Establecer el valor máximo permitido para la fecha de vencimiento en 4 días a partir de la fecha actual
  const fechaMax = new Date();
  fechaMax.setDate(fechaMax.getDate() + 4);

  inputFechaVenc.addEventListener("change", function () {
    const fechaSeleccionada = new Date(this.value);
    const fechaActual = new Date();
    const diasDiferencia = Math.round(
      (fechaSeleccionada - fechaActual) / (1000 * 60 * 60 * 24)
    );

    // Verificar si se ha seleccionado una fecha anterior a la actual
    if (
      fechaSeleccionada < fechaActual &&
      fechaSeleccionada.toDateString() !== fechaActual.toDateString()
    ) {
      Swal.fire({
        title: "Error",
        text: "La fecha de vencimiento no puede ser posterior a 6 días a partir de la fecha actual normado por sunat",
        icon: "error",
        confirmButtonText: "OK",
      });
      this.value = fechaActual.toISOString().slice(0, 10);
    }
    // Verificar si se ha seleccionado una fecha posterior a 4 días a partir de la fecha actual
    else if (diasDiferencia > 4) {
      Swal.fire({
        title: "Error",
        text: "La fecha de vencimiento no puede ser posterior a 6 días a partir de la fecha actual",
        icon: "error",
        confirmButtonText: "OK",
      });
      this.value = fechaActual.toISOString().slice(0, 10);
    }
  });

  document.getElementById("tarjetadc").checked = false;
  document.getElementById("transferencia").checked = false;
  $("#tadc").val("0");
  $("#trans").val("0");
  var now = new Date();
  var day = ("0" + now.getDate()).slice(-2);
  var month = ("0" + (now.getMonth() + 1)).slice(-2);
  var today = now.getFullYear() + "-" + month + "-" + day;
  $("#fecha_emision_01").val(today);
  $("#fechavenc").val(today);
  cont = 0;
  conNO = 1;
}

//Función mostrar formulario

function mostrarform(flag) {
  limpiar();
  if (flag) {
    $("#listadoregistros").hide();
    $("#formularioregistros").show();
    //$("#btnGuardar").prop("disabled",false);
    $("#btnagregar").hide();
    listarArticulos();
    //listarArticulosItem();
    //listarServicios();
    $("#btnGuardar").hide();
    $("#btnCancelar").show();
    $("#btnAgregarArt").show();
    $("#btnAgregarCli").hide();
    $("#refrescartabla").hide();
    $.post("../ajax/boleta.php?op=selectSerie", function (r) {
      $("#serie").html(r);
      //$("#serie").selectpicker('refresh');
      var serieL = document.getElementById("serie");
      var opt = serieL.value;
      $.post(
        "../ajax/boleta.php?op=autonumeracion&ser=" +
        opt +
        "&idempresa=" +
        $idempresa,
        function (r) {
          var n2 = pad(r, 0);
          $("#numero_boleta").val(n2);
          var SerieReal = $("#serie option:selected").text();
          $("#SerieReal").val(SerieReal);
        }
      );
    });

    document.getElementById("codigob").focus();
  } else {
    $("#listadoregistros").show();
    $("#formularioregistros").hide();
    $("#btnagregar").show();
    $("#refrescartabla").show();
  }
}

//Función cancelarform

function cancelarform() {
  Swal.fire({
    title: "¿Desea cancelar boleta?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí",
    cancelButtonText: "No",
  }).then((result) => {
    if (result.value) {
      window.location = "boleta";
    } else {
      limpiar();
      evaluar2();
      detalles = 0;
      mostrarform(false);
    }
  });
}

//Función Listar

function listar() {
  tabla = $("#tbllistado")
    .dataTable({
      scrollCollapse: true,
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "Bfrtip", //Definimos los elementos del control de tabla
      searching: true,
      searchHighlight: true,
      buttons: [],
      ajax: {
        url: "../ajax/boleta.php?op=listar&idempresa=" + $idempresa,
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },

      bDestroy: true,
      iDisplayLength: 5, //Paginación
      order: [[0, "desc"]], //Ordenar (columna,orden)
    })
    .DataTable();

}

//Función Listar

function listarenvioautomatico() {
  tabla = $("#tbllistado")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "Bfrtip", //Definimos los elementos del control de tabla
      buttons: [],
      ajax: {
        url: "../ajax/boleta.php?op=envioautomatico&idempresa=" + $idempresa,
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },
      rowCallback: function (row, data) {
        //$(row).addClass('selected');
        //$(row).id(0).addClass('selected');
      },
      bDestroy: true,
      iDisplayLength: 15, //Paginación
      order: [[0, "desc"]], //Ordenar (columna,orden)
    })
    .DataTable();
  let refreshTable = setInterval(function () {
    tabla.ajax.reload();
  }, 10000); // Actualiza la tabla cada 30 segundos
}

function cerrarModal() {
  $("#myModalArt").modal("hide");
  // var modal = document.getElementById("myModalArt");
  // modal.style.display = "none";
  $(".modal-backdrop").remove();
}

//Función ListarClientes
function listarClientes() {
  tabla = $("#tblaclientes")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      dom: "Bfrtip", //Definimos los elementos del control de tabla
      buttons: [],
      ajax: {
        url: "../ajax/boleta.php?op=listarClientesboleta",
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },

      bDestroy: true,
      iDisplayLength: 5, //Paginación
      order: [[0, "desc"]], //Ordenar (columna,orden)
    })
    .DataTable();
}

//Función ListarArticulos

function listarArticulos() {
  tpb = $("#tipoboleta").val();
  $tipoprecio = $("#tipoprecio").val();
  $iteno = $("#itemno").val();
  almacen = $("#almacenlista").val();
  tablaArti = $("#tblarticulos")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables
      aServerSide: true, //Paginación y filtrado realizados por el servidor
      searching: true,

      dom: "Bfrtip", //Definimos los elementos del control de tabla
      buttons: [],
      ajax: {
        url:
          "../ajax/boleta.php?op=listarArticulosboleta&tprecio=" +
          $tipoprecio +
          "&tb=" +
          tpb +
          "&itm=" +
          $iteno +
          "&alm=" +
          almacen,
        type: "get",
        dataType: "json",
        error: function (e) {
          console.log(e.responseText);
        },
      },

      //Para cambiar el color del stock cuando es 0
      fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        // Agregar el input y el botón en la columna correspondiente (índice 5)
        $(nRow)
          .find("td:eq(5)")
          .html(
            '<div class="row">' +
            '<div class="col-8">' +
            '<input type="number" class="form-control hidebutton text-end" style="width: 100px;" id="product_stock" value="' +
            aData[5] +
            '" />' +
            "</div>" +
            '<div class="col-2 p-0">' +
            '<button class="btn btn-secondary m-0" id="btn_editarstock"><i class="fas fa-save"></i></button>' +
            "</div>" +
            "</div>"
          );

        if (aData[5] == "0.00") {
          $("td", nRow).css("background-color", "#fd96a9");

          // Agregar evento de clic al botón de guardar
          $(nRow)
            .find("#btn_editarstock")
            .click(function () {
              var newStock = $(nRow).find("#product_stock").val();

              var idarticulo = aData[8];

              var formData = new FormData();
              formData.append("idarticuloproduct", idarticulo);
              formData.append("stockproduct", newStock);

              console.log("Nuevo data:", formData);
              $.ajax({
                url: "../ajax/articulo.php?op=editarstockarticulo",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                  Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    showConfirmButton: false,
                    timer: 1500,
                    text: response,
                  });
                  $("#tblarticulos").DataTable().ajax.reload();
                },
                error: function () {
                  Swal.fire({
                    icon: "error",
                    title: "Error al guardar",
                    text: "Ha ocurrido un error al actualizar los datos",
                  });
                },
              });
            });
        } else {
          $("td", nRow).css("background-color", "");

          // Agregar evento de clic al botón de guardar
          $(nRow)
            .find("#btn_editarstock")
            .click(function () {
              var newStock = $(nRow).find("#product_stock").val();

              var idarticulo = aData[8];

              var formData = new FormData();
              formData.append("idarticuloproduct", idarticulo);
              formData.append("stockproduct", newStock);

              Swal.fire({
                title: "Aún tienes suficiente stock",
                text: "¿Deseas agregar más?",
                showDenyButton: true,
                confirmButtonText: "Sí",
                denyButtonText: "No",
              }).then((result) => {
                if (result.isConfirmed) {
                  $.ajax({
                    url: "../ajax/articulo.php?op=editarstockarticulo",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                      Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        showConfirmButton: false,
                        timer: 1500,
                        text: response,
                      });
                      $("#tblarticulos").DataTable().ajax.reload();
                    },
                    error: function () {
                      Swal.fire({
                        icon: "error",
                        title: "Error al guardar",
                        text: "Ha ocurrido un error al actualizar los datos",
                      });
                    },
                  });
                } else if (result.isDenied) {
                  Swal.fire("Los cambios no se guardaron", "", "info");
                  $("#tblarticulos").DataTable().ajax.reload();
                }
              });
            });
        }

        // Agregar evento de clic a la imagen
        $(nRow)
          .find("td:eq(6) img")
          .css("cursor", "pointer") // Cambiar el cursor a una mano
          .click(function () {
            mostrarModal(aData); // Llamada a la función para mostrar el modal con los datos
          });
      },

      bDestroy: true,
      iDisplayLength: 5, //Paginación
      order: [[5, "desc"]], //Ordenar (columna,orden)
    })
    .DataTable();
  $("div.dataTables_filter input").focus(); // PARA PONER INPUT FOCUS

  $("#tblarticulos").DataTable().ajax.reload();
  $("#tblarticulos [type='search']").focus();
}

/* ---------------------------------------------------- */
//                 VISUALIZAR IMG PRODUCT

function mostrarModal(data) {
  // Aquí puedes acceder a los datos de la fila utilizando el índice correspondiente
  var nombre = data[1];
  var img_product = data[7];
  // console.log(data);

  var modalFooter =
    '<div class="modal-footer">' +
    '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>' +
    "</div>";

  Swal.fire({
    title: "Detalles del Artículo",
    html:
      '<img class="mb-3" style="width:80%;" src="' +
      img_product +
      '" />' +
      '<p class="text-center">' +
      nombre +
      "</p>",

    showCancelButton: false,
    showConfirmButton: true,
    confirmButtonText: "Cerrar",
  });
}

/* ---------------------------------------------------- */
//                   CALCULAR VUELTO

function cleanNumber(value) {
  return parseFloat(value.replace(/,/g, ''));
}

$("#ipagado").on("input", function () {
  var importe_pagado = cleanNumber($(this).val());
  var total_pagar = cleanNumber($("#total").text());

  var vuelto;

  if (isNaN(importe_pagado)) {
    $("#vuelto_text").html("Vuelto : ");
    $("#saldo").text("0.00");
    return;
  }

  if (importe_pagado > total_pagar) {
    vuelto = (importe_pagado - total_pagar).toFixed(2);
    $("#vuelto_text").html("Vuelto : ");
    $("#saldo").text(vuelto).css("color", "green");
  } else {
    $("#vuelto_text").html(
      'Vuelto :  <span style="color: red; font-weight: bold">Falta</span>'
    );
    vuelto = (total_pagar - importe_pagado).toFixed(2);
    $("#saldo").text(vuelto).css("color", "red");
  }
});


function listarServicios() {
  tablaArti = $("#tblaservicios")
    .dataTable({
      aProcessing: true, //Activamos el procesamiento del datatables

      aServerSide: true, //Paginación y filtrado realizados por el servidor

      dom: "Bfrtip", //Definimos los elementos del control de tabla

      buttons: [],

      ajax: {
        url: "../ajax/boleta.php?op=listarArticulosservicio",

        type: "get",

        dataType: "json",

        error: function (e) {
          console.log(e.responseText);
        },
      },

      bDestroy: true,

      iDisplayLength: 8, //Paginación

      order: [[0, "desc"]], //Ordenar (columna,orden)
    })
    .DataTable();

  $("#tblaservicios").DataTable().ajax.reload();
}

// //Función ListarArticulos

// function listarArticulosItem()

// {

//     tpb=$("#tipoboleta").val();

//     $tipoprecio=$('#tipoprecio').val();

//     tablaArti2=$('#tblarticulositem').dataTable(

//     {

//         "aProcessing": true,//Activamos el procesamiento del datatables

//         "aServerSide": true,//Paginación y filtrado realizados por el servidor

//         dom: 'Bfrtip',//Definimos los elementos del control de tabla

//         buttons: [

//                 ],

//         "ajax":

//                 {

//                     url: '../ajax/boleta.php?op=listarArticulosboletaitem',

//                     type : "get",

//                     dataType : "json",

//                     error: function(e){

//                     console.log(e.responseText);

//                     }

//                 },

//                 //Para cambiar el color del stock cuando es 0

//                 "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {

//                     if ( aData[5] == "0.00" )

//                     {

//                         $('td', nRow).css('background-color', '#fd96a9');

//                     }

//                     else

//                     {

//                         $('td', nRow).css('background-color', '');

//                     }

//                 },

//         "bDestroy": true,

//         "iDisplayLength": 5,//Paginación

//         "order": [[ 5, "desc" ]]//Ordenar (columna,orden)

//     }).DataTable();

//   $('#tblarticulositem').DataTable().ajax.reload();

// }

//Función para guardar o editar

function guardaryeditarBoleta(e) {
  //HEREEE
  e.preventDefault(); //No se activará la acción predeterminada del evento
  // Verificar si la tabla tiene al menos una fila
  if ($("#tipo_doc_ide").val() === "") {
    swal.fire({
      title: "Por favor, selecciona un tipo de documento.",
      icon: "warning",
      showCancelButton: false,
      showConfirmButton: true,
    });
    return;
  }

  var tipoDoc = $("#tipo_doc_ide").val();

  // Suponiendo que el valor de "DNI" en el select es "1" o "2", según lo que mencionaste
  if (tipoDoc === "1" || tipoDoc === "2") {
    var dni = $("#numero_documento");
    var regexDNI = /^[0-9]{8}$/;

    if (!regexDNI.test(dni.val())) {
      swal.fire({
        title: "El DNI debe ser de 8 dígitos.",
        icon: "warning",
        showCancelButton: false,
        showConfirmButton: true,
      });
      dni.focus();
      return;
    }
  }


  var regexNombre = /^[A-Za-záéíóúÁÉÍÓÚñÑ ]+$/;
  var nombre = document.getElementById("razon_social");
  if (!regexNombre.test(nombre.value)) {
    swal.fire({
      title: "Por favor, introduce un nombre válido (sin caracteres especiales).",
      icon: "warning",
      showCancelButton: false,
      showConfirmButton: true,
    });
    nombre.focus();
    return;
  }


  var rowCount = $("#detalles tbody tr").length;
  if (rowCount == 0) {
    swal.fire({
      title: "La tabla está vacía, por favor agregue al menos un producto.",
      icon: "error",
      showCancelButton: false,
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }

  var cant = document.getElementsByName("cantidad_item_12[]");
  var prec = document.getElementsByName("precio_unitario[]");
  var stk = document.getElementsByName("stock[]");
  sw = 0;
  for (var i = 0; i < cant.length; i++) {
    var inpC = cant[i];
    var inpP = prec[i];
    var inStk = stk[i];
    if (
      inpP.value == 0.0 ||
      inpP.value == "" ||
      inpC.value == 0 ||
      inStk.value == 0 ||
      $("#numero_boleta").val() == ""
    ) {
      sw = sw + 1;
    }
  }

  if (sw != 0) {
    swal.fire({
      title: "Revizar précio!, cantidad, número de boleta o Stock",
      icon: "error",
      showCancelButton: false,
      confirmButtonText: "Ok",
    });
    inpP.focus();
  } else {
    swal
      .fire({
        title: "¿Desea emitir la boleta?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, emitir boleta",
        cancelButtonText: "Cancelar",
      })
      .then((result) => {
        if (result.value) {
          capturarhora();
          var formData = new FormData($("#formulario")[0]);
          $.ajax({
            url: "../ajax/boleta.php?op=guardaryeditarBoleta",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (datos) {
              tipoimpresion();
              mostrarform(false);
              refrescartabla();
            },
          });
          limpiar();
          //$("#numero_documento").val("");
          //$("#razon_social").val("");
          //$("#domicilio_fiscal").val("");
          $("#tdescuentoL").text("");
          $("#ipagado_input").val("");
          $("#ipagado_input").replaceWith(
            '<h6 id="ipagado">' + $("#ipagado_final").val() + "</h6>"
          );
          sw = 0;
        }
      });
  }

  setTimeout(function () {
    $("#modalPreview2").modal("hide");
  }, 6000);
}

function tipoimpresion() {
  $.post(
    "../ajax/boleta.php?op=mostrarultimocomprobanteId",
    function (data, status) {
      data = JSON.parse(data);
      if (data != null) {
        $("#idultimocom").val(data.idboleta);
      } else {
        $("#idultimocom").val("");
      }

      if (data.tipoimpresion == "00") {
        var rutacarpeta = "../reportes/exTicketBoleta.php?id=" + data.idboleta;
        $("#modalCom").attr("src", rutacarpeta);
        $("#modalPreview2").modal("show");
      } else if (data.tipoimpresion == "01") {
        var rutacarpeta = "../reportes/exBoleta.php?id=" + data.idboleta;
        $("#modalCom").attr("src", rutacarpeta);
        $("#modalPreview2").modal("show");
      } else {
        var rutacarpeta =
          "../reportes/exBoletaCompleto.php?id=" + data.idboleta;
        $("#modalCom").attr("src", rutacarpeta);
        $("#modalPreview2").modal("show");
      }
    }
  );
}

function guardaryeditararticulo(e) {
  e.preventDefault();

  var formData = new FormData($("#formularionarticulo")[0]);
  $.ajax({
    url: "../ajax/articulo.php?op=guardarnuevoarticulo",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (datos) {
      Swal.fire({
        title: "Resultado",
        text: datos,
        icon: "success",
        confirmButtonText: "Aceptar",
      });
      tabla.ajax.reload();
      refrescartabla();
      limpiarnuevoarticulo();
    },
  });

  $("#modalnuevoarticulo").modal("hide");
}

$(function () {
  $("#myModalArt").on("shown.bs.modal", function (e) {
    //$("#tblarticulos [type='search']").focus();
    $("div.dataTables_filter input").focus();
  });
});

$(function () {
  $("#modalnuevoarticulo").on("shown.bs.modal", function (e) {
    $(".focus").focus();
  });
});

function limpiarnuevoarticulo() {
  $("#nombrenarticulo").val("");

  $("#stocknarticulo").val("");

  $("#precioventanarticulo").val("");

  $("#codigonarticulonarticulo").val("");

  $("#descripcionnarticulo").val("");
}

function mostrarultimocomprobante() {
  $.post(
    "../ajax/boleta.php?op=mostrarultimocomprobante",
    function (data, status) {
      //data=JSON.parse(data);
      data = JSON.parse(data);
      if (data != null) {
        $("#idultimocom").val(data.numeracion_07); //Se captura el numero de la boleta
      } else {
        $("#idultimocom").val("");
      }
      var rutacarpeta = "../boletasPDF/"; //Rura deonde se encuentra pdf
      var nombrearchivopdf = $("#idultimocom").val(); //Aignacion de numero a variable oculta
      var extension = ".pdf"; //Extension
      var fileName = rutacarpeta.concat(nombrearchivopdf, extension); //Concatenado de variables
      $("#modalCom").attr("src", fileName); //Accediendo al atributo source del modal
      $("#modalPreview").modal("show");
    }
  ); // codigo igual hasta aqui.
}

function preticket() {
  $.post(
    "../ajax/boleta.php?op=mostrarultimocomprobanteId",
    function (data, status) {
      data = JSON.parse(data);
      if (data != null) {
        $("#modalComticket").val(data.idboleta);
      } else {
        $("#modalComticket").val("");
      }

      var rutacarpeta = "../reportes/exTicketBoleta.php?id=" + data.idboleta;
      $("#modalComticket").attr("src", rutacarpeta);
      $("#modalPreviewticket").modal("show");
    }
  ); // codigo igual hasta aqui.
}

function preticket2(idboleta) {
  var rutacarpeta = "../reportes/exTicketBoleta.php?id=" + idboleta;
  $("#modalComticket").attr("src", rutacarpeta);
  $("#modalPreviewticket").modal("show");
}

function prea42copias() {
  $.post(
    "../ajax/boleta.php?op=mostrarultimocomprobanteId",
    function (data, status) {
      data = JSON.parse(data);
      if (data != null) {
        $("#idultimocom").val(data.idboleta);
      } else {
        $("#idultimocom").val("");
      }

      var rutacarpeta = "../reportes/exBoleta.php?id=" + data.idboleta;
      $("#modalCom").attr("src", rutacarpeta);
      $("#modalPreview2").modal("show");
    }
  ); // codigo igual hasta aqui.
}

function prea42copias2(idboleta) {
  var rutacarpeta = "../reportes/exBoleta.php?id=" + idboleta;
  $("#modalCom").attr("src", rutacarpeta);
  $("#modalPreview2").modal("show");
}

function prea4completo() {
  $.post(
    "../ajax/boleta.php?op=mostrarultimocomprobanteId",
    function (data, status) {
      data = JSON.parse(data);
      if (data != null) {
        $("#idultimocom").val(data.idboleta);
      } else {
        $("#idultimocom").val("");
      }
      var rutacarpeta = "../reportes/exBoletaCompleto.php?id=" + data.idboleta;
      $("#modalCom").attr("src", rutacarpeta);
      $("#modalPreview2").modal("show");
    }
  ); // codigo igual hasta aqui.
}

function prea4completo2(idboleta) {
  var rutacarpeta = "../reportes/exBoletaCompleto.php?id=" + idboleta;
  $("#modalCom").attr("src", rutacarpeta);
  $("#modalPreview2").modal("show");
}

function actualizarNum(e) {
  var numero = $("#numero_boleta").val();

  var idnumeracion = $("#serie option:selected").val();

  $.post(
    "../ajax/boleta.php?op=actualizarNumero&Num=" +
    numero +
    "&Idnumeracion=" +
    idnumeracion,
    function (r) { }
  );
}

function mostrar(idboleta) {
  $.post(
    "../ajax/boleta.php?op=mostrar",
    { idboleta: idboleta },
    function (data, status) {
      data = JSON.parse(data);

      mostrarform(true);

      $("#idboleta").val(data.idboleta);

      $("#numero_factura").val(data.numeracion_08);

      $("#numero_documento").val(data.numero_documento);

      $("#razon_social").val(data.cliente);

      $("#domicilio_fiscal").val(data.domicilio_fiscal);

      $("#fecha_emision").prop("disabled", true);

      $("#fecha_emision").val(data.fecha);

      $("#subtotal").html(data.total_operaciones_gravadas_monto_18_2);

      $("#igv_").html(data.sumatoria_igv_22_1);

      $("#total").html(data.importe_total_venta_27);

      //Ocultar y mostrar los botones

      $("#btnGuardar").hide();

      $("#btnCancelar").show();

      $("#btnAgregarArt").hide();
    }
  );

  $.post("../ajax/boleta.php?op=listarDetalle&id=" + idfactura, function (r) {
    $("#detalles").html(r);
  });
}

//Función para anular registros

function anular(idboleta) {
  swal
    .fire({
      title: "¿Está Seguro de anular la Boleta?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Si, Anular",
    })
    .then((result) => {
      if (result.value) {
        $.post(
          "../ajax/boleta.php?op=anular",
          { idboleta: idboleta },
          function (e) {
            swal.fire("Anulado", e, "success");
            tabla.ajax.reload();
          }
        );
      }
    });
}

function mayus(e) {
  e.value = e.value.toUpperCase();
}

function baja(idboleta) {
  var f = new Date();
  cad = f.getHours() + ":" + f.getMinutes() + ":" + f.getSeconds();

  swal.fire({
    title: "Escriba el motivo de baja de la boleta.",
    input: "textarea",
    showCancelButton: true,
    confirmButtonText: "Aceptar",
    cancelButtonText: "Cancelar",
    preConfirm: (result) => {
      if (!result) {
        return false;
      }
      var f = new Date();
      cad = f.getHours() + ":" + f.getMinutes() + ":" + f.getSeconds();
      $.post(
        "../ajax/boleta.php?op=baja&comentario=" + result + "&hora=" + cad,
        { idboleta: idboleta },
        function (e) {
          swal.fire("Exito", e, "success");
          tabla.ajax.reload();
        }
      );
    },
  });
}

function accesoTicket(idboleta) {
  var f = new Date();
  cad = f.getHours() + ":" + f.getMinutes() + ":" + f.getSeconds();
  Swal.fire({
    title: "Escriba el nro de ticket.",
    input: "textarea",
    showCancelButton: true,
    confirmButtonText: "Ok",
    cancelButtonText: "Cancelar",
    showLoaderOnConfirm: true,
    preConfirm: (result) => {
      return new Promise((resolve) => {
        if (result) {
          $.post(
            "../ajax/boleta.php?op=baja&comentario=" + result + "&hora=" + cad,
            { idboleta: idboleta },
            function (e) {
              resolve(e);
            }
          );
        }
      });
    },
    allowOutsideClick: () => !Swal.isLoading(),
  }).then((result) => {
    if (result.value) {
      Swal.fire({
        title: result.value,
      });
      tabla.ajax.reload();
    }
  });
}

//Declaración de variables necesarias para trabajar con las compras y

//sus detalles

var impuesto = 18;

var cont = 0;

var detalles = 0;

//$("#guardar").hide();

$("#btnGuardar").hide();

$("#tipo_comprobante").change(marcarImpuesto);

//$("#tipo_comprobante").change(Correlativo);

function marcarImpuesto() {
  var tipo_comprobante = $("#tipo_comprobante option:selected").text();

  if (tipo_comprobante == "FACTURA") {
    $("#impuesto").val(impuesto);
  } else {
    $("#impuesto").val("0");
  }
}

function agregarCliente(
  idpersona,
  razon_social,
  numero_documento,
  domicilio_fiscal,
  tipo_documento
) {
  if (idpersona != "") {
    $("#idcliente").val(idpersona);

    $("#numero_documento").val(numero_documento);

    $("#razon_social").val(razon_social);

    $("#domicilio_fiscal").val(domicilio_fiscal);

    $("#tipo_documento_cliente").val(tipo_documento);

    $("#myModalCli").modal("hide");
  } else {
    Swal.fire({
      title: "Error al ingresar el detalle, revisar los datos del cliente",
      icon: "warning",
    });
  }
}

//Función para aceptar solo numeros con dos decimales

function NumCheck(e, field) {
  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which;

  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("precio_unitario[]").focus();
  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {
    if ($(field).val() === "") return true;

    var existePto = /[.]/.test($(field).val());

    if (existePto === false) {
      regexp = /.[0-9]{10}$/;
    } else {
      regexp = /.[0-9]{2}$/;
    }

    return !regexp.test($(field).val());
  }

  if (key == 46) {
    if (field.val() === "") return false;

    regexp = /^[0-9]+$/;

    return regexp.test(field.val());
  }

  return false;
}

//Función para aceptar solo numeros con dos decimales

function NumCheck2(e, field) {
  // Backspace = 8, Enter = 13, ’0′ = 48, ’9′ = 57, ‘.’ = 46

  key = e.keyCode ? e.keyCode : e.which;

  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("codigob").focus();
  }

  // backspace

  if (key == 8) return true;

  if (key == 9) return true;

  if (key > 47 && key < 58) {
    if (field.val() === "") return true;

    var existePto = /[.]/.test(field.val());

    if (existePto === false) {
      regexp = /.[0-9]{10}$/;
    } else {
      regexp = /.[0-9]{2}$/;
    }

    return !regexp.test(field.val());
  }

  if (key == 46) {
    if (field.val() === "") return false;

    regexp = /^[0-9]+$/;

    return regexp.test(field.val());
  }

  return false;
}

function agregarDetalle(
  tipoagregacion,
  idarticulo,
  familia,
  codigo_proveedor,
  codigo,
  nombre,
  precio_factura,
  stock,
  unidad_medida,
  precio_unitario,
  cicbper,
  mticbperuSunat,
  factorconversion,
  factorc,
  descrip,
  tipoitem
) {
  var cantidad = 0;
  if (idarticulo != "") {
    var subtotal = cantidad * precio_factura;
    var igv = subtotal * ($iva / 100);
    //var pvu = document.getElementsByName("pvu_");
    var total_fin;
    var contador = 1;
    if (parseFloat(stock) == "0") {
      Swal.fire({
        title: "El stock es 0, actualizar stock!",
        icon: "warning",
      });
      $("#codigob").val("");
      quitasuge3();
    } else {
      if ($("#codigo_tributo_18_3").val() == "9997") {
        exo = "";
        op = "";
        precioOculto = precio_factura;
        precio_factura = precio_factura;
        rd = "readonly";
      } else {
        op = "";
        exo = "";
        rd = "";
        precioOculto = precio_factura;
      }

      // TIPO CAMBIO

      if ($("#tipo_moneda_24").val() === "USD") {
        var tipoCambio = parseFloat($("#tcambio").val());

        precio_factura /= tipoCambio;
        precioOculto /= tipoCambio;

        precio_factura = precio_factura.toFixed(2);
        precioOculto = precioOculto.toFixed(2);
      }

      // <textarea class="" name="descdet[]" id="descdet[]" rows="1" cols="70" onkeyup="mayus(this)" onkeypress="return focusDescdet(event, this)">'+descrip+'</textarea>
      var fila =
        '<tr class="filas" id="fila' +
        cont +
        '">' +
        '<td><i class="fa fa-close" onclick="eliminarDetalle(' +
        cont +
        ')" style="color:red;"  data-toggle="tooltip" title="Eliminar item"></i></td>' +
        '<td><span name="numero_orden" id="numero_orden' +
        cont +
        '" ></span>' +
        '<input type="hidden" name="numero_orden_item_29[]" id="numero_orden_item_29[]" value="' +
        conNO +
        '"  ></td>' +
        '<td><input type="hidden" name="idarticulo[]" style="font-family: times, serif; font-size:14pt; font-style:italic" value="' +
        idarticulo +
        '">' +
        nombre +
        "</td>" +
        '<td hidden><select name="codigotributo[]" class="" style="display:none;"> <option value="1000">IGV</option><option value="9997">EXO</option><option value="9998">INA</option></select>' +
        '<select name="afectacionigv[]" class="" style="display:none;"> <option value="10">10-GOO</option><option value="20">20-EOO</option><option value="30">30-FRE</option></select></td>' +
        '<td><input type="text"  class="" required="true" name="cantidad_item_12[]" id="cantidad_item_12[]"  onBlur="modificarSubototales(1)" size="6" onkeypress="return NumCheck(event, this)" value="1" ></td>' +
        '<td><input type="text"  class="" name="descuento[]" id="descuento[]"  onBlur="modificarSubototales(1)" size="2" onkeypress="return NumCheck(event, this)" >' +
        '<span name="SumDCTO" id="SumDCTO' +
        cont +
        '" style="display:none"></span> <input type="hidden"  required="true" class="" name="sumadcto[]" id="sumadcto[]" >  </td>' +
        '<td hidden><input type="hidden" name="codigo_proveedor[]" id="codigo_proveedor[]" value="' +
        codigo_proveedor +
        '">' +
        codigo_proveedor +
        "</td>" +
        '<td hidden><input type="text" name="codigo[]" id="codigo[]" value="' +
        codigo +
        '" class="" style="display:none;" ></td>' +
        '<td><input type="hidden" name="unidad_medida[]" id="unidad_medida[]" value="' +
        unidad_medida +
        '">' +
        unidad_medida +
        "</td>" +
        '<td><input type="text" class="" name="precio_unitario[]" id="precio_unitario[]" value="' +
        precio_factura +
        '" onBlur="modificarSubototales(1)" size="7" onkeypress="return NumCheck2(event, this)" OnFocus="focusTest(this); return false;"  ></td>' +
        '<td><input type="text" class="" name="valor_unitario[]" id="valor_unitario[]" size="5"  value="' +
        precioOculto +
        '"    ' +
        exo +
        ' onBlur="modificarSubototales(1"></td>' +
        '<td><input type="text" class="" name="stock[]" id="stock[]" value="' +
        factorconversion +
        '" disabled="true" size="7"></td>' +
        '<td><span name="subtotal" id="subtotal' +
        cont +
        '"></span>' +
        '<input type="hidden" name="subtotalBD[]" id="subtotalBD["' +
        cont +
        '"]">' +
        '<span name="igvG" id="igvG' +
        cont +
        '" style="background-color:#9fde90bf; display:none;"></span>' +
        '<input type="hidden" name="igvBD[]" id="igvBD["' +
        cont +
        '"]"><input type="hidden" name="igvBD2[]" id="igvBD2["' +
        cont +
        '"]">' +
        '<span name="total" id="total' +
        cont +
        '" style="background-color:#9fde90bf; display:none;" ></span>' +
        '<span name="pvu_" id="pvu_' +
        cont +
        '"  style="display:none"  ></span>' +
        '<input  type="hidden" name="vvu[]" id="vvu["' +
        cont +
        '"] size="2">' +
        '<input  type="hidden" name="cicbper[]" id="cicbper["' +
        cont +
        '"] value="' +
        cicbper +
        '" >' +
        '<input  type="hidden" name="mticbperu[]" id="mticbperu["' +
        cont +
        '"]" value="' +
        mticbperuSunat +
        '">' +
        '<input type="hidden"  class="" required="true" name="factorc[]" id="factorc[]"   value="' +
        factorc +
        '">' +
        '<input type="hidden"  class="" required="true" name="cantidadreal[]" id="cantidadreal[]" >' +
        '<span name="mticbperuCalculado" id="mticbperuCalculado' +
        cont +
        '" style="background-color:#9fde90bf;display:none;"></span>' +
        "</td>" +
        "</tr>";

      var id = document.getElementsByName("idarticulo[]");
      var can = document.getElementsByName("cantidad_item_12[]");
      var cantiS = 0;

      for (var i = 0; i < id.length; i++) {
        //for (var c = 0; c < can.length; c++) {
        var idA = id[i];
        var cantiS = can[i];

        if (tipoagregacion == "0") {
          if (tipoitem != "servicios") {
            if (idA.value == idarticulo) {
              cantiS.value = parseFloat(cantiS.value) + 1; //Agrega a la cantidad en 1
              fila = "";
              cont = cont - 1;
              conNO = conNO - 1;
            } else {
              detalles = detalles;
            }
          }
        } else {
          detalles = detalles;
          $("#myModalArt").modal("hide");
        }
      } //Fin for

      detalles = detalles + 1;
      cont++;
      conNO++;
      $("#detalles").append(fila);
      //}

      document.getElementById("numero_documento").focus();
      modificarSubototales(1);
      tributocodnon();

      toastr.success("Agregado al detalle " + nombre);
      //$("#myModalArt").modal('hide');

      //para foco
      setTimeout(function () {
        document.getElementById("cantidad_item_12[]").focus();
      }, 500);

      //$('#tblarticulos').DataTable().ajax.reload();
      $("input[type=search]").focus();
    } //If de stock menor a 20
  } else {
    Swal.fire({
      title: "Error al ingresar el detalle, revisar los datos del artículo",
      icon: "warning",
    });

    cont = 0;
  }
  //if (stock<=20) { alert("El stock esta al limite, verificar!");}
}

// TIPO CAMBIO
//   $("#tipo_moneda_24").change(function () {
//     // Obtener el valor del tipo de cambio

//     if ($(this).val() === "USD") {

//       var tipoCambio = parseFloat($("#tcambio").val());
//       console.log('tipoCambio', tipoCambio);
//         $(".filas").each(function () {
//             var fila = $(this);
//             var descuento = parseFloat(fila.find("[name='descuento[]']").val());
//             var precioUnitario = parseFloat(fila.find("[name='precio_unitario[]']").val());
//             var valorUnitario = parseFloat(fila.find("[name='valor_unitario[]']").val());
//             var subtotal = parseFloat(fila.find("[name='subtotal']").text());

//             console.log(descuento);
//             console.log(precioUnitario);
//             console.log(valorUnitario);
//             console.log(subtotal);
//             // Realizar cálculos basados en el tipo de cambio
//             descuento /= tipoCambio;
//             precioUnitario /= tipoCambio;
//             valorUnitario /= tipoCambio;
//             subtotal /= tipoCambio;

//             console.log('reuslt, ', descuento);
//             console.log('reuslt, ', precioUnitario);
//             console.log('reuslt, ', valorUnitario);
//             console.log('reuslt, ', subtotal);

//             // Actualizar los campos con los nuevos valores
//             fila.find("[name='descuento[]']").val(descuento.toFixed(2));
//             fila.find("[name='precio_unitario[]']").val(precioUnitario.toFixed(2));
//             fila.find("[name='valor_unitario[]']").val(valorUnitario.toFixed(2));
//             fila.find("[name='subtotal']").text(subtotal.toFixed(2));
//         });
//     }
// });

$("body").on("keydown", function (e) {
  if (e.ctrlKey && e.shiftKey && e.which === 83) {
    alert("You pressed Ctrl + Shift + s");
    e.preventDefault();
  } else if (e.which === 112) {
    $("#myModalArt").modal("show");
  } else if (e.which === 113) {
    guardaryeditarBoleta(e);
  } else if (e.ctrlKey && e.which === 74) {
    mostrarform(true);
  }
});

function agregarArticuloxCodigo(e) {
  var codigob = $("#codigob").val();

  if (e.keyCode === 13 && !e.shiftKey) {
    $.post(
      "../ajax/boleta.php?op=listarArticulosboletaxcodigo&codigob=" +
      codigob +
      "&idempresa=" +
      $idempresa,
      function (data, status) {
        data = JSON.parse(data);
        if (data != null) {
          if (parseFloat(data.stock) == "0") {
            Swal.fire({
              title: "El stock es 0, actualizar stock!",
              icon: "warning",
            });
            $("#codigob").val("");
            quitasuge3();
          } else {
            if ($("#codigo_tributo_18_3").val() == "9997") {
              exo = "";
              op = "";
              precioOculto = data.precio_venta;
              precio_factura = 0;
              rd = "readonly";
            } else {
              op = "";
              exo = "";
              rd = "";
              precioOculto = data.precio_venta;
            }

            var contador = 1;
            var fila =
              '<tr class="filas" id="fila' +
              cont +
              '">' +
              '<td><i class="fa fa-close" onclick="eliminarDetalle(' +
              cont +
              ')" style="color:red;"  data-toggle="tooltip" title="Eliminar item"></i></td>' +
              '<td><span name="numero_orden" id="numero_orden' +
              cont +
              '" ></span>' +
              '<input type="hidden" name="numero_orden_item_29[]" id="numero_orden_item_29[]" value="' +
              conNO +
              '"  ></td>' +
              '<td><input type="hidden" name="idarticulo[]" style="font-family: times, serif; font-size:14pt; font-style:italic" value="' +
              data.idarticulo +
              '">' +
              data.nombre +
              "</td>" +
              '<td hidden><select name="codigotributo[]" class="" style="display:none;"> <option value="1000">IGV</option><option value="9997">EXO</option><option value="9998">INA</option></select>' +
              '<select name="afectacionigv[]" class="" style="display:none;"> <option value="10">10-GOO</option><option value="20">20-EOO</option><option value="30">30-FRE</option></select></td>' +
              '<td><input type="text"  class="" required="true" name="cantidad_item_12[]" id="cantidad_item_12[]"  onBlur="modificarSubototales(1)" size="6" onkeypress="return NumCheck(event, this)" value="1" ></td>' +
              '<td><input type="text"  class="" name="descuento[]" id="descuento[]"  onBlur="modificarSubototales(1)" size="2" onkeypress="return NumCheck(event, this)" >' +
              '<span name="SumDCTO" id="SumDCTO' +
              cont +
              '" style="display:none"></span> <input type="hidden"  required="true" class="" name="sumadcto[]" id="sumadcto[]" >  </td>' +
              '<td hidden><input type="hidden" name="codigo_proveedor[]" id="codigo_proveedor[]" value="' +
              data.codigo_proveedor +
              '">' +
              data.codigo_proveedor +
              "</td>" +
              '<td hidden><input type="text" name="codigo[]" id="codigo[]" value="' +
              data.codigo +
              '" class="" style="display:none;" ></td>' +
              '<td><input type="hidden" name="unidad_medida[]" id="unidad_medida[]" value="' +
              data.abre +
              '">' +
              data.abre +
              "</td>" +
              '<td><input type="text" class="" name="precio_unitario[]" id="precio_unitario[]" value="' +
              data.precio_venta +
              '" onBlur="modificarSubototales(1)" size="7" onkeypress="return NumCheck2(event, this)" OnFocus="focusTest(this); return false;"  ></td>' +
              '<td><input type="text" class="" name="valor_unitario[]" id="valor_unitario[]" size="5"  value="' +
              precioOculto +
              '"    ' +
              exo +
              ' onBlur="modificarSubototales(1"></td>' +
              '<td><input type="text" class="" name="stock[]" id="stock[]" value="' +
              data.factorconversion +
              '" disabled="true" size="7"></td>' +
              '<td><span name="subtotal" id="subtotal' +
              cont +
              '"></span>' +
              '<input type="hidden" name="subtotalBD[]" id="subtotalBD["' +
              cont +
              '"]">' +
              '<span name="igvG" id="igvG' +
              cont +
              '" style="background-color:#9fde90bf; display:none;"></span>' +
              '<input type="hidden" name="igvBD[]" id="igvBD["' +
              cont +
              '"]"><input type="hidden" name="igvBD2[]" id="igvBD2["' +
              cont +
              '"]">' +
              '<span name="total" id="total' +
              cont +
              '" style="background-color:#9fde90bf; display:none;" ></span>' +
              '<span name="pvu_" id="pvu_' +
              cont +
              '"  style="display:none"  ></span>' +
              '<input  type="hidden" name="vvu[]" id="vvu["' +
              cont +
              '"] size="2">' +
              '<input  type="hidden" name="cicbper[]" id="cicbper["' +
              cont +
              '"] value="' +
              data.cicbper +
              '" >' +
              '<input  type="hidden" name="mticbperu[]" id="mticbperu["' +
              cont +
              '"]" value="' +
              data.mticbperuSunat +
              '">' +
              '<input type="hidden"  class="" required="true" name="factorc[]" id="factorc[]"   value="' +
              data.factorc +
              '">' +
              '<input type="hidden"  class="" required="true" name="cantidadreal[]" id="cantidadreal[]" >' +
              '<span name="mticbperuCalculado" id="mticbperuCalculado' +
              cont +
              '" style="background-color:#9fde90bf;display:none;"></span>' +
              "</td>" +
              "</tr>";
            var id = document.getElementsByName("idarticulo[]");
            var can = document.getElementsByName("cantidad_item_12[]");

            for (var i = 0; i < id.length; i++) {
              var idA = id[i];
              var cantiS = can[i];
              if (data.tipoitem != "servicios") {
                if (idA.value == data.idarticulo) {
                  cantiS.value = parseFloat(cantiS.value) + 1;
                  fila = "";
                  cont = cont - 1;
                  conNO = conNO - 1;
                } else {
                  detalles = detalles;
                }
              }

              detalles = detalles;
            } //Fin while
            detalles = detalles + 1;
            cont++;
            conNO++;
            $("#detalles").append(fila);

            document.getElementById("numero_documento").focus();
            tributocodnon();
            modificarSubototales(1);
            $("#codigob").val("");

            document.getElementById("codigob").focus();
          }
        } else {
          Swal.fire({
            title: "No existe",
            icon: "warning",
          });

          $("#codigob").val("");
          document.getElementById("btnAgregarArt").style.backgroundColor =
            "#35770c";
          document.getElementById("codigob").focus();
        }
      }
    );
  }

  quitasuge3();
  quitasuge1();
  quitasuge2();
}

function modificarSubototales(tipoumm) {
  var noi = document.getElementsByName("numero_orden_item_29[]");
  var cant = document.getElementsByName("cantidad_item_12[]");
  var prec = document.getElementsByName("precio_unitario[]");
  var vuni = document.getElementsByName("valor_unitario[]");
  var st = document.getElementsByName("stock[]");
  var igv = document.getElementsByName("igvG");
  var sub = document.getElementsByName("subtotal");
  var tot = document.getElementsByName("total");
  var pvu = document.getElementsByName("pvu_");
  var mti = document.getElementsByName("mticbperuCalculado");
  var cicbper = document.getElementsByName("cicbper[]");
  var mticbperu = document.getElementsByName("mticbperu[]");
  var dcto = document.getElementsByName("descuento[]");
  var sumadcto = document.getElementsByName("sumadcto[]");
  var dcto2 = document.getElementsByName("SumDCTO");
  var factorc = document.getElementsByName("factorc[]");
  var cantiRe = document.getElementsByName("cantidadreal[]");

  for (var i = 0; i < cant.length; i++) {
    var inpNOI = noi[i];
    var inpC = cant[i];
    var inpP = prec[i];
    var inpS = sub[i];
    var inpI = igv[i];
    var inpT = tot[i];
    var inpPVU = pvu[i];
    var inStk = st[i];
    var inpVuni = vuni[i];
    var inD2 = dcto2[i];
    var dctO = dcto[i];
    var sumaDcto = sumadcto[i];
    var codIcbper = cicbper[i];
    var mticbperuNN = mticbperu[i];
    var mtiMonto = mti[i];
    var factorcc = factorc[i];
    var inpCantiR = cantiRe[i];

    inStk.value = inStk.value;
    mticbperuNN.value = mticbperuNN.value;
    //Validar cantidad no sobrepase stock actual
    if (parseFloat(inpC.value) > parseFloat(inStk.value)) {
      swal.fire({
        title: "Mensaje",
        text: "La cantidad supera al stock.",
        type: "warning",
      });
    } else {
      if (codIcbper.value == "7152") {
        //SI ES BOLSA
        if ($("#codigo_tributo_h").val() == "1000") {
          //inpPVU.value=inpP.value / 1.18; //Obtener el valor unitario
          inpPVU.value = inpP.value / ($iva / 100 + 1); //Obtener valor unitario
          document.getElementsByName("valor_unitario[]")[i].value = redondeo(
            inpPVU.value,
            5
          ); // Se asigan el valor al campo
          dctO.value = dctO.value;
          sumaDcto.value = sumaDcto.value;
          inpNOI.value = inpNOI.value;
          inpI.value = inpI.value;
          inpS.value = inpC.value * (inpP.value / 1.18); //Calculo de subtotal excluyendo el igv
          inD2.value = (inpC.value * inpP.value * dctO.value) / 100; //Calculo acumulado del descuento
          //FOMULA IGV
          //inpI.value=(inpS.value * 0.18);      //Calculo de IGV
          inpI.value = inpS.value * ($iva / 100); //Calculo de IGV
          //inpIitem = inpPVU.value * 0.18;    // Calculo de igv del valor unitario
          inpIitem = inpPVU.value * ($iva / 100); // Calculo de igv del valor unitario
          mtiMonto.value = mticbperuNN.value * inpC.value; // Calculo de ICbper * cantidad (0.10 * 20)
          inpT.value = inpS.value + inpI.value + mtiMonto.value; //calcula total Icbper subtotal + igv + Icbper
        } else {
          inpPVU.value = inpP.value; // / ($iva/100+1); //Obtener valor unitario
          document.getElementsByName("valor_unitario[]")[i].value = redondeo(
            inpPVU.value,
            5
          ); // Se asigan el valor al campo
          dctO.value = dctO.value;
          sumaDcto.value = sumaDcto.value;
          inpNOI.value = inpNOI.value;
          inpI.value = inpI.value;
          inpS.value = inpC.value * inpP.value; //Calculo de subtotal excluyendo el igv
          inD2.value = (inpC.value * inpP.value * dctO.value) / 100; //Calculo acumulado del descuento
          inpI.value = 0.0; //Calculo de IGV
          inpIitem = inpPVU.value; // * ($iva/100);    // Calculo de igv del valor unitario
          mtiMonto.value = mticbperuNN.value * inpC.value; // Calculo de ICbper * cantidad (0.10 * 20)
          inpT.value = inpS.value + inpI.value + mtiMonto.value; //calcula total Icbper subtotal + igv + Icbper
        }
      } else {
        // sino es bolsa

        if ($("#codigo_tributo_h").val() == "1000") {
          // +IGV
          //inpPVU.value=inpP.value / 1.18; //Obtener el valor unitario
          inpPVU.value = inpP.value / ($iva / 100 + 1); //Obtener el valor unitario
          document.getElementsByName("valor_unitario[]")[i].value = redondeo(
            inpPVU.value,
            5
          ); // Se asigan el valor al campo
          dctO.value = dctO.value;
          sumaDcto.value = sumaDcto.value;
          inpNOI.value = inpNOI.value;
          inpI.value = inpI.value;
          inpS.value = inpC.value * (inpP.value / ($iva / 100 + 1)); //Calculo de subtotal excluyendo el igv
          inD2.value = (inpC.value * inpP.value * dctO.value) / 100; //Calculo acumulado del descuento
          //FOMULA IGV
          inpI.value =
            inpC.value * inpP.value -
            (inpC.value * inpP.value) / ($iva / 100 + 1); //Calculo de IGV
          inpT.value =
            inpC.value * inpP.value -
            (inpC.value * inpP.value * dctO.value) / 100; //Calculo del total
          inpIitem = (inpPVU.value * $iva) / 100; // Calculo de igv del valor unitario
          mtiMonto.value = 0.0; // Calculo de ICbper * cantidad (0.10 * 20)

          if (tipoumm == "1") {
            inpCantiR.value =
              inStk.value / factorcc.value -
              (inStk.value - inpC.value) / factorcc.value;
          } else {
            inpCantiR.value = inpC.value;
          }
          //alert(inpCantiR.value);
        } else {
          // EXONERADA

          //document.getElementsByName("precio_unitario[]")[i].value = redondeo(inpVuni.value,5);
          document.getElementsByName("precio_unitario[]")[i].value = redondeo(
            inpP.value,
            5
          );
          inpNOI.value = inpNOI.value;
          inpI.value = inpI.value;
          dctO.value = dctO.value;
          sumaDcto.value = sumaDcto.value;
          inpS.value = inpC.value * inpP.value;
          inD2.value = (inpC.value * inpVuni.value * dctO.value) / 100; //Calculo acumulado del descuento
          //FOMULA IGV
          inpI.value = 0.0;
          inpT.value =
            inpC.value * inpP.value -
            (inpC.value * inpVuni.value * dctO.value) / 100; //Calculo del total;
          inpPVU.value =
            document.getElementsByName("precio_unitario[]")[i].value;
          //inpIitem = 0.00;
          inpIitem = inpP.value;
          mtiMonto.value = mticbperuNN.value * inpC.value; // Calculo de ICbper * cantidad (0.10 * 20)
          document.getElementsByName("valor_unitario[]")[i].value = redondeo(
            inpP.value,
            5
          ); // Se asigan el valor al campo
        }
      }

      document.getElementsByName("subtotal")[i].innerHTML = redondeo(
        inpS.value,
        2
      );
      document.getElementsByName("igvG")[i].innerHTML = redondeo(inpI.value, 4);
      document.getElementsByName("mticbperuCalculado")[i].innerHTML = redondeo(
        mtiMonto.value,
        2
      );
      document.getElementsByName("total")[i].innerHTML = redondeo(
        inpT.value,
        2
      );
      document.getElementsByName("pvu_")[i].innerHTML = redondeo(
        inpPVU.value,
        5
      );

      document.getElementsByName("numero_orden")[i].innerHTML = inpNOI.value;

      //Lineas abajo son para enviar el arreglo de inputs con los valor de IGV, Subtotal, y precio de venta

      //a la tala detalle_fact_art.

      document.getElementsByName("subtotalBD[]")[i].value = redondeo(
        inpS.value,
        2
      );
      document.getElementsByName("igvBD[]")[i].value = redondeo(inpI.value, 4);
      document.getElementsByName("igvBD2[]")[i].value = redondeo(inpIitem, 4);
      document.getElementsByName("vvu[]")[i].value = redondeo(inpPVU.value, 5);
      document.getElementsByName("SumDCTO")[i].innerHTML = redondeo(
        inD2.value,
        2
      );
      document.getElementsByName("sumadcto[]")[i].value = redondeo(
        inD2.value,
        2
      );
      //Fin de comentario
    } //Final de if

    if (inpP.value == 0) {
      inpP.style.backgroundColor = "#ffa69e";
      //document.getElementById("precio_unitario[]").style.backgroundColor= '#ffa69e';
    } else {
      inpP.style.backgroundColor = "#fffbfe";
      //document.getElementById("precio_unitario[]").style.backgroundColor= '#fffbfe';
    }

    if (inpC.value == 0) {
      inpC.style.backgroundColor = "#ffa69e";
      //document.getElementById("precio_unitario[]").style.backgroundColor= '#ffa69e';
    } else {
      inpC.style.backgroundColor = "#fffbfe";
      //document.getElementById("precio_unitario[]").style.backgroundColor= '#fffbfe';
    }

    if (inStk.value == 0) {
      inStk.style.backgroundColor = "#ffa69e";
      //document.getElementById("precio_unitario[]").style.backgroundColor= '#ffa69e';
    } else {
      inStk.style.backgroundColor = "#fffbfe";
      //document.getElementById("precio_unitario[]").style.backgroundColor= '#fffbfe';
    }
  } //Final de for
  calcularTotales();
}

function calcularTotales() {
  //var noi = document.getElementsByName("numero_orden_item");
  var sub = document.getElementsByName("subtotal");
  var igv = document.getElementsByName("igvG");
  var mticbperuCalculado = document.getElementsByName("mticbperuCalculado");
  var tot = document.getElementsByName("total");
  var pvu = document.getElementsByName("pvu_");
  var tdcto = document.getElementsByName("SumDCTO");
  var subtotal = 0.0;
  var total_igv = 0.0;
  var total_mticbperu = 0.0;
  var total = 0.0;
  var noi = 0;
  var pvu = 0.0;
  var tdcto = 0.0;
  for (var i = 0; i < sub.length; i++) {
    //noi+=document.getElementsByName("numero_orden_item")[i].value;
    subtotal += document.getElementsByName("subtotal")[i].value;
    total_igv += document.getElementsByName("igvG")[i].value;
    total_mticbperu +=
      document.getElementsByName("mticbperuCalculado")[i].value;
    total += document.getElementsByName("total")[i].value;
    pvu += document.getElementsByName("pvu_")[i].value;
    tdcto += document.getElementsByName("SumDCTO")[i].value;
  }
  //Para validar si el monto es >= a 700 y poder agregar los datos del cliente.
  var botonE = document.getElementById("btnAgregarCli");
  //botonE.disabled=true;
  $("#tdescuentoL").html(redondeo(tdcto, 2));
  $("#total_dcto").val(redondeo(tdcto, 2)); // a base de datos
  $("#subtotal_boleta").val(redondeo(subtotal, 2));
  $("#subtotalflotante").html(redondeo(subtotal, 2));
  $("#total_igv").val(redondeo(total_igv, 2));
  $("#igvflotante").html(redondeo(total_igv, 2));
  $("#icbper").html(redondeo(parseFloat(total_mticbperu), 2));
  $("#total_icbper").val(redondeo(total_mticbperu, 4)); //Base de datos
  $("#total").html(number_format(redondeo(total, 2), 2));
  $("#totalflotante").html(number_format(redondeo(total, 2), 2));
  $("#total_final").val(redondeo(total, 2));
  $("#pre_v_u").val(redondeo(pvu, 2));
  ipag = $("#ipagado").html();
  itot = $("#total").html();
  if (parseFloat(itot) > parseFloat(ipag)) {
    $("#ipagado").html("0.00");
    $("#saldo").html("0.00");
  } else {
    $("#saldo").html(
      number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
    );
  }
  evaluar();
}

function focusnroreferencia() {
  document.getElementById("nroreferencia").focus();
}

// function botonrapido1() {
//   $("#ipagado").html(number_format(redondeo("1", 2), 2));
//   ipag = $("#ipagado").html();
//   itot = $("#total").html();
//   if (parseFloat(itot) > parseFloat(ipag)) {
//     Swal.fire({
//       title: "Monto inferior al total",
//       icon: "warning",
//     });
//     $("#ipagado").html("0.00");
//     $("#saldo").html("0.00");
//   } else {
//     $("#saldo").html(
//       number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//     );
//     $("#saldo_final").val(
//       number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//     );
//   }
//   $("#ipagado_final").val(ipag);
//   $("#saldo_final").val(
//     number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//   );
// }

// function botonrapido2() {
//   $("#ipagado").html(number_format(redondeo("2", 2), 2));

//   ipag = $("#ipagado").html();

//   itot = $("#total").html();

//   if (parseFloat(itot) > parseFloat(ipag)) {
//     Swal.fire({
//       title: "Monto inferior al total",
//       icon: "warning",
//     });

//     $("#ipagado").html("0.00");

//     $("#saldo").html("0.00");
//   } else {
//     $("#saldo").html(
//       number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//     );
//   }

//   $("#ipagado_final").val(ipag);

//   $("#saldo_final").val(
//     number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//   );
// }

// function botonrapido5() {
//   $("#ipagado").html(number_format(redondeo("5", 2), 2));

//   ipag = $("#ipagado").html();

//   itot = $("#total").html();

//   if (parseFloat(itot) > parseFloat(ipag)) {
//     Swal.fire({
//       title: "Monto inferior al total",
//       icon: "warning",
//     });

//     $("#ipagado").html("0.00");

//     $("#saldo").html("0.00");
//   } else {
//     $("#saldo").html(
//       number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//     );
//   }

//   $("#ipagado_final").val(ipag);

//   $("#saldo_final").val(
//     number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//   );
// }

// function botonrapido10() {
//   $("#ipagado").html(number_format(redondeo("10", 2), 2));

//   ipag = $("#ipagado").html();

//   itot = $("#total").html();

//   if (parseFloat(itot) > parseFloat(ipag)) {
//     Swal.fire({
//       title: "Monto inferior al total",
//       icon: "warning",
//     });

//     $("#ipagado").html("0.00");

//     $("#saldo").html("0.00");
//   } else {
//     $("#saldo").html(
//       number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//     );
//   }

//   $("#ipagado_final").val(ipag);

//   $("#saldo_final").val(
//     number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//   );
// }

// function botonrapido20() {
//   $("#ipagado").html(number_format(redondeo("20", 2), 2));
//   ipag = $("#ipagado").html();
//   itot = $("#total").html();
//   if (parseFloat(itot) > parseFloat(ipag)) {
//     Swal.fire({
//       title: "Monto inferior al total",
//       icon: "warning",
//     });
//     $("#ipagado").html("0.00");
//     $("#saldo").html("0.00");
//   } else {
//     $("#saldo").html(
//       number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//     );
//   }
//   $("#ipagado_final").val(ipag);
//   $("#saldo_final").val(
//     number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//   );
// }

// function agregarMontoPer() {
//   // Reemplazar el h6 con un input
//   $("#ipagado").replaceWith('<input style="text-align: right;height: 2%;border: none; font-weight: bold;width: 70%;float: right;position: relative;/*! margin-right: ; */" type="text" step="0.01" min="0" id="ipagado_input" placeholder="0.00">');

//   // Obtener el input y el total
//   var ipag_input = $("#ipagado_input");
//   var itot=$("#total").html();

//   // Al cambiar el valor del input, actualizar el saldo
//   ipag_input.on("input", function() {
//       var ipag = parseFloat(ipag_input.val());
//       var saldo = ipag - parseFloat(itot);
//       $("#saldo").html(number_format(redondeo(saldo, 2), 2));
//       $("#ipagado_final").val(number_format(redondeo(ipag, 2), 2));
//       $("#saldo_final").val(number_format(redondeo(saldo, 2), 2));
//   });

//   // Enfocar el input para que sea más fácil ingresar el monto
//   ipag_input.focus();

//   $(window).on("beforeunload", function() {
//     $("#ipagado_input").replaceWith('<h6 id="ipagado" class="d-inline">0.00</h6>');
//   });
// }

// function botonrapido50() {
//   $("#ipagado").html(number_format(redondeo("50", 2), 2));
//   ipag = $("#ipagado").html();
//   itot = $("#total").html();
//   if (parseFloat(itot) > parseFloat(ipag)) {
//     Swal.fire({
//       title: "Monto inferior al total",
//       icon: "warning",
//     });
//     $("#ipagado").html("0.00");
//     $("#saldo").html("0.00");
//   } else {
//     $("#saldo").html(
//       number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//     );
//   }
//   $("#ipagado_final").val(ipag);
//   $("#saldo_final").val(
//     number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//   );
// }

// function botonrapido100() {
//   $("#ipagado").html(number_format(redondeo("100", 2), 2));
//   ipag = $("#ipagado").html();
//   itot = $("#total").html();
//   if (parseFloat(itot) > parseFloat(ipag)) {
//     Swal.fire({
//       title: "Monto inferior al total",
//       icon: "warning",
//     });

//     $("#ipagado").html("0.00");
//     $("#saldo").html("0.00");
//   } else {
//     $("#saldo").html(
//       number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//     );
//   }
//   $("#ipagado_final").val(ipag);
//   $("#saldo_final").val(
//     number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//   );
// }

// function botonrapido200() {
//   $("#ipagado").html(number_format(redondeo("200", 2), 2));
//   ipag = $("#ipagado").html();
//   itot = $("#total").html();
//   if (parseFloat(itot) > parseFloat(ipag)) {
//     Swal.fire({
//       title: "Monto inferior al total",
//       icon: "warning",
//     });

//     $("#ipagado").html("0.00");
//     $("#saldo").html("0.00");
//   } else {
//     $("#saldo").html(
//       number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//     );
//   }
//   $("#ipagado_final").val(ipag);
//   $("#saldo_final").val(
//     number_format(redondeo(parseFloat(ipag) - parseFloat(itot), 2), 2)
//   );
// }

function evaluar() {
  if (detalles > 0) {
    $("#btnGuardar").show();
    mayor700();
  } else {
    $("#btnGuardar").hide();
    cont = 0;
  }
}

function evaluar2() {
  if (detalles > 0) {
    $("#btnGuardar").hide();
    cont = 0;
  }
}

function eliminarDetalle(indice) {
  $("#fila" + indice).remove();

  calcularTotales();

  detalles = detalles - 1;

  conNO = conNO - 1;

  actualizanorden();

  evaluar();
}

function mayor700() {
  //=============================================
  var total = $("#total_final").val();
  if (total >= 700) {
    Swal.fire({
      title: "Agregar DNI o C.E. del cliente",
      text: "Es obligatorio agregar un documento nacional de identidad. Tienes el monto MAYOR a 700 soles es normado por sunat",
      icon: "warning",
    });
    $("#btnGuardar").prop("disabled", true);
  } else {
    $("#mensaje700").hide();
    $("#btnGuardar").prop("disabled", false);
  }
}

function redondeo(numero, decimales) {
  var flotante = parseFloat(numero);

  var resultado =
    Math.round(flotante * Math.pow(10, decimales)) / Math.pow(10, decimales);

  return resultado;
}

function decimalAdjust(type, value, exp) {
  // Si el exp no está definido o es cero...

  if (typeof exp === "undefined" || +exp === 0) {
    return Math[type](value);
  }

  value = +value;

  exp = +exp;

  // Si el valor no es un número o el exp no es un entero...

  if (isNaN(value) || !(typeof exp === "number" && exp % 1 === 0)) {
    return NaN;
  }

  // Shift

  value = value.toString().split("e");

  value = Math[type](+(value[0] + "e" + (value[1] ? +value[1] - exp : -exp)));

  // Shift back

  value = value.toString().split("e");

  return +(value[0] + "e" + (value[1] ? +value[1] + exp : exp));

  // Decimal round

  if (!Math.round10) {
    Math.round10 = function (value, exp) {
      return decimalAdjust("round", value, exp);
    };
  }

  // Decimal floor

  if (!Math.floor10) {
    Math.floor10 = function (value, exp) {
      return decimalAdjust("floor", value, exp);
    };
  }

  // Decimal ceil

  if (!Math.ceil10) {
    Math.ceil10 = function (value, exp) {
      return decimalAdjust("ceil", value, exp);
    };
  }
}

function round(value, exp) {
  if (typeof exp === "undefined" || +exp === 0) return Math.round(value);

  value = +value;

  exp = +exp;

  if (isNaN(value) || !(typeof exp === "number" && exp % 1 === 0)) return NaN;

  // Shift

  value = value.toString().split("e");

  value = Math.round(+(value[0] + "e" + (value[1] ? +value[1] + exp : exp)));

  // Shift back

  value = value.toString().split("e");

  return +(value[0] + "e" + (value[1] ? +value[1] - exp : -exp));
}

//Función para el formato de los montos

function number_format(amount, decimals) {
  amount += ""; // por si pasan un numero en vez de un string

  amount = parseFloat(amount.replace(/[^0-9\.]/g, "")); // elimino cualquier cosa que no sea numero o punto

  decimals = decimals || 0; // por si la variable no fue fue pasada

  // si no es un numero o es igual a cero retorno el mismo cero

  if (isNaN(amount) || amount === 0) return parseFloat(0).toFixed(decimals);

  // si es mayor o menor que cero retorno el valor formateado como numero

  amount = "" + amount.toFixed(decimals);

  var amount_parts = amount.split("."),
    regexp = /(\d+)(\d{3})/;

  while (regexp.test(amount_parts[0]))
    amount_parts[0] = amount_parts[0].replace(regexp, "$1" + "," + "$2");

  return amount_parts.join(".");
}

function focusI() {
  var tipo = $("#tipo_doc_ide option:selected").val();

  if (tipo == "0") {
    $.post(
      "../ajax/persona.php?op=mostrarClienteVarios",
      function (data, status) {
        data = JSON.parse(data);
        $("#idcliente").val(data.idpersona);
        $("#numero_documento").val(data.numero_documento);
        $("#razon_social").val(data.razon_social);
        $("#domicilio_fiscal").val(data.domicilio_fiscal);
      }
    );

    //document.getElementById('numero_documento').focus();
  }

  if (tipo == "1") {
    //$('#idcliente').val("");
    $("#numero_documento").val("");
    $("#razon_social").val("");
    $("#domicilio_fiscal").val("");
    document.getElementById("numero_documento").focus();
    document.getElementById("numero_documento").maxLength = 20;
  }

  if (tipo == "4") {
    $("#numero_documento").val("");
    $("#razon_social").val("");
    $("#domicilio_fiscal").val("");
    document.getElementById("numero_documento").focus();
    document.getElementById("numero_documento").maxLength = 15;
  }

  if (tipo == "7") {
    $("#numero_documento").val("");
    $("#razon_social").val("");
    $("#domicilio_fiscal").val("");
    document.getElementById("numero_documento").focus();
    document.getElementById("numero_documento").maxLength = 15;
  }

  if (tipo == "A") {
    $("#numero_documento").val("");

    $("#razon_social").val("");

    $("#domicilio_fiscal").val("");

    document.getElementById("numero_documento").focus();

    document.getElementById("numero_documento").maxLength = 15;
  }

  if (tipo == "6") {
    $("#numero_documento").val("");

    $("#razon_social").val("");

    $("#domicilio_fiscal").val("");

    document.getElementById("numero_documento").focus();

    document.getElementById("numero_documento").maxLength = 11;
  }
}

function agregardni() {
  var dni = $("#numero_documento").val();
  $.post(
    "../ajax/boleta.php?op=listarClientesboletaxDoc&doc=" + dni,
    function (data, status) {
      data = JSON.parse(data);
      if (data != null) {
        $("#idcliente").val(data.idpersona);
        $("#razon_social").val(data.nombres + " " + data.apellidos);
        $("#domicilio_fiscal").val(data.domicilio_fiscal);
        document.getElementById("btnAgregarArt").style.backgroundColor =
          "#367fa9";
        document.getElementById("mensaje700").style.display = "none";
        document.getElementById("btnAgregarArt").focus();
      } else {
        var url = "../ajax/consulta_reniec.php";
        $.ajax({
          type: "POST",
          url: url,
          data: "dni=" + dni,
          success: function (datos_dni) {
            var datos = eval(datos_dni);
            if (datos != null) {
              $("#idcliente").val("N");
              $("#razon_social").val(
                datos[1] + " " + datos[2] + " " + datos[3]
              );
              $("#domicilio_fiscal").val("");
              document.getElementById("domicilio_fiscal").focus();
            } else {
              $("#idcliente").val("N");
              $("#razon_social").val("");
              document.getElementById("razon_social").placeholder =
                "No Registrado";
              $("#domicilio_fiscal").val("");
              document.getElementById("domicilio_fiscal").placeholder =
                "No Registrado";
              document.getElementById("btnAgregarArt").style.backgroundColor =
                "#35770c";
              document.getElementById("razon_social").style.Color = "#35770c";
              document.getElementById("razon_social").focus();
            }
          },
        });
      }
    }
  );
}

function agregarClientexDoc(e) {
  var dni = $("#numero_documento").val();

  if (e.keyCode === 13 && !e.shiftKey) {
    $("#razon_social").val("");
    $("#domicilio_fiscal").val("");

    $.post(
      "../ajax/boleta.php?op=listarClientesboletaxDoc&doc=" + dni,
      function (data, status) {
        data = JSON.parse(data);
        if (data != null) {
          $("#idcliente").val(data.idpersona);
          $("#razon_social").val(data.nombres);
          $("#domicilio_fiscal").val(data.domicilio_fiscal);
          document.getElementById("btnAgregarArt").style.backgroundColor =
            "#367fa9";
          document.getElementById("mensaje700").style.display = "none";
          document.getElementById("btnAgregarArt").focus();
          $("#suggestions").fadeOut();
          $("#suggestions2").fadeOut();
          $("#suggestions3").fadeOut();
        } else if ($("#tipo_doc_ide").val() == "1") {
          // SI ES DNI
          $("#razon_social").val("");
          $("#domicilio_fiscal").val("");
          var dni = $("#numero_documento").val();
          //var url = '../ajax/consulta_reniec.php';
          $.post(
            "../ajax/boleta.php?op=consultaDniSunat&nrodni=" + dni,
            function (data, status) {
              data = JSON.parse(data);
              if (data != null) {
                $("#idcliente").val("N");
                // $("#numero_documento3").val(data.numeroDocumento);
                $("#razon_social").val(data.nombre);
              } else {
                alert(data);
                document.getElementById("razon_social").focus();
                $("#idcliente").val("N");
              }
            }
          );
          $("#suggestions").fadeOut();
          $("#suggestions2").fadeOut();
          $("#suggestions3").fadeOut();
        } else if ($("#tipo_doc_ide").val() == "6") {
          // SI ES RUC
          $("#razon_social").val("");
          $("#domicilio_fiscal").val("");
          var dni = $("#numero_documento").val();
          $.post(
            "../ajax/factura.php?op=listarClientesfacturaxDoc&doc=" + dni,
            function (data, status) {
              data = JSON.parse(data);
              if (data != null) {
                $("#idcliente").val(data.idpersona);
                $("#razon_social").val(data.razon_social);
                $("#domicilio_fiscal").val(data.domicilio_fiscal);
              } else {
                $("#idcliente").val("");
                $("#razon_social").val("No registrado");
                $("#domicilio_fiscal").val("No registrado");
                Swal.fire({
                  title: "Cliente no registrado",
                  icon: "warning",
                });

                $("#ModalNcliente").modal("show");
                $("#nruc").val($("#numero_documento").val());
              }
            }
          );
          $("#suggestions").fadeOut();
          $("#suggestions2").fadeOut();
          $("#suggestions3").fadeOut();
        } else {
          $("#idcliente").val("N");
          $("#razon_social").val("");
          document.getElementById("razon_social").placeholder = "No Registrado";
          $("#domicilio_fiscal").val("");
          document.getElementById("domicilio_fiscal").placeholder =
            "No Registrado";
          document.getElementById("btnAgregarArt").style.backgroundColor =
            "#35770c";
          document.getElementById("razon_social").style.Color = "#35770c";
          document.getElementById("razon_social").focus();
        }
      }
    );
  }
}

function agregarClientexDocCha() {
  var dni = $("#numero_documento").val();

  $("#razon_social").val("");
  $("#domicilio_fiscal").val("");

  $.post(
    "../ajax/boleta.php?op=listarClientesboletaxDoc&doc=" + dni,
    function (data, status) {
      data = JSON.parse(data);
      if (data != null) {
        $("#idcliente").val(data.idpersona);
        $("#razon_social").val(data.nombres);
        $("#domicilio_fiscal").val(data.domicilio_fiscal);
        document.getElementById("btnAgregarArt").style.backgroundColor =
          "#367fa9";
        document.getElementById("mensaje700").style.display = "none";
        document.getElementById("btnAgregarArt").focus();
        $("#suggestions").fadeOut();
        $("#suggestions2").fadeOut();
        $("#suggestions3").fadeOut();
      } else if ($("#tipo_doc_ide").val() == "1") {
        // SI ES DNI
        $("#razon_social").val("");
        $("#domicilio_fiscal").val("");
        var dni = $("#numero_documento").val();
        console.log(dni);
        //var url = '../ajax/consulta_reniec.php';
        $.post(
          "../ajax/boleta.php?op=consultaDniSunat&nrodni=" + dni,
          function (data, status) {
            data = JSON.parse(data);
            console.log(data);
            //swal.fire("Error","Nro DNI debe contener 8 digitos", "error");
            if (data != null) {
              $("#idcliente").val("N");

              console.log(data);
              //$("#numero_documento3").val(data.numeroDocumento);
              $("#razon_social").val(data.nombre);
              console.log(data.nombre);
              //swal.fire("Error","Datos no encontrados", "error");
            } else {
              swal.fire("Error", "Datos no encontrados", "error");
              //alert(data);
              console.log(data);
              document.getElementById("razon_social").focus();
              $("#idcliente").val("N");
            }
          }
        );
        $("#suggestions").fadeOut();
        $("#suggestions2").fadeOut();
        $("#suggestions3").fadeOut();
      } else if ($("#tipo_doc_ide").val() == "6") {
        // SI ES RUC
        $("#razon_social").val("");
        $("#domicilio_fiscal").val("");
        var dni = $("#numero_documento").val();
        $.post(
          "../ajax/factura.php?op=listarClientesfacturaxDoc&doc=" + dni,
          function (data, status) {
            data = JSON.parse(data);
            if (data != null) {
              $("#idcliente").val(data.idpersona);
              $("#razon_social").val(data.razon_social);
              $("#domicilio_fiscal").val(data.domicilio_fiscal);
            } else {
              $("#idcliente").val("");
              $("#razon_social").val("No registrado");
              $("#domicilio_fiscal").val("No registrado");
              Swal.fire({
                title: "Cliente no registrado",
                icon: "warning",
              });

              $("#ModalNcliente").modal("show");
              $("#nruc").val($("#numero_documento").val());
            }
          }
        );
        $("#suggestions").fadeOut();
        $("#suggestions2").fadeOut();
        $("#suggestions3").fadeOut();
      } else {
        $("#idcliente").val("N");
        $("#razon_social").val("");
        document.getElementById("razon_social").placeholder = "No Registrado";
        $("#domicilio_fiscal").val("");
        document.getElementById("domicilio_fiscal").placeholder =
          "No Registrado";
        document.getElementById("btnAgregarArt").style.backgroundColor =
          "#35770c";
        document.getElementById("razon_social").style.Color = "#35770c";
        document.getElementById("razon_social").focus();
      }
    }
  );
}

function agregarClientexDoc2() {
  var dni = $("#numero_documento").val();
  $.post(
    "../ajax/boleta.php?op=listarClientesboletaxDoc&doc=" + dni,
    function (data, status) {
      data = JSON.parse(data);
      if (data != null) {
        $("#idcliente").val(data.idpersona);
        $("#razon_social").val(data.nombres);
        $("#domicilio_fiscal").val(data.domicilio_fiscal);
        document.getElementById("btnAgregarArt").style.backgroundColor =
          "#367fa9";
        document.getElementById("mensaje700").style.display = "none";
        document.getElementById("btnAgregarArt").focus();
      } else if ($("#tipo_doc_ide").val() == "1") {
        //  var dni=$("#numero_documento").val();
        //   var url = '../ajax/consulta_reniec.php';
        //   $.ajax({
        //   type:'POST',
        //   url:url,
        //   data:'dni='+dni,
        //   success: function(datos_dni){
        // var datos = eval(datos_dni);
        // if (datos!=null) {
        $("#idcliente").val("N");
        document.getElementById("razon_social").focus();
        //   $('#razon_social').val(datos[1]+" "+datos[2]+" "+datos[3]);
        //   $("#domicilio_fiscal").val("");
        //   document.getElementById('domicilio_fiscal').focus();
        //      }
        // }});
      } else if ($("#tipo_doc_ide").val() == "6") {
        // SI ES RUC
        var dni = $("#numero_documento").val();
        $.post(
          "../ajax/factura.php?op=listarClientesfacturaxDoc&doc=" + dni,
          function (data, status) {
            data = JSON.parse(data);
            if (data != null) {
              $("#idcliente").val(data.idpersona);
              $("#razon_social").val(data.razon_social);
              $("#domicilio_fiscal").val(data.domicilio_fiscal);
            } else {
              $("#idcliente").val("");
              $("#razon_social").val("Registrar");
              $("#domicilio_fiscal").val("Registrar");
              Swal.fire({
                title: "Cliente no registrado",
                icon: "warning",
              });

              //$("#ModalNcliente").modal('show');
              //$("#nruc").val($("#numero_documento2").val());
            }
          }
        );
      } else {
        $("#idcliente").val("N");
        $("#razon_social").val("");
        document.getElementById("razon_social").placeholder = "No Registrado";
        $("#domicilio_fiscal").val("");
        document.getElementById("domicilio_fiscal").placeholder =
          "No Registrado";
        document.getElementById("btnAgregarArt").style.backgroundColor =
          "#35770c";
        document.getElementById("razon_social").style.Color = "#35770c";
        document.getElementById("razon_social").focus();
      }
    }
  );
}

//Función para anular registros

function enviarcorreo(idboleta) {
  var mmcliente = $("#correo").val();
  Swal.fire({
    title: "Ingresa el correo electrónico",
    input: "email",
    inputValue: mmcliente,
    showCancelButton: true,
    confirmButtonText: "Enviar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.value) {
      $.post(
        "../ajax/boleta.php?op=enviarcorreo&idbol=" +
        idboleta +
        "&ema=" +
        result.value,
        function (e) {
          Swal.fire({
            title: e,
            icon: "success",
          });
          tabla.ajax.reload();
        }
      );
    }
  });
}

function enviarwhatsapp(idboleta) {
  var telefonoCliente = $("#telefono").val();
  Swal.fire({
    title: "Ingresa el número de teléfono",
    input: "tel",
    inputValue: telefonoCliente,
    showCancelButton: true,
    confirmButtonText: "Enviar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.value) {
      // Construir URL de WhatsApp
      const whatsappUrl = `https://api.whatsapp.com/send?phone=${result.value}&text=¡Hola! Tu boleta con ID ${idboleta} ha sido enviada por correo electrónico.`;

      // Abrir URL de WhatsApp en una nueva ventana
      window.open(whatsappUrl, "_blank");

      Swal.fire({
        title: `Mensaje enviado a ${result.value}`,
        icon: "success",
      });

      tabla.ajax.reload();
    }
  });
}

function mayus(e) {
  e.value = e.value.toUpperCase();
}

function focusDir(e) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("domicilio_fiscal").focus();
  }
}

function agregarArt(e) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("btnAgregarArt").focus();
  }
}

function focusAgrArt(e) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("btnAgregarArt").focus();

    document.getElementById("btnAgregarArt").style.backgroundColor = "#35770c";
  }
}

function focusTdoc() {
  document.getElementById("tipo_doc_ide").focus();
}

function stopRKey(evt) {
  var evt = evt ? evt : event ? event : null;

  var node = evt.target ? evt.target : evt.srcElement ? evt.srcElement : null;

  if (evt.keyCode == 13 && node.type == "text") {
    return false;
  }
}

//PARA ELIMINAR ENTER

document.onkeypress = stopRKey;

function capturarhora() {
  var f = new Date();

  cad = f.getHours() + ":" + f.getMinutes() + ":" + f.getSeconds();

  $("#hora").val(cad);
}

function actualizanorden() {
  var total = document.getElementsByName("numero_orden_item_29[]");

  for (var i = 0; i < total.length; i++) {
    //var contNO=total[i];

    var contNO = total[i];

    contNO.value = i + 1;

    //Lineas abajo son para enviar el arreglo de inputs con los valor de IGV, Subtotal, y precio de venta

    document.getElementsByName("numero_orden")[i].innerHTML = contNO.value;

    document.getElementsByName("numero_orden_item_29[]")[i].value =
      contNO.value;

    //Fin de comentario
  } //Final de for
}

$(document).ready(function () {
  var table = $("#tbllistado").DataTable();
  $("#tbllistado tbody").on("click", "tr", function () {
    if ($(this).hasClass("selected")) {
      $(this).removeClass("selected");
    } else {
      table.$("tr.selected").removeClass("selected");
      table.$("tr").removeClass("selected");
      $(this).addClass("selected");
    }
  });

  $("#tbllistado").parents("tr").css("background-color", "green");

  $("#button").click(function () {
    table.row(".selected").remove().draw(false);
  });
});

//Foco para el input cantidad

function focusDescdet(e, field) {
  if (e.keyCode === 13 && !e.shiftKey) {
    document.getElementById("cantidad_item_12[]").focus();
  }
}

function redirecionescritorio() {
  window.location.replace("escritorio");
}

//Función para dar de baja registros

function downFtp(idboleta) {
  Swal.fire({
    title: "¿Está seguro de descargar los archivos?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, descargar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.post(
        "../ajax/boleta.php?op=downFtp",
        { idboleta: idboleta },
        function (e) {
          data = JSON.parse(e);
          Swal.fire({
            icon: "success",
            html:
              '<a href="' +
              data.cabext +
              '" download="' +
              data.cab +
              '">ARCHIVO JSON: ' +
              data.cab +
              "</a>",
            confirmButtonText: "Aceptar",
          });
        }
      );
    }
  });
}

//Función ListarClientes

function listarClientes() {
  tablaCLiente = $("#tblaclientes")
    .dataTable({
      aProcessing: false, //Activamos el procesamiento del datatables

      "bRetrieve ": false, //Paginación y filtrado realizados por el servidor

      dom: "Bfrtip", //Definimos los elementos del control de tabla

      buttons: [],

      ajax: {
        url: "../ajax/factura.php?op=listarClientesfactura",

        type: "get",

        dataType: "json",

        error: function (e) {
          console.log(e.responseText);
        },
      },

      bDestroy: true,

      iDisplayLength: 8, //Paginación

      order: [[0, "desc"]], //Ordenar (columna,orden)
    })
    .DataTable();

  $("#tblaclientes").DataTable().ajax.reload();
}

function guardaryeditarcliente(e) {
  e.preventDefault(); //No se activará la acción predeterminada del evento

  var formData = new FormData($("#formularioncliente")[0]);

  $.ajax({
    url: "../ajax/persona.php?op=guardaryeditarNclienteBoleta",

    type: "POST",

    data: formData,

    contentType: false,

    processData: false,

    success: function (datos) {
      bootbox.alert(datos);

      tabla.ajax.reload();

      limpiarcliente();

      agregarClientexRucNuevo();
    },
  });

  $("#ModalNcliente").modal("hide");

  $("#myModalCli").modal("hide");
}

function agregarClientexRucNuevo() {
  $.post(
    "../ajax/factura.php?op=listarClientesfacturaxDocNuevos",
    function (data, status) {
      data = JSON.parse(data);

      if (data != null) {
        $("#numero_documento").val(data.numero_documento);

        $("#idcliente").val(data.idpersona);

        $("#razon_social").val(data.razon_social);

        $("#domicilio_fiscal").val(data.domicilio_fiscal);

        $("#tipo_documento_cliente").val(data.tipo_documento);

        document.getElementById("btnAgregarArt").style.backgroundColor =
          "#367fa9";

        document.getElementById("btnAgregarArt").focus();
      } else {
        $("#idcliente").val("");

        $("#razon_social").val("No existe");

        $("#domicilio_fiscal").val("No existe");

        $("#tipo_documento_cliente").val("");

        document.getElementById("btnAgregarArt").style.backgroundColor =
          "#35770c";

        document.getElementById("btnAgregarCli").focus();
      }
    }
  );
}

function limpiarcliente() {
  //NUEVO CLIENTE

  $("#numero_documento3").val("");

  $("#razon_social3").val("");

  $("#domicilio_fiscal3").val("");

  $("#iddepartamento").val("");

  $("#idciudad").val("");

  $("#iddistrito").val("");

  $("#telefono1").val("");

  $("#email").val("");

  $("#nruc").val("");

  $("#numero_documento3").val("");

  //=========================
}

function limitestockf(event, field) {
  // Aquí va el código de la función
}

function refrescartabla() {
  // Push.create("Hello world!",{
  // body: "This is example of Push.js Tutorial",
  // icon: '../files/push/alerta.png',
  // timeout: 2000,
  // onClick: function () {
  //     window.focus();
  //     this.close();
  // }
  // });

  tabla.ajax.reload();
  listarArticulos();
}

init();
//console.log(init());

// $(document).ready(function() {

//     $('#numero_documento').on('keyup', function() {

//         var key = $(this).val();

//         $('#suggestions2').fadeOut();

//         $('#suggestions3').fadeOut();

//         var dataString = 'key='+key;

//     $.ajax({

//             type: "POST",

//             url: "../ajax/persona.php?op=buscarclienteRuc",

//             data: dataString,

//             success: function(data) {

//                 //Escribimos las sugerencias que nos manda la consulta

//                 $('#suggestions').fadeIn().html(data);

//                 //Al hacer click en algua de las sugerencias

//                 $('.suggest-element').on('click', function(){

//                         //Obtenemos la id unica de la sugerencia pulsada

//                         var id = $(this).attr('id');

//                         //Editamos el valor del input con data de la sugerencia pulsada

//                         $('#numero_documento').val($('#'+id).attr('ndocumento'));

//                         $('#razon_social').val($('#'+id).attr('ncomercial'));

//                         $('#domicilio_fiscal').val($('#'+id).attr('domicilio'));

//                         $("#idpersona").val(id);

//                         //$("#resultado").html("<p align='center'><img src='../public/images/spinner.gif' /></p>");

//                         //Hacemos desaparecer el resto de sugerencias

//                         $('#suggestions').fadeOut();

//                         //alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));

//                         return false;

//                 });

//             }

//         });

//     });

// });

$(document).ready(function () {
  $("#razon_social").on("keyup", function () {
    $("#suggestions").fadeOut();
    $("#suggestions3").fadeOut();
    var key = $(this).val();
    var dataString = "key=" + key;
    $.ajax({
      type: "POST",
      url: "../ajax/persona.php?op=buscarclienteDomicilio",
      data: dataString,
      success: function (data) {
        //Escribimos las sugerencias que nos manda la consulta
        $("#suggestions2").fadeIn().html(data);
        // autocomplete(document.getElementById(".suggest-element"),  data);
        //Al hacer click en algua de las sugerencias
        $(".suggest-element").on("click", function () {
          //Obtenemos la id unica de la sugerencia pulsada
          var id = $(this).attr("id");
          //Editamos el valor del input con data de la sugerencia pulsada
          $("#numero_documento").val($("#" + id).attr("ndocumento"));
          $("#razon_social").val($("#" + id).attr("ncomercial"));
          $("#domicilio_fiscal").val($("#" + id).attr("domicilio"));
          $("#idpersona").val(id);
          //$("#resultado").html("<p align='center'><img src='../public/images/spinner.gif' /></p>");
          //Hacemos desaparecer el resto de sugerencias
          $("#suggestions2").fadeOut();
          //alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
          return false;
        });
      },
    });
  });
}); // Ready function

// $(document).ready(function() {

//     $('#codigob').on('keyup', function() {

//         $('#suggestions').fadeOut();

//         $('#suggestions3').fadeOut();

//         var key = $(this).val();

//         var dataString = 'key='+key;

//     $.ajax({

//             type: "POST",

//             url: "../ajax/articulo.php?op=buscararticulo",

//             data: dataString,

//             success: function(data) {

//                 //Escribimos las sugerencias que nos manda la consulta

//                 $('#suggestions3').fadeIn().html(data);

//                // autocomplete(document.getElementById(".suggest-element"),  data);

//                 //Al hacer click en algua de las sugerencias

//                 $('.suggest-element').on('click', function(){

//                         //Obtenemos la id unica de la sugerencia pulsada

//                         var id = $(this).attr('id');

//                         //Editamos el valor del input con data de la sugerencia pulsada

// agregarDetalle(id,

//     '',

//     '',

//     $('#'+id).attr('codigo'),

//     $('#'+id).attr('nombre'),

//     $('#'+id).attr('precio_venta'),

//     $('#'+id).attr('stock'),

//     $('#'+id).attr('unidad_medida'),

//     $('#'+id).attr('precio_unitario'),

//     $('#'+id).attr('cicbper'),

//     $('#'+id).attr('mticbperu'));

//                         $('#codigob').val('');

//                         $('#codigob').focus();

//                         //$("#resultado").html("<p align='center'><img src='../public/images/spinner.gif' /></p>");

//                         //Hacemos desaparecer el resto de sugerencias

//                         $('#suggestions3').fadeOut();

//                         //alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));

//                         return false;

//                 });

//             }

//         });

//     });

// }); // Ready function

function quitasuge2() {
  if ($("#razon_social").val() == "") {
    $("#suggestions2").fadeOut();
  }

  $("#suggestions2").fadeOut();
}

function quitasuge3() {
  // if ($('#codigob').val()=="")

  // {

  //  $('#suggestions3').fadeOut();

  // }

  $("#suggestions3").fadeOut();
}

function quitasuge1() {
  if ($("#numero_documento").val() == "") {
    $("#suggestions").fadeOut();
  }

  $("#suggestions").fadeOut();
}

function tributocodnon() {
  $("#codigo_tributo_h").val($("#codigo_tributo_18_3").val());
  $("#nombre_tributo_h").val($("#codigo_tributo_18_3 option:selected").text());
  //$(".filas").remove();
  tribD = $("#codigo_tributo_h").val();
  var id = document.getElementsByName("idarticulo[]");
  var codtrib = document.getElementsByName("codigotributo[]");
  var nombretrib = document.getElementsByName("afectacionigv[]");
  var cantiRe = document.getElementsByName("cantidadreal[]");

  if (tribD == "1000") {
    for (var i = 0; i < id.length; i++) {
      var codtrib2 = codtrib[i];
      var nombretrib2 = nombretrib[i];
      codtrib2.value = "1000";
      nombretrib2.value = "10";
      //cantiRe[i].value=cantidadreal;
    } //PARA VALIDACION SI YA ESTA INGRESADO EL ITEM
  } else if (tribD == "9997") {
    for (var i = 0; i < id.length; i++) {
      var codtrib2 = codtrib[i];
      var nombretrib2 = nombretrib[i];
      codtrib2.value = "9997";
      nombretrib2.value = "20";
    } //PARA VALIDACION SI YA ESTA INGRESADO EL ITEM
  } else {
    for (var i = 0; i < id.length; i++) {
      var codtrib2 = codtrib[i];
      var nombretrib2 = nombretrib[i];
      codtrib2.value = "9998";
      nombretrib2.value = "30";
    } //PARA VALIDACION SI YA ESTA INGRESADO EL ITEM
  }

  $("#subtotal").html("0");
  $("#subtotal_factura").val("");
  $("#igv_").html("0");
  $("#total_igv").val("");
  $("#total").html("0");
  $("#total_final").val("");
  $("#pre_v_u").val("");
  $("#ipagado").html("0");
  $("#saldo").html("0");
  $("#ipagado_final").val("");
  $("#saldo_saldo").val("");

  modificarSubototales(0);
}

function agregarDetalleItem(
  idarticulo,

  familia,
  codigo_proveedor,
  codigo,
  nombre,

  precio_venta,
  stock,
  abre,

  precio_unitario,
  cicbper,
  mticbperu,
  factorconversion,
  factorc
) {
  $.post(
    "../ajax/boleta.php?op=selectunidadmedida&idar=" + idarticulo,
    function (r) {
      $("#unidadm").html(r);

      //$('#unidadm').selectpicker('refresh');
    }
  );

  var cantidad = 0;

  if (idarticulo != "") {
    if (familia == "SERVICIO") {
      $("#icantidad").val("1");

      document.getElementById("iicbper2").disabled = true;

      document.getElementById("cicbper").disabled = true;

      document.getElementById("iimpicbper").disabled = true;
    }

    $("#nombrearti").val(nombre);

    $("#iiditem").val(idarticulo);

    $("#icodigo").val(codigo);

    $("#nombre").val(nombre);

    $("#familia").val(familia);

    $("#codigo_proveedor").val(codigo_proveedor);

    $("#stoc").val(stock);

    $("#factorcitem").val(factorc);

    $("#iumedida").val(abre);

    //$("#unidadm").val(abre);

    $("#ipunitario").val(precio_venta);

    $cantiitem = $("#icantidad").val();

    $valoruni = precio_venta / 1.18;

    $("#ivunitario").val($valoruni);

    $("#iicbper2").val(mticbperu);

    $("#cicbper").val(cicbper);

    $("#iimpicbper").val($cantiitem * $("#iicbper2").val());

    $("#myModalArt").modal("hide");

    //$("#myModalserv").modal('hide');

    $("#icantidad").val("1");

    $("#cantidadrealitem").val(factorconversion);

    $("#icantidad").focus();

    calculartotalitem();
  }

  $("#itemno").val("0");

  iit = $("#itemno").val();

  listarArticulos();
}

function cambioUm() {
  //$("#iumedida").val( $("#unidadm").val());

  $("#umedidaoculto").val($("#unidadm").val());
}

function calculartotalitem() {
  calcuigv();

  $cantiitem = $("#icantidad").val();

  $precioitem = $("#ipunitario").val();

  $valoru = $("#ivunitario").val();

  $igvitem = $("#iigvresu").val();

  $mtoicbper = $("#iicbper2").val();

  $impicbper = $cantiitem * $mtoicbper; //Impuesto ICBPER

  $ImpoTotalItem =
    parseFloat($valoru) * parseFloat($cantiitem) + parseFloat($igvitem);

  $("#iimportetotalitem").val($ImpoTotalItem.toFixed(2));

  $("#iimpicbper").val($impicbper.toFixed(2)); //Impuesto ICBPER
}

function calcuigv() {
  $seligv = $("input[name='iigv']:checked").val();

  $valoru = $("#ivunitario").val();

  $cvigv = 0;

  $cantiitem = 0;

  $precioitem = 0;

  $cantiitem = $("#icantidad").val();

  //$precioitem=$("#ipunitario").val();

  //$totaluni=$precioitem * $cantiitem;

  if ($seligv == "grav") {
    $cvigv = $valoru * $cantiitem * ($iva / 100);

    $("#iigvresu").val($cvigv);
  } else if ($seltipo == "exo") {
    $cvigv = 0;

    $("#iigvresu").val($cvigv);
  } else {
    $cvigv = 0;

    $("#iigvresu").val($cvigv);
  }

  $ImpoTotalItem =
    parseFloat($valoru) * parseFloat($cantiitem) + parseFloat($cvigv);

  $("#iimportetotalitem").val($ImpoTotalItem.toFixed(2));
}

function calvaloruniitem() {
  $precioitem = $("#ipunitario").val();

  $valoruItem = parseFloat($precioitem / 1.18);

  $("#ivunitario").val($valoruItem);

  $valoru = $("#ivunitario").val();

  calcuigv();

  $igvitem = $("#iigvresu").val();

  $ImpoTotalItem =
    parseFloat($valoru) * parseFloat($cantiitem) + parseFloat($igvitem);

  $("#iimportetotalitem").val($ImpoTotalItem.toFixed(2));
}

function agregarItemdetalle() {
  $idarticulo = $("#iiditem").val();
  $familia = $("#familia").val();
  $codigo_proveedor = $("#codigo_proveedor").val();
  $codigo = $("#icodigo").val();
  $nombre = $("#nombre").val();
  $detalleItem = $("#idescripcion").val();
  $precio_boleta = $("#ipunitario").val();
  $stock = $("#stoc").val();
  $unidad_medida = $("#iumedida").val();
  $precio_unitario = $("#idescripcion").val();
  $cicbper = $("#cicbper").val();
  $mticbperuSunat = $("#iicbper2").val();
  $cantidad = $("#icantidad").val();
  $cantiRea = $("#cantidadrealitem").val();
  $factorCi = $("#factorcitem").val();
  if ($unidad_medida != $("#umedidaoculto").val()) {
    $cantidadreal = $cantidad;
    //alert($cantidadreal);
    $unidad_medida = $("#unidadm").val();
  }
  var cantidad = 0;
  if ($idarticulo != "") {
    if (parseFloat(stock) == "0") {
      Swal.fire({
        title: "El stock es 0, actualizar stock!",
        icon: "warning",
      });
      quitasuge3();
    } else {
      if ($("#nombre_tributo_4_p").val() == "9997") {
        exo = "";
        op = "";
        precioOculto = $precio_boleta;
        $precio_boleta = "0";
        rd = "readonly";
      } else {
        op = "";
        exo = "";
        rd = "";
        precioOculto = $precio_boleta;
      }

      var fila =
        '<tr class="filas" id="fila' +
        cont +
        '">' +
        '<td><i class="fa fa-close" onclick="eliminarDetalle(' +
        cont +
        ')" style="color:red;"  data-toggle="tooltip" title="Eliminar item"></i></td>' +
        '<td><span name="numero_orden" id="numero_orden' +
        cont +
        '" ></span>' +
        '<input type="hidden" name="numero_orden_item_29[]" id="numero_orden_item_29[]" value="' +
        conNO +
        '"  ></td>' +
        '<td><input type="hidden" name="idarticulo[]" style="font-family: times, serif; font-size:14pt; font-style:italic" value="' +
        $idarticulo +
        '">' +
        $nombre +
        "</td>" +
        '<td><textarea class="" name="descdet[]" id="descdet[]" rows="1" cols="70" onkeyup="mayus(this)" onkeypress="return focusDescdet(event, this)">' +
        $detalleItem +
        "</textarea>" +
        '<select name="codigotributo[]" class="" style="display:none;"> <option value="1000">IGV</option><option value="9997">EXO</option><option value="9998">INA</option></select>' +
        '<select name="afectacionigv[]" class="" style="display:none;"> <option value="10">10-GOO</option><option value="20">20-EOO</option><option value="30">30-FREE</option></select></td>' +
        '<td><input type="text"  class="" required="true" name="cantidad_item_12[]" id="cantidad_item_12[]"  size="6" onkeypress="return NumCheck(event, this)"  value="' +
        $cantidadreal +
        '" >' +
        '<input type="hidden"  name="cantidad2[]" id="cantidad2[]"  readonly value="' +
        $cantidadreal +
        '"  size="6" onkeypress="return NumCheck(event, this)" ></td>' +
        '<td><input type="text"  class="" name="descuento[]" id="descuento[]"   size="2" onkeypress="return NumCheck(event, this)" >' +
        '<span name="SumDCTO" id="SumDCTO' +
        cont +
        '" style="display:none"></span> <input type="hidden"  required="true" class="" name="sumadcto[]" id="sumadcto[]" >  </td>' +
        '<td><input type="hidden" name="codigo_proveedor[]" id="codigo_proveedor[]" value="' +
        $codigo_proveedor +
        '">' +
        $codigo_proveedor +
        "</td>" +
        '<td><input type="text" name="codigo[]" id="codigo[]" value="' +
        $codigo +
        '" class="" style="display:none;" ></td>' +
        '<td><input type="hidden" name="unidad_medida[]" id="unidad_medida[]" value="' +
        $unidad_medida +
        '">' +
        $unidad_medida +
        "</td>" +
        '<td><input type="text" class="" name="precio_unitario[]" id="precio_unitario[]" value="' +
        $precio_boleta +
        '"  size="7" onkeypress="return NumCheck2(event, this)" OnFocus="focusTest(this); return false;"  ></td>' +
        '<td><input type="text" class="" name="valor_unitario[]" id="valor_unitario[]" size="5"  value="' +
        precioOculto +
        '"    ' +
        exo +
        " ></td>" +
        '<td><input type="text" class="" name="stock[]" id="stock[]" value="' +
        $stock +
        '" disabled="true" size="7"></td>' +
        '<td><span name="subtotal" id="subtotal' +
        cont +
        '"></span>' +
        '<input type="hidden" name="subtotalBD[]" id="subtotalBD["' +
        cont +
        '"]">' +
        '<span name="igvG" id="igvG' +
        cont +
        '" style="background-color:#9fde90bf; display:none;"></span>' +
        '<input type="hidden" name="igvBD[]" id="igvBD["' +
        cont +
        '"]"><input type="hidden" name="igvBD2[]" id="igvBD2["' +
        cont +
        '"]">' +
        '<span name="total" id="total' +
        cont +
        '" style="background-color:#9fde90bf; display:none;" ></span>' +
        '<span name="pvu_" id="pvu_' +
        cont +
        '"  style="display:none"  ></span>' +
        '<input  type="hidden" name="vvu[]" id="vvu["' +
        cont +
        '"] size="2">' +
        '<input  type="hidden" name="cicbper[]" id="cicbper["' +
        cont +
        '"] value="' +
        $cicbper +
        '" >' +
        '<input  type="hidden" name="mticbperu[]" id="mticbperu["' +
        cont +
        '"]" value="' +
        $mticbperuSunat +
        '">' +
        '<input type="hidden"  class="" required="true" name="factorc[]" id="factorc[]" >' +
        '<input type="hidden"  class="" required="true" name="cantidadreal[]" id="cantidadreal[]" >' +
        '<span name="mticbperuCalculado" id="mticbperuCalculado' +
        cont +
        '" style="background-color:#9fde90bf;display:none;"></span>' +
        "</td>" +
        "</tr>";

      var id = document.getElementsByName("idarticulo[]");
      var ntrib = document.getElementsByName("nombre_tributo_4[]");
      var can = document.getElementsByName("cantidad_item_12[]");
      var cantiS = 0;
      for (var i = 0; i < id.length; i++) {
        var idA = id[i];
        var cantiS = can[i];
        if (idA.value == $idarticulo) {
          //alert("Ya esta ingresado el articulo!");
          cantiS.value = parseFloat(cantiS.value) + parseFloat($cantidad); //Agrega a la cantidad en 1
          fila = "";
          cont = cont - 1;
          conNO = conNO - 1;
        } else {
          detalles = detalles;
        }
      } //PARA VALIDACION SI YA ESTA INGRESADO EL ITEM
      detalles = detalles + 1;
      cont++;
      conNO++;
      $("#detalles").append(fila);
      $("#myModalnuevoitem").modal("hide");
      tributocodnon();
      modificarSubototales(0);
      limpiarItem();
    } //IF si tiene menos d e 20
  } else {
    alert("Error al ingresar el detalle, revisar los datos del artículo");
    cont = 0;
  }
  //del stock si es 0
}

function cargarbien() {
  $("#myModalArt").modal("show");
  $("#itemno").val("1");
  iit = $("#itemno").val();
  listarArticulos();
}

function cargarservicio() {
  $("#myModalserv").modal("show");
}

function limpiarItem() {
  $("#icantidad").val("");
  $("#iumedida").val("");
  $("#nombrearti").val("");
  $("#stoc").val("");
  $("#ipunitario").val("");
  $("#ivunitario").val("");
  $("#icodigo").val("");
  $("#idescripcion").val("");
  $("#iicbper2").val("");
  $("#iimpicbper").val("");
  $("#iigvresu").val("");
  $("#iimportetotalitem").val("");
  $("#idescuento").val("");
}

//Funcion para enviararchivo xml a SUNAT
// Funcion para enviar archivo xml a SUNAT
function mostrarxml(idboleta) {
  // Hacer una solicitud POST con jQuery
  $.post("../ajax/boleta.php?op=mostrarxml", { idboleta }, function (response) {
    const data = JSON.parse(response);
    if (data.rutafirma) {
      // Si se encuentra una ruta de archivo, obtener el archivo y formatear el XML
      const rutaArchivo = data.rutafirma;
      $.get(rutaArchivo, function (xml) {
        const serializer = new XMLSerializer();
        const formattedXml = formatXml(serializer.serializeToString(xml));

        // Mostrar el XML formateado en un modal y agregar un enlace de descarga
        $("#modalxml").contents().find("body").get(0).textContent =
          formattedXml;
        $("#modalPreviewXml").modal("show");
        $("#bajaxml").attr("href", rutaArchivo);
      });
    } else {
      // Si no se encuentra una ruta de archivo, mostrar un mensaje de error
      Swal.fire({
        icon: "error",
        text: data.cabextxml,
        timer: 1000,
      });
    }
  });
}

// Funcion para formatear el XML
function formatXml(xml) {
  let formatted = "";
  const reg = /(>)(<)(\/*)/g;
  xml = xml.replace(reg, "$1\r\n$2$3");
  let pad = 0;
  xml.split("\r\n").forEach(function (node) {
    let indent = 0;
    if (node.match(/.+<\/\w[^>]*>$/)) {
      indent = 0;
    } else if (node.match(/^<\/\w/)) {
      if (pad != 0) {
        pad -= 1;
      }
    } else if (node.match(/^<\w[^>]*[^\/]>.*$/)) {
      indent = 1;
    } else {
      indent = 0;
    }
    let padding = "";
    for (let i = 0; i < pad; i++) {
      padding += "  ";
    }
    formatted += `${padding}${node}\r\n`;
    pad += indent;
  });
  return formatted;
}

//Funcion para enviararchivo xml a SUNAT
function mostrarrpta(idboleta) {
  $.post(
    "../ajax/boleta.php?op=mostrarrpta",
    { idboleta: idboleta },
    function (e) {
      data = JSON.parse(e);
      var rptaS = data.rutaxmlr;
      $("#modalcdr").attr("src", rptaS);
      $("#modalPreviewCdr").modal("show");
      $("#bajacrd").attr("href", rptaS);
    }
  );
}

//Funcion para enviararchivo xml a SUNAT

function generarxml(idboleta) {
  Swal.fire({
    title: "¿Está seguro de generar el archivo XML?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, estoy seguro!",
  }).then((result) => {
    if (result.value) {
      $.post(
        "../ajax/boleta.php?op=generarxml",
        { idboleta: idboleta },
        function (e) {
          data = JSON.parse(e);
          Swal.fire({
            title: "Archivo XML generado",
            html:
              'Se ha generado el archivo XML: <a href="' +
              data.cabextxml +
              '" download="' +
              data.cabxml +
              '">' +
              data.cabxml +
              "</a> Haz clic en el nombre para descargarlo.",
            icon: "success",
          });
          tabla.ajax.reload();
        }
      );
      refrescartabla();
    }
  });
  tabla.ajax.reload();
  refrescartabla();
}

//Función para enviar respuestas por correo

function enviarxmlSUNAT(idboleta) {
  swal
    .fire({
      title: "¿Está Seguro de enviar archivo firmado a SUNAT?",
      text: "",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, estoy seguro",
      cancelButtonText: "No, cancelar",
    })
    .then((result) => {
      if (result.value) {
        $.post(
          "../ajax/boleta.php?op=enviarxmlSUNAT",
          { idboleta: idboleta },
          function (e) {
            swal.fire("Enviado!", e, "success");
            tabla.ajax.reload();
          }
        );
        refrescartabla();
      }
    });
}

function refrescartabla2() {
  listarArticulosItem();
}

$.post("../ajax/factura.php?op=datostemporizadopr", function (data) {
  data = JSON.parse(data);

  if (data != null) {
    $("#estado").val(data.estado);
  }
});

if ($("#estado").val() == "1") {
  $(document).ready(function () {
    setInterval(function () {
      $.ajax({
        type: "POST",

        url: "../ajax/ventas.php?op=listarValidarComprobantesSiempre",
      });
    }, 10000);

    tabla.ajax.reload();
  });
}

function cambiarlistado() {
  listarArticulos();
}

function cambiarlistadoum() {
  $("#itemno").val("1");

  listarArticulos();
}

function cambiarlistadoum2() {
  $("#itemno").val("0");

  listarArticulos();
}

function generarcodigonarti() {
  //alert("asdasdas");

  var caracteres1 = $("#nombrenarticulo").val();

  var codale = "";

  codale = caracteres1.substring(-3, 3);

  var caracteres2 = "ABCDEFGHJKMNPQRTUVWXYZ012346789";

  codale2 = "";

  for (i = 0; i < 3; i++) {
    var autocodigo = "";

    codale2 += caracteres2.charAt(
      Math.floor(Math.random() * caracteres2.length)
    );
  }

  $("#codigonarticulonarticulo").val(codale + codale2);
}

//Función para enviar respuestas por correo
function consultarcdr(idboleta) {
  bootbox.confirm(
    "Se consultará si existe el comprobante en SUNAT.",
    function (result) {
      if (result) {
        $.post(
          "../ajax/boleta.php?op=consultarcdr",
          { idboleta: idboleta },
          function (e) {
            //data2=JSON.parse(e);
            bootbox.alert(e);
            tabla.ajax.reload();
          }
        );
        refrescartabla();
      }
    }
  );
  tabla.ajax.reload();
  refrescartabla();
}

function tipodecambiosunat() {
  if ($("#tipo_moneda_24").val() == "USD") {
    fechatcf = $("#fecha_emision_01").val();
    $.post(
      "../ajax/boleta.php?op=tcambiog&feccf=" + fechatcf,
      function (data, status) {
        data = JSON.parse(data);
        $("#tcambio").val(data.venta);

        // TIPO CAMBIO
        //  var tipoCambio = parseFloat($("#tcambio").val());
        //  console.log('tipoCambio', tipoCambio);
        //    $(".filas").each(function () {
        //        var fila = $(this);
        //        var descuento = parseFloat(fila.find("[name='descuento[]']").val());
        //        var precioUnitario = parseFloat(fila.find("[name='precio_unitario[]']").val());
        //        var valorUnitario = parseFloat(fila.find("[name='valor_unitario[]']").val());
        //        var subtotal = parseFloat(fila.find("[name='subtotal']").text());

        //        console.log(descuento);
        //        console.log(precioUnitario);
        //        console.log(valorUnitario);
        //        console.log(subtotal);
        //        // Realizar cálculos basados en el tipo de cambio
        //        descuento /= tipoCambio || 0;
        //        precioUnitario /= tipoCambio || 0;
        //        valorUnitario /= tipoCambio || 0;
        //        subtotal /= tipoCambio || 0;

        //        console.log('reuslt, ', descuento);
        //        console.log('reuslt, ', precioUnitario);
        //        console.log('reuslt, ', valorUnitario);
        //        console.log('reuslt, ', subtotal);

        //        // Actualizar los campos con los nuevos valores
        //        fila.find("[name='descuento[]']").val(descuento.toFixed(2));
        //        fila.find("[name='precio_unitario[]']").val(precioUnitario.toFixed(2));
        //        fila.find("[name='valor_unitario[]']").val(valorUnitario.toFixed(2));
        //        fila.find("[name='subtotal']").text(subtotal.toFixed(2));
        //    });
      }
    );
  } else {
    $("#tcambio").val("0");
  }
}

function activartarjetadc() {
  var tarjadc = document.getElementById("tarjetadc").checked;
  if (tarjadc == true) {
    $("#tadc").val("1");
  } else {
    $("#tadc").val("0");
  }
}

function activartransferencia() {
  var tran_f = document.getElementById("transferencia").checked;
  if (tran_f == true) {
    $("#trans").val("1");
  } else {
    $("#trans").val("0");
  }
}

function cambiartarjetadc(idboleta) {
  swal
    .fire({
      title: "Desea modificar pago con tarjeta",
      input: "select",
      inputOptions: {
        1: "SI",
        0: "NO",
      },
      showCancelButton: true,
      inputValidator: function (value) {
        return new Promise(function (resolve, reject) {
          if (value) {
            resolve();
          } else {
            reject("Debe seleccionar una opción");
          }
        });
      },
    })
    .then(function (result) {
      if (result) {
        $.post(
          "../ajax/boleta.php?op=cambiartarjetadc_&opcion=" + result,
          { idboleta: idboleta },
          function (e) {
            swal.fire("Información", e, "info");
            tabla.ajax.reload();
          }
        );
      }
    });
}

function montotarjetadc(idboleta) {
  Swal.fire({
    title: "Desea modificar monto de pago con tarjeta",
    input: "number",
    inputValue: "0",
    showCancelButton: true,
    confirmButtonText: "Aceptar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.value) {
      $.post(
        "../ajax/boleta.php?op=montotarjetadc_&monto=" + result.value,
        {
          idboleta: idboleta,
        },
        function (e) {
          Swal.fire({
            title: e,
            icon: "success",
          });
          tabla.ajax.reload();
        }
      );
    }
  });
}

function cambiartransferencia(idboleta) {
  Swal.fire({
    title: "Desea modificar pago con transferencia",
    input: "select",
    inputOptions: {
      1: "SI",
      0: "NO",
    },
    inputValue: "1",
    showCancelButton: true,
    confirmButtonText: "Aceptar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.value) {
      $.post(
        "../ajax/boleta.php?op=cambiartransferencia&opcion=" + result.value,
        {
          idboleta: idboleta,
        },
        function (e) {
          Swal.fire({
            title: e,
            icon: "success",
          });
          tabla.ajax.reload();
        }
      );
    }
  });
}

function montotransferencia(idboleta) {
  swal
    .fire({
      title: "Desea modificar monto de transferencia",
      input: "number",
      showCancelButton: true,
      confirmButtonText: "OK",
      inputValue: "0",
      inputValidator: (value) => {
        return new Promise((resolve) => {
          if (value) {
            resolve();
          } else {
            resolve("Debe ingresar un valor");
          }
        });
      },
    })
    .then((result) => {
      if (result.value) {
        $.post(
          `../ajax/boleta.php?op=montotransferencia&monto=${result.value}`,
          { idboleta: idboleta },
          function (e) {
            swal.fire({
              text: e,
              type: "success",
            });
            tabla.ajax.reload();
          }
        );
      }
    });
}

function focusnroreferencia() {
  opttp = $("#tipopago").val();
  var x = document.getElementById("tipopagodiv");
  if (opttp == "Credito") {
    toFi = $("#total_final").val();
    cuo = $("#ccuotas").find("option:selected").text();
    $("#montocuota").val(toFi / cuo);
    document.getElementById("nroreferenciaf").focus();
  } else {
    $("#montocuota").val("0");
    $("#ccuotas").val("0");
  }
}

function contadocredito() {
  opttp = $("#tipopago").val();
  var x = document.getElementById("tipopagodiv");
  if (opttp == "Credito") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
    $("#montocuota").val("0");
    $("#ccuotas").val("0");
  }
}

function duplicarb(idboleta) {
  var f = new Date();
  cad = f.getHours() + ":" + f.getMinutes() + ":" + f.getSeconds();

  bootbox.confirm({
    message: "Desea suplicar la boleta.",
    buttons: {
      confirm: {
        label: "SI",
        className: "btn-success",
      },
      cancel: {
        label: "NO",
        className: "btn-danger",
      },
    },
    callback: function (result) {
      if (result) {
        $.post(
          "../ajax/boleta.php?op=duplicar",
          { idboleta: idboleta },
          function (e) {
            bootbox.alert(e);
            tabla.ajax.reload();
          }
        );
      }
    },
  });
}

// jQuery.fn.extend({
//     printElem: function() {
//         var cloned = this.clone();
//     var printSection = $('#printSection');
//     if (printSection.length == 0) {
//         printSection = $('<div id="printSection"></div>')
//         $('body').append(printSection);
//     }
//     printSection.append(cloned);
//     var toggleBody = $('body *:visible');
//     toggleBody.hide();
//     $('#printSection, #printSection *').show();
//     window.print();
//     printSection.remove();
//     toggleBody.show();
//     }
// });

// $(document).ready(function(){
//     $(document).on('click', '#btnPrint', function(){
//     $('.printMe').printElem();
//   });
// });

function contadocredito() {
  opttp = $("#tipopago").val();
  var x = document.getElementById("tipopagodiv");
  if (opttp == "Credito") {
    x.style.display = "block";
    $("#ccuotas").val("1");
    document.getElementById("fechavenc").readOnly = false;
    focusnroreferencia();
  } else {
    x.style.display = "none";
    $("#montocuota").val("0");
    $("#ccuotas").val("0");
    //focusnroreferencia();
    document.getElementById("divmontocuotas").innerHTML = "";
    document.getElementById("divfechaspago").innerHTML = "";
    document.getElementById("ccuotas").readOnly = false;

    var now = new Date();
    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);
    var today = now.getFullYear() + "-" + month + "-" + day;
    $("#fechavenc").val(today);
    document.getElementById("fechavenc").readOnly = true;
  }
}

function focusnroreferencia() {
  countmes = 30;
  ncuota = $("#ccuotas").val();
  totalcompCu = $("#total_final").val();
  document.getElementById("totalcomp").innerHTML =
    "TOTAL COMPROBANTE " + totalcompCu;
  $("#modalcuotas").modal("show");
  toFi = $("#total_final").val();
  for (var i = 1; i <= ncuota; i++) {
    countmes = countmes + 30;
    fechahoy = new Date();
    dia = fechahoy.getDate();
    mes = ("0" + fechahoy.getMonth() + 1).slice(-2); // +1 porque los meses empiezan en 0
    anio = fechahoy.getFullYear();
    fechahoy.setDate(fechahoy.getDate() + countmes);
    today =
      fechahoy.getFullYear() +
      "-" +
      ("0" + fechahoy.getMonth()).slice(-2) +
      "-" +
      ("0" + fechahoy.getDate()).slice(-2);

    var input = document.createElement("input");
    input.setAttribute("type", "text");
    input.setAttribute("id", "montocuotacre[]");
    input.setAttribute("name", "montocuotacre[]");
    input.setAttribute("size", "4");
    input.setAttribute("value", (toFi / ncuota).toFixed(2));
    var parent = document.getElementById("divmontocuotas");
    parent.appendChild(document.createElement("br"));
    parent.appendChild(input);

    var input = document.createElement("input");
    input.setAttribute("type", "hidden");
    input.setAttribute("id", "ncuotahiden[]");
    input.setAttribute("name", "ncuotahiden[]");
    input.setAttribute("value", i);
    var parent = document.getElementById("divmontocuotas");
    parent.appendChild(input);
    parent.appendChild(document.createElement("br"));

    var date = document.createElement("input");
    date.setAttribute("type", "date");
    date.setAttribute("id", "fechapago[]");
    date.setAttribute("name", "fechapago[]");
    input.setAttribute("size", "4");
    date.setAttribute("value", today);
    var parent = document.getElementById("divfechaspago");
    parent.appendChild(document.createElement("br"));
    parent.appendChild(date);
    parent.appendChild(document.createElement("br"));

    document.getElementById("ccuotas").readOnly = true;
  }
}

function borrarcuotas() {
  $("#ccuotas").val("1");
  document.getElementById("divmontocuotas").innerHTML = "";
  document.getElementById("divfechaspago").innerHTML = "";
  document.getElementById("ccuotas").readOnly = false;
}

//llenar documentos
function cargarTiposDocIde() {
  var baseURL = window.location.protocol + '//' + window.location.host;

  // Verificar si pathname contiene '/vistas/' y eliminarlo.
  var path = window.location.pathname;
  if (path.includes("/vistas/")) {
    path = path.replace("/vistas/", "/");
  }

  var ajaxURL = new URL("ajax/catalogo6.php?action=listar2", baseURL + path);

  $.ajax({
    url: ajaxURL.href,
    type: "GET",
    dataType: "json",
    success: function (data) {
      llenarSelect(data.aaData);
    },
    error: function (xhr, status, error) {
      console.error("Error al cargar los tipos de documento de identidad");
      console.error(error);
    },
  });
}


function llenarSelect(data) {
  var select = $("#tipo_doc_ide");
  select.empty();
  select.append(
    $("<option>", {
      value: "",
      text: "Tipo de documento",
    })
  );
  $.each(data, function (index, value) {
    if (value.estado === "1") {
      select.append(
        $("<option>", {
          value: value.codigo,
          text: value.documento,
        })
      );
    }
  });
}

$(document).ready(function () {
  cargarTiposDocIde();
});
